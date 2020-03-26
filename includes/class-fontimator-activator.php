<?php

/**
 * Fired during plugin activation
 *
 * @link       https://alefalefalef.co.il
 * @since      2.0.0
 *
 * @package    Fontimator
 * @subpackage Fontimator/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      2.0.0
 * @package    Fontimator
 * @subpackage Fontimator/includes
 * @author     Reuven Karasik <rubik@karasik.org>
 */
class Fontimator_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    2.0.0
	 */
	public static function activate() {
		self::install_free_downloads_db();
	}


	private static function install_free_downloads_db() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'ftm_free_downloads';

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					download_id tinytext NOT NULL,
					user_name text DEFAULT '',
					user_email text NOT NULL,
					time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
					PRIMARY KEY  (id)
				) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}


}
