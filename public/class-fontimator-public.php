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
	 * @access   protected
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	protected $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var      string    $version    The current version of this plugin.
	 */
	protected $version;
	
	/**
	 * Enable/Disable DevTools detection. Change here to enable.
	 *
	 * @var boolean
	 */
	public $devtools_detection = false;

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
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/fontimator-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    2.0.0
	 */
	public function enqueue_scripts() {
		wp_register_script(
			'fontimator-email-preferences',
			plugin_dir_url( __FILE__ ) . 'js/fontimator-email-preferences.js',
			array( 'jquery' ),
			$this->version,
			true
		);

		wp_enqueue_script( 'fontimator-public-js', plugin_dir_url( __FILE__ ) . 'js/fontimator-public.js', array( 'jquery' ), $this->version, true );

		if ( $this->devtools_detection && ! current_user_can('administrator') && ! isset( $_GET['allow-devtools'] ) ) {
			wp_enqueue_script( 'devtools-detect', plugin_dir_url( __FILE__ ) . 'js/devtools-detector.js', array( ), $this->version, true );
		}

		if ( wp_script_is( 'ivrita-lib-js', 'registered' ) && ! wp_script_is( 'ivrita-lib-js', 'enqueued' ) ) {
			wp_enqueue_script( 'ivrita-lib-js' );
		}

		if (Fontimator_I18n::get_user_gender() !== Fontimator_I18n::GENDER_NEUTRAL) {
			wp_localize_script(
				'fontimator-public-js', 'UserGender', array(
					'gender' => Fontimator_I18n::get_user_gender(),
				)
			);
		}

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
		
		wp_localize_script(
			'fontimator-public-js', 'FontimatorPublic', array(
				'licenseAttributeName' => FTM_LICENSE_ATTRIBUTE,
				'placeholders' => array(
					'web' => _x( 'example.com', 'The website address field placeholder', 'fontimator' ),
					'app' => _x( 'App Name', 'The app name field placeholder', 'fontimator' ),
				),
			)
		);
	}

	public function add_redirect_hosts( $hosts ) {
		array_push( $hosts, 'alefalefalef.co.il', 'fontimonim.co.il' );
		return $hosts;
	}

	public function devtools_detect_notice() {
		if ( current_user_can( 'administrator' ) ) {
			return;
		}

		$github_link = 'https://github.com/alefalefalef';

		if ( FTM_SITE_NAME === 'fontimonim' ) {
			$colophon_page = get_page_by_path( 'info/brand-assets' );
		} else {
			$colophon_page = get_page_by_path( 'about/colophon' );
		}
		$colophon_link = get_permalink( $colophon_page );

		$eula_link = get_permalink( get_page_by_path( 'more/license' ) );

		?>
		<div class="pop-up-wrapper">
			<section class="pop-up" id="devtools-pop-up" style="display:none;">
				<header>
					<div class="title">
						<?php _e( 'Inspecting Elements Ey?!', 'fontimator' ); ?>
					</div>
				</header>
				<section class="message">
					<p>
						<?php echo sprintf(
							esc_html( __( 'Hi! We see you\'re interested in the behind the scenes of our site. That\'s great! Some of the code can even be found at %1$sour github%2$s and the %3$sColophon Page%4$s.', 'fontimator' ) ),
							"<a href='$github_link'>",'</a>',
							"<a href='$colophon_link'>",'</a>'
						); ?>
					</p>
					<p>
						<?php _e( 'We just wanted to remind you that downloading font files from our site is a criminal offence and a violation of intelectual property.', 'fontimator' ); ?>
					</p>
					<p>
						<?php _e( 'Happy inspecting.', 'fontimator' ); ?>
					</p>
				</section>
				<footer>
					<div class="small-info">
						<p><?php echo sprintf(
							esc_html( __( 'More information can be found in our %1$sEnd-User License Agreement%2$s page, and feel free to contact us for any issues.', 'fontimator' ) ),
							"<a href='$eula_link'>",'</a>'
						); ?></p>
						<?php 
						$user_ip = $_SERVER['REMOTE_ADDR'];
						if ( !($user_ip) ) {
							echo '<p>Recorded IP: ' . $user_ip . '</p>';
						}
						?>
					</div>
					<a class="exit" href="#"><?php _ex( 'Close', 'Close button on popups', 'fontimator' ); ?></a>
				</footer>
			</section>
		</div>
		<?php
	}

	public function add_family_calculation_to_dropdown( $name ) {
		$family_text = get_term_by( 'slug', '000-family', 'pa_' . FTM_WEIGHT_ATTRIBUTE )->name;
		if ( $name === $family_text ) {
			if ( $GLOBALS['post'] && $GLOBALS['post']->ID ) {
				$font = new Fontimator_Font( $GLOBALS['post']->ID );
				$fontprice_ratios = $font->get_fontprice_ratios();
				$ratio = $fontprice_ratios['family'];
				$discount = number_format( (1 - floatval( $ratio )) * 100 );
				$display_family_discount_percentage = Fontimator::acf()->get_field( 'display_family_discount_percentage', 'options' );
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
	public static function display_licenseapp_field() {
		$license_info_page_link = Fontimator::acf()->get_field( 'license_info_page', 'options' );
		?>
		<label for="licenseapp" class="licenseapp-field" style="display: none;">
			<span data-license="app"><?php _e( 'The app name:', 'fontimator' ); ?></span>
			<span data-license="web" style="display: none"><?php _e( 'The website address:', 'fontimator' ); ?></span>
			<a class="info-tooltip" href="<?php echo $license_info_page_link; ?>" title="<?php esc_attr_e( 'Every web/app license is purchased per one app/website. If you need it for two different websites, you must purchase it twice.', 'fontimator' ); ?>" target="blank"><i class="icon" data-icon="."></i></a>
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
	function validate_licenseapp_field( $passed, $product_id, $quantity, $variation_id = '' ) {
		$variation = new Fontimator_Font_Variation( empty( $variation_id ) ? $product_id : $variation_id );
		if ( ! $variation ) {
			return $passed;
		}

		$is_licenseapp_required = in_array( $variation->get_license_type(), array( 'app', 'web' ) )
			&& $variation->get_license() !== 'web-reseller';
		if ( $is_licenseapp_required && empty( $_REQUEST['licenseapp'] ) ) {
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
	function add_licenseapp_cart_data( $cart_item_data, $product_id, $variation_id, $quantity ) {
		if ( ! empty( $_REQUEST['licenseapp'] ) ) {
			// Add the item data
			$cart_item_data['licenseapp'] = $_REQUEST['licenseapp'];
		}
		return $cart_item_data;
	}

	/**
	 * Add the licenseapp to cart item data, e.g. cart page
	 */
	function add_licenseapp_get_item_data( $item_data, $cart_item ) {
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
	function add_licenseapp_to_order_line_item( $item, $cart_item_key, $values, $order ) {
		foreach ( $item as $cart_item_key => $values ) {
			if ( isset( $values['licenseapp'] ) ) {
				$item->add_meta_data( __( 'License For', 'fontimator' ), $values['licenseapp'], true );
			}
		}
	}


	/**
	 * Add the discount to order items
	 */
	function add_discount_price_to_cart( $old_display, $cart_item, $cart_item_key ) {
		$sale_price = $cart_item['data']->get_sale_price();
		$regular_price = $cart_item['data']->get_regular_price();
		$price = $cart_item['data']->get_price();

		if ( $sale_price !== $regular_price && $sale_price == $price ) {
			$output = sprintf( '<del>%1$s</del>&nbsp;&nbsp;%2$s', wc_price($regular_price), wc_price($sale_price) );
			
			if ( $discount_reason = $cart_item['data']->get_meta('discount_reason') ) {
				$output .= sprintf( ' <br><small class="discount-reason">(%s)</small>', $discount_reason );
			}

			return $output;
		}
		return $old_display;
	}

	public static function show_already_bought_notice() {
		global $product;
		$gender_specific_yourelooking = Fontimator_I18n::genderize_string(
			_x( 'you are looking', 'Gender-nuetral', 'fontimator' ),
			_x( 'you are looking', 'Male', 'fontimator' ),
			_x( 'you are looking', 'Female', 'fontimator' )
		);
		$gender_specific_enter = Fontimator_I18n::genderize_string(
			_x( 'Enter', 'Gender-nuetral "Enter"', 'fontimator' ),
			_x( 'Enter', 'Male "Enter"', 'fontimator' ),
			_x( 'Enter', 'Female "Enter"', 'fontimator' )
		);
		if ( is_user_logged_in() ) {
			global $product;
			$current_user = wp_get_current_user();
			if ( wc_customer_bought_product( $current_user->user_email, $current_user->ID, $product->get_id() ) ) {
				echo '<div class="user-bought">';
					printf( 
					__( 'Hello, %1$s. If %2$s for the files and licenses of font %3$s you have purchased - %4$s to the %5$sDownloads page%6$s.', 'fontimator' ), 
						$current_user->first_name, 
						$gender_specific_yourelooking,
						$product->get_name(), 
						$gender_specific_enter,
						'<a class="button b-small b-inline" href="' . esc_url( get_permalink( wc_get_page_id( 'myaccount' ) ) . 'downloads' ) . '">',
						'</a>'
				);
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
		$_adjust_lineheight = get_field( 'font_adjust_lineheight', $font_id );
		$adjust_lineheight_of_box = get_field( 'font_adjust_lineheight_of_box', $font_id );
		$adjust_lineheight = ( !empty($adjust_lineheight_of_box) && '' != $adjust_lineheight_of_box ) ? $adjust_lineheight_of_box : $_adjust_lineheight;
		$weight_alef = (get_field( 'font_weight_alef', $font_id )) ? get_field( 'font_weight_alef', $font_id )->slug : '400-regular';

		simple_font_face( $family, $weight_alef );
		?>
		<div
			class="font-name-with-preview font-name-<?php echo $family; ?>"
			style="font-family: <?php echo $family; ?>;
				font-size:<?php echo 1.4 * $adjust_size; ?>em;
				line-height:<?php echo 0.7 * $adjust_lineheight; ?>em;
				height:<?php echo 0.7 / $adjust_size; ?>em;
				z-index: 1;
				font-weight:<?php echo get_weight_number( $weight_alef ); ?>;">

			<?php echo $text; ?>
		</div>
		<?php
	}

	public function display_share_cart_url() {
		global $woocommerce;

		if ( !isset($_REQUEST['ftm-add-to-cart']) && !isset($_REQUEST['ftm-automatic-cart'])) {
			$items = $woocommerce->cart->get_cart();
			$product_ids = array_values( wp_list_pluck( $items, 'variation_id' ) );
			$cart_url = esc_url_raw( add_query_arg( 'ftm-add-to-cart', implode( ',', $product_ids ), wc_get_cart_url() ) );
			?>
			<a href="<?php echo $cart_url; ?>" title="<?php esc_attr_e( 'Click here to copy the link to this cart, which you can send to your client or save for later.', 'fontimator' ); ?>" data-success-text="<?php esc_attr_e( 'Link to cart was copied!', 'fontimator' ); ?>" class="share-cart-button button tooltip copyable-link">
				<i class="icon" data-icon="t"></i> <?php _e( 'Share a link to this cart', 'fontimator' ); ?>
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

	public function shortcode_genderize( $atts, $content = null ) {
		$default = $atts['default'];
		$male = $atts['male'];
		$female = $atts['female'];

		return Fontimator_I18n::genderize_string( $default, $male, $female );
	}

	public function terms_and_conditions_checkbox_text( $option ){
		$gender_specific_agree = Fontimator_I18n::genderize_string(
			_x( 'agree', 'Gender-nuetral agree', 'fontimator' ),
			_x( 'agree', 'Male agree', 'fontimator' ),
			_x( 'agree', 'Female agree', 'fontimator' )
		);
		$option = get_option( 'woocommerce_checkout_terms_and_conditions_checkbox_text', 
			sprintf( 
			__( 'I have read and %1$s to the ‫‫‫%2$s', 'fontimator' ), 
			$gender_specific_agree,	
			'[terms]' )
		);
		return $option;
	}

	public function returning_customers_custom_message( $content, $email ) {
		$user = get_user_by('email', $email);
		if ( $user && $user->first_name && preg_match("/\p{Hebrew}/u", $user->first_name) ) {
			$content = Fontimator_I18n::genderize_string( 
				sprintf( __( 'An account is already registered with your email address. %1$sPlease log in%2$s.', 'fontimator' ), '<a class="showlogin" href="#">', '</a>' ),
				sprintf( _x('%1$s? is that you?! We need you to %2$slog in%3$s so we can complete your purchase.', 'Male', 'fontimator' ), $user->first_name, '<a class="showlogin" href="#">', '</a>' ),
				sprintf( _x('%1$s? is that you?! We need you to %2$slog in%3$s so we can complete your purchase.', 'Female', 'fontimator' ), $user->first_name, '<a class="showlogin" href="#">', '</a>' ),
				$email
			);
		} else {
			$content = sprintf( __( 'An account is already registered with your email address. %1$sPlease log in%2$s.', 'fontimator' ), '<a class="showlogin" href="#">', '</a>' );
		}
		return $content;
	}

}
