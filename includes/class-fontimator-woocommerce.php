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
		$return = preg_replace( '/(\d+)\.(\d{0,2})(\d*)/', '$1.<span data-wtf="class-fontimator-woocommerce.php:add_span_to_decimals_in_price" class="decimal-digits">$2</span>', $return );
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
		//unset( $fields['billing_country'] );
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

	/**
	 * Adds variations with specified IDs to the cart and applies correct discounts from session
	 *
	 * @param array<int> $variation_ids
	 * @return void
	 */
	static function add_variations_to_cart( $variation_ids ) {
		$was_added_to_cart = false;
		foreach ( (array) $variation_ids as $variation_id ) {
			$was_added_to_cart = WC()->cart->add_to_cart( wp_get_post_parent_id( $variation_id ), 1, $variation_id, wc_get_product_variation_attributes( $variation_id ) );
		}

		wp_safe_redirect( add_query_arg( 'ftm-automatic-cart', '1', wc_get_cart_url() ) );
	}

	/**
	 * Hooked on woocommerce_before_calculate_totals, checks the session for preset discounts
	 *
	 * @param WC_Cart $cart_object
	 * @return void
	 */
	public static function apply_session_discounts( $cart_object ) {
		session_start();
		foreach ( $cart_object->cart_contents as $value ) {
			$session_key = 'ftm_variation_discount_' . $value['variation_id'];
			if ( isset( $_SESSION[$session_key] ) ) {
				$discount = $_SESSION[$session_key];
				if ( time() <= $discount['expiry'] ) {
					if ( is_numeric( $discount['discount_percent'] ) ) {
						$custom_price = $value['data']->get_price() * ( 1 - $discount['discount_percent'] / 100 );
						$value['data']->set_price($custom_price);
						$value['data']->set_sale_price($custom_price);
						$value['data']->add_meta_data( 'discount_reason', $discount['discount_reason'], true);
					}
				} else {
					unset( $_SESSION[$session_key] );
				}
			}
		}
	}

	/**
	 * Handle lost password form.
	 * Copied from WC_Form_Handler::process_lost_password (woocommerce/includes/class-wc-form-handler.php:980)
	 */
	public static function process_lost_password() {
		if ( isset( $_POST['wc_reset_password'], $_POST['user_login'] ) ) {
			$nonce_value = wc_get_var( $_REQUEST['woocommerce-lost-password-nonce'], wc_get_var( $_REQUEST['_wpnonce'], '' ) ); // @codingStandardsIgnoreLine.

			if ( ! wp_verify_nonce( $nonce_value, 'lost_password' ) ) {
				return;
			}

			// Fontimator Hack:
			$login = isset( $_POST['user_login'] ) ? sanitize_user( wp_unslash( $_POST['user_login'] ) ) : '';
			if ( is_email( $login ) && ! get_user_by( 'email', $login ) ) {
				// Check if email exists in MailChimp.
				if ( Fontimator::mc()->enabled() ) {
					remove_action( 'profile_update', array( 'MC4WP_Ecommerce_Object_Observer', 'on_user_update' ) ); // Don't update mailchimp fname lname when user is created!
					$merge_fields = Fontimator::mc()->get_user_merge_fields( null, $login );
					if ( $merge_fields ) { // Subscribed
						$first_name = $merge_fields->FNAME;
						$last_name = $merge_fields->LNAME ?: '';
						$default_password = wp_generate_password();
						$user_id = wc_create_new_customer( $login, sanitize_user( $login, true ), $default_password );
						if ( is_numeric( $user_id ) && 0 != $user_id && isset( $first_name ) ) {
							wp_update_user(
								array(
									'ID' => $user_id,
									'display_name' => $first_name . ' ' . $last_name,
									'user_nicename' => $first_name . ' ' . $last_name,
									'first_name' => $first_name,
									'last_name' => $last_name,
								)
							);

							add_user_meta( $user_id, 'automatic_user_from_mailchimp', true );

							// do_action( 'woocommerce_new_customer', $user_id );

						}
					}
				}
			}
			// return;
			// End Fontimator Hack

			$success = WC_Shortcode_My_Account::retrieve_password();

			// If successful, redirect to my account with query arg set.
			if ( $success ) {
				wp_redirect( add_query_arg( 'reset-link-sent', 'true', wc_get_account_endpoint_url( 'lost-password' ) ) );
				exit;
			}
		}
	}

	public static function check_email_mailchimp_on_login( $user, $email, $password ) {
		if ( is_email( $email ) && ! get_user_by( 'email', $email ) ) {
			if ( Fontimator::mc()->enabled() ) {
				$merge_fields = Fontimator::mc()->get_user_merge_fields( null, $email );
				if ( $merge_fields ) { // Subscribed
					wc_add_notice( sprintf( __( 'Hello there, %3$s! Your account is almost ready - please reset your password %1$shere%2$s.', 'fontimator' ), '<a href="' . wc_lostpassword_url() . '">', '</a>', $merge_fields->FNAME ), 'success' );
					return new WP_Error();
				}
			}
		}

		return $user;
	}

}
