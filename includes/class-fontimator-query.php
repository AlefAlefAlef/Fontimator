<?php

/**
 * Fontimator WP_Query function aliases
 *
 * @link       https://alefalefalef.co.il
 * @since      2.0.0
 *
 * @package    Fontimator
 * @subpackage Fontimator/includes
 */

/**
 * Fontimator WP_Query function aliases
 *
 * @since      2.0.0
 * @package    Fontimator
 * @subpackage Fontimator/includes
 * @author     Reuven Karasik <rubik@karasik.org>
 */
class Fontimator_Query {

	/**
	 * Get the *IDs* of weights that are on sale, excluding family and familybasic
	 *
	 * Family and Familybasic are always "on sale". This is to get manually-defined sales.
	 *
	 * @return int the ID of a WC_Product_Variation / Fontimator_Font_Variation
	 */
	public static function on_sale_weights() {
		$data_store = WC_Data_Store::load( 'product' );
		$on_sale_variations = wp_list_pluck( $data_store->get_on_sale_products(), 'id' );
		$on_sale_weights_query = new WP_Query(
			array(
				'post_type' => 'product_variation',
				'post_status'       => 'publish',
				'post__in'          => $on_sale_variations,
				'posts_per_page'    => -1,
				'meta_query' => array(
					array(
						'key' => 'attribute_pa_' . FTM_WEIGHT_ATTRIBUTE,
						'compare'    => 'NOT IN',
						'value'    => array( '000-family', '000-familybasic' ),
					),
				),
				// Waiting for an answer here: https://wordpress.stackexchange.com/questions/319176/query-child-posts-with-tax-query-on-parents
				// 'tax_query' => array(
				// 	array(
				// 		'taxonomy' => 'product_cat',
				// 		'field'    => 'slug',
				// 		'terms'    => array( 'archive', 'membership' ),
				// 		'operator' => 'NOT IN',
				// 	),
				// ),
				'fields' => 'ids',
			)
		);

		return $on_sale_weights_query;
	}

}
