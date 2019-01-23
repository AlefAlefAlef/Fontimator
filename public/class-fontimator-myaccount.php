<?php

/**
 * The WooCommerce My Account section Helper
 *
 * @link       https://alefalefalef.co.il
 * @since      2.0.0
 *
 * @package    Fontimator
 * @subpackage Fontimator/public
 */

/**
 * The WooCommerce My Account section Helper
 *
 * @package    Fontimator
 * @subpackage Fontimator/public
 * @author     Reuven Karasik <rubik@karasik.org>
 */
class Fontimator_MyAccount extends Fontimator_Public {
	public $downloads_table_notes;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.0.0
	 */
	public function __construct( $version ) {
		parent::__construct( 'fontimator-myaccount', $version );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    2.0.0
	 */
	public function enqueue_scripts() {
		if ( is_account_page() ) {
			wp_enqueue_script(
				'fontimator-my-account',
				plugin_dir_url( __FILE__ ) . 'js/fontimator-my-account.js',
				array( 'jquery' ),
				$this->version,
				true // is_footer true here, to make sure the tab selection works
			);

			$zipomator_base_url = home_url( Zipomator::get_variation_endpoint() );
			$zipomator_nonce = Zipomator::get_nonce();
			wp_localize_script(
				'fontimator-my-account', 'FontimatorDownloadCheckboxesButtons', array(
					'zipomatorBaseURL' => $zipomator_base_url,
					'zipomatorNonce' => $zipomator_nonce,
					'disabledText' => __( 'Select some fonts using the small checkboxes first.' , 'fontimator' ),
				)
			);

			wp_localize_script(
				'fontimator-my-account', 'FontimatorSubscriptionActions', array(
					'cancelConfirmationText' => esc_html__( "Are you sure? You can't re-enable the membership without purchasing it again.", 'fontimator' ),
				)
			);
		}
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    2.0.0
	 */
	public function enqueue_styles() {
		if ( is_account_page() ) {
			wp_enqueue_style(
				'fontimator-my-account',
				plugin_dir_url( __FILE__ ) . 'css/fontimator-my-account.css',
				array(),
				$this->version
			);
		}
	}

	public function locate_template( $template, $template_name, $template_path ) {
		switch ( basename( $template ) ) {
			case 'dashboard.php':
				$template = trailingslashit( plugin_dir_path( __FILE__ ) ) . 'partials/fontimator-myaccount-dashboard.php';
				break;

			// Add title to forgot password page
			case 'lost-password-confirmation.php':
			case 'form-reset-password.php':
			case 'form-lost-password.php':
				echo '<h2>' . __( 'Recover password or old purchases', 'fontimator' ) . '</h2>';
				break;
		}

		return $template;
	}

	public function get_myaccount_template( $downloads ) {
		require trailingslashit( plugin_dir_path( __FILE__ ) ) . 'partials/fontimator-downloads-table.php';
	}

	public function add_myaccount_notice_not_subscribed() {
		if ( class_exists( 'WC_Integration_WSMS' ) ) {
			$user_info = wp_get_current_user();
			$first_name = $user_info->first_name;
			$subscribe_link = sprintf( 'https://us2.list-manage.com/subscribe?MERGE0=%1$s&MERGE1=%2$s&MERGE2=%3$s&u=768a22048620e253477cb794b&id=0b3d24ccab', urlencode( $user_info->user_email ), urlencode( $user_info->first_name ), urlencode( $user_info->last_name ) );

			global $woocommerce;
			$wsms_integration = $woocommerce->integrations->integrations['wsms'];
			$merge_fields = $wsms_integration->get_user_merge_fields();
			if ( ! $merge_fields ) { // Not subscribed
				wc_print_notice( sprintf( __( 'Hold on, %1$s! You are not subscribed to our newsletter, and miss all the fun. <a href="%2$s" target="_blank">Subscribe Now!</a>', 'fontimator' ), $first_name, esc_url( $subscribe_link ) ), 'notice' );
			}
		}
	}


	public function add_columns_to_downloads_table( $columns ) {
		return array(
			'download-select'      => '&nbsp;',
			'download-product'   => __( 'Family', 'fontimator' ),
			'download-font-weight'   => __( 'Weight', 'fontimator' ),
			'download-font-license'   => __( 'License', 'fontimator' ),
			'download-font-version'   => __( 'Version', 'fontimator' ),
			'download-file'      => __( 'Download', 'fontimator' ),
			'download-actions'   => '&nbsp;',
		);
	}

	public function prepend_icon_to_download_name( $download ) {
		$order_id = $download['order_id'];

		$membership = new Fontimator_Membership( $order_id );
		if ( $membership && 'active' === $membership->get_status() ) {
			$membership_variation = new Fontimator_Font_Variation( $membership->get_variation_id() );
			echo '<a href="' . $membership_variation->get_permalink() . '" title="' . $membership_variation->get_title() . '"><i class="icon" data-icon="ø"></i></a>';
			echo ' <span class="downloads-table-icon-membership-seperator">&rsaquo;</span> ';
		}

		if ( 'academic' === $order_id ) {
			echo '<a href="/eula/?licenses=otf,academic" title="' . __( 'Your Academic License', 'fontimator' ) . '"><i class="icon" data-icon="Ÿ"></i></a>';
			echo ' <span class="downloads-table-icon-membership-seperator">&rsaquo;</span> ';
		}

		$parent_id = wp_get_post_parent_id( $download['product_id'] );
		echo '<a href="' . get_permalink( $parent_id ) . '" title="' . get_the_title( $parent_id ) . '">';

		if ( 'mailchimp_font_gift' === $order_id ) {
			echo '<i class="icon" title="' . __( 'Your Newsletter Birthday Gift!', 'fontimator' ) . '" data-icon="‚"></i> ';
		}

		if ( has_term( 'moved_to_fm', 'product_cat', $parent_id ) ) {
			// TRANSLATORS: %s: the other site's name, i.e. 'Fontimonim'
			echo '<i class="icon" title="' . sprintf( __( 'Moved to %s', 'fontimator' ), __( 'Fontimonim', 'fontimator' ) ) . '" data-icon="ℶ"></i> ';
		} elseif ( has_term( 'archive', 'product_cat', $parent_id ) ) {
			echo '<i class="icon" title="' . __( 'Archived', 'fontimator' ) . '" data-icon="׳"></i> ';
		}

		echo $download['product_name'];

		echo '</a>';
	}

	public function font_version_for_download( $download ) {
		$product_id = wp_get_post_parent_id( $download['product_id'] );
		$acf = Fontimator::get_instance()->get_acf();

		$font_version = $acf->get_field( 'font_version', $product_id );
		$font_release_year = $acf->get_field( 'font_release_year', $product_id );
		$font_update_year = $acf->get_field( 'font_update_year', $product_id );
		if ( $font_version ) {
			echo $font_version;
		}
		if ( $font_update_year && $font_release_year != $font_update_year ) {
			echo ' <span class="year">[' . $font_update_year . ']</span>';
		}
	}

	public function font_weight_for_download( $download ) {
		echo $this->get_font_attribute_for_download( $download, FTM_WEIGHT_ATTRIBUTE );
	}

	public function font_license_for_download( $download ) {
		echo $this->get_font_attribute_for_download( $download, FTM_LICENSE_ATTRIBUTE );
	}

	public function get_font_attribute_for_download( $download, $type ) {
		$variation_id = $download['product_id'];
		$variation = new WC_Product_Variation( $variation_id );
		$attributes = $variation->get_variation_attributes();
		if ( ! isset( $attributes[ 'attribute_pa_' . $type ] ) ) {
			return '&infin;';
		}
		$attribute_slug = $attributes[ 'attribute_pa_' . $type ];
		$term = get_term_by( 'slug', $attribute_slug, 'pa_' . $type );
		return $term->name;
	}

	public function sort_downloads_by_family( $downloads ) {
		foreach ( $downloads as $index => $download ) {
			if ( ! $download['ftm_font_family'] ) {
				$font_variation = new Fontimator_Font_Variation( $download['product_id'] );
				$downloads[ $index ]['ftm_font_family'] = $font_variation->get_family();
			}
			if ( null === $download['download_url'] ) {
				unset( $downloads[ $index ] );
			}
		}
		usort(
			$downloads, function( $a, $b ) {
				return strcmp( $a['ftm_font_family'], $b['ftm_font_family'] );
			}
		);
		return $downloads;
	}

	public static function group_downloads_by_family( $downloads ) {
		$groups = array();
		foreach ( $downloads as $index => $download ) {
			// Override archive fonts ftm_font_family
			if ( 'gift' !== $download['ftm_font_family'] && has_term( 'archive', 'product_cat', wp_get_post_parent_id( $download['product_id'] ) ) ) {
				$download['ftm_font_family'] = 'archive';
			}

			if ( $download['ftm_font_family'] ) {
				$font_family = $download['ftm_font_family'];

				if ( ! $groups[ $font_family ] ) {
					$groups[ $font_family ] = array();
				}

				$groups[ $font_family ][] = $download;
			}
		}
		$ordered_groups = array();
		if ( $groups['membership'] ) {
			$ordered_groups['membership'] = $groups['membership'];
		}
		if ( $groups['academic'] ) {
			$ordered_groups['academic'] = $groups['academic'];
		}
		if ( $groups['gift'] ) {
			$ordered_groups['gift'] = $groups['gift'];
		}
		$groups = $ordered_groups + $groups;
		return $groups;
	}

	protected function get_new_download_obj( $variation, $order_id, $font_family_override = null ) {
		$variation_downloads = $variation->get_downloads();
		$variation_download = reset( $variation_downloads );
		if ( ! $variation_download ) {
			return array();
		}

		return array(
			'ftm_font_family'     => $font_family_override ?: $variation->get_family(),
			'download_url'        => Zipomator::get_nonced_url( $variation->get_id() ),
			'download_id'         => $variation_download->get_id(),
			'product_id'          => $variation->get_id(),
			'product_name'        => strip_tags( $variation->get_title() ),
			'product_url'         => $variation->is_visible() ? $variation->get_permalink() : '', // Since 3.3.0.
			'download_name'       => strip_tags( $variation_download->get_name() ),
			'order_id'            => $order_id,
			// 'order_key'           => $order->get_order_key(),
			// 'downloads_remaining' => $result->downloads_remaining,
			// 'access_expires'      => $result->access_expires,
			'file'                => array(
				'name' => strip_tags( $variation_download->get_name() ),
				'file' => $variation_download->get_file(),
			),
		);
	}

	public function get_all_fonts_downloads( $license, $order_id, $font_family_override = null ) {
		$downloads = array();
		$fonts = Fontimator::get_catalog_fonts();

		if ( ! $fonts ) {
			wp_die( __( 'Fontimator Error: No fonts found. Are you sure you have WooCommerce products active?', 'fontimator' ) );
		}

		$fonts = array_map(
			function ( $font_id ) {
					return new Fontimator_Font( $font_id );
			}, $fonts
		);

		foreach ( $fonts as $font ) {
			$font_variation_for_membership = $font->get_variation_for_membership( $license );

			if ( ! $font_variation_for_membership || ! $font_variation_for_membership->is_downloadable() ) {
				continue;
			} else {
				$downloads[] = $this->get_new_download_obj( $font_variation_for_membership, $order_id, $font_family_override );
			}
		}

		return $downloads;
	}

	public function membership_add_all_fonts_downloads_table( $downloads ) {
		// get membership
		$subscriptions = wcs_get_users_subscriptions();
		foreach ( $subscriptions as $subscription ) {
			if ( 'active' === $subscription->get_status() ) {
				$membership_id = $subscription->get_id();
				$membership = new Fontimator_Membership( $membership_id );
				$membership_license = $membership->get_license();

				if ( ! $membership_license ) {
					continue;
				}

				$membership_downloads = $this->get_all_fonts_downloads( $membership_license, $membership_id, 'membership' );
				$downloads = array_merge( $downloads, $membership_downloads );
			}
		}
		return $downloads;
	}

	public function wsms_add_gifts_downloads_table( $downloads ) {
		global $woocommerce;
		if ( class_exists( 'WC_Integration_WSMS' ) ) {
			$wsms_integration = $woocommerce->integrations->integrations['wsms'];
			$merge_fields = $wsms_integration->get_user_merge_fields();
			$font_gifts = (array) Fontimator::get_instance()->get_acf()->get_field( 'mailchimp_font_gifts', 'options' );

			foreach ( $font_gifts as $gift ) {
				$merge_field = strtoupper( $gift['merge_field'] );
				if ( $merge_fields->$merge_field ) {
					$font = new Fontimator_Font( $gift['font_product'] );
					$font_weight = $gift['font_weight']->slug;
					$font_license = $gift['font_license'] ? $gift['font_license']->slug : 'otf-2';

					$gift_variation = $font->get_specific_variation( $font_weight, $font_license );

					if ( ! $gift_variation || ! $gift_variation->is_downloadable() ) {
						continue;
					} else {
						$downloads[] = $this->get_new_download_obj( $gift_variation, 'mailchimp_font_gift', 'gift' );
					}
				}
			}
		}

		return $downloads;
	}

	public function add_free_fonts_downloads_table( $downloads ) {
		global $wpdb;
		$table_name = $wpdb->prefix . Fontimator_Free_Download::$db_table_name;
		$user_email = wp_get_current_user()->user_email;
		$user_downloads = $wpdb->get_results(
			$wpdb->prepare( "SELECT DISTINCT download_id FROM {$table_name} WHERE user_email = %s", $user_email )
		);

		foreach ( $user_downloads as $user_download ) {
			$download_id = $user_download->download_id;
			$free_download = new Fontimator_Free_Download( $download_id );

			$downloads[] = array(
				'ftm_font_family'     => 'free',
				'download_url'        => $free_download->get_url(),
				'download_id'         => 'ftm_free_download_' . $download_id,
				'download_name'       => $download_id . '.zip',
				'product_name'        => $free_download->get_name(),
			);
		}

		return $downloads;
	}

	public function wsms_add_academic_downloads_table( $downloads ) {
		global $woocommerce;
		if ( class_exists( 'WC_Integration_WSMS' ) ) {
			$wsms_integration = $woocommerce->integrations->integrations['wsms'];
			$list_id = '0f16472e00';
			$merge_fields = $wsms_integration->get_user_merge_fields( $list_id );
			$academic_year = (int) $merge_fields->YEAR;
			$graduation_date = new DateTime( $academic_year . '-07-31' );
			$now = new DateTime();
			if ( $graduation_date > $now ) {
				$academic_downloads = $this->get_all_fonts_downloads( 'otf-2', 'academic', 'academic' );
				$downloads = array_merge( $downloads, $academic_downloads );
			} elseif ( $academic_year ) {
				$this->downloads_table_notes .= sprintf( __( '<strong>Please note:</strong> You had an Academic License until July 31st, %s, but it is out of date.', 'fontimator' ), $academic_year );
			}
		}

		return $downloads;
	}

	public function disable_subsciprion_cancellation( $actions, $subscription ) {
		$membership = new Fontimator_Membership( $subscription );
		if ( ! $actions['cancel'] ) {
			return $actions;
		}

		if ( ! $membership->can_cancel() ) {
				unset( $actions['cancel'] );

				$actions['disabled'] = array();
				$actions['disabled']['url'] = '#';
				$actions['disabled']['name'] = __( 'Cancel (unavailable during the first year)', 'fontimator' );

				return $actions;
		}

		return $actions;

	}

	public function remove_items_from_subscription() {
		return false;
	}

	public function checkbox_for_download( $download ) {
		?>

		<input type="checkbox" name="fontimator-downloads" value="<?php echo $download['product_id']; ?>" id="fontimator_download_<?php echo $download['product_id']; ?>" />

		<?php
	}

	public function reset_all_downloads() {
		if ( 'true' === $_GET['ftm_reset_downloads'] ) {
			$count = Fontimator_Woocommerce::reset_downloads_for_customer();
			$downloads_link = wc_get_page_permalink( 'myaccount' ) . 'downloads';
			wp_redirect( add_query_arg( 'ftm_reset_downloads', 'done', $downloads_link ) );
		} elseif ( 'done' === $_GET['ftm_reset_downloads'] ) {
			wc_print_notice( __( 'Updated your downloads list. We hope everything is here this time!', 'fontimator' ), 'success' );
		}
	}

	public function get_reset_downloads_link() {
		$downloads_link = wc_get_page_permalink( 'myaccount' ) . 'downloads';
		return add_query_arg( 'ftm_reset_downloads', 'true', $downloads_link );
	}

	public function downloads_table_buttons() {
		?>
		<div class="fontimator-buttons">
			<div class="download-buttons">
				<button class="fontimator-select-all button alt" type="button"><?php _e( 'Select All', 'fontimator' ); ?></button>
				<button class="fontimator-unselect-all button alt" type="button"><?php _e( 'Unselect All', 'fontimator' ); ?></button>
				<button class="fontimator-bulk-download button alt" type="submit" disabled><?php _e( 'Download Selected', 'fontimator' ); ?></button>
			</div>
			<?php
			/*
			<div class="action-buttons">
				<a href="<?php echo $this->get_reset_downloads_link(); ?>" class="fontimator-refresh-list button alt" type="button"><?php _e( 'Refresh Downloads', 'fontimator' ); ?></a>
			</div>
			*/
			?>
		</div>
		<?php
	}

	public function add_edit_account_to_edit_address() {
		WC_Shortcode_My_Account::edit_address( false );
	}

	public function add_message_after_downloads() {
		echo '<div class="footnotes">';
		// TRANSLATORS: %s is the link to contact form
		echo sprintf( __( "Each time you download fonts from this page you agree to the <strong>current</strong> <a href='%s'>EULA</a>.", 'fontimator' ), esc_url( home_url( 'eula' ) ) );
		echo '<br />';
		echo sprintf( __( "Missing something? If you have previously purchased a font license that isn't listed here, please <a href='%s'>click here</a> to refresh your downloads list.", 'fontimator' ), esc_url( $this->get_reset_downloads_link() ) );
		echo '<br><br><h5>מקרא</h5><i class="icon" data-icon="Ÿ"></i> רישיון אקדמי &emsp;<i class="icon" data-icon="‚"></i> מתנת יום הולדת מ<a href="http://eepurl.com/hSaLY" target="_blank">הניוזלטר</a> &emsp;<i class="icon" data-icon="ø"></i> מינוי לספריית הפונטים &emsp;<i class="icon" data-icon="׳"></i> הפונט עבר ל<a href="/resources/archive/" target="_blank">ארכיון</a> &emsp;<i class="icon" data-icon="ℶ"></i> הפונט עבר ל<a href="http://fontimonim.co.il" target="_blank">פונטימונים</a>';
		if ( $this->downloads_table_notes ) {
			echo '<br />' . $this->downloads_table_notes;
		}
		echo '</div>';
	}

}
