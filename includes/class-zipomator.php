<?php

/**
 * The Font Zipping functionality.
 *
 * @link       https://alefalefalef.co.il
 * @since      2.0.0
 *
 * @package    Zipomator
 * @subpackage Fontimator/Zipomator
 */

/**
 * The Font Zipping functionality.
 *
 * @since      2.0.0
 * @package    Zipomator
 * @subpackage Fontimator/Zipomator
 * @author     Reuven Karasik <rubik@karasik.org>
 */
class Zipomator {
	protected static $zip_endpoint = 'get-zip';
	protected static $variation_endpoint = 'download-font';
	protected static $eula_endpoint = 'get-eula';
	protected static $catalog_endpoint = 'get-catalog';
	protected static $nonce_action = 'zipomator_download';

	protected $specimen_filename_prefix;


	public function add_rewrite_rules() {
		$zip_endpoint = self::$zip_endpoint;
		$variation_endpoint = self::$variation_endpoint;
		$eula_endpoint = self::$eula_endpoint;
		if ( $zip_endpoint ) {
			add_rewrite_rule( "^{$zip_endpoint}/membership/(.*)$", 'index.php?zipomator_api=1&membership=1&license=$matches[1]', 'top' );
			add_rewrite_rule( "^{$zip_endpoint}/(.*)$", 'index.php?zipomator_api=1&packages=$matches[1]', 'top' );
		}
		if ( $variation_endpoint ) {
			add_rewrite_rule( "^{$variation_endpoint}/(.*)$", 'index.php?zipomator_api=1&variations=$matches[1]', 'top' );
		}
		if ( $eula_endpoint ) {
			add_rewrite_rule( "^{$eula_endpoint}/?(.*)$", 'index.php?zipomator_api=1&eula=$matches[1]', 'top' );
		}
		$catalog_endpoint = self::$catalog_endpoint;
		if ( $catalog_endpoint ) {
			add_rewrite_rule( "^{$catalog_endpoint}", 'index.php?zipomator_api=1&catalog=1', 'top' );
		}
	}

	public function query_vars( $query_vars ) {
		$query_vars[] = 'zipomator_api';
		$query_vars[] = 'packages';
		$query_vars[] = 'membership';
		$query_vars[] = 'catalog';
		$query_vars[] = 'license';
		$query_vars[] = 'variations';
		$query_vars[] = 'eula';
		return $query_vars;
	}

	public function parse_request( &$wp ) {
		if ( array_key_exists( 'zipomator_api', $wp->query_vars ) ) {
			if ( array_key_exists( 'membership', $wp->query_vars ) ) {
				$license = urldecode( $wp->query_vars['license'] );
				$this->serve_membership( $license );
				exit();
			}
			if ( array_key_exists( 'catalog', $wp->query_vars ) ) {
				$this->serve_catalog();
				exit();
			}
			if ( array_key_exists( 'packages', $wp->query_vars ) ) {
				$package_string = urldecode( $wp->query_vars['packages'] );
				$this->serve_package( $package_string );
				exit();
			}
			if ( array_key_exists( 'variations', $wp->query_vars ) ) {
				$variations = explode( ',', urldecode( $wp->query_vars['variations'] ) );
				$this->serve_variations( $variations );
				exit();
			}
			if ( array_key_exists( 'eula', $wp->query_vars ) ) {
				$eula = explode( ',', urldecode( $wp->query_vars['eula'] ) );
				$this->serve_eula( $eula );
				exit();
			}
			exit();
		}
		return;
	}

	public static function single_name( $family, $weight, $license, $version = null ) {
		return implode( '-', array_filter( array( $family, $weight, $license, $version ) ) );
	}



	public static function get_eula_url( $licenses ) {
		return home_url( self::$eula_endpoint . '/' . implode( ',', (array) $licenses ) );
		//return 'https://alefalefalef.co.il/' . self::$eula_endpoint . '/' . implode( ',', (array) $licenses );
	}

	public static function get_variation_endpoint() {
		return self::$variation_endpoint;
	}

	public static function get_nonce() {
		return wp_create_nonce( self::$nonce_action );
	}

	public static function zipomator_bundle_url( $url = '', $scheme = 'https' ) {
		$url = home_url( self::$zip_endpoint, $scheme ) . '/' . $url;
		return $url;
	}

	public static function zipomator_variation_url( $variations = array(), $scheme = 'relative' ) {
		$url = home_url( self::$variation_endpoint, $scheme ) . '/' . implode( ',', $variations );
		return wp_nonce_url( $url, self::$nonce_action );
	}

	public static function get_bundle_url( $family, $weight_clean, $license = false ) {
		$zip_endpoint = self::$zip_endpoint;
		if ( 'membership' === $family ) {
			$license = $license ?: $weight_clean;
			$url_params = urlencode( $license );
			return self::zipomator_bundle_url( 'membership/' . $url_params );
		}
		$url_params = urlencode( sprintf( '\{%s,%s,%s\}', $family, $weight_clean, $license ) );
		return self::zipomator_bundle_url( $url_params );
	}

	public static function get_clean_weight( $weight ) {
		$weight_parts = explode( '-', $weight, 2 );
		if ( count( $weight_parts ) > 1 ) {
			$weight_clean = $weight_parts[1];
			return $weight_clean;
		}
		return $weight;
	}

	public static function get_clean_license( $license ) {
		$license_parts = explode( '-', $license, 2 );
		if ( count( $license_parts ) > 1 ) {
			$license_clean = $license_parts[0];
			return $license_clean;
		}
		return $license;
	}

	public static function get_nonced_url( $variations ) {
		if ( ! is_array( $variations ) ) {
			$variations = [$variations];
		}
		return self::zipomator_variation_url( $variations );
	}

	public static function is_nonce_valid( $nonce = null ) {
		if ( ! $nonce ) {
			$nonce = $_REQUEST['_wpnonce'];
		}
		return wp_verify_nonce( $nonce, self::$nonce_action );
	}

	/**
	 * Serve the fonts catalog
	 */
	protected function serve_catalog() {
		$specimen_filename_prefix = Fontimator::acf()->get_field( 'specimen_filename_prefix', 'options' );
		$fonts = wc_get_products(
			array(
				'type' => 'variable',
				'paginate' => false,
				'limit' => -1,
			)
		);

		$files = array(
			FTM_FONTS_PATH . FTM_SITE_PREFIX . '-catalog-prefix.pdf',
		);

		foreach ( $fonts as $font ) {
			$font_slug = $font->get_slug();
			$file_path = FTM_FONTS_PATH . "{$font_slug}/{$specimen_filename_prefix}-{$font_slug}.pdf";
			$files[] = $file_path;
		}

		$files[] = FTM_FONTS_PATH . FTM_SITE_PREFIX . '-catalog-suffix.pdf';

		$month = strtolower( date( 'F' ) );
		$year = date( 'Y' );
		$pdf_nice_file_name = FTM_SITE_NAME . "-catalog-{$month}-{$year}";

		try {
			$pdf = new PDF_File( $files, $pdf_nice_file_name );
			$pdf->serve();
		} catch ( Exception $e ) {
			wp_die( $e->getMessage() );
		}
	}

	/**
	 * Serve the fonts package
	 *
	 * @param String $package_string A string of items. e.g. {bamberger,light,otf-12}{bamberger,regular,otf-12}{damka,regular,otf-12}
	 */
	protected function serve_package( $package_string ) {
		if ( ! isset( $package_string ) ) {
			wp_die(
				__(
					'Zipomator Error: no parameters were passed.
				Please make sure you are trying to access via the correct URL,
				and that your htaccess file is set up properly.', 'fontimator'
				)
			);
		}

		// Turn package format into variables
		preg_match_all( '{([a-z0-9,-]+)}', $package_string, $items );
		$items = $items[0];
		foreach ( $items as $key => $item ) {
			$items[ $key ] = explode( ',', $item );
			if ( count( $items[ $key ] ) !== 3 ) {
				// translators: This is (probably) the name of the invalid font.
				wp_die( sprintf( __( 'Zipomator Error: invalid amount of arguments in item: %s' ), $item ), 'fontimator' );
			}
		}

		// Verify user has access to download these fonts
		if ( ! $this->verify_package_access( $items ) ) {
			wp_safe_redirect( home_url() );
			exit();
		}

		// Create the package
		$font_package = new Zipomator_Font_Package( $items );
		if ( current_user_can( 'administrator' ) && ! $font_package->is_valid() ) {
			wp_die( 'Invalid input. Check the family, weights and license.' );
		}

		$zip_file = $font_package->make_zip();
		$zip_file->serve();
	}

/**
 * Verify user has access to download the requested package items
 *
 * @param array $items Array of items in format [family, weight, license]
 * @return bool True if user has access to all items
 */
protected function verify_package_access( $items ) {
	$log_file = WP_CONTENT_DIR . '/zipomator-access.log';
	$log = "\n=== verify_package_access called at " . date( 'Y-m-d H:i:s' ) . " ===\n";
	$log .= "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
	file_put_contents( $log_file, $log, FILE_APPEND );
	
	$user_id = get_current_user_id();
	$order_key = isset( $_REQUEST['order'] ) ? sanitize_text_field( $_REQUEST['order'] ) : null;
	$email = isset( $_REQUEST['email'] ) ? sanitize_email( $_REQUEST['email'] ) : null;
	
	if ( $user_id > 0 && ! $email ) {
		$user = get_userdata( $user_id );
		if ( $user ) {
			$email = $user->user_email;
			$log = "Extracted email from logged-in user: {$email}\n";
			file_put_contents( $log_file, $log, FILE_APPEND );
		}
	}
	
	$log = "Email: " . ( $email ?: 'NOT SET' ) . "\n";
	$log .= "User ID: " . $user_id . "\n";
	$log .= "Items count: " . count( $items ) . "\n";
	$log .= "Items: " . json_encode( $items ) . "\n";
	file_put_contents( $log_file, $log, FILE_APPEND );
	
	if ( $user_id === 0 && ( ! $order_key || ! $email ) ) {
		$log = "FAILED - No authentication (not logged in and missing order/email)\n";
		file_put_contents( $log_file, $log, FILE_APPEND );
		return false;
	}
	
	foreach ( $items as $index => $item ) {
		$log = "Checking item #{$index}: " . json_encode( $item ) . "\n";
		file_put_contents( $log_file, $log, FILE_APPEND );
		
		list( $family, $weight, $license ) = $item;
		
		$font_post = get_page_by_path( $family, OBJECT, 'product' );
		if ( ! $font_post ) {
			$log = "FAILED - Font post not found for family: {$family}\n";
			file_put_contents( $log_file, $log, FILE_APPEND );
			return false;
		}
		
		$log = "Found font post ID: {$font_post->ID}\n";
		file_put_contents( $log_file, $log, FILE_APPEND );
		
		$font = new Fontimator_Font( $font_post->ID );
		if ( ! $font || $font->get_slug() !== $family ) {
			$log = "FAILED - Invalid font or slug mismatch. Font slug: " . ( $font ? $font->get_slug() : 'NULL' ) . ", Expected: {$family}\n";
			file_put_contents( $log_file, $log, FILE_APPEND );
			return false;
		}
		
		$log = "Font loaded successfully. Slug: {$family}\n";
		$log .= "Looking for variation with weight: '{$weight}', license: '{$license}'\n";
		file_put_contents( $log_file, $log, FILE_APPEND );
		
		$product = wc_get_product( $font_post->ID );
		if ( ! $product ) {
			$log = "FAILED - Could not load WooCommerce product\n";
			file_put_contents( $log_file, $log, FILE_APPEND );
			return false;
		}
		
		$variation_ids = $product->get_children();
		$log = "Found " . count( $variation_ids ) . " variations for product {$font_post->ID}\n";
		file_put_contents( $log_file, $log, FILE_APPEND );
		
		$variation_id = null;
		foreach ( $variation_ids as $vid ) {
			$variation_product = wc_get_product( $vid );
			if ( ! $variation_product ) {
				continue;
			}
			
			$attributes = $variation_product->get_variation_attributes();
			
			$matches_weight = false;
			$matches_license = false;
			
			foreach ( $attributes as $attr_key => $attr_value ) {
				if ( strpos( $attr_key, 'weight' ) !== false ) {
					$clean_attr_weight = self::get_clean_weight( $attr_value );
					if ( $clean_attr_weight === $weight ) {
						$matches_weight = true;
					}
				}
				if ( strpos( $attr_key, 'license' ) !== false && $attr_value === $license ) {
					$matches_license = true;
				}
			}
			
			if ( $matches_weight && $matches_license ) {
				$variation_id = $vid;
				$log = "MATCH FOUND! Variation ID: {$vid} with attributes: " . json_encode( $attributes ) . "\n";
				file_put_contents( $log_file, $log, FILE_APPEND );
				break;
			}
		}
		
		if ( ! $variation_id ) {
			$log = "FAILED - No matching variation found for weight: '{$weight}', license: '{$license}'\n";
			file_put_contents( $log_file, $log, FILE_APPEND );
			return false;
		}
		
		$log = "Using variation ID: {$variation_id}\n";
		file_put_contents( $log_file, $log, FILE_APPEND );
		
		$has_access = false;
		
		if ( $user_id > 0 ) {
			$log = "Checking logged-in user access for variation {$variation_id}\n";
			file_put_contents( $log_file, $log, FILE_APPEND );
			$has_access = $this->user_has_variation_access( $user_id, $variation_id );
			$log = "User access result: " . ( $has_access ? 'TRUE' : 'FALSE' ) . "\n";
			file_put_contents( $log_file, $log, FILE_APPEND );
			
			if ( ! $has_access ) {
				$log = "Checking all user orders for variation {$variation_id}\n";
				file_put_contents( $log_file, $log, FILE_APPEND );
				$has_access = $this->user_has_variation_in_any_order( $user_id, $email, $variation_id );
				$log = "User orders check result: " . ( $has_access ? 'TRUE' : 'FALSE' ) . "\n";
				file_put_contents( $log_file, $log, FILE_APPEND );
			}
		} elseif ( $order_key && $email ) {
			$log = "Checking guest access for variation {$variation_id}\n";
			file_put_contents( $log_file, $log, FILE_APPEND );
			$has_access = $this->guest_has_variation_access( $order_key, $email, $variation_id );
			$log = "Guest access result: " . ( $has_access ? 'TRUE' : 'FALSE' ) . "\n";
			file_put_contents( $log_file, $log, FILE_APPEND );
		}
		
		// Check if user purchased the complete family (000-family) for this license
		if ( ! $has_access ) {
			$log = "Checking 000-family access for license: '{$license}'\n";
			file_put_contents( $log_file, $log, FILE_APPEND );

			$family_variation = $font->get_specific_variation( '000-family', $license );
			if ( $family_variation ) {
				$family_variation_id = $family_variation->get_id();
				$log = "Found 000-family variation ID: {$family_variation_id}\n";
				file_put_contents( $log_file, $log, FILE_APPEND );

				$has_access = $this->check_variation_access( $user_id, $email, $order_key, $family_variation_id );
				$log = "000-family access result: " . ( $has_access ? 'TRUE' : 'FALSE' ) . "\n";
				file_put_contents( $log_file, $log, FILE_APPEND );
			} else {
				$log = "No 000-family variation found for this font+license\n";
				file_put_contents( $log_file, $log, FILE_APPEND );
			}
		}

		// Check if user purchased familybasic (000-familybasic) and requested weight is included
		if ( ! $has_access ) {
			$log = "Checking 000-familybasic access for license: '{$license}'\n";
			file_put_contents( $log_file, $log, FILE_APPEND );

			$familybasic_variation = $font->get_specific_variation( '000-familybasic', $license );
			if ( $familybasic_variation ) {
				$familybasic_weights = $font->get_familybasic_weights( 'slug' );
				$clean_familybasic_weights = array_map( array( 'Zipomator', 'get_clean_weight' ), $familybasic_weights );
				$log = "Found 000-familybasic variation. Included weights: " . json_encode( $clean_familybasic_weights ) . "\n";
				file_put_contents( $log_file, $log, FILE_APPEND );

				if ( in_array( $weight, $clean_familybasic_weights, true ) ) {
					$familybasic_variation_id = $familybasic_variation->get_id();
					$has_access = $this->check_variation_access( $user_id, $email, $order_key, $familybasic_variation_id );
					$log = "000-familybasic access result: " . ( $has_access ? 'TRUE' : 'FALSE' ) . "\n";
					file_put_contents( $log_file, $log, FILE_APPEND );
				} else {
					$log = "Weight '{$weight}' is not included in familybasic weights\n";
					file_put_contents( $log_file, $log, FILE_APPEND );
				}
			} else {
				$log = "No 000-familybasic variation found for this font+license\n";
				file_put_contents( $log_file, $log, FILE_APPEND );
			}
		}

		if ( ! $has_access ) {
			$log = "FAILED - No access to variation {$variation_id} (also checked family/familybasic)\n";
			file_put_contents( $log_file, $log, FILE_APPEND );
			return false;
		}

		$log = "Item #{$index} access granted\n";
		file_put_contents( $log_file, $log, FILE_APPEND );
	}
	
	$log = "All items verified - ACCESS GRANTED\n";
	file_put_contents( $log_file, $log, FILE_APPEND );
	return true;
}

/**
 * Check if user has purchased a variation in any of their orders
 *
 * @param int $user_id User ID
 * @param string $email User email
 * @param int $variation_id Variation ID
 * @return bool
 */
protected function user_has_variation_in_any_order( $user_id, $email, $variation_id ) {
	$log_file = WP_CONTENT_DIR . '/zipomator-access.log';
	
	// Get all customer orders
	$customer_orders = wc_get_orders( array(
		'customer_id' => $user_id,
		'limit'       => -1,
		'status'      => array( 'completed', 'processing' ),
	) );
	
	$log = "Found " . count( $customer_orders ) . " orders for user {$user_id}\n";
	file_put_contents( $log_file, $log, FILE_APPEND );
	
	foreach ( $customer_orders as $order ) {
		$log = "Checking order #" . $order->get_id() . " (status: " . $order->get_status() . ")\n";
		file_put_contents( $log_file, $log, FILE_APPEND );
		
		// Check if order contains this variation
		foreach ( $order->get_items() as $item ) {
			$item_variation_id = $item->get_variation_id();
			$log = "  Checking item variation ID: {$item_variation_id}\n";
			file_put_contents( $log_file, $log, FILE_APPEND );
			
			if ( (int) $item_variation_id === (int) $variation_id ) {
				$log = "  MATCH FOUND in order #" . $order->get_id() . "\n";
				file_put_contents( $log_file, $log, FILE_APPEND );
				return true;
			}
		}
	}
	
	$log = "No matching orders found for variation {$variation_id}\n";
	file_put_contents( $log_file, $log, FILE_APPEND );
	return false;
}

	/**
	 * Check if logged-in user has access to a variation
	 *
	 * @param int $user_id User ID
	 * @param int $variation_id Variation ID
	 * @return bool
	 */
	protected function user_has_variation_access( $user_id, $variation_id ) {
		$downloads = wc_get_customer_available_downloads( $user_id );
		foreach ( $downloads as $download ) {
			if ( (int) $download['product_id'] === (int) $variation_id ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if guest user has access via order key and email
	 *
	 * @param string $order_key Order key
	 * @param string $email Email address
	 * @param int $variation_id Variation ID
	 * @return bool
	 */
	protected function guest_has_variation_access( $order_key, $email, $variation_id ) {
		global $wpdb;

		$order_id = $wpdb->get_var( $wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} 
			WHERE meta_key = '_order_key' AND meta_value = %s 
			LIMIT 1",
			$order_key
		) );

		if ( ! $order_id ) {
			return false;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return false;
		}

		if ( strtolower( $order->get_billing_email() ) !== strtolower( $email ) ) {
			return false;
		}

		if ( ! in_array( $order->get_status(), array( 'wc-completed', 'wc-processing' ) ) ) {
			return false;
		}

		foreach ( $order->get_items() as $item ) {
			if ( (int) $item->get_variation_id() === (int) $variation_id ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if a user (logged-in or guest) has access to a specific variation
	 *
	 * @param int $user_id User ID (0 if guest)
	 * @param string|null $email Email address
	 * @param string|null $order_key Order key (for guest access)
	 * @param int $variation_id Variation ID to check
	 * @return bool
	 */
	protected function check_variation_access( $user_id, $email, $order_key, $variation_id ) {
		if ( $user_id > 0 ) {
			$has_access = $this->user_has_variation_access( $user_id, $variation_id );
			if ( ! $has_access ) {
				$has_access = $this->user_has_variation_in_any_order( $user_id, $email, $variation_id );
			}
			return $has_access;
		} elseif ( $order_key && $email ) {
			return $this->guest_has_variation_access( $order_key, $email, $variation_id );
		}
		return false;
	}

	protected function get_membership_items( $license ) {
		$fonts = Fontimator_Query::get_catalog_fonts();

		if ( ! count( $fonts ) ) {
			wp_die( __( 'Zipomator Error: No fonts found. Are you sure you have WooCommerce products active?', 'fontimator' ) );
		}

		$items = array();
		foreach ( $fonts as $product_id ) {
			$font = new Fontimator_Font( $product_id );
			$font_weights = $font->get_visible_weights( 'slug' );
			foreach ( $font_weights as $weight ) {
				$items[] = array( $font->get_slug(), self::get_clean_weight( $weight ), $license );
			}
		}

		return $items;

	}

	/**
	 * Serve the membership package
	 *
	 * @param String $license The membership license
	 */
	protected function serve_membership( $license ) {
		if ( ! isset( $license ) ) {
			wp_die(
				__(
					'Zipomator Error: no membership license was passed.
					Please make sure you are trying to access via the correct URL,
					and that your htaccess file is set up properly.', 'fontimator'
				)
			);
		}

		$items = $this->get_membership_items( $license );

		// Create the package
		$font_package = new Zipomator_Font_Package( $items );
		if ( current_user_can( 'administrator' ) && ! $font_package->is_valid() ) {
			wp_die( 'Invalid input. Check the family, weights and license.' );
		}

		$font_package->make_zip()->serve();

	}

	/**
	 * Serve the downloads for the variations
	 *
	 * @param Array $variations An array of variation IDs
	 */
	protected function serve_variations( $variations ) {
		if ( ! isset( $variations ) ) {
			wp_die(
				__(
					'Zipomator Error: no variations were passed.
					Please make sure you are trying to access via the correct URL,
					and that your htaccess file is set up properly.', 'fontimator'
				)
			);
		}

		if ( ! current_user_can( 'administrator' ) && ! self::is_nonce_valid() ) {
			// Redirect to downloads page.
			// TRANSLATORS: URL to the downloads page
			wp_die( sprintf( __( 'Invalid download link. Go to your <a href="%s">Downloads Page</a> to download the font.', 'fontimator' ), esc_url( wc_get_endpoint_url( 'downloads' ) ) ) );
		}

		$items = array();
		foreach ( $variations as $variation_id ) {
			$product_variation = wc_get_product( $variation_id );
			$variation_type = $product_variation->get_type();
			switch ( $variation_type ) {
				case 'variation':
					$variation = new Fontimator_Font_Variation( $product_variation );

					if ( ! $variation || ! $variation->get_family() ) {
						// TRANSLATORS: %s is the variation ID
						wp_die( sprintf( __( 'Zipomator Error: Invalid download link. (%s)', 'fontimator' ), $variation_id ) );
					}

					$family = $variation->get_family();
					$weight = self::get_clean_weight( $variation->get_weight() );
					$license = $variation->get_license();
					$version = $variation->get_version();
					$items[] = array( $family, $weight, $license, $version );
					break;

				case 'subscription_variation':
					$var_attrs = $product_variation->get_variation_attributes();
					$license = $var_attrs[ 'attribute_pa_' . FTM_LICENSE_ATTRIBUTE ];
					if ( ! $license ) {
						// TRANSLATORS: %s is the variation ID
						wp_die( sprintf( __( 'Zipomator Error: Invalid download link. (%s)', 'fontimator' ), $variation_id ) );
					}

					$items = array_merge( $items, $this->get_membership_items( $license ) );
					break;

				default:
					// TRANSLATORS: %s is the variation ID
					wp_die( sprintf( __( 'Zipomator Error: Invalid download link. (%s)', 'fontimator' ), $variation_id ) );
					break;
			}
		}

		// Create the package
		$font_package = new Zipomator_Font_Package( $items );
		if ( current_user_can( 'administrator' ) && ! $font_package->is_valid() ) {
			wp_die( 'Invalid input. Check the family, weights and license.' );
		}

		$zip_file = $font_package->make_zip();
		$zip_file->serve();
	}

	/**
	 * Serve the EULA for specified licenses
	 *
	 * @param Array $licenses An array of license types
	 */
	protected function serve_eula( $licenses ) {
		$eula = new Zipomator_EULA( $licenses );
		$eula->file();
	}

}
