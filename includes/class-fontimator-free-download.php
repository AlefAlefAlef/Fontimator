<?php
/**
 * A Free Download File.
 *
 * @since      2.2.0
 * @package    Fontimator
 * @subpackage Fontimator/includes
 * @author     Reuven Karasik <rubik@karasik.org>
 */
class Fontimator_Free_Download {

	public static $db_table_name = 'ftm_free_downloads';

	private $download_id,
			$download_name,
			$download_url;

	public function __construct( $download_id ) {

		$acf = Fontimator::get_instance()->get_acf();
		$downloads = $acf->get_field( 'ftm_free_downloads', 'options' );

		$download = null;
		foreach ( $downloads as $download_i ) {
			if ( $download_id == $download_i['download_id'] ) {
				$download = $download_i;
				break;
			}
		}

		$this->download_id = $download_id;
		$this->download_name = $download['download_name'];
		$this->download_url = $download['download_url'];

	}

	public function get_name() {
		return $this->download_name;
	}

	public function get_url() {
		return FTM_FONTS_URL . $this->download_url;
	}

	public function has_downloaded( $email ) {
		global $wpdb;

		$table_name = $wpdb->prefix . self::$db_table_name;

		$results = $wpdb->query(
			$wpdb->prepare(
				"
				SELECT id FROM $table_name
				WHERE download_id = %s
				AND user_email = %s
				",
				$this->download_id, $email
			)
		);

		return $results > 0;
	}

	public function register_download( $name, $email ) {
		global $wpdb;

		if ( ! $this->has_downloaded( $email ) ) {
			$table_name = $wpdb->prefix . self::$db_table_name;

			$wpdb->insert(
				$table_name,
				array(
					'download_id' => $this->download_id,
					'user_name' => $name,
					'user_email' => $email,
					'time' => current_time( 'mysql' ),
				)
			);
		}

	}


}

