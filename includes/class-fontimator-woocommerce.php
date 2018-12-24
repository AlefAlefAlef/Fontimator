<?php

/**
 * The woocommerce-specific functionality of the plugin.
 *
 * @link       https://alefalefalef.co.il
 * @since      2.0.0
 *
 * @package    Fontimator
 * @subpackage Fontimator/admin
 */

/**
 * The woocommerce-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Fontimator
 * @subpackage Fontimator/admin
 * @author     Reuven Karasik <rubik@karasik.org>
 */
class Fontimator_WooCommerce {
	public function _return_true() {
		return true;
	}
	public function _return_false() {
		return false;
	}
	public function _return_empty() {
		return '';
	}

	public function add_span_to_decimals_in_price( $return ) {
		$return = preg_replace( '/(\d+)\.(\d+)/', '$1.<span data-wtf="class-fontimator-woocommerce.php:38" class="decimal-digits">$2</span>', $return );
		return $return;
	}

	function custom_billing_fields( $fields = array() ) {
		unset( $fields['billing_address_1'] );
		unset( $fields['billing_address_2'] );
		unset( $fields['billing_state'] );
		unset( $fields['billing_postcode'] );
		unset( $fields['billing_city'] );
		return $fields;
	}

	function remove_expires_head_from_email( $columns ) {
		unset( $columns['download-expires'] );
		return $columns;
	}

	function subscription_message_store_url_to_product( $url ) {
		// The Query
		$post = get_page_by_path( 'membership', OBJECT, 'product' );
		return get_permalink( $post );
	}

	function woocommerce_get_price_html_from_text( $span ) {
		return str_replace( ' </span>', '</span>', $span );
	}

	function woocommerce_variable_price_html( $price, $product ) {
		$prefix = __( 'From: ', 'fontimator' );

		$wcv_reg_min_price = $product->get_variation_regular_price( 'min', true );
		$wcv_min_sale_price    = $product->get_variation_sale_price( 'min', true );
		$wcv_max_price = $product->get_variation_price( 'max', true );
		$wcv_min_price = $product->get_variation_price( 'min', true );

		$wcv_price = ( $wcv_min_sale_price == $wcv_reg_min_price ) ?
			wc_price( $wcv_reg_min_price ) :
			'<del>' . wc_price( $wcv_reg_min_price ) . '</del>' . '<ins>' . wc_price( $wcv_min_sale_price ) . '</ins>';

		return ( $wcv_min_price == $wcv_max_price ) ?
			$wcv_price :
			sprintf( '%s%s', $prefix, $wcv_price );
	}

}
