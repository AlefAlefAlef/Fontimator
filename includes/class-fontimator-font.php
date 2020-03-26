<?php

/**
 * Class that extends WC_Product_Variable and adds additional Fontimator functionality.
 *
 * @link       https://alefalefalef.co.il
 * @since      2.0.0
 *
 * @package    Fontimator
 * @subpackage Fontimator/includes
 */

/**
 * Class that extends WC_Product_Variable and adds additional Fontimator functionality.
 *
 * @since      2.0.0
 * @package    Fontimator
 * @subpackage Fontimator/includes
 * @author     Reuven Karasik <rubik@karasik.org>
 */
class Fontimator_Font extends WC_Product_Variable {

	/**
	 * The variation's base price, as defined in ACF.
	 *
	 * @var int
	 */
	protected $base_price;

	public function __construct( $product ) {
		// if ( ! wc_get_product( $product ) || 'variable' !== wc_get_product( $product )->get_type() ) {
		// 	return false;
		// }
		parent::__construct( $product );

		$fontimator = Fontimator::get_instance();
		$this->base_price = $fontimator->get_acf()->get_field( 'font_price_base', $this->id );

	}

	/**
	 * Returns the font's base price, as defined in ACF.
	 *
	 * @return int $base_price
	 */
	public function get_base_price() {
		return $this->base_price;
	}

	/**
	 * Get the fontprice_ratios for this font
	 *
	 * @return array $fontprice_ratios The fontprice_ratios for this font
	 */
	public function get_fontprice_ratios() {
		$acf = Fontimator::get_instance()->get_acf();
		$global_ratios        = (array) $acf->get_field( 'fontprice_ratios', 'options' );
		$font_specific_ratios = (array) $acf->get_acf_field( 'fontprice_ratios', $this->id );
		$fontprice_ratios = array_merge(
			array_filter( $global_ratios ),
			array_filter( $font_specific_ratios )
		);
		return $fontprice_ratios;
	}

	/**
	 * Returns the weights included in familybasic, as defined in ACF.
	 */
	public function get_familybasic_weights( $return_format = 'id' ) {
		$acf = Fontimator::get_instance()->get_acf();
		$ids = $acf->get_field( 'familybasic_weights', $this->id );

		if ( 'slug' === $return_format ) {
			return array_map(
				function( $id ) {
						return get_term_by( 'id', $id, 'pa_' . FTM_WEIGHT_ATTRIBUTE )->slug;
				}, $ids
			);
		}

		if ( 'name' === $return_format ) {
			$weights = array_map(
				function( $id ) {
						return get_term_by( 'id', $id, 'pa_' . FTM_WEIGHT_ATTRIBUTE )->name;
				}, $ids
			);
		}

		return $ids;

	}

	/**
	 * Returns the weights defined as "archived" in ACF.
	 */
	public function get_archived_weights() {
		$acf = Fontimator::get_instance()->get_acf();
		return $acf->get_field( 'archived_weights', $this->id );
	}

	public function get_visible_weights( $return_format = 'id' ) {
		$weights = $this->get_attributes()[ 'pa_' . FTM_WEIGHT_ATTRIBUTE ];
		if ( ! $weights ) {
			return null;
		}
		if ( 'id' === $return_format ) {
			$all_weights = $weights['options'];
			$family_weights = array(
				get_term_by( 'slug', '000-family', 'pa_' . FTM_WEIGHT_ATTRIBUTE )->term_id,
				get_term_by( 'slug', '000-familybasic', 'pa_' . FTM_WEIGHT_ATTRIBUTE )->term_id,
			);
		} else {
			$all_weights = $weights->get_slugs();
			$family_weights = array( '000-family', '000-familybasic' );

		}

		$archived_weights = Fontimator::get_instance()->get_acf()->get_field( 'archived_weights', $this->id );
		if ( is_array( $archived_weights ) ) {
			$archived_weights = wp_list_pluck( $archived_weights, 'id' === $return_format ? 'term_id' : 'slug' );
		} else {
			$archived_weights = array();
		}

		$visible_weights = array_diff( $all_weights, $family_weights, $archived_weights );
		array_multisort( $visible_weights, SORT_ASC ); //sort weights by weight
		return $visible_weights;
	}

	/**
	 * Check whether this font has only one visible weight
	 *
	 * @return boolean
	 */
	public function is_single_weight() {
		return count( $this->get_visible_weights() ) < 2;
	}

	public function setup_variations() {
		$fontprice_ratios = $this->get_fontprice_ratios();
		$variations = array_map(
			function ( $variation_id ) {
					return new Fontimator_Font_Variation( $variation_id );
			}, $this->get_children()
		);
		$updated = 0;
		foreach ( $variations as $variation ) {
			if ( $variation->get_id() && 'yes' !== $variation->get_meta( '_fontimator_ignore' ) ) {
				$variation->setup();
				$updated++;
			}
		}

		return $updated;
	}

	public function generate_variations() {
		// Copied from woocommerce/includes/class-wc-ajax.php:658
		wc_set_time_limit( 0 );

		$attributes = wc_list_pluck( array_filter( $this->get_attributes(), 'wc_attributes_array_filter_variation' ), 'get_slugs' );

		if ( ! empty( $attributes ) ) {
			// Get existing variations so we don't create duplicates.
			$existing_variations = array_map( 'wc_get_product', $this->get_children() );
			$existing_attributes = array();

			foreach ( $existing_variations as $existing_variation ) {
				if ( $existing_variation ) {
					$existing_attributes[] = $existing_variation->get_attributes();
				}
			}

			$added               = 0;
			$possible_attributes = array_reverse( wc_array_cartesian( $attributes ) );

			foreach ( $possible_attributes as $possible_attribute ) {
				if ( in_array( $possible_attribute, $existing_attributes ) ) {
					continue;
				}
				$variation = new Fontimator_Font_Variation();
				$variation->set_parent_id( $post_id );
				$variation->set_attributes( $possible_attribute );

				do_action( 'product_variation_linked', $variation->save() );

				// Fontimator Setup for the freshly-created variation
				$variation->setup();

				if ( ( $added ++ ) > WC_MAX_LINKED_VARIATIONS ) {
					break;
				}
			}
		}

		$data_store = $this->get_data_store();
		$data_store->sort_all_product_variations( $this->get_id() );
		return $added;
	}


	/**
	 * Get the matching variation for a specific set of weight and license
	 *
	 * @param string $weight The required weight
	 * @param string $license The required license
	 * @return Fontimator_Font_Variation The matching variation.
	 */
	public function get_specific_variation( $weight, $license ) {
		$args = array(
			'attribute_pa_' . FTM_WEIGHT_ATTRIBUTE => $weight,
			'attribute_pa_' . FTM_LICENSE_ATTRIBUTE => $license,
		);

		$data_store = WC_Data_Store::load( 'product' );
		$variation_id = $data_store->find_matching_product_variation( $this, $args );

		if ( $variation_id ) {
			return new Fontimator_Font_Variation( $variation_id );
		}
		return null;
	}

	/**
	 * Get the correct variation for a specific membership license
	 *
	 * @param string $license The membership's license
	 * @return Fontimator_Font_Variation The correct variation.
	 */
	public function get_variation_for_membership( $license ) {
		if ( $this->is_single_weight() ) { // If no family
			$weight = reset( $this->get_visible_weights( 'slug' ) );
		} else {
			$weight = '000-family';
		}

		return $this->get_specific_variation( $weight, $license );
	}

}
