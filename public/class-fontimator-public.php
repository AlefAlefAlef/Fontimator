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
		// wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/fontimator-public.js', array( 'jquery' ), $this->version, false );
	}

	public function add_family_calculation_to_dropdown( $name ) {
		$family_text = get_term_by( 'slug', '000-family', 'pa_' . FTM_WEIGHT_ATTRIBUTE )->name;
		if ( $name === $family_text ) {
			if ( $GLOBALS['post'] && $GLOBALS['post']->ID ) {
				$font = new Fontimator_Font( $GLOBALS['post']->ID );
				$fontprice_ratios = $font->get_fontprice_ratios();
				$ratio = $fontprice_ratios['family'];
				$discount = number_format( (1 - floatval( $ratio )) * 100 );
				if ( 0 !== $discount ) {
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
			$html .= '
			<script>
			jQuery(function($){
				$("form.variations_form").on("woocommerce_update_variation_values", function(event){
					$(this).find("optgroup:empty").remove();
				});
			});
			</script>
		';
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

	public function shortcode_zip_table( $atts, $content = null ) {
		ob_start();
		include_once( 'partials/shortcode-zip-table.php' );
		return ob_get_clean();
	}

	public function shortcode_eula( $atts, $content = null ) {
		ob_start();
		$eula = new Zipomator_EULA( isset( $_GET['licenses'] ) ? explode( ',', $_GET['licenses'] ) : null );
		$eula->html();
		return ob_get_clean();
	}

}
