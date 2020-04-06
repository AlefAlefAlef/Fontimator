<?php

/**
 * Class that handles all MailChimp-related integrations.
 *
 * @since      2.4.0
 * @package    Fontimator
 * @subpackage Fontimator/includes
 * @author     Reuven Karasik <rubik@karasik.org>
 */
class Fontimator_MC {

  /**
   * Instance of MC4WP_MailChimp
   *
   * @var MC4WP_MailChimp
   */
  protected $mc4wp_mailchimp;

  /**
   * The selected MailChimp main list ID
   *
   * @var string
   */
  protected $main_list;

  function __construct() {
    if ($this->enabled()) {
      $this->mc4wp_mailchimp = new MC4WP_MailChimp();
    }
  }

  public function set_private_config() {
    $acf = Fontimator::acf();
    $this->main_list = $acf->get_field('ftm_main_list');
    $this->academic_list = $acf->get_field('ftm_academic_list');
    $this->gender_field = $acf->get_field('ftm_gender_merge_field');
    $this->subscribed_field = $acf->get_field('ftm_subscribed_merge_field');
  }

  public function enabled() {
    return class_exists('MC4WP_MailChimp');
  }

  public function get_academic_list() {
    return $this->academic_list;
  }

  /**
   * Filters an ACF field to display all MailChimp lists as options
   *
   * @since 2.4.0
   * @param array $field
   * @return array $field
   */
  public function populate_acf_field_with_mailchimp_lists( $acf_field ) {
    $choices = array();
    $lists = $this->mc4wp_mailchimp->get_lists();

    if ( count($lists) ) {
      foreach ( $lists as $list ) {
        $choices[$list->id] = $list->name;
      }
    } else {
      $choices[] = __( '(no lists found, configure MC4WP first)', 'fontimator' );
    }
    
    $acf_field['choices'] = $choices;
    return $acf_field;
  }

  /**
   * Filters an ACF field to display all MailChimp Merge Fields from the main list as options
   *
   * @since 2.4.0
   * @param array $field
   * @return array $field
   */
  public function populate_acf_field_with_mailchimp_merge_fields( $acf_field ) {
    $choices = array();
    $fields = $this->mc4wp_mailchimp->get_list_merge_fields($this->main_list);

    if ( ! $this->main_list ) {
      $choices[] = __( '(Please save the page first)', 'fontimator' );
    } else if ( ! count($fields) ) {
      $choices[] = __( '(no fields found, configure MC4WP first)', 'fontimator' );
    } else {
      foreach ( $fields as $field ) {
        $choices[$field->tag] = sprintf( '%s (%s)', $field->name, $field->tag);
      }
    }
    
    $acf_field['choices'] = $choices;
    return $acf_field;
  }

  /**
	 * Get all merge fields of a user, or false if not subscribed
	 *
	 * @param string $list_id or null for main list
	 * @param string $user_email or null for current user
	 * @return stdClass
	 */
	public function get_user_merge_fields( $list_id = null, $user_email = null ) {
		if ( ! is_user_logged_in() && ! $user_email ) {
			return null;
		}

		if ( ! $user_email ) {
			$user_email = strtolower( wp_get_current_user()->user_email );
		}

		if ( ! $list_id ) {
			$list_id = $this->main_list;
		}

		$api = mc4wp_get_api_v3();
		try {
			$member = $api->get_list_member( $list_id, $user_email );
			if ( $member ) {
				return $member->merge_fields;
			}
			
		} catch (\Throwable $th) {
			return false;
		}

		return null;
	}

  /**
	 * Gets the gender of a user, based on the mailchimp MERGE field
	 *
	 * @param string $user_email (or null for current user)
	 * @return Fontimator_I18n::GENDER The value in the list, or neutral if doesn't exist
	 */
	public function get_user_gender( $user_email = null ) {
		if ( ! $this->gender_field ) {
			return Fontimator_I18n::GENDER_NEUTRAL;
    }
    
    
    $merge_fields = $this->get_user_merge_fields( $this->main_list, $user_email );
		if ( $merge_fields ) {
      $mailchimp_gender = $merge_fields->{$this->gender_field};
			if ( $mailchimp_gender == __( 'Man', 'fontimator' ) ) {
				return Fontimator_I18n::GENDER_MALE;
			} else if ( $mailchimp_gender == __( 'Woman', 'fontimator' ) ) {
				return Fontimator_I18n::GENDER_FEMALE;
			}
    }
    
		return Fontimator_I18n::GENDER_NEUTRAL;
  }

  /**
	 * Sets the gender of a user, on a mailchimp MERGE field
	 *
	 * @param int $new_gender
	 * @param string $user_email or empty for current user
	 * @return bool Success
	 */
	public function update_user_gender( $new_gender, $user_email = null ) {
		if ( ! $gender_field = $this->gender_field ) {
			return null;
		}

		switch ($new_gender) {
			case Fontimator_I18n::GENDER_MALE:
				$mailchimp_gender = __( 'Man', 'fontimator' );
				break;
			
			case Fontimator_I18n::GENDER_FEMALE:
				$mailchimp_gender = __( 'Woman', 'fontimator' );
				break;
			
			default:
				return false;
				break;
		}

		return $this->set_user_merge_field( $gender_field, $mailchimp_gender );
	}

	/**
	 * Set a user's merge field values
	 *
	 * @param string $field_name
	 * @param mixed $field_newval
	 * @param string $user_email If null, set to current user email
	 * @param string $list_id If null, set to main list id
	 * @return bool Success
	 */
	public function set_user_merge_field( $field_name, $field_newval, $user_email = null, $list_id = null ) {
		if ( ! $list_id ) {
			$list_id = $this->main_list;
		}

		if ( ! $user_email ) {
			$user_email = strtolower( wp_get_current_user()->user_email );
		}

		try {
			$api = mc4wp_get_api_v3();
			$api->update_list_member( $list_id, $user_email, array(
				'merge_fields' => array(
					$field_name => $field_newval
				),
			) );
		} catch (\Throwable $th) {
			return false;
		}
		return true;
	}
  
  /**
   * Check if a user is subscribed to a list
   *
   * @param string $user_email or empty for current user
   * @param string $list_id or empty for main list
   * @return boolean
   */
  public function is_user_subscribed( $user_email = null, $list_id = null ) {
		if ( ! $user_email ) {
			$user_email = strtolower( wp_get_current_user()->user_email );
		}

		if ( ! $list_id ) {
			$list_id = $this->main_list;
		}

		$api = mc4wp_get_api_v3();
		try {
			$member = $api->get_list_member( $list_id, $user_email );
			if ( $member && $member->status === 'subscribed' ) {
				return true;
			}
			
		} catch (\Throwable $th) {
			return false;
		}

		return false;
	}
}