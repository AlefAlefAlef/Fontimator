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
			wp_enqueue_script( 'fontimator-email-preferences' );

			wp_enqueue_script(
				'fontimator-my-account',
				plugin_dir_url( __FILE__ ) . 'js/fontimator-my-account.js',
				array( 'jquery' ),
				$this->version,
				true // is_footer true here, to make sure the tab selection works
			);

			$zipomator_base_url = home_url( Zipomator::get_variation_endpoint() );
			$zipomator_nonce    = Zipomator::get_nonce();
			wp_localize_script(
				'fontimator-my-account',
				'FontimatorDownloadCheckboxesButtons',
				array(
					'zipomatorBaseURL' => $zipomator_base_url,
					'zipomatorNonce'   => $zipomator_nonce,
					'disabledText'     => __( 'Select some fonts using the small checkboxes first.', 'fontimator' ),
				)
			);

			wp_localize_script(
				'fontimator-my-account',
				'FontimatorSubscriptionActions',
				array(
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
		if ( Fontimator::mc()->enabled() ) {
			global $wp;
			$request = explode( '/', $wp->request );
			
			if( ( end($request) == 'my-account' && is_account_page() ) ){  // If dashboard page
				$is_subscribed     = Fontimator::mc()->is_user_subscribed();
				
				if ( ! $is_subscribed ) { // Not subscribed
					Fontimator::mc()->print_newsletter_banner();
				}
			}
		}
	}


	public function add_columns_to_downloads_table( $columns ) {
		return array(
			'download-select'       => '&nbsp;',
			'download-product'      => __( 'Family', 'fontimator' ),
			'download-font-license' => __( 'License', 'fontimator' ),
			'download-font-version' => __( 'Version', 'fontimator' ),
			'download-file'         => __( 'Download', 'fontimator' ),
			'download-actions'      => '&nbsp;',
		);
	}

	public function prepend_icon_to_download_name( $download ) {
		if ( 'free' === $download['ftm_font_family'] ) {
			echo esc_html( $download['product_name'] );
			return;
		}

		$order_id = $download['order_id'];

		$membership = new Fontimator_Membership( $order_id );
		if ( $membership && 'active' === $membership->get_status() ) {
			$membership_variation = new Fontimator_Font_Variation( $membership->get_variation_id() );
			echo '<a href="' . $membership_variation->get_permalink() . '" title="' . $membership_variation->get_title() . '"><i class="icon" data-icon="ø"></i></a>';
			echo ' <span class="downloads-table-icon-membership-seperator">&rsaquo;</span> ';
		}

		if ( 'academic' === $order_id ) {
			echo '<a href="https://alefalefalef.co.il/eula/?licenses=otf,academic" title="' . __( 'Your Academic License', 'fontimator' ) . '"><i class="icon" data-icon="Ÿ"></i></a>';
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

	public function append_weight_to_download_name( $download ) {
		$font_weight = $this->get_font_attribute_for_download( $download, FTM_WEIGHT_ATTRIBUTE );

		if ( $font_weight ) {
			echo ' <span class="downloads-table-icon-membership-seperator">&rsaquo;</span> ';
			echo $font_weight;
		}
	}

	public function font_version_for_download( $download ) {
		$product_id = wp_get_post_parent_id( $download['product_id'] );
		$acf        = Fontimator::acf();

		$font_version      = $acf->get_field( 'font_version', $product_id );
		$font_release_year = $acf->get_field( 'font_release_year', $product_id );
		$font_update_year  = $acf->get_field( 'font_update_year', $product_id );
		if ( $font_version ) {
			echo $font_version;
		}
		if ( $font_update_year && $font_release_year != $font_update_year ) {
			echo ' <span class="year">[' . $font_update_year . ']</span>';
		}
	}

	public function font_license_for_download( $download ) {
		echo $this->get_font_attribute_for_download( $download, FTM_LICENSE_ATTRIBUTE );
	}

	public function get_font_attribute_for_download( $download, $type ) {
		$variation_id = $download['product_id'];
		$variation    = new WC_Product_Variation( $variation_id );
		$attributes   = $variation->get_variation_attributes();
		if ( ! isset( $attributes[ 'attribute_pa_' . $type ] ) ) {
			if ( 'free' === $download['ftm_font_family'] && 'license' === $type ) {
				return _x( 'Free', 'License column for free downloads (instead of ♾)', 'fontimator' );
			}
			return '&infin;';
		}
		$attribute_slug = $attributes[ 'attribute_pa_' . $type ];
		$term           = get_term_by( 'slug', $attribute_slug, 'pa_' . $type );
		return $term->name;
	}

	public function sort_downloads_by_family( $downloads ) {
		foreach ( $downloads as $index => $download ) {
			if ( ! $download['ftm_font_family'] ) {
				$font_variation                         = new Fontimator_Font_Variation( $download['product_id'] );
				$downloads[ $index ]['ftm_font_family'] = $font_variation->get_family();
			}
			if ( null === $download['download_url'] ) {
				unset( $downloads[ $index ] );
			}
		}
		usort(
			$downloads,
			function( $a, $b ) {
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

		// Sort alphabetically
		$groups_to_sort_abc = array( 'archive', 'gift', 'academic', 'membership', 'free' );
		foreach ( $groups as $group_name => $group_items ) {
			if ( in_array( $group_name, $groups_to_sort_abc ) ) {
				usort( $group_items, function ( $a, $b ) {
					return $a['product_name'] <=> $b['product_name'];
				} );
			$groups[$group_name] = $group_items;
			}
		}

		// Reorder
		$archive_group = $groups['archive'];
		$free_group = $groups['free'];
		$gift_group = $groups['gift'];
		unset( $groups['archive'], $groups['gift'], $groups['free'] );

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

		if ( $gift_group ) {
			$groups['gift'] = $gift_group;
		}
		if ( $free_group ) {
			$groups['free'] = $free_group;
		}
		if ( $archive_group ) {
			$groups['archive'] = $archive_group;
		}
		return $groups;
	}

	protected function get_new_download_obj( $variation, $order_id, $font_family_override = null ) {
		$variation_downloads = $variation->get_downloads();
		$variation_download  = reset( $variation_downloads );
		if ( ! $variation_download ) {
			return array();
		}

		return array(
			'ftm_font_family' => $font_family_override ?: $variation->get_family(),
			'download_url'    => Zipomator::get_nonced_url( $variation->get_id() ),
			'download_id'     => $variation_download->get_id(),
			'product_id'      => $variation->get_id(),
			'product_name'    => strip_tags( $variation->get_title() ),
			'product_url'     => $variation->is_visible() ? $variation->get_permalink() : '', // Since 3.3.0.
			'download_name'   => strip_tags( $variation_download->get_name() ),
			'order_id'        => $order_id,
			// 'order_key'           => $order->get_order_key(),
			// 'downloads_remaining' => $result->downloads_remaining,
			// 'access_expires'      => $result->access_expires,
			'file'            => array(
				'name' => strip_tags( $variation_download->get_name() ),
				'file' => $variation_download->get_file(),
			),
		);
	}

	public function get_all_fonts_downloads( $license, $order_id, $font_family_override = null ) {
		$downloads = array();
		$fonts     = Fontimator_Query::get_catalog_fonts();

		if ( ! $fonts ) {
			wp_die( __( 'Fontimator Error: No fonts found. Are you sure you have WooCommerce products active?', 'fontimator' ) );
		}

		$fonts = array_map(
			function ( $font_id ) {
					return new Fontimator_Font( $font_id );
			},
			$fonts
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
				$membership_id      = $subscription->get_id();
				$membership         = new Fontimator_Membership( $membership_id );
				$membership_license = $membership->get_license();

				if ( ! $membership_license ) {
					continue;
				}

				$membership_downloads = $this->get_all_fonts_downloads( $membership_license, $membership_id, 'membership' );
				$downloads            = array_merge( $downloads, $membership_downloads );
			}
		}
		return $downloads;
	}

	public function mc4wp_add_gifts_downloads_table( $downloads ) {
		if ( Fontimator::mc()->enabled() ) {
			$merge_fields     = Fontimator::mc()->get_user_merge_fields();
			$font_gifts       = (array) Fontimator::acf()->get_field( 'mailchimp_font_gifts', 'options' );
			
			if ( ! $merge_fields ) {
				return $downloads;
			}

			foreach ( $font_gifts as $gift ) {
				$merge_field = strtoupper( $gift['merge_field'] );
				if ( $merge_fields->$merge_field ) {
					$font         = new Fontimator_Font( $gift['font_product'] );
					$font_weight  = $gift['font_weight']->slug;
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
		$table_name     = $wpdb->prefix . Fontimator_Free_Download::$db_table_name;
		$user_email     = wp_get_current_user()->user_email;
		$user_downloads = $wpdb->get_results(
			$wpdb->prepare( "SELECT DISTINCT download_id FROM {$table_name} WHERE user_email = %s", $user_email )
		);

		foreach ( $user_downloads as $user_download ) {
			$download_id   = $user_download->download_id;
			$free_download = new Fontimator_Free_Download( $download_id );

			$downloads[] = array(
				'ftm_font_family' => 'free',
				'download_url'    => $free_download->get_url(),
				'download_id'     => 'ftm_free_download_' . $download_id,
				'download_name'   => $download_id . '.zip',
				'product_name'    => $free_download->get_name(),
			);
		}

		return $downloads;
	}

	public function mc4wp_add_academic_downloads_table( $downloads ) {
		if ( Fontimator::mc()->enabled() ) {
			$academic_year = Fontimator::mc()->get_academic_license_year();
			if ( ! is_numeric( $academic_year ) ) {
				return $downloads;
			}

			$graduation_date  = new DateTime( $academic_year . '-12-31' );
			$now              = new DateTime();
			if ( $graduation_date > $now ) {
				$academic_downloads = $this->get_all_fonts_downloads( 'otf-2', 'academic', 'academic' );
				$downloads          = array_merge( $downloads, $academic_downloads );
			} elseif ( $academic_year ) {
				$this->downloads_table_notes .= sprintf( __( '<strong>Please note:</strong> You had an Academic License until December 31st, %s, but it is out of date.', 'fontimator' ), $academic_year );
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

			$actions['disabled']        = array();
			$actions['disabled']['url'] = '#';

			if ( $membership->get_license() === 'web-reseller' ) {
				$actions['disabled']['name'] = __( 'Cancel (unavailable during the first 32 months)', 'fontimator' );
			} else {
				$actions['disabled']['name'] = __( 'Cancel (unavailable during the first year)', 'fontimator' );
			}

			return $actions;
		}

		return $actions;

	}

	public function remove_items_from_subscription() {
		return false;
	}

	public function checkbox_for_download( $download ) {
		if ( ! $download['product_id'] || is_checkout() ) {
			return;
		}
		?>

		<input type="checkbox" name="fontimator-downloads" value="<?php echo $download['product_id']; ?>" id="fontimator_download_<?php echo $download['product_id']; ?>" />

		<?php
	}

	public function reset_all_downloads() {
		if ( 'true' === $_GET['ftm_reset_downloads'] ) {
			$count          = Fontimator_Woocommerce::reset_downloads_for_customer();
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

	public function complete_family_banner( $family_name, $family_group ) {
		$acf = Fontimator::acf();
		if ( ! $acf->get_acf_field( 'complete_family_enabled', 'options' ) ) {
			return;
		}

		$special_groups = array( 'membership', 'academic', 'archive', 'gift', 'free', 'membership-reseller', 'otf-extended', 'web-extended' );
		if ( in_array($family_name, $special_groups) ) {
			return; // Don't display banner for these special groups
		}

		$font_id = get_page_by_path( $family_name, OBJECT, 'product' )->ID;
		if ( ! $font_id ) {
			return;
		}

		// Check if font is in banner whitelist
		$limited_to_fonts = $acf->get_acf_field( 'complete_family_limit_fonts', 'options' );
		if ( $limited_to_fonts && ! in_array( $font_id, $limited_to_fonts ) ) {
			return;
		}		

		// Check if font was purchased long enough ago
		$limit_days = $acf->get_acf_field( 'complete_family_limit_days_from_purchase', 'options' );
		if ( $limit_days ) {
			$first_order = wc_get_order( $family_group[0]['order_id'] );
			$date_created       = $first_order->get_date_created();
			$timestamp_created	= $date_created->getTimestamp();

			$datetime_now       = new WC_DateTime(); // Get now datetime (from Woocommerce datetime object)
			$timestamp_now      = $datetime_now->getTimestamp(); // Get now timestamp

			$time_delta         = $timestamp_now - $timestamp_created; // Difference in seconds
			$days_in_seconds    = $limit_days * 24 * 60 * 60; // x days in seconds
			
			if ( $time_delta < $days_in_seconds ) {
				return; // Skip fonts purchased too recently.
			}
		}

		
		$font = new Fontimator_Font( $font_id );
		$visible_weights = $font->get_visible_weights( 'slug' );
		
		$purchased_weights = array_reduce(
			$family_group,
			function($weights, $download) use ( &$font ) {
				$variation = new Fontimator_Font_Variation( $download['product_id'] );
				if ( $variation && $variation->get_license_type() === 'otf') {
					$weights[] = $variation->get_weight();

					if ( $variation->get_weight() === '000-familybasic' ) {
						$weights = array_unique( array_merge( $weights, $font->get_familybasic_weights( 'slug' ) ) );
					}
				}
				return $weights;
			},
			[]
		);

		$not_purchased_weights = array_diff( $visible_weights, $purchased_weights, [ '000-variable', '000-familybasic' ] );
		if ( count( $purchased_weights ) // There are desktop weights, not just web/app
			&& count( $not_purchased_weights ) // There are weights not yet purchased
			&& ! in_array( '000-family', $purchased_weights ) // Person doesn't have both the entire family...
			&& ! in_array( '000-variable', $purchased_weights ) ) { // and the variable font.
			?>
			<tr>
				<td colspan="6" class="family-reunion-td">
					<?php
					include trailingslashit( plugin_dir_path( __FILE__ ) ) . 'partials/fontimator-downloads-complete-family-banner.php';
					?>
				</td>
			</tr>
			<?php
		}
	}

	public function add_message_after_downloads() {
		echo '<div class="footnotes">';
		?>
		
		<?php if ( 'alefalefalef' === FTM_SITE_NAME ) { ?>
			<div class="legend">
				<h5>מקרא</h5>
				<dl>
					<dt class="icon" data-icon="‚"></dt><dd>מתנת יום הולדת מ<a href="<?php esc_attr( Fontimator_MC::SIGNUP_URL ) ?>" target="_blank">הניוזלטר</a></dd>
					<dt class="icon" data-icon="ø"></dt><dd><a href="https://alefalefalef.co.il/font/membership/">מינוי</a> לספריית הפונטים</dd>
					<dt class="icon" data-icon="׳"></dt><dd>הפונט עבר ל<a href="<?php echo home_url( 'resources/archive' ); ?>" target="_blank">ארכיון</a></dd>
					<dt class="icon" data-icon="ℶ"></dt><dd>הפונט עבר ל<a href="https://fontimonim.co.il" target="_blank">פונטימונים</a></dd>
					<dt class="icon" data-icon="ė"></dt><dd>רישיונות <a href="https://alefalefalef.co.il/minisection/giveaway/" target="_blank">חינמיים</a> שהורדת</dd>
					<dt class="icon" data-icon="Ÿ"></dt><dd>רישיון אקדמי</dd>
				</dl>
			</div>
		<?php } ?>

		<p>
			<?php
			// TRANSLATORS: %s is the link to contact form
			echo sprintf( __( "Each time you download fonts from this page you agree to the <strong>current</strong> <a href='%s'>EULA</a>.", 'fontimator' ), get_permalink( get_page_by_path( 'eula' ) ) );
			?>
		</p>
		
		<!--<p style="display:none;">
			<?php
			echo sprintf( __( "Missing something? If you have previously purchased a font license that isn't listed here, please <a href='%s'>click here</a> to refresh your downloads list.", 'fontimator' ), esc_url( $this->get_reset_downloads_link() ) );
			?>
		</p>-->

		<?php
		// הודעה אם רישיון אקדמי פג תוקף
		if ( $this->downloads_table_notes ) {
			echo '<br />' . $this->downloads_table_notes;
		}
		echo '</div>';
	}

	public function birthday_message_on_downlaods_page() {
		if ( Fontimator::mc()->is_user_birthday( 1, 1 ) ) {
			if ( 'fontimonim' !== FTM_SITE_NAME ) {
				echo '<div class="birthday-song-box"><iframe id="youtube-birthday" 
					width="280" height="210" 
					src="https://www.youtube.com/embed/bYb2UOo1YqE?start=50&autoplay=1&modestbranding=1&mute=2&origin=&widget_referrer=" 
					frameborder="0" 
					allow="autoplay; encrypted-media" allowfullscreen></iframe></div>';
			}
		}
	}

	public function add_gender_field_to_edit_account () {
		if ( ! Fontimator::mc()->enabled() ) {
			return false;
		}

		if ( ! Fontimator::mc()->is_user_subscribed( null, false ) ) {
			return false;
		}
		
		$user_gender = Fontimator_I18n::get_user_gender();
		?>
		<fieldset>
			<legend><?php _e( 'How would you like to be addressed?', 'fontimator' ); ?></legend>
			<p class="form-row-small feat-mgdr">
				<small><?php _e( 'Throughout the site we try to customize the messeges to you so you can feel at home.', 'fontimator' ); ?></small>
			</p>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide form-row-gender">
				<?php
				$options = array(
					Fontimator_I18n::GENDER_FEMALE => _x( 'As female', 'Gender field option in edit account form', 'fontimator' ),
					Fontimator_I18n::GENDER_MALE => _x( 'As male', 'Gender field option in edit account form', 'fontimator' ),
				);
				foreach ( $options as $key => $option ) {
					?>
					<label>
						<input type="radio" name="mailchimp_gender" value="<?php echo $key; ?>" <?php checked( $user_gender, $key ); ?> />
						<?php echo $option; ?>
					</label>
					<?php
				}
				?>
			</p>
		</fieldset>
		<?php
	}

	public function save_gender_field_on_edit_account () {
		if ( isset( $_POST['mailchimp_gender'] ) && Fontimator::mc()->enabled() ) {
			Fontimator::mc()->update_user_gender($_POST['mailchimp_gender']);
		}
	}

	public function add_address_field_to_edit_account () {
		if ( ! Fontimator::mc()->enabled() ) {
			return false;
		}

		if ( ! Fontimator::mc()->is_user_subscribed( null, false ) ) {
			return false;
		}
		
		$user_address = Fontimator::mc()->get_user_address();
		if ( ! $user_address ) {
			$user_address = new stdClass();
		}
		if ( ! $user_address->country ) {
			$user_address->country = __( 'Israel', 'woocommerce' ); // Default to Israel
		}

		?>
		<fieldset>
			<legend><?php _e( 'What is your (physical) address?', 'fontimator' ); ?></legend>
			<p class="form-row-small">
				<small class="feat-mgdr"><?php _e( 'So we can send you cool stuff by mail!', 'fontimator' ); ?></small>
			</p>
			
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="mcAddress"><?php _e( 'Address', 'fontimator' ); ?></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="mailchimp_address[address]" id="mcAddress" placeholder="<?php esc_attr_e( 'Example St. 5 Apt 10', 'fontimator' ); ?>" autocomplete="off" value="<?php echo esc_attr( $user_address->addr1 ); ?>">
			</p>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="mcCity"><?php _e( 'City', 'fontimator' ); ?></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="mailchimp_address[city]" id="mcCity" placeholder="" autocomplete="off" value="<?php echo esc_attr( $user_address->city ); ?>">
			</p>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="mcZip"><?php _e( 'Zip Code', 'fontimator' ); ?> <a href="https://mypost.israelpost.co.il/%D7%A9%D7%99%D7%A8%D7%95%D7%AA%D7%99%D7%9D/%D7%90%D7%99%D7%AA%D7%95%D7%A8-%D7%9E%D7%99%D7%A7%D7%95%D7%93/" target="_blank" class="zip-link"><?php _e( 'Locate your Zip code', 'fontimator' ); ?> ⇱</a></label>
				<input type="number" class="woocommerce-Input woocommerce-Input--text input-text" name="mailchimp_address[zip]" id="mcZip" placeholder="7 ספרות" autocomplete="off" value="<?php echo esc_attr( $user_address->zip ); ?>" min="1000000" max="9999999">
			</p>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="mcCountry"><?php _e( 'Country', 'fontimator' ); ?></label>
				<?php
				$countries = WC()->countries->get_countries();
				?>
				<select name="mailchimp_address[country]" id="mcCountry" autocomplete="off" class="woocommerce-Input woocommerce-Input--select input-select option-tree-ui-select">
					<?php foreach ( $countries as $country_code => $country_name ) { ?>
						<option <?php selected( $user_address->country, $country_name ); ?> value="<?php echo esc_attr( $country_name ); ?>"><?php echo esc_html( $country_name ); ?></option>
					<?php } ?>
				</select>
			</p>


		</fieldset>
		<?php
	}

	public function save_address_field_on_edit_account () {
		if ( isset( $_POST['mailchimp_address'] ) && Fontimator::mc()->enabled() ) {
			Fontimator::mc()->update_user_address( $_POST['mailchimp_address']['address'], $_POST['mailchimp_address']['city'], $_POST['mailchimp_address']['zip'], $_POST['mailchimp_address']['country'] );
		}
	}


	/**
	 * Adds the URL rewrite for the email preferences tab
	 *
	 * @since 4.2.2
	 */
	public function add_email_preferences_tab_rewrite() {
		add_rewrite_endpoint( 'email-preferences', EP_ROOT | EP_PAGES );
	}

	/**
	 * Adds the query var for the email preferences tab
	 *
	 * @since 4.2.2
	 */
	public function add_email_preferences_tab_query_var( $vars ) {
		$vars[] = 'email-preferences';
    return $vars;
	}

	/**
	 * Adds the menu item for the email preferences tab
	 *
	 * @since 4.2.2
	 */
	public function add_email_preferences_tab_menu_item( $items ) {
		$position = 4;

		return array_slice($items, 0, $position, true) +
    	array(
				'email-preferences' => __( 'Email Preferences', 'fontimator' )
				) +
    	array_slice($items, $position, count($items)-$position, true);
	}

	/**
	 * Prints the content for the email preferences tab
	 *
	 * @since 4.2.2
	 */
	public function email_preferences_tab_content() {
		include trailingslashit( plugin_dir_path( __FILE__ ) ) . 'partials/fontimator-myaccount-email-preferences.php';
	}

	/**
	 * Save the email preferences and redirect back to the my account page.
	 * Code from WC_Form_Handler->save_account_details()
	 */
	public static function save_email_preferences() {
		$nonce_value = wc_get_var( $_REQUEST['save-email-preferences-nonce'], wc_get_var( $_REQUEST['_wpnonce'], '' ) ); // @codingStandardsIgnoreLine.

		if ( ! wp_verify_nonce( $nonce_value, 'save_email_preferences' ) ) {
			return;
		}

		if ( empty( $_POST['action'] ) || 'save_email_preferences' !== $_POST['action'] ) {
			return;
		}

		if ( ! empty( $_POST['user_email'] ) && wp_verify_nonce( $_POST['save-email-preferences-address-nonce'], 'email_prefs_' . $_POST['user_email'] ) && is_email( $_POST['user_email'] ) ) {
			$user_email = $_POST['user_email'];
		} else {
			if ( ! is_user_logged_in() ) {
				wp_die( __( 'Error: could not validate email address to update preferences for.', 'fontimator' ) );
			}
		}

		wc_nocache_headers();

		$updated_interests = array();

		$valid_interests = wp_list_pluck( Fontimator::mc()->interest_groups, 'ftm_interest_group' );

		foreach ( $valid_interests as $id ) {
			if ( 'on' === $_POST['interests'][ $id ] ) {
				$updated_interests[ $id ] = true;
			} else {
				$updated_interests[ $id ] = false;
			}
		}
	
		// save the preferences
		Fontimator::mc()->update_user_groups( $updated_interests, null, $user_email );

		wc_add_notice( __( 'Email preferences were updated successfully.', 'fontimator' ) );
	}

	protected function handle_reseller_domains_actions( $subscription ) {

		function is_valid_domain_name($domain_name) {
			return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain_name) //valid chars check
						&& preg_match("/^.{1,253}$/", $domain_name) //overall length check
						&& preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain_name)   ); //length of each label
		}

		if ( isset( $_REQUEST['reseller-domains-action'] ) ) {
			$domains_array = $subscription->get_meta( 'ftm_reseller_domains' ) ?: array();

			switch( $_REQUEST['reseller-domains-action'] ) {
				case 'add':
					if ( isset( $_REQUEST['reseller-new-domain'] ) ) {
						$clean_domain = preg_replace('(^https?://)', '', $_REQUEST['reseller-new-domain'] );
						if ( is_valid_domain_name( $clean_domain ) ) {
							$families = array();
							if ( isset( $_REQUEST['reseller-new-domain-families'] ) && is_array( $_REQUEST['reseller-new-domain-families'] ) ) {
								foreach ( (array) $_REQUEST['reseller-new-domain-families'] as $font_id ) {
									if ( 'product' === get_post_type( (int) $font_id ) ) {
										$families[] = $font_id;
									}
								}
							}

							$domains_array[ $clean_domain ] = array(
								'timestamp' => time(),
								'families' => $families
							);
						} else {
							wc_print_notice( __( 'The domain specified is not a valid domain.', 'fontimator' ) );
						}
					}
				break;
				case 'delete':
					if ( isset( $_REQUEST['reseller-deleted-domain'] ) && in_array( $_REQUEST['reseller-deleted-domain'], array_keys( $domains_array ) ) ) {
						unset( $domains_array[ $_REQUEST['reseller-deleted-domain'] ] );
					}
				break;
			}

			$subscription->update_meta_data( 'ftm_reseller_domains', $domains_array );
			$subscription->save();
		}
	}

	protected function print_reseller_domains_table( $domains = array() ) {
		?>
		<h2 id="reseller-domains"><?php _e( 'Web Domains', 'fontimator' ); ?></h2>
		<h6><?php _e( 'Your web license will only be valid for the domains bellow. You can add how many you want.', 'fontimator' ); ?></h6>
		<table class="shop_table reseller_domains">
			<?php if ( !empty( $domains ) ) { ?>
				<thead>
					<tr>
						<th class="domain-added"><?php _ex( 'Added on', 'Column in the reseller domains table', 'fontimator' ); ?></th>
						<th class="domain-name"><?php _e( 'Domain Name', 'fontimator' ); ?></th>
						<th class="domain-families"><?php _ex( 'Font/s', 'Column in the reseller domains table', 'fontimator' ); ?></th>
						<th class="domain-actions"><?php _e( 'Actions', 'fontimator' ); ?></th>
					</tr>
				</thead>
			<?php } ?>

			<tbody class="reseller-domains-list">
				<?php foreach ( (array) $domains as $domain => $meta ) {
					$creation_time = $meta['timestamp'] ?: time();
					$families_ids = $meta['families'] ?: array();
					$families = array();

					foreach ( $families_ids as $font_id ) {
						$families[] = wc_get_product( $font_id )->get_name();
					}
					?>
					<tr>
						<td class="domain-added" title="<?php echo esc_attr( date_i18n( 'Y-m-d h:i:s', $creation_time ) ); ?>"><?php echo esc_html( date_i18n( get_option( 'date_format' ), $creation_time ) ); ?></td>
						<th class="domain-name" scope="row"><a href="http://<?php echo esc_attr( $domain ); ?>" target="_blank"><?php echo esc_html( $domain ); ?></a></th>
						<th class="domain-families" scope="row"><?php echo esc_html( implode( ', ' , $families ) ); ?></th>
						<td class="domain-actions">
							<form method="post" action="#reseller-domains">
								<input type="hidden" value="<?php echo esc_attr( $domain ); ?>" name="reseller-deleted-domain" />
								<button name="reseller-domains-action" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to remove this domain?', 'fontimator' ); ?>');" value="delete" class="button alt b-white b-icon-before" data-icon="Â"></button>
							</form>
						</td>
					</tr>
				<?php } ?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="4">
						<form method="post" action="#reseller-domains" class="add-domain">
							<h4><?php esc_attr_e('Add a new domain...', 'fontimator'); ?></h4>
							<div class="form-inner-wrap">
								<p>
									<label for="reseller-new-domain"><?php esc_attr_e('Domain', 'fontimator'); ?></label>
									<input
										type="text"
										placeholder="example.co.il"
										required
										<?php // source: https://stackoverflow.com/a/26987741/2588319 ?>
										pattern="^(https?://)?(www\.)?(((?!-))(xn--|_{1,1})?[a-z0-9-]{0,61}[a-z0-9]{1,1}\.)*(xn--)?([a-z0-9][a-z0-9\-]{0,60}|[a-z0-9-]{1,30}\.[a-z]{2,})$"
										name="reseller-new-domain"
										class="reseller-new-domain"
										id="reseller-new-domain" />
								</p>
								<p class="reseller-new-domain-families">
									<label for="reseller-new-domain-families"><?php _ex( 'Font/s', 'Column in the reseller domains table', 'fontimator' ); ?></label>
									<select name="reseller-new-domain-families[]" multiple="multiple" id="reseller-new-domain-families" required>
										<?php
										$families = array();
										foreach ( Fontimator_Query::get_catalog_fonts() as $font_id ) {
											?>
											<option value="<?php echo esc_attr( $font_id ); ?>">
												<?php echo esc_html( wc_get_product( $font_id )->get_name() ); ?>
											</option>
											<?php
										}
										?>
									</select>
								</p>
							</div>
							<button type="submit" name="reseller-domains-action" class="reseller-domains-action" value="add"><?php _ex( 'Add', 'Button in the reseller domains table', 'fontimator'); ?></button>
						</form>
					</td>
				</tr>
			</tfoot>
		</table>
		<?php
	}

	/**
	 * Hooked on woocommerce_subscription_totals_table
	 *
	 * @param WC_Subscription $subscription
	 * @return void
	 */
	public function reseller_domains_section( $subscription ) {
		// Check if this subscription order contains a reseller membership
		if ( Fontimator_WooCommerce::is_subscription_of_type( $subscription, 'membership-reseller' ) ) {
			wp_enqueue_script( 'select2' );
			wp_enqueue_style( 'select2' );
			
			$max_families_per_font = 2;

			wp_localize_script(
				'fontimator-my-account',
				'FontimatorResellerDomains',
				array(
					'maximumFamiliesSelectedError' => sprintf( __( "A maximum of %d fonts can be selected per domain.", 'fontimator' ), $max_families_per_font ),
					'maximumFamiliesLimit' => $max_families_per_font,
				)
			);

			$this->handle_reseller_domains_actions( $subscription );
			$this->print_reseller_domains_table( $subscription->get_meta( 'ftm_reseller_domains' ) ?: array() );
		}
	}
}
