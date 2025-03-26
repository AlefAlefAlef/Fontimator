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
		$acf = Fontimator::acf();
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
		$acf = Fontimator::acf();
		$ids = $acf->get_field( 'familybasic_weights', $this->id );

		// Ensure $ids is an array
		if ( ! is_array( $ids ) ) {
			$ids = [];
		}

		if ( 'slug' === $return_format ) {
			return array_map(
				function( $id ) {
					$term = get_term_by( 'id', $id, 'pa_' . FTM_WEIGHT_ATTRIBUTE );
					return $term ? $term->slug : null;
				},
				$ids
			);
		}

		if ( 'name' === $return_format ) {
			return array_map(
				function( $id ) {
					$term = get_term_by( 'id', $id, 'pa_' . FTM_WEIGHT_ATTRIBUTE );
					return $term ? $term->name : null;
				},
				$ids
			);
		}

		return $ids;
	}

	/**
	 * Returns the weights defined as "archived" in ACF.
	 */
	public function get_archived_weights() {
		$acf = Fontimator::acf();
		return $acf->get_field( 'archived_weights', $this->id );
	}

	public function get_visible_weights( $return_format = 'id', $include_family = false ) {
		$weights = $this->get_attributes()[ 'pa_' . FTM_WEIGHT_ATTRIBUTE ];
		if ( ! $weights ) {
			return null;
		}
		if ( 'id' === $return_format ) {
			$all_weights = $weights['options'];
            $family = get_term_by( 'slug', '000-family', 'pa_' . FTM_WEIGHT_ATTRIBUTE );
            $familybasic = get_term_by( 'slug', '000-familybasic', 'pa_' . FTM_WEIGHT_ATTRIBUTE );
            if(isset($family->term_id)) {
                $family_weights[] = $family->term_id;
            }
            if(isset($familybasic->term_id)) {
                $family_weights[] = $familybasic->term_id;
            }
		} else {
			$all_weights = $weights->get_slugs();
			$family_weights = array( '000-family', '000-familybasic' );

		}

		$archived_weights = Fontimator::acf()->get_field( 'archived_weights', $this->id );
		if ( is_array( $archived_weights ) ) {
			$archived_weights = wp_list_pluck( $archived_weights, 'id' === $return_format ? 'term_id' : 'slug' );
		} else {
			$archived_weights = array();
		}

		$visible_weights = array_diff( $all_weights, $archived_weights );
		if ( ! $include_family ) {
			$visible_weights = array_diff( $visible_weights,  $family_weights );
		}
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

		$attributes = wc_list_pluck( array_filter( $this->get_attributes() ?? [], 'wc_attributes_array_filter_variation' ), 'get_slugs' );

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


/**
 * Retrieves fonts purchased by a user with detailed variation information.
 *
 * This function queries WooCommerce orders to find fonts purchased by a specified user,
 * filtering out subscriptions and free products. It calculates variation counts based
 * on unique weights and identifies if a font family package was purchased.
 *
 * @param mixed $user_identifier User ID (int) or email (string). Defaults to null (current user).
 * @param int|null $font_id Optional specific font ID to filter results.
 * @return array Associative array containing:
 *     - success (bool): Operation status
 *     - message (string): Status message
 *     - fonts (array): Array of font data with variations
 *     - font (array, optional): Single font data if font_id is specified
 */
function get_user_purchased_fonts($user_identifier = null, $font_id = null) {
    global $wpdb;
    if (!$user_identifier) {
        return format_response(false, 'Invalid user identifier provided.');
    }

    // Fetch order items
    $order_items = fetch_user_order_items($wpdb, $user_identifier);
    if (empty($order_items)) {
        return format_response(true, 'No orders found for this user.', []);
    }

    // Process purchased fonts
    $purchased_fonts = process_order_items($wpdb, $order_items, $font_id);

    // Handle specific font ID case when no purchases found
    if ($font_id !== null && empty($purchased_fonts)) {
        $purchased_fonts = handle_unpurchased_font($wpdb, $font_id, $purchased_fonts);
    }

    // Prepare final response
    $response = format_response(true, count($purchased_fonts) . ' fonts found.', $purchased_fonts);
    if ($font_id !== null && !empty($purchased_fonts)) {
        $response['font'] = $purchased_fonts[0];
    }

    return $response;
}

/**
 * Fetches order items for a user from the database.
 *
 * @param wpdb $wpdb WordPress database object.
 * @param int $user_id User ID.
 * @return array Array of order item objects.
 */
function fetch_user_order_items($wpdb, $user_id) {
    $query = "
        SELECT 
            p.ID as order_id,
            p.post_date,
            woi.order_item_id,
            woi.order_item_name,
            MAX(CASE WHEN woim.meta_key = '_product_id' THEN woim.meta_value END) as product_id,
            MAX(CASE WHEN woim.meta_key = '_variation_id' THEN woim.meta_value END) as variation_id
        FROM 
            {$wpdb->posts} p
        JOIN 
            {$wpdb->prefix}woocommerce_order_items woi ON p.ID = woi.order_id
        JOIN 
            {$wpdb->prefix}woocommerce_order_itemmeta woim ON woi.order_item_id = woim.order_item_id
        WHERE 
            p.post_type = 'shop_order'
            AND p.post_status IN ('wc-completed', 'wc-processing')
            AND woi.order_item_type = 'line_item'
            AND p.ID IN (
                SELECT post_id FROM {$wpdb->postmeta} 
                WHERE meta_key = '_customer_user' AND meta_value = %d
            )
        GROUP BY 
            p.ID, woi.order_item_id
        ORDER BY 
            p.post_date DESC
    ";

    return $wpdb->get_results($wpdb->prepare($query, $user_id));
}

/**
 * Processes order items into structured font data.
 *
 * @param wpdb $wpdb WordPress database object.
 * @param array $order_items Array of order item objects.
 * @param int|null $font_id Optional font ID filter.
 * @return array Array of processed font data.
 */
function process_order_items($wpdb, $order_items, $font_id) {
    $purchased_fonts = [];
    $processed_font_ids = [];
    $all_variations_count = [];

    foreach ($order_items as $item) {
        $product_id = (int) $item->product_id;
        $variation_id = (int) ($item->variation_id ?: 0);

        if ($font_id !== null && $product_id !== $font_id) {
            continue;
        }

        $product_post = get_post($product_id);
        if (!$product_post || is_product_excluded($wpdb, $product_id, $variation_id)) {
            continue;
        }

        $variation_data = $variation_id > 0 ? process_variation($wpdb, $variation_id, $product_post, $item) : null;
        $all_variations_count[$product_id] = $all_variations_count[$product_id] ?? calculate_total_variations($wpdb, $product_id);

        $existing_font_index = array_search($product_id, $processed_font_ids);
        if ($existing_font_index !== false && $variation_data) {
            update_existing_font($purchased_fonts[$existing_font_index], $variation_data);
        } else {
            $purchased_fonts[] = create_new_font_data($product_id, $item, $variation_data, $all_variations_count[$product_id]);
            $processed_font_ids[] = $product_id;
        }
    }

    foreach ($purchased_fonts as &$font) {
        unset($font['purchased_weights']);
    }
    unset($font);

    return $purchased_fonts;
}

/**
 * Checks if a product should be excluded (subscription or free).
 *
 * @param wpdb $wpdb WordPress database object.
 * @param int $product_id Product ID.
 * @param int $variation_id Variation ID.
 * @return bool True if product should be excluded.
 */
function is_product_excluded($wpdb, $product_id, $variation_id) {
    $is_subscription = $wpdb->get_var($wpdb->prepare(
        "SELECT meta_key FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = '_subscription_period' LIMIT 1",
        $product_id
    ));

    $price = get_post_meta($variation_id > 0 ? $variation_id : $product_id, '_price', true);
    return $is_subscription || $price === '' || floatval($price) == 0;
}

/**
 * Processes variation data for a given variation ID.
 *
 * @param wpdb $wpdb WordPress database object.
 * @param int $variation_id Variation ID.
 * @param WP_Post $product_post Parent product post object.
 * @param stdClass $item Order item data.
 * @return array|null Variation data array or null if invalid.
 */
function process_variation($wpdb, $variation_id, $product_post, $item) {
    $variation_post = get_post($variation_id);
    if (!$variation_post) {
        return null;
    }

    $attributes = fetch_variation_attributes($wpdb, $variation_id);
    $variation_name = generate_variation_name($attributes, $product_post, $item, $variation_post);

    return [
        'id' => $variation_id,
        'name' => $variation_name,
        'item_name' => $item->order_item_name,
        'attributes' => $attributes ?: null
    ];
}

/**
 * Fetches variation attributes from post meta.
 *
 * @param wpdb $wpdb WordPress database object.
 * @param int $variation_id Variation ID.
 * @return array Associative array of attributes.
 */
function fetch_variation_attributes($wpdb, $variation_id) {
    $attributes_query = $wpdb->prepare(
        "SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key LIKE 'attribute_%%'",
        $variation_id
    );
    $results = $wpdb->get_results($attributes_query);

    $attributes = [];
    foreach ($results as $attr) {
        $attr_name = str_replace('attribute_', '', $attr->meta_key);
        $attributes[$attr_name] = $attr->meta_value;
    }
    return $attributes;
}

/**
 * Generates a readable variation name from attributes.
 *
 * @param array $attributes Variation attributes.
 * @param WP_Post $product_post Parent product post object.
 * @param stdClass $item Order item data.
 * @param WP_Post $variation_post Variation post object.
 * @return string Generated variation name.
 */
function generate_variation_name($attributes, $product_post, $item, $variation_post) {
    if (!empty($attributes)) {
        $attr_values = array_map(function ($key, $value) {
            $clean_key = str_replace('pa_', '', $key);
            if (strpos($key, 'pa_') === 0 && is_numeric($value)) {
                $term = get_term($value, $key);
                $value = $term && !is_wp_error($term) ? $term->name : $value;
            }
            return ucfirst(str_replace('-', ' ', $value));
        }, array_keys($attributes), $attributes);
        return implode(' - ', $attr_values);
    }

    $name = str_replace($product_post->post_title . ' - ', '', $variation_post->post_title);
    if ($name === $product_post->post_title) {
        $name = str_replace($product_post->post_title . ' - ', '', $item->order_item_name);
    }
    if (empty($name) || $name === $product_post->post_title) {
        $name = $item->order_item_name;
        if (strpos($name, $product_post->post_title) === 0) {
            $name = trim(str_replace($product_post->post_title, '', $name), ' -');
        }
    }
    return empty($name) || $name === $product_post->post_title ? 'Variation #' . $variation_post->ID : $name;
}

/**
 * Calculates total available variations excluding family packages.
 *
 * @param wpdb $wpdb WordPress database object.
 * @param int $product_id Product ID.
 * @return int Number of unique non-family variations.
 */
function calculate_total_variations($wpdb, $product_id) {
    $query = $wpdb->prepare(
        "SELECT p.ID, pm.meta_value 
         FROM {$wpdb->posts} p
         JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'attribute_pa_weight'
         WHERE p.post_parent = %d 
         AND p.post_type = 'product_variation'
         AND p.post_status = 'publish'",
        $product_id
    );

    $variations = $wpdb->get_results($query);
    $weight_groups = [];
    foreach ($variations as $var) {
        if (strpos($var->meta_value, 'family') === false) {
            $weight_groups[$var->meta_value] = true;
        }
    }
    return count($weight_groups);
}

/**
 * Updates existing font data with new variation.
 *
 * @param array &$font_data Existing font data array.
 * @param array $variation_data New variation data.
 */
function update_existing_font(&$font_data, $variation_data) {
    $variation_exists = false;
    foreach ($font_data['variations'] as $existing) {
        if ($existing['id'] === $variation_data['id']) {
            $variation_exists = true;
            break;
        }
    }

    if (!$variation_exists) {
        $font_data['variations'][] = $variation_data;
        update_variation_counts($font_data, $variation_data['attributes'] ?? []);
    }
}

/**
 * Creates new font data structure.
 *
 * @param int $product_id Product ID.
 * @param stdClass $item Order item data.
 * @param array|null $variation_data Initial variation data.
 * @param int $total_variations Total available variations.
 * @return array New font data array.
 */
function create_new_font_data($product_id, $item, $variation_data, $total_variations) {
    $font_data = [
        'id' => $product_id,
        'name' => get_post($product_id)->post_title,
        'permalink' => get_permalink($product_id),
        'date' => $item->post_date,
        'order_id' => $item->order_id,
        'variations' => $variation_data ? [$variation_data] : [],
        'total_font_variations_purchased' => 0,
        'total_font_variations' => $total_variations,
        'purchased_weights' => [],
        'purchased_font_family' => false
    ];

    if ($variation_data && !empty($variation_data['attributes'])) {
        update_variation_counts($font_data, $variation_data['attributes']);
    }

    return $font_data;
}

/**
 * Updates variation counts and family flag based on attributes.
 *
 * @param array &$font_data Font data array to update.
 * @param array $attributes Variation attributes.
 */
function update_variation_counts(&$font_data, $attributes) {
    $weight_value = $attributes['pa_weight'] ?? 'default';
    $is_family = strpos($weight_value, 'family') !== false;

    if ($is_family) {
        $font_data['purchased_font_family'] = true;
    } elseif (!isset($font_data['purchased_weights'][$weight_value])) {
        $font_data['purchased_weights'][$weight_value] = true;
        $font_data['total_font_variations_purchased']++;
    }
}

/**
 * Handles case where specific font ID is requested but not purchased.
 *
 * @param wpdb $wpdb WordPress database object.
 * @param int $font_id Font ID.
 * @param array $purchased_fonts Current fonts array.
 * @return array Updated fonts array.
 */
function handle_unpurchased_font($wpdb, $font_id, $purchased_fonts) {
    $product_post = get_post($font_id);
    if (!$product_post) {
        return $purchased_fonts;
    }

    $is_subscription = $wpdb->get_var($wpdb->prepare(
        "SELECT meta_key FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = '_subscription_period' LIMIT 1",
        $font_id
    ));
    $price = get_post_meta($font_id, '_price', true);

    if (!$is_subscription && $price !== '' && floatval($price) > 0) {
        $purchased_fonts[] = [
            'id' => $font_id,
            'name' => $product_post->post_title,
            'permalink' => get_permalink($font_id),
            'purchased' => false,
            'variations' => [],
            'total_font_variations_purchased' => 0,
            'purchased_font_family' => false
        ];
    }

    return $purchased_fonts;
}

/**
 * Formats the response array.
 *
 * @param bool $success Operation success status.
 * @param string $message Response message.
 * @param array $fonts Font data array.
 * @return array Formatted response.
 */
function format_response($success, $message, $fonts = []) {
    return [
        'success' => $success,
        'message' => $message,
        'fonts' => $fonts
    ];
}