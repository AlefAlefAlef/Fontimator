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

	public function woocommerce_json_search_limit() {
		return 200;
	}

	public function add_span_to_decimals_in_price( $return ) {
		$return = preg_replace( '/(\d+)\.(\d{0,2})(\d+)/', '$1.<span data-wtf="class-fontimator-woocommerce.php:38" class="decimal-digits">$2</span>', $return );
		return $return;
	}

	public function limit_decimals_to_two( $formatted_price, $price, $decimals, $decimal_separator, $thousand_separator ) {
		return number_format( $price, 2, $decimal_separator, $thousand_separator );
	}

	public function custom_billing_fields( $fields = array() ) {
		unset( $fields['billing_address_1'] );
		unset( $fields['billing_address_2'] );
		unset( $fields['billing_state'] );
		unset( $fields['billing_postcode'] );
		unset( $fields['billing_city'] );
		return $fields;
	}

	public function remove_expires_head_from_email( $columns ) {
		unset( $columns['download-expires'] );
		return $columns;
	}

	public function subscription_message_store_url_to_product( $url ) {
		// The Query
		$post = get_page_by_path( 'membership', OBJECT, 'product' );
		return get_permalink( $post );
	}

	public function woocommerce_get_price_html_from_text( $span ) {
		return str_replace( ' </span>', '</span>', $span );
	}

	public function woocommerce_variable_price_html( $price, $product ) {
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

	static function reset_downloads_for_customer( $customer_id = false ) {

		if ( ! $customer_id ) {
			$customer_id = get_current_user_id();
		}

		$customer_orders = wc_get_orders(
			array(
				'status' => 'completed',
				// 'type' => 'shop_order',
				'limit'  => -1,
				'customer_id' => $customer_id,
				'return' => 'ids',
			)
		);

		$count = 0;
		foreach ( $customer_orders as $order_id ) {
			$data_store = WC_Data_Store::load( 'customer-download' );
			$data_store->delete_by_order_id( $order_id );
			wc_downloadable_product_permissions( $order_id, true );
			$count++;
		}

		return $count;
	}


	static function add_variations_from_url() {
		if ( $_REQUEST['ftm-add-to-cart'] ) {
			$variation_ids = explode( ',', $_REQUEST['ftm-add-to-cart'] );
			self::add_variations_to_cart( $variation_ids );

			wc_add_notice( __( 'We added all the right products to your cart. Now all that is left is to checkout!', 'fontimator' ), 'success' );
			exit;
		}
	}
	static function add_variations_to_cart( $variation_ids ) {

		$was_added_to_cart = false;
		foreach ( (array) $variation_ids as $variation_id ) {
			$was_added_to_cart = WC()->cart->add_to_cart( wp_get_post_parent_id( $variation_id ), 1, $variation_id, wc_get_product_variation_attributes( $variation_id ) );
		}

		wp_safe_redirect( add_query_arg( 'ftm-automatic-cart', '1', wc_get_cart_url() ) );
	}

}
