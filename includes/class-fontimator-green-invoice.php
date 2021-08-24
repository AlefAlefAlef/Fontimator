<?php

/**
 * The Green Invoice integration
 *
 * @link       https://alefalefalef.co.il
 * @since      2.4.39
 *
 * @package    Fontimator
 */
class Fontimator_GreenInvoice {

  protected static $download_invoice_endpoint = 'download-invoice';
  protected static $greeninvoice_global_endpoint = true;

  public function add_download_invoice_rewrite_endpoint() {
    add_rewrite_endpoint( self::$download_invoice_endpoint , EP_ROOT );
  }

  /**
   * Returns a link to a special page for downloading a Green Invoice,
   * or false if there are no invoice IDs in the database
   *
   * @param int $order_id
   * @return false
   */
  public static function get_download_link( $order_id ) {
    if ( ! ( $order = wc_get_order( $order_id ) ) ) {
      return false;
    }
    
    if ( ! $invoice_id = $order->get_meta( 'invoice_uid', true ) ) {
      if ( $old_link = $order->get_meta( 'invoice_link', true ) ) {
        return $old_link;
      }

      return false;
    }
    
    return sprintf( '%s/%d', home_url( self::$download_invoice_endpoint ), $order_id );
  }

  public function maybe_redirect_download_invoice() {
    global $wp_query;
 
    // if this is not a request for download link - return
    if ( ! isset( $wp_query->query_vars[ self::$download_invoice_endpoint ] ) ) {
      return;
    }

    if ( ! isset( $GLOBALS['tb_wc_green_invoice'] ) ) {
      wp_die(__( 'Error in Green Invoice plugin load.', 'fontimator' )); // Maybe our changes to the plugin were reverted?
    }
    
    global $tb_wc_green_invoice;
    
    if ( ! ( $order_id = intval( get_query_var( self::$download_invoice_endpoint ) ) ) || ! ( $order = wc_get_order( $order_id ) ) ) {
      wp_die( sprintf( __( 'Bad order ID: %d', 'fontimator' ), $order_id ) );
    }
    
    if ( $order->get_customer_id() != get_current_user_id() && ! current_user_can( 'administrator' ) ) {
      wp_die( __( 'You don\'t have permissions to download this invoice.', 'fontimator' ) );
    }
    
    if ( ! $invoice_id = $order->get_meta( 'invoice_uid', true ) ) {
      wp_die( sprintf( __( 'Order %d has no invoices', 'fontimator' ), $order_id ) );
    }
    
    $url = sprintf( '%s/%s', $tb_wc_green_invoice->decide_about_greeninvoice_url(), $invoice_id );
    
    if ( ! self::$greeninvoice_global_endpoint ) {
      $url .= '/download/links';
    }

    $options = array(
      CURLOPT_URL => $url,
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HEADER => true,
      CURLOPT_HTTPHEADER => array("Content-Type: application/json",
      "Authorization: Bearer " . $tb_wc_green_invoice->get_jwt_token()));

    $ch = curl_init();
    curl_setopt_array($ch, $options);
    $result = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $result = substr($result, $header_size);
    curl_close($ch);
        
    $response = json_decode($result, true);
  
    if ($httpcode == 201 || $httpcode == 200) {
      $language = $tb_wc_green_invoice->get_language(); // TODO: get customer ID

      if ( self::$greeninvoice_global_endpoint ) {
        $response = $response["url"];
      }

      if ($language == "he" && isset( $response["he"] )) {
        $download_link = $response["he"];
      } else {
        if ($language == "en" && isset( $response["en"] )) {
          $download_link = $response["en"];
        } else {
          $download_link = $response["origin"];
        }
      }

      ob_end_clean();
      header( sprintf( 'Location: %s', $download_link ) );
      die();
      
    } else {
      $tb_wc_green_invoice->log('Fontimator API call ERROR: ' . print_r($result, true));
      if ( $green_invoice_temp_link = $order->get_meta( 'invoice_link', true ) ) {
        header( sprintf( 'Location: %s', $green_invoice_temp_link ) );
      } else {
        wp_die( __( 'Error in Green Invoice download link API call', 'fontimator' ) ); // Maybe there's a problem with the API key, or sandbox mode is on?
      }
    }
  }

}
