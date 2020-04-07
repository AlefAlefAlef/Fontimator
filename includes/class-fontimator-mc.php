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
    $this->subscribe_groups = $acf->get_field('ftm_subscribe_groups');
    $this->academic_group = $acf->get_field('ftm_academic_group');
    $this->interest_groups = $acf->get_field('ftm_interests_groups');
    $this->gender_field = $acf->get_field('ftm_gender_merge_field');
  }

  public function get_subscription_tag( $format = 'array' ) {
    $tag = Fontimator::acf()->get_field('ftm_subscription_tag_obj');
    if ( $tag ) {
      switch ( $format ) {
        case 'label':
        case 'name':
          return $tag[ 'label' ];
        break;

        case 'value':
        case 'id':
          return $tag[ 'value' ];
        break;

        case 'array':
        default:
          return $tag;
        break;  
      }
    }

    return null;
  }

  public function enabled() {
    return class_exists('MC4WP_MailChimp');
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
   * Filters an ACF field to display all MailChimp interest groups from the main list as options
   *
   * @since 2.4.2
   * @param array $field
   * @return array $field
   */
  public function populate_acf_field_with_mailchimp_groups( $acf_field ) {
    $choices = array();
    $groups = $this->mc4wp_mailchimp->get_list_interest_categories($this->main_list);
    if ( ! $this->main_list ) {
      $choices[] = __( '(Please save the page first)', 'fontimator' );
    } else if ( ! count($groups) ) {
      $choices[] = __( '(no groups found, configure MC4WP first)', 'fontimator' );
    } else {
      foreach ( $groups as $group_category ) {
        foreach ($group_category->interests as $id => $group) {
          $choices[$id] = sprintf( '%s >> %s', $group_category->title, $group);
        }
      }
    }
    
    $acf_field['choices'] = $choices;
    return $acf_field;
  }

  /**
   * Filters an ACF field to display all MailChimp interest group categories from the main list as options
   *
   * @since 2.4.2
   * @param array $field
   * @return array $field
   */
  public function populate_acf_field_with_mailchimp_group_categories( $acf_field ) {
    $choices = array();
    $groups = $this->mc4wp_mailchimp->get_list_interest_categories($this->main_list);
    if ( ! $this->main_list ) {
      $choices[] = __( '(Please save the page first)', 'fontimator' );
    } else if ( ! count($groups) ) {
      $choices[] = __( '(no groups found, configure MC4WP first)', 'fontimator' );
    } else {
      foreach ( $groups as $group_category ) {
        $choices[$group_category->id] = sprintf( '%s (%s)', $group_category->title, implode( ', ', array_values( $group_category->interests ) ));
      }
    }
    
    $acf_field['choices'] = $choices;
    return $acf_field;
  }

  /**
   * Filters an ACF field to display all MailChimp Tags from the main list as options
   *
   * @since 2.4.2
   * @param array $field
   * @return array $field
   */
  public function populate_acf_field_with_mailchimp_tags( $acf_field ) {
    $choices = array();
    $tags = mc4wp_get_api_v3()->get_list_segments( $this->main_list, array(
      'type' => 'static',
      'count' => 1000,
    ) )->segments;
    if ( ! $this->main_list ) {
      $choices[] = __( '(Please save the page first)', 'fontimator' );
    } else if ( ! count( (array) $tags ) ) {
      $choices[] = __( '(no tags found, configure MC4WP first)', 'fontimator' );
    } else {
      foreach ( $tags as $tag ) {
        // Using tag->name here because API only accepts names and not IDs for tags
        $choices[$tag->id] = $tag->name; // Must be the name without any additions because it is saved and used.
      }
    }
    
    $acf_field['choices'] = $choices;
    return $acf_field;
  }

  /**
   * Adds the subscriber to the defined groups
   *
   * @param MC4WP_MailChimp_Subscriber $subscriber
   * @return MC4WP_MailChimp_Subscriber
   */
  public function add_subscriber_to_groups( MC4WP_MailChimp_Subscriber $subscriber ) {
    foreach ( (array) $this->subscribe_groups as $group_id ) {
      $subscriber->interests[ $group_id ] = true;
    }
    return $subscriber;
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
	 * Get all tags of a user, or false if not subscribed
	 *
	 * @param string $list_id or null for main list
	 * @param string $user_email or null for current user
	 * @return array<stdClass>
	 */
	public function get_user_tags( $list_id = null, $user_email = null ) {
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
			$member = $api->get_list_member_tags( $list_id, $user_email );
			if ( $member ) {
				return $member->tags;
			}
			
		} catch (\Throwable $th) {
			return false;
		}

		return null;
	}
  
  /**
	 * Get all tags of a user, or false if not subscribed
	 *
	 * @param string $list_id or null for main list
	 * @param string $user_email or null for current user
	 * @return array<stdClass>
	 */
	public function get_user_groups( $list_id = null, $user_email = null ) {
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
        $result = array();
        foreach ( $member->interests as $interest_id => $active ) {
          if ( $active ) {
            $result[] = $interest_id;
          }
        }
        return $result;
			}
			
		} catch (\Throwable $th) {
      return false;
		}
    
		return null;
	}


  /**
	 * Checks if user has the academic tag
	 *
	 * @param string $user_email (or null for current user)
	 * @return bool
	 */
	public function get_academic_license_year( $user_email = null ) {
		if ( ! $this->academic_group ) {
      return false;
    }

    // Find academic group cat in all groups
    foreach ( (array) $this->mc4wp_mailchimp->get_list_interest_categories($this->main_list) as $group_cat ) {
      if ( $group_cat->id == $this->academic_group ) {
        $academic_group_cat = $group_cat;
        break;
      }
    }

    if ( ! $academic_group_cat ) {
      return false;
    }

    $academic_groups = $academic_group_cat->interests;
    
    $groups = $this->get_user_groups( $user_email );
		foreach ( (array) $groups as $group ) {
      if ( in_array( $group, array_keys( (array) $academic_groups ) ) ) {
        return $academic_groups[ $group ];
      }
    }
    
		return false;
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
  
  
  /**
   * Set the user groups
   *
   * @return boolean
   */
  public function update_user_groups( $groups_to_change, $list_id = null, $user_email = null ) {
    if ( ! $list_id ) {
      $list_id = $this->main_list;
    }

    if ( ! $user_email ) {
      $user_email = strtolower( wp_get_current_user()->user_email );
    }

    try {
      $api = mc4wp_get_api_v3();
      $api->update_list_member( $list_id, $user_email, array(
        'interests' => $groups_to_change,
      ) );
    } catch (\Throwable $th) {
      return false;
    }
    return true;
  }
  
  /**
   * Switch a user tag on or off
   *
   * @param string $tag_name The actual name of the tag (notice: not the ID)
   * @param boolean $new_status
   * @param string $user_email or null for current user
   * @param string $list_id or null for main list
   * @return boolean
   */
  public function set_user_tag( $tag_name, $new_status, $user_email = null, $list_id = null ) {
    if ( ! $list_id ) {
      $list_id = $this->main_list;
    }

    if ( ! $user_email ) {
      $user_email = strtolower( wp_get_current_user()->user_email );
    }

    try {
      $api = mc4wp_get_api_v3();
      $api->update_list_member_tags( $list_id, $user_email, array(
        'tags' => array(
          array( 
            'name' => $tag_name,
            'status' => $new_status ? 'active' : 'inactive',
          ),
        )
      ) );
    } catch (\Throwable $th) {
      return false;
    }
    return true;
  }
  


  /**
	 * Set subscriber status
	 *
	 * @param string $email
	 * @param bool $new_status
	 * @return bool Success
	 */
	public function set_subscription_tag( $email, $new_status ) {
    $tag_name = $this->get_subscription_tag( 'name' );
		if ($tag_name) {
      return $this->set_user_tag( $tag_name, $new_status, $email );
    }
    return false;
	}

  /**
   * Fires on any subscription status change to update the MC tag
   *
   * @param WC_Subscription $subscription
   * @param string $new_status
   * @param string $old_status
   * @return void
   */
  public function update_subscription_status( $subscription, $new_status, $old_status ) {
    $email = $subscription->get_billing_email();
		if ( 'active' === $new_status ) {
			$merge_code = 'on';
			$success = $this->set_subscription_tag( $email, true );
		} else {
			$merge_code = 'off';
			$success = $this->set_subscription_tag( $email, false );
		}

  
    if ( ! $success ) {
      WC_Admin_Notices::add_custom_notice(
        "ftm_sync_subscription_error_{$email}",
        // TRANSLATORS: %$1s: User email, %2$s: new status
        sprintf( __( 'ERROR: Fontimator could not set the appropriate merge fields for user %1$s to %2$s', 'fontimator' ), $email, $merge_code )
      );
    }
  }

  /**
   * Update subscribers in bulk on a segment or tag
   *
   * @param string $tag_id
   * @param array $members_to_add
   * @param array $members_to_remove
   * @param string $list_id or null for the main list
   * @return array counts of the removed & added emails
   */
  public function bulk_update_tag_subscribers( $tag_id, $members_to_add = array(), $members_to_remove = array(), $list_id = null ) {
    if ( ! $list_id ) {
      $list_id = $this->main_list;
    }

    try {
      $resource = sprintf( '/lists/%s/segments/%s', $list_id, $tag_id );
      $data = array(
        'members_to_add' => $members_to_add,
        'members_to_remove' => $members_to_remove,
      );
      $response = mc4wp_get_api_v3()->get_client()->post( $resource, $data );
      
      function map_member_to_email( $member ) {
        return $member->email_address;
      }

      return array(
        'added' => array_map( 'map_member_to_email', $response->members_added ),
        'removed' => array_map( 'map_member_to_email', $response->members_removed ),
      );
    } catch (\Throwable $th) {
      return $th;
    }
		return true;
  }
}