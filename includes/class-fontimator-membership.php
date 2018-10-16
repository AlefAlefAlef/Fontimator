<?php

/**
 * Class that extends WC_Subscription and adds additional Fontimator functionality.
 *
 * @link       https://alefalefalef.co.il
 * @since      2.0.0
 *
 * @package    Fontimator
 * @subpackage Fontimator/includes
 */

/**
 * Class that extends WC_Subscription and adds additional Fontimator functionality.
 *
 * @since      2.0.0
 * @package    Fontimator
 * @subpackage Fontimator/includes
 * @author     Reuven Karasik <rubik@karasik.org>
 */
class Fontimator_Membership extends WC_Subscription {

	/**
	 * The purchased membership's license.
	 *
	 * @var bool
	 */
	protected $license = false;

	public function __construct( $order ) {
		// if ( ! is_a( $order, 'WC_Subscription' ) ) {
		// 	return false;
		// }
		parent::__construct( $order );
	}

	/**
	 * Returns the purchased membership's variation ID.
	 *
	 * @return int $variation_id
	 */
	public function get_variation_id() {
		$membership_order_items = $this->get_items();
		if ( count( $membership_order_items ) ) {
			$membership_order_item = reset( $membership_order_items );
			return $membership_order_item->get_variation_id();
		}

		return false;
	}

	/**
	 * Returns the purchased membership's license.
	 *
	 * @return int $license
	 */
	public function get_license() {
		if ( ! $this->license ) {
			$variation_id = $this->get_variation_id();
			if ( $variation_id ) {
				$variation = wc_get_product( $variation_id );
				$this->license = $variation->get_variation_attributes()[ 'attribute_pa_' . FTM_LICENSE_ATTRIBUTE ];
			}
		}

		return $this->license;
	}

	/**
	 * Is this subscription in trial mode?
	 *
	 * @return boolean
	 */
	public function is_in_trial() {
		if ( ! $this->get_date( 'trial_end' ) ) {
			return false;
		}
		$trial_end = date_create( $this->get_date( 'trial_end' ) );
		return date_diff( $trial_end, date_create() )->format( '%r%a' ) < 0;
	}

	/**
	 * Can the user cancel this membership?
	 *
	 * @return boolean
	 */
	public function can_cancel() {
		if ( $this->is_in_trial() ) {
			return true;
		}

		$date = $this->get_date( 'date_created' );
		$date = explode( ' ', $date )[0];
		$date = date_create_from_format( 'Y-m-d', $date );
		// $date = date_create_from_format("d/m/Y", '06/9/2017');
		$diff = date_diff( $date, date_create() );
		$diff_m = $diff->m + ($diff->y * 12);

		return $diff_m >= 11;
	}



}
