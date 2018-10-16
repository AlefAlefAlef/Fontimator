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

		parent::__construct( $product );

		$var_attrs = $this->get_variation_attributes();

		$this->weight = $var_attrs[ 'attribute_pa_' . FTM_WEIGHT_ATTRIBUTE ];
		$this->license = $var_attrs[ 'attribute_pa_' . FTM_LICENSE_ATTRIBUTE ];

		$font = new Fontimator_Font( $this->get_parent_id() );
		$this->family = $font->get_slug();
	}

	public function setup() {
		// Set downloadable & virtual
		$this->set_downloadable( true );
		$this->set_virtual( true );

		// Set regular price and sale price
		$this->setup_prices();

		// Set download link
		$this->setup_download_link();

		do_action( 'fontimator_variation_setup', $this, $var_attrs );
	}

	public function setup_download_link() {
		$weight_clean = Zipomator::get_clean_weight( $this->weight );

		$zip_file = new WC_Product_Download();
		$zip_file->set_id( wp_generate_uuid4() );
		$zip_file->set_name( Zipomator::single_name( $this->family, $weight_clean, $this->license ) . '.zip' );

		$download_url = Zipomator::get_url( $this->family, $weight_clean, $this->license );

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

		if ( '000-family' === $weight ) {
			$visible_weights = $font->get_visible_weights();
			$weight_count = count( $visible_weights );

			$price = $weight_count * $price;
			$sale_price = $price * floatval( $fontprice_ratios['family'] );
		} elseif ( '000-familybasic' === $weight ) {
			$weights = $font->get_familybasic_weights();

			$price = (count( $weights )) * $price;
			$sale_price = $price * floatval( $fontprice_ratios['family'] );
		}

		// Set prices
		$this->set_regular_price( ceil( $price / 5 ) * 5 );

		if ( $sale_price ) {
			$this->set_sale_price( ceil( $sale_price / 5 ) * 5 );
		}

		return $this->save();
	}

	public function get_weight() {
		return $this->weight;
	}

	public function get_license() {
		return $this->license;
	}

	public function get_family() {
		return $this->family;
	}


}
