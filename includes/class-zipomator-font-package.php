<?php

/**
 * The Font Bundling functionality.
 *
 * @link       https://alefalefalef.co.il
 * @since      2.0.0
 *
 * @package    Zipomator
 * @subpackage Fontimator/Zipomator
 */

/**
 * The Font Bundling functionality.
 *
 * @since      2.0.0
 * @package    Zipomator
 * @subpackage Fontimator/Zipomator
 * @author     Reuven Karasik <rubik@karasik.org>
 */
class Zipomator_Font_Package {

	private $_items, $_file_list, $_zip_file;

	/**
	 * Font Package
	 * @param array $items An array of all font packages bought.
	 */
	function __construct( $items = [] ) {
		foreach ( $items as $item ) {
			list( $family, $weight, $license ) = $item;
			if ( 'web-reseller' === $license ) { // Add desktop 1-2 to reseller license
				$items[] = array( $family, $weight, 'otf-web-reseller' );
			}
		}
		$this->_items = $items;
	}

	private function get_server_path( $type = 'font', $family = '', $format = 'otf', $weight = 'regular', $license = 'app' ) {

		if ( 'ttf' === $format && 'web' !== $license && 'variable' != $weight ) {
			$office_postfix = '-OFFICE';
		} else {
			$office_postfix = '';
		}

		$site_prefix = FTM_SITE_PREFIX;

		switch ( $type ) {
			case 'font':
				return FTM_FONTS_PATH . "{$family}/{$family}-{$weight}-{$site_prefix}{$office_postfix}.{$format}";
				break;

			case 'license':
				// return FTM_FONTS_PATH . "/_licenses/{$license}.txt";
				if ( 'otf-web-reseller' === $license ) {
					return Zipomator::get_eula_url( array( 'otf', 'web', 'reseller' ) );
				} elseif ( 'web-reseller' === $license ) {
					return;
				}
				return Zipomator::get_eula_url( array( self::simplify_license( $license ) ) );
				break;

			case 'thumbnail':
				return get_the_post_thumbnail_url( self::get_font_post( $family ) );
				break;

			case 'specimen':
				return FTM_FONTS_PATH . "{$family}/specimen.pdf";
				break;

			case 'poster':
				return FTM_FONTS_PATH . "{$family}/poster.pdf";
				break;

			default:
				return false;
				break;
		}
	}

	public function is_valid() {
		if ( ! $this->_items ) {
			return false;
		}
		if ( count( $this->_items ) < 1 ) {
			return false;
		}
		foreach ( $this->_items as $item ) {
			if ( count( $item ) < 3 ) { // Can be either 3 or 4, depending on whether or not font version
				return false;
			}

			// Don't check for files actually, just for item validity
			// list( $family, $weight, $license ) = $item;
			// $font_id = self::get_font_post( $family )->ID;
			// $font = new Fontimator_Font( $font_id );

			// if ( 'family' === $weight ) {
			// 	$weight = reset( $font->get_visible_weights( 'slug' ) );
			// } elseif ( 'familybasic' === $weight ) {
			// 	$weight = reset( $font->get_familybasic_weights( 'slug' ) );
			// }

			// $weight = Zipomator::get_clean_weight( $weight );

			// $file_path = $this->get_server_path( 'font', $family, 'ttf', $weight, 'web' );
			// if ( ! file_exists( $file_path ) ) {
			// 	/* TRANSLATORS: %1\$s: Font Name, %2\$s: File Format, %3\$s: Font Weight */
			// 	echo sprintf( __( "Font file doesn't exist for %1\$s, %2\$s, %3\$s. Please contact us and we'll solve this problem in no-time :)", 'fontimator' ), $item[0], 'ttf', $weight );
			// 	return false;
			// }
		}

		return true;
	}

	public function generate_file_list() {
		$file_list = [];
		$already_included_licenses = [];
		$site_name = FTM_SITE_NAME;
		$site_prefix = FTM_SITE_PREFIX;

		foreach ( $this->_items as $item ) {
			list( $family, $weight, $license ) = $item;
			$font_id = self::get_font_post( $family )->ID;
			$font = new Fontimator_Font( $font_id );

			// Whole family trick
			if ( 'family' === $weight ) {
				$weights = $font->get_visible_weights( 'slug' );
				foreach ( $weights as $key => $i_weight ) {
					$weights[ $key ] = Zipomator::get_clean_weight( $i_weight );

					if ( 'family' === $i_weight || 'familybasic' === $i_weight || 'variable' === $i_weight ) {
						unset( $weights[ $key ] );
					}
				}
			} elseif ( 'familybasic' === $weight ) {
				$weights = $font->get_familybasic_weights( 'slug' );
				foreach ( $weights as $key => $i_weight ) {
					$weights[ $key ] = Zipomator::get_clean_weight( $i_weight );

					if ( 'family' === $i_weight || 'familybasic' === $i_weight ) {
						unset( $weights[ $key ] );
					}
				}
			} else {
				$weights = [ $weight ];
			}

			$clean_license = self::simplify_license( $license );

			$local_path_prefix = FTM_SITE_PREFIX . '-fonts/';
			if ( count( $this->_items ) > 1 ) {
				$local_path_prefix .= "{$family}/";
			}

			foreach ( $weights as $weight ) {
				// If weight is full, just use ZIP.
				if ( 'full' === $weight ) {
					$file_path = $this->get_server_path( 'font', $family, 'zip', 'full', $clean_license );
					$local_path = $local_fontfiles_path_prefix . "{$family}-{$weight}-{$site_prefix}.zip";
					$file_list[] = [ $file_path, $local_path ];
					continue;
				}

				if ( 'web' === $clean_license ) {
					$local_misc_path_prefix = $local_path_prefix;
					$local_fontfiles_path_prefix = $local_path_prefix . 'webfont_files/';

					// Add font files
					// $web_formats = array( 'eot', 'ttf', 'woff', 'woff2' ); // OLD
					$web_formats = array( 'eot', 'woff', 'woff2' );
					foreach ( $web_formats as $format ) {
						$file_path = $this->get_server_path( 'font', $family, $format, $weight, 'web' );
						$local_path = $local_fontfiles_path_prefix . "{$family}-{$weight}-{$site_prefix}.{$format}";
						$file_list[] = [ $file_path, $local_path ];
					}
				} elseif ( 'variable' === $weight ) {
					$local_fontfiles_path_prefix = $local_path_prefix;
					$local_misc_path_prefix = $local_path_prefix . '_misc/';

					// Add font files
					$file_path = $this->get_server_path( 'font', $family, 'ttf', $weight, 'web' );
					$local_path = $local_fontfiles_path_prefix . "{$family}-{$weight}-{$site_prefix}.ttf";
					$file_list[] = [ $file_path, $local_path ];
				} else {
					$local_misc_path_prefix = $local_path_prefix . '_misc/';
					$local_fontfiles_path_prefix = $local_path_prefix;

					// Add font files
					$file_path = $this->get_server_path( 'font', $family, 'otf', $weight, $license );
					$local_path = $local_fontfiles_path_prefix . "{$family}-{$weight}-{$site_prefix}.otf";
					$file_list[] = [ $file_path, $local_path ];

					// Add MS Office files
					$file_path = $this->get_server_path( 'font', $family, 'ttf', $weight, $license );
					$local_path = $local_misc_path_prefix . "_for microsoft office users/{$family}-{$weight}-{$site_prefix}.ttf";
					$file_list[] = [ $file_path, $local_path ];
				}
			}

			// Add misc files
			// link-to-xxx.url
			$font_url = get_permalink( self::get_font_post( $family ) );
			$local_path = $local_misc_path_prefix . "link-to-{$site_name}.url";
			$file_list[] = [ false, "[InternetShortcut]\nURL={$font_url}", $local_path ];

			// how-to-use.url
			if ( 'web' === $clean_license ) {
				$guide_url = 'https://alefalefalef.co.il/webfont-guide/?source=Zipomator&font=' . $family;
				$local_path = $local_misc_path_prefix . 'how-to-use.url';
				$file_list[] = [ false, "[InternetShortcut]\nURL={$guide_url}", $local_path ];
			}

			// xxx-font-license-aaa.txt
			$local_path = $local_misc_path_prefix . "{$license}-font-license-{$site_prefix}.html";
			if ( ! in_array( $local_path, $already_included_licenses ) ) {
				$file_path = $this->get_server_path( 'license', false, false, false, $license );

				if ( $file_path ) {
					$license_html_content = file_get_contents(
						$file_path, false, stream_context_create(
							array(
								'ssl' => array(
									'verify_peer' => false,
									'verify_peer_name' => false,
								),
							)
						)
					);
					if ( $license_html_content ) {
						$file_list[] = [ false, $license_html_content, $local_path ];
						$already_included_licenses[] = $local_path;
					}
				}
			}

			// poster, thumbnail, specimen
			$file_path = $this->get_server_path( 'thumbnail', $family );
			$local_path = $local_path_prefix . "thumbnail-{$family}.png";
			if ( $file_path ) {
				if ( file_exists( $file_path ) ) {
					$file_list[] = [ false, file_get_contents( $file_path ), $local_path ];
				}
			}

			$file_path = $this->get_server_path( 'poster', $family );
			$local_path = $local_misc_path_prefix . "poster-{$family}.pdf";
			$file_list[] = [ $file_path, $local_path ];

			$file_path = $this->get_server_path( 'specimen', $family );
			$local_path = $local_misc_path_prefix . "{$family}-specimen-a4.pdf";
			$file_list[] = [ $file_path, $local_path ];
		}

		$this->_file_list = $file_list;
		return $file_list;
	}

	public function make_zip() {
		$this->generate_file_list();
		$nice_filename = FTM_SITE_PREFIX . '-fonts';
		if ( count( $this->_items ) < 2 ) {
			$single = $this->_items[0];
			$nice_filename = Zipomator::single_name( $single[0], $single[1], self::simplify_license( $single[2] ), $single[3] );
		};
		$this->_zip_file = new Zip_File( $this->_file_list, $nice_filename );
		return $this->_zip_file;
	}


	public static function get_font_post( $family = '' ) {
		return get_page_by_path( $family, OBJECT, 'product' );
	}

	public static function simplify_license( $license ) {
		if ( strpos( $license, '-' ) > -1 ) {
			return explode( '-', $license, 2 )[0];
		}
		return $license;
	}

	public static function get_weights_for_family( $family ) {
		$product = new Fontimator_Font( self::get_font_post( $family ) );
		$weights = $product->get_attributes()[ 'pa_' . FTM_WEIGHT_ATTRIBUTE ]->get_slugs();
		return $weights;
	}
}
