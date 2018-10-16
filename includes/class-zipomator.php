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
	protected static $catalog_endpoint = 'get-catalog';

	protected $specimen_filename_prefix;

	public function __construct( $specimen_filename_prefix ) {
		// $acf = Fontimator::get_instance();
		// $this->specimen_filename_prefix = $acf->get_field( 'specimen_filename_prefix', 'options' );
		$this->specimen_filename_prefix = $specimen_filename_prefix;
	}

	public function add_rewrite_rules() {
		$zip_endpoint = self::$zip_endpoint;
		if ( $zip_endpoint ) {
			add_rewrite_rule( "^{$zip_endpoint}/membership/(.*)$", 'index.php?zipomator_api=1&membership=1&license=$matches[1]', 'top' );
			add_rewrite_rule( "^{$zip_endpoint}/(.*)$", 'index.php?zipomator_api=1&packages=$matches[1]', 'top' );
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
			$package_string = urldecode( $wp->query_vars['packages'] );
			$this->serve_package( $package_string );
			exit();
		}
		return;
	}

	public static function single_name( $family, $weight, $license ) {
		return implode( '-', [ $family, $weight, $license ] );
	}

	public static function zipomator_url( $url = '', $scheme = 'relative' ) {
		return home_url( self::$zip_endpoint, $scheme ) . '/' . $url;
	}

	public static function get_bundle_url( $family, $weight_clean, $license = false ) {
		$zip_endpoint = self::$zip_endpoint;
		if ( 'membership' === $family ) {
			$license = $license ?: $weight_clean;
			$url_params = urlencode( $license );
			return self::zipomator_url( 'membership/' . $url_params );
		}
		$url_params = urlencode( sprintf( '\{%s,%s,%s\}', $family, $weight_clean, $license ) );
		return self::zipomator_url( $url_params );
	}

	public static function get_clean_weight( $weight ) {
		$weight_parts = explode( '-', $weight );
		if ( count( $weight_parts ) > 1 ) {
			$weight_clean = $weight_parts[1];
			return $weight_clean;
		}
		return $weight;
	}

	public static function get_url_for_variation( $variation_id ) {
		$variation = new WC_Product_Variation( $variation_id );
		$var_attrs = $variation->get_variation_attributes();

		$product_id = $variation->get_parent_id();
		$product    = new WC_Product( $product_id );

		$family = $product->get_slug();
		if ( count( $var_attrs ) < 2 ||
			! isset( $var_attrs[ 'attribute_pa_' . FTM_WEIGHT_ATTRIBUTE ] ) ||
			! isset( $var_attrs[ 'attribute_pa_' . FTM_LICENSE_ATTRIBUTE ] ) ) {
			return false;
		}
		$weight = self::get_clean_weight( $var_attrs[ 'attribute_pa_' . FTM_WEIGHT_ATTRIBUTE ] );
		$license = $var_attrs[ 'attribute_pa_' . FTM_LICENSE_ATTRIBUTE ];

		return self::get_bundle_url( $family, $weight, $license );
	}

	/**
	 * Serve the fonts catalog
	 */
	protected function serve_catalog() {
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
			$file_path = FTM_FONTS_PATH . "{$font_slug}/{$this->specimen_filename_prefix}-{$font_slug}.pdf";
			$files[] = $file_path;
		}

		$files[] = FTM_FONTS_PATH . FTM_SITE_PREFIX . '-catalog-suffix.pdf';

		try {
			$pdf = new PDF_File( $files, FTM_SITE_PREFIX . '-catalog' );
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
			wp_die(__('Zipomator Error: no parameters were passed. 
				Please make sure you are trying to access via the correct URL,
				and that your htaccess file is set up properly.', 'fontimator'));
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

		// Create the package
		$font_package = new Zipomator_Font_Package( $items );
		if ( current_user_can( 'administrator' ) && ! $font_package->is_valid() ) {
			wp_die( 'Invalid input. Check the family, weights and license.' );
		}

		$zip_file = $font_package->make_zip();
		$zip_file->serve();
	}

	/**
	 * Serve the membership package
	 *
	 * @param String $license The membership license
	 */
	protected function serve_membership( $license ) {
		if ( ! isset( $license ) ) {
			wp_die( __( 'Zipomator Error: no membership license was passed. 
				Please make sure you are trying to access via the correct URL,
				and that your htaccess file is set up properly.', 'fontimator' ) );
		}

		// Look for items
		$archives = wc_get_products(
			array(
				'type' => 'variable',
				'paginate' => false,
				'limit' => -1,
				'category' => array( 'archive' ),
				'return' => 'ids',
			)
		);

		$fonts = wc_get_products(
			array(
				'type' => 'variable',
				'paginate' => false,
				'limit' => -1,
				'exclude' => $archives,
			)
		);

		if ( ! count( $fonts ) ) {
			wp_die( __( 'Zipomator Error: No fonts found. Are you sure you have WooCommerce products active?', 'fontimator' ) );
		}

		$items = array();
		foreach ( $fonts as $product ) {
			$font = new Fontimator_Font( $product->get_ID() );
			$font_weights = $font->get_visible_weights( 'slug' );
			foreach ( $font_weights as $weight ) {
				$items[] = array( $font->get_slug(), self::get_clean_weight( $weight ), $license );
			}
		}

		// Create the package
		$font_package = new Zipomator_Font_Package( $items );
		if ( current_user_can( 'administrator' ) && ! $font_package->is_valid() ) {
			wp_die( 'Invalid input. Check the family, weights and license.' );
		}

		$font_package->make_zip()->serve();

	}


}

