<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://alefalefalef.co.il
 * @since      2.0.0
 *
 * @package    Fontimator
 * @subpackage Fontimator/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      2.0.0
 * @package    Fontimator
 * @subpackage Fontimator/includes
 * @author     Reuven Karasik <rubik@karasik.org>
 */
class Fontimator_I18n {

	const GENDER_NEUTRAL = 0,
	      GENDER_MALE = 1,
	      GENDER_FEMALE = 2;

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    2.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'fontimator',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}

	/**
	 * Get the appropriate string based on the current user's gender, or the neutral if gender is unknown
	 *
	 * @param string $nuetral
	 * @param string $male
	 * @param string $female
	 * @return string
	 */
	public static function genderize_string( $nuetral, $male = null, $female = null ) {
		switch ( self::get_user_gender() ) {
			case self::GENDER_MALE:
				if ( $male ) return $male;
				break;
			case self::GENDER_FEMALE:
				if ( $female ) return $female;
				break;
			}
			
		return $nuetral;
	}

	/**
	 * Gets the user gender and returns the appropriate enum constant
	 *
	 * @param string $user_email
	 * @return int
	 */
	public static function get_user_gender( $user_email = null ) {
		if ( Fontimator::mc()->enabled() ) {
			return Fontimator::mc()->get_user_gender( $user_email );
		}
		return self::GENDER_NEUTRAL;
	}

	public static function is_abroad_user( $ip_address = '', $homeland = 'IL' ) {
		$location = WC_Geolocation::geolocate_ip( $ip_address, true, false );
		if ( $location['country'] && $location['country'] != $homeland ) {
			return true;
		}

		return false;
	}

}
