<?php
defined('ABSPATH') or exit;

if (! class_exists('WooCommerce') || ! class_exists('MC4WP_WooCommerce_Integration') ) {
	return;
}

/**
 * Class MC4WP_FTM_WooCommerce_Integration
 *
 * @ignore
 */
class MC4WP_FTM_WooCommerce_Integration extends MC4WP_WooCommerce_Integration
{

	/**
	 * @var string
	 */
	public $name = "WooCommerce Checkout (Fontimator enhanced)";

	/**
	 * @var string
	 */
	public $description = "Subscribes people with gender and birthday from WooCommerce's checkout form.";

	/**
	 * Add hooks
	 */
	public function add_hooks()
	{
		// Postponse to later when MC API is ready
		add_action( 'wp_loaded', function() {

			// Skip if user already subscribed
			if ( ! Fontimator::mc()->is_user_subscribed() ) {
				parent::add_hooks();
			}
			add_action( 'mc4wp_admin_after_' . $this->slug . '_integration_settings', array( $this, 'admin_after' ) );
			add_action( 'mc4wp_integration_' . $this->slug . '_before_checkbox_wrapper', array( $this, 'catch_checkbox_html' ) );
			add_action( 'mc4wp_integration_' . $this->slug . '_after_checkbox_wrapper', array( $this, 'print_checkbox_html' ), 10 );
			add_action( 'mc4wp_integration_' . $this->slug . '_after_checkbox_wrapper', array( $this, 'print_merge_fields' ), 20 );
			add_action( 'woocommerce_after_checkout_validation', array( $this, 'validate_merge_fields' ), 10, 2 );
		} );
	}
	
	public function admin_after() {
		$integration = $this;

		$file = WP_PLUGIN_DIR . '/mailchimp-for-wp/integrations/woocommerce/admin-after.php';
		if (file_exists($file)) {
			include $file;
		}
	}

	/**
	 * Catch the actual checkbox code since we want to hide it in case there's a subsription in the cart
	 *
	 * @return void
	 */
	public function catch_checkbox_html() {
		ob_start();
	}

	/**
	 * Print the original checkbox code or a always-on-hidden checkbox based on if there's a subsription in the cart
	 *
	 * @return void
	 */
	public function print_checkbox_html() {
		$original_checkbox = ob_get_clean();
		if ( class_exists( 'WC_Subscriptions_Cart' ) && WC_Subscriptions_Cart::cart_contains_subscription() ) {
			?>
			<input type="checkbox" checked="checked" style="display: none;" name="_mc4wp_subscribe_<?php echo esc_attr( $this->slug ); ?>" data-always-on="true" value="1"  />
			<?php
		} else {
			echo $original_checkbox;
		}
		
	}

	public function print_merge_fields() {
		global $wp_locale;
		/**
		 * Helper function to remove form-row class, which collides with the address-i18n.js script
		 *
		 * @param string $html
		 * @return string
		 */
		function remove_form_row_class( $html = '' ) {
			return preg_replace( "/form-row/i", "", $html );
		}
		?>
		<div class="form-row mailchimp_merge_fields" data-priority="120">
			<?php
			echo remove_form_row_class(woocommerce_form_field( 'mc4wp_merge_gender', [
				'type' => 'radio',
				'label' => __( 'How would you like to be addressed?', 'fontimator' ),
				'required' => true,
				'return' => true,
				'options' => array(
					Fontimator_I18n::GENDER_FEMALE => _x( 'As female', 'Gender field option in edit account form', 'fontimator' ),
					Fontimator_I18n::GENDER_MALE => _x( 'As male', 'Gender field option in edit account form', 'fontimator' ),
				)
			] ));

			?>
			<div class="mailchimp_merge_fields_birthday">
				<label for="mc4wp_merge_bday_day">
					<?php _e( 'When should we celebrate your birthday?', 'fontimator' ); ?>
					<abbr class="required" title="<?php esc_attr_e( 'required', 'woocommerce' ); ?>">*</abbr>
				</label>
				<?php
				echo remove_form_row_class(woocommerce_form_field( 'mc4wp_merge_bday_day', [
					'type' => 'number',
					'required' => true,
					'custom_attributes' => array(
						'min' => 0,
						'max' => 31,
						'required' => 'required',
					),
					'return' => true,
					'placeholder' => _x( 'Day', 'Mailchimp birthday day field plaeholder', 'fontimator' ),
				] ));
				?>

				<span class="of">
					<?php _ex( ' of', 'Mailchimp birthday field seperator', 'fontimator' ); ?>
				</span>

				<?php
				echo remove_form_row_class(woocommerce_form_field( 'mc4wp_merge_bday_month', [
					'type' => 'select',
					'required' => true,
					'return' => true,
					//'options' => array_merge( array( '' => _x( 'Month', 'Mailchimp birthday month field plaeholder', 'fontimator' ) ), $wp_locale->month ),
					'options' => array( 'חודש', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12' ),
				] ));
				?>
			</div>
		</div>
		<script>
		jQuery(function($){
			// Checkout mailchimp signup
			var handle_merge_fields = function (e) {
				if ($(this).data('always-on') || $(this).prop('checked')) {
					$('.mailchimp_merge_fields').show();
				} else {
					$('.mailchimp_merge_fields').hide();
				}
			};
			$('.woocommerce-checkout input[name=_mc4wp_subscribe_<?php echo $this->slug; ?>][value=1]').each(handle_merge_fields).change(handle_merge_fields);
		});
		</script>
		<?php
	}

    public function validate_merge_fields( $fields, WP_Error $errors ) {
        if ( isset($_REQUEST[ '_mc4wp_subscribe_' . $this->slug ]) && $_REQUEST[ '_mc4wp_subscribe_' . $this->slug ] ) {
            if ( !isset($_REQUEST['mc4wp_merge_gender'] )) {
                $errors->add( 'validation', __( 'Missing gender', 'fontimator' ) );
            }

            if ( !isset($_REQUEST['mc4wp_merge_bday_day']) ) {
                $errors->add( 'validation', __( 'Missing birthday day', 'fontimator' ) );
            }

            if ( !isset($_REQUEST['mc4wp_merge_bday_month']) ) {
                $errors->add( 'validation', __( 'Missing birthday month', 'fontimator' ) );
            }
        }
    }

    /**
    * @param int $order_id
    */
    public function save_woocommerce_checkout_checkbox_value($order_id)
    {
		update_post_meta($order_id, '_mc4wp_optin', $this->checkbox_was_checked());
		if ( $this->checkbox_was_checked() ) {
			update_post_meta($order_id, '_mc4wp_bday', $this->get_request_bdate());
			update_post_meta($order_id, '_mc4wp_gender', $this->get_request_gender());
		}
	}
	
	protected function get_request_bdate() {
		$data = $this->get_data();
		if ( isset( $data['mc4wp_merge_bday_day'] ) && isset( $data['mc4wp_merge_bday_month'] ) ) {
			return str_pad( $data['mc4wp_merge_bday_day'], 2, "0", STR_PAD_LEFT ) . '/' . $data['mc4wp_merge_bday_month'];
		}

		return false;
	}

	protected function get_request_gender() {
		$data = $this->get_data();
		if ( isset( $data['mc4wp_merge_gender'] ) ) {
			switch ( $data['mc4wp_merge_gender'] ) {
				case Fontimator_I18n::GENDER_MALE:
					return __( 'Man', 'fontimator' );
					break;
				
				case Fontimator_I18n::GENDER_FEMALE:
					return __( 'Woman', 'fontimator' );
					break;
			} 
		}

		return false;
	}
    
    /**
    * @param int $order_id
    * @return boolean
    */
    public function subscribe_from_woocommerce_checkout($order_id)
    {
        if (! $this->triggered($order_id)) {
            return false;
        }

        $order = wc_get_order($order_id);

        if (method_exists($order, 'get_billing_email')) {
            $data = array(
                'EMAIL' => $order->get_billing_email(),
                'NAME' => "{$order->get_billing_first_name()} {$order->get_billing_last_name()}",
                'FNAME' => $order->get_billing_first_name(),
                'LNAME' => $order->get_billing_last_name(),
            );
        } else {
            // NOTE: for compatibility with WooCommerce < 3.0
            $data = array(
                'EMAIL' => $order->billing_email,
                'NAME' => "{$order->billing_first_name} {$order->billing_last_name}",
                'FNAME' => $order->billing_first_name,
                'LNAME' => $order->billing_last_name,
            );
        }

        $data['GENDER'] = $this->get_request_gender();
        $data['BDAY'] = $this->get_request_bdate();

        return $this->subscribe($data, $order_id);
    }

    /**
     * @return bool
     */
    public function is_installed()
    {
        return class_exists('WooCommerce') && class_exists('MC4WP_WooCommerce_Integration') && class_exists('Fontimator');
    }

}
mc4wp_register_integration('ftm-woocommerce', 'MC4WP_FTM_WooCommerce_Integration');