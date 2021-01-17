<?php

/**
 * Class that extends WC_Product_Variation and adds additional Fontimator functionality.
 *
 * @link       https://alefalefalef.co.il
 * @since      2.0.0
 *
 * @package    Fontimator
 * @subpackage Fontimator/includes
 */

/**
 * Class that extends WC_Product_Variation and adds additional Fontimator functionality.
 *
 * @since      2.0.0
 * @package    Fontimator
 * @subpackage Fontimator/includes
 * @author     Reuven Karasik <rubik@karasik.org>
 */
class Fontimator_Font_Variation extends WC_Product_Variation {

	/**
	 * The variation's weight.
	 *
	 * @var string
	 */
	protected $weight;

	/**
	 * The variation's license.
	 *
	 * @var string
	 */
	protected $license;

	/**
	 * The font family name.
	 *
	 * @var string
	 */
	protected $family;

	public function __construct( $product ) {
		// if ( 'variation' !== wc_get_product( $product )->get_type() ) {
		// 	return false;
		// }

		if ( is_a( $product, 'WC_Product_Variation' ) || get_post( wp_get_post_parent_id( $product ) ) ) {
			parent::__construct( $product );
			$var_attrs = $this->get_variation_attributes();
			if ( count( $var_attrs ) ) {
				$this->weight = $var_attrs[ 'attribute_pa_' . FTM_WEIGHT_ATTRIBUTE ];
				$this->license = $var_attrs[ 'attribute_pa_' . FTM_LICENSE_ATTRIBUTE ];
				$font = new Fontimator_Font( $this->get_parent_id() );
				$this->family = $font->get_slug();
			}
		}

	}

	public function setup() {
		// Set downloadable & virtual
		$this->set_downloadable( true );
		$this->set_virtual( true );

		// Set regular price and sale price
		$this->setup_prices();

		// Set download link
		$this->setup_download_link();

		do_action( 'fontimator_variation_setup', $this );
	}

	public function setup_download_link() {
		$weight_clean = Zipomator::get_clean_weight( $this->weight );
		$license_clean = Zipomator::get_clean_license( $this->license );

		$downloads = $this->get_downloads();
		if ( count( $downloads ) && reset( $downloads ) ) {
			$old_download = reset( $downloads );
			$download_id = $old_download->get_id();
		} else {
			$download_id = wp_generate_uuid4();
		}

		$zip_file = new WC_Product_Download();
		$zip_file->set_id( $download_id );
		$zip_file->set_name( Zipomator::single_name( $this->family, $weight_clean, $license_clean, $this->get_version() ) . '.zip' );

		$download_url = Zipomator::get_bundle_url( $this->family, $weight_clean, $this->license );

		$zip_file->set_file( $download_url );
		$this->set_downloads( [ $zip_file ] );

		return $this->save();
	}

	public function setup_prices() {
		$font = new Fontimator_Font( $this->get_parent_id() );
		$fontprice_ratios = $font->get_fontprice_ratios();
		$base_price = $font->get_base_price();

		if ( in_array( $this->license, array_keys( $fontprice_ratios ) ) && is_numeric( $fontprice_ratios[ $this->license ] ) ) {
			$price = $base_price * floatval( $fontprice_ratios[ $this->license ] );
		} else {
			$price = $base_price;
		}

		if ( '000-family' === $this->weight ) {
			$visible_weights = $font->get_visible_weights();
			$weight_count = count( $visible_weights );

			if ( in_array( '000-variable', $visible_weights ) ) {
				$weight_count--; // Don't include the variable font when calculating family package price
			}

			$price = $weight_count * $price;
			$sale_price = $price * floatval( $fontprice_ratios['family'] );
		} elseif ( '000-familybasic' === $this->weight ) {
			$weights = $font->get_familybasic_weights();

			$price = (count( $weights )) * $price;
			$sale_price = $price * floatval( $fontprice_ratios['family'] );
		}

		// Set prices
		$this->set_regular_price( ceil( $price / 5 ) * 5 );

		if ( isset( $sale_price ) && is_numeric( $sale_price ) ) {
			$this->set_sale_price( ceil( $sale_price / 5 ) * 5 );
		}

		return $this->save();
	}

	public function get_weight( $context = 'view' ) {
		// $context is not usable, it's to suport the WC_Product_Variation::get_weight declaration
		return $this->weight;
	}

	public function get_license() {
		return $this->license;
	}

	public function get_version() {
		return Fontimator::acf()->get_field( 'font_version', $this->get_parent_id() );
	}

	public function get_license_type() {
		$license_parts = explode( '-', $this->license, 2 );
		return $license_parts[0];
	}

	public function get_family() {
		return $this->family;
	}

	/**
	 * Get the name of the variation, with the weight and the license
	 *
	 * @param string $context not used, it's to suport the WC_Product::get_name declaration
	 * @param boolean $full_license Optional: if true, the license includes the level (how many computers/etc...)
	 * @return string
	 */
	public function get_name( $context = 'view', $full_license = false ) {
		$name = $this->get_title(); // Family

		if ( $this->weight ) {
			$weight_name = get_term_by( 'slug', $this->weight, 'pa_' . FTM_WEIGHT_ATTRIBUTE )->name;
			// TRANSLATORS: The Weight name
			$name .= sprintf( __( ', weight of %s', 'fontimator' ), $weight_name );
		}

		if ( $this->license ) {
			if ( $full_license ) {
				$license_name = get_term_by( 'slug', $this->license, 'pa_' . FTM_LICENSE_ATTRIBUTE )->name;
			} else {
				switch ( explode( '-', $this->license, 2 )[0] ) {
					case 'web':
						$license_name = __( 'web', 'fontimator' );
						break;
					case 'app':
						$license_name = __( 'app', 'fontimator' );
						break;
					case 'otf':
						$license_name = __( 'desktop', 'fontimator' );
						break;
				}
			}
			// TRANSLATORS: The License name
			$name .= sprintf( __( ', %s license', 'fontimator' ), $license_name );
		}

		return $name;
	}


}
