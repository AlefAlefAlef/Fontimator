<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://alefalefalef.co.il
 * @since      2.0.0
 *
 * @package    Fontimator
 * @subpackage Fontimator/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Fontimator
 * @subpackage Fontimator/public
 * @author     Reuven Karasik <rubik@karasik.org>
 */
class Fontimator_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    2.0.0
	 */
	public function enqueue_styles() {
		// wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/fontimator-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    2.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'fontimator-public-js', plugin_dir_url( __FILE__ ) . 'js/fontimator-public.js', array( 'jquery' ), $this->version, true );

		wp_localize_script(
			'fontimator-public-js', 'FontimatorTimedMessages', array(
				'greetings' => array(
					'morning' => __( 'Good Morning, %s.', 'fontimator' ),
					'afternoon' => __( 'Good Afternoon, %s.', 'fontimator' ),
					'evening' => __( 'Good Evening, %s.', 'fontimator' ),
					'night' => __( 'Good Night, %s.', 'fontimator' ),
					'saturday' => __( 'Good Shabbos, %s.', 'fontimator' ),
				),
				'welcome' => array(
					'morning' => __( 'How great to start the day with some stats about your purchases!', 'fontimator' ),
					'afternoon' => __( "We hope you had your lunch, and you're ready to kick in with some stats about your fonts!", 'fontimator' ),
					'evening' => __( 'Still in the office? How about some typography before sleep?', 'fontimator' ),
					'night' => __( 'Working late? We got all your font information in one place.', 'fontimator' ),
				),
			)
		);
	}

	public function add_family_calculation_to_dropdown( $name ) {
		$family_text = get_term_by( 'slug', '000-family', 'pa_' . FTM_WEIGHT_ATTRIBUTE )->name;
		if ( $name === $family_text ) {
			if ( $GLOBALS['post'] && $GLOBALS['post']->ID ) {
				$font = new Fontimator_Font( $GLOBALS['post']->ID );
				$fontprice_ratios = $font->get_fontprice_ratios();
				$ratio = $fontprice_ratios['family'];
				$discount = number_format( (1 - floatval( $ratio )) * 100 );
				$display_family_discount_percentage = Fontimator::get_instance()->get_acf()->get_field( 'display_family_discount_percentage', 'options' );
				if ( 0 != $discount && $display_family_discount_percentage ) {
					// translators: discount percentage
					return $name . ' = ' . sprintf( __( '%d%% Discount', 'fontimator' ), $discount );
				}
			}
		}

		$familybasic_text = get_term_by( 'slug', '000-familybasic', 'pa_' . FTM_WEIGHT_ATTRIBUTE )->name;
		if ( $name === $familybasic_text ) {
			if ( $GLOBALS['post'] && $GLOBALS['post']->ID ) {
				$font = new Fontimator_Font( $GLOBALS['post']->ID );
				$weights = $font->get_familybasic_weights( 'name' );
				if ( count( $weights ) ) {
					$weight_list = implode( '+', $weights );
					return $name . " ($weight_list)";
				}
			}
		}
		return $name;
	}

	public function group_licenses_dropdown( $html, $args ) {
		if ( 'pa_' . FTM_LICENSE_ATTRIBUTE === $args['attribute'] ) {
			$options               = $args['options'];
			$product               = $args['product'];
			$attribute             = $args['attribute'];
			$name                  = $args['name'] ? $args['name'] : 'attribute_' . sanitize_title( $attribute );
			$id                    = $args['id'] ? $args['id'] : sanitize_title( $attribute );
			$class                 = $args['class'];
			$show_option_none      = $args['show_option_none'] ? true : false;
			$show_option_none_text = $args['show_option_none'] ? $args['show_option_none'] : __( 'Choose an option', 'woocommerce' ); // We'll do our best to hide the placeholder, but we'll need to show something when resetting options.

			if ( empty( $options ) && ! empty( $product ) && ! empty( $attribute ) ) {
				$attributes = $product->get_variation_attributes();
				$options    = $attributes[ $attribute ];
			}

			$html  = '<select id="' . esc_attr( $id ) . '" class="' . esc_attr( $class ) . '" name="' . esc_attr( $name ) . '" data-attribute_name="attribute_' . esc_attr( sanitize_title( $attribute ) ) . '" data-show_option_none="' . ( $show_option_none ? 'yes' : 'no' ) . '">';
			$html .= '<option value="">' . esc_html( $show_option_none_text ) . '</option>';

			if ( ! empty( $options ) ) {
				if ( $product && taxonomy_exists( $attribute ) ) {
					// Get terms if this is a taxonomy - ordered. We need the names too.
					$terms = wc_get_product_terms(
						$product->get_id(), $attribute, array(
							'fields' => 'all',
						)
					);

					$license_platforms = array();
					foreach ( $terms as $term ) {
						$platform = explode( ' ', $term->name )[0];
						if ( ! isset( $license_platforms[ $platform ] ) ) {
							$license_platforms[ $platform ] = array();
						}
						$license_platforms[ $platform ][] = $term;
					}

					foreach ( $license_platforms as $platform => $terms ) {
						// TRANSLATORS: Platform Name
						$html .= '<optgroup label="' . sprintf( __( '%s Licenses:', 'fontimator' ),  $platform ) . '">';
						foreach ( $terms as $term ) {
							if ( in_array( $term->slug, $options, true ) ) {
								$html .= '<option value="' . esc_attr( $term->slug ) . '" ' . selected( sanitize_title( $args['selected'] ), $term->slug, false ) . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name ) ) . '</option>';
							}
						}
						$html .= '</optgroup>';
					}
				}
			}

			$html .= '</select>';
			return $html;
		}

		return $html;
	}

	public function hide_dead_weights_from_dropdown( $args ) {
		if ( 'pa_' . FTM_WEIGHT_ATTRIBUTE === $args['attribute'] ) {
			$font = new Fontimator_Font( $args['product'] );
			$archived_weights = wc_list_pluck( (array) $font->get_archived_weights(), 'slug' );
			$visible_weights = array_diff( (array) $args['options'], $archived_weights );

			$args['options'] = $visible_weights;
		}
		return $args;
	}
	public function hide_sorting_options_from_dropdown( $orderby ) {
		unset( $orderby['price-desc'] );
		return $orderby;
	}

	/**
	 * Display licenseapp field on the front end
	 */
	function display_licenseapp_field() {
		wp_localize_script(
			'fontimator-public-js', 'FontimatorPublic', array(
				'licenseAttributeName' => FTM_LICENSE_ATTRIBUTE,
				'placeholders' => array(
					'web' => _x( 'example.com', 'The website address field placeholder', 'fontimator' ),
					'app' => _x( 'App Name', 'The app name field placeholder', 'fontimator' ),
				),
			)
		);
		$license_info_page_link = Fontimator::get_instance()->get_acf()->get_field( 'license_info_page', 'options' );
		?>
		<label for="licenseapp" class="licenseapp-field" style="display: none">
			<span data-license="app"><?php _e( 'The app name:', 'fontimator' ); ?></span>
			<span data-license="web" style="display: none"><?php _e( 'The website address:', 'fontimator' ); ?></span>
			<a class="info-tooltip" href="<?php echo $license_info_page_link; ?>" title="<?php esc_attr_e( 'Every web/app license is purchased per one app/website. If you need it for two different websites, you must purchase it twice.', 'fontimator' ); ?>" target="blank"><i class="icon">.</i></a>
			<input type="text" id="licenseapp" name="licenseapp" required>
		</label>
		<?php
	}

	/**
	 * Validate the licenseapp field
	 * @param Array     $passed         Validation status.
	 * @param Integer   $product_id     Product ID.
	 * @param Boolean   $quantity       Quantity
	 */
	function validate_licenseapp_field( $passed, $product_id, $quantity, $variation_id ) {
		$variation = new Fontimator_Font_Variation( $variation_id );
		$is_licenseapp_required = in_array( $variation->get_license_type(), array( 'app', 'web' ) );
		if ( $is_licenseapp_required && empty( $_POST['licenseapp'] ) ) {
			// Fails validation
			$passed = false;
			wc_add_notice( __( 'Please enter a website URL/app name for this license', 'fontimator' ), 'error' );
		}
		return $passed;
	}

	/**
	 * Add the licenseapp as item data to the cart object
	 * @param Array      $cart_item_data Cart item meta data.
	 * @param Integer   $product_id     Product ID.
	 * @param Integer   $variation_id   Variation ID.
	 * @param Boolean   $quantity           Quantity
	*/
	function add_licenseapp_item_data( $cart_item_data, $product_id, $variation_id, $quantity ) {
		if ( ! empty( $_POST['licenseapp'] ) ) {
			// Add the item data
			$cart_item_data['licenseapp'] = $_POST['licenseapp'];
		}
		return $cart_item_data;
	}

	/**
	 * Add the licenseapp to cart item data, e.g. cart page
	 */
	function licenseapp_get_item_data( $item_data, $cart_item ) {
		if ( ! empty( $cart_item['licenseapp'] ) ) {
			$item_data[] = array(
				'key' => __( 'License For', 'fontimator' ),
				'display' => $cart_item['licenseapp'],
				'value' => $cart_item['licenseapp'],
			);
		}
		return $item_data;
	}

	/**
	 * Add the licenseapp to order items
	 */
	function add_licenseapp_to_order( $item, $cart_item_key, $values, $order ) {
		foreach ( $item as $cart_item_key => $values ) {
			if ( isset( $values['licenseapp'] ) ) {
				$item->add_meta_data( __( 'License For', 'fontimator' ), $values['licenseapp'], true );
			}
		}
	}

	public function show_already_bought_notice() {
		global $product;
		if ( is_user_logged_in() ) {
			global $product;
			$current_user = wp_get_current_user();
			if ( wc_customer_bought_product( $current_user->user_email, $current_user->ID, $product->get_id() ) ) {
				echo '<div class="user-bought">';
					printf( __( 'Hello, %1$s. You have purchased some weights of %2$s font before. You can download the files in your <a href="%3$s">Dashboard</a>.', 'fontimator' ), $current_user->first_name, $product->get_name(), esc_url( get_permalink( wc_get_page_id( 'myaccount' ) ) . 'downloads' ) );
				echo '</div>';
			}
		}

	}

	public function print_with_font_preview( $text, $font_id = false ) {
		$font = new Fontimator_Font( $font_id );

		$family = $font->get_slug();
		if ( ! $family ) {
			echo $text;
			return;
		}
		$adjust_size = get_field( 'font_adjust_size', $font_id );
		$adjust_lineheight_of_box = get_field( 'font_adjust_lineheight_of_box', $font_id );
		$weight_alef = (get_field( 'font_weight_alef', $font_id )) ? get_field( 'font_weight_alef', $font_id )->slug : '400-regular';

		simple_font_face( $family, $weight_alef );
		?>
		<span
			style="font-family:<?php echo $family; ?>-variable, <?php echo $family; ?>, blank;
				font-size:<?php echo 1 * $adjust_size; ?>em;
				line-height:<?php echo .85 * $adjust_lineheight_of_box; ?>em;
				height:<?php echo .85 / $adjust_size; ?>em;
				z-index: 1;
				font-weight:<?php echo get_weight_number( $weight_alef ); ?>;">

			<?php echo $text; ?>
		</span>
		<?php
	}

	public function display_share_cart_url() {
		global $woocommerce;

		if ( ! $_REQUEST['ftm-add-to-cart'] && ! $_REQUEST['ftm-automatic-cart'] ) {
			$items = $woocommerce->cart->get_cart();
			$product_ids = array_values( wp_list_pluck( $items, 'variation_id' ) );
			$cart_url = esc_url_raw( add_query_arg( 'ftm-add-to-cart', implode( ',', $product_ids ), wc_get_cart_url() ) );
			?>
			<a href="<?php echo $cart_url; ?>" title="<?php esc_attr_e( 'Click here to copy the link to this cart, which you can send to your client or save for later.', 'fontimator' ); ?>" data-success-text="<?php esc_attr_e( 'Link to cart was copied!', 'fontimator' ); ?>" class="share-cart-button button tooltip copyable-link">
				<?php _e( 'Share a link to this cart', 'fontimator' ); ?>
			</a>
			<?php

		}

	}


	public function shortcode_zip_table( $atts, $content = null ) {
		ob_start();
		require plugin_dir_path( __FILE__ ) . 'partials/shortcode-zip-table.php';
		return ob_get_clean();
	}

	public function shortcode_eula( $atts, $content = null ) {
		ob_start();
		$eula = new Zipomator_EULA( isset( $_GET['licenses'] ) ? explode( ',', $_GET['licenses'] ) : null );
		$eula->html();
		return ob_get_clean();
	}

	public function shortcode_sale_products( $atts, $content = null ) {
		ob_start();
		require plugin_dir_path( __FILE__ ) . 'partials/shortcode-sale-products.php';
		return ob_get_clean();
	}

	public function shortcode_free_download( $atts, $content = null ) {
		ob_start();
		require plugin_dir_path( __FILE__ ) . 'partials/shortcode-free-download.php';
		return ob_get_clean();
	}

}
