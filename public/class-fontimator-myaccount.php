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
			wp_localize_script( 'fontimator-my-account', 'FontimatorDownloadCheckboxesButtons', array(
				'zipomatorBaseURL' => $zipomator_base_url,
				'zipomatorNonce' => $zipomator_nonce,
				'disabledText' => __( 'Select some fonts using the small checkboxes first.' , 'fontimator' ),
			) );

			wp_localize_script( 'fontimator-my-account', 'FontimatorSubscriptionActions', array(
				'cancelConfirmationText' => esc_html__( "Are you sure? You can't re-enable the membership without purchasing it again.", 'fontimator' ),
			) );
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
			echo '<a href="' . $membership_variation->get_permalink() . '" title="' . $membership_variation->get_title() . '"><i class="icon" data-icon="ö"></i></a>';
			echo ' <span class="downloads-table-icon-membership-seperator">&rsaquo;</span> ';
		}

		if ( 'academic' === $order_id ) {
			echo '<a href="/eula/?license=otf-accademic" title="' . __( 'Your Academic License', 'fontimator' ) . '"><i class="icon" data-icon="Þ"></i></a>';
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

	protected function get_new_download_obj( $variation, $order_id ) {
		$variation_downloads = $variation->get_downloads();
		$variation_download = reset( $variation_downloads );
		if ( ! $variation_download ) {
			return array();
		}

		return array(
			'download_url'        => Zipomator::get_nonced_url( $variation->get_id() ),
			'download_id'         => $variation_download->get_id(),
			'product_id'          => $variation->get_id(),
			'product_name'        => strip_tags( $variation->get_name() ),
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

	public function get_all_fonts_downloads( $license, $order_id ) {
		$downloads = array();
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
				$downloads[] = $this->get_new_download_obj( $font_variation_for_membership, $order_id );
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

				$membership_downloads = $this->get_all_fonts_downloads( $membership_license, $membership_id );
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

					$gift_variation = $font->get_matching_variation( $font_weight, $font_license );

					if ( ! $gift_variation || ! $gift_variation->is_downloadable() ) {
						continue;
					} else {
						$downloads[] = $this->get_new_download_obj( $gift_variation, 'mailchimp_font_gift' );
					}
				}
			}
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
				$academic_downloads = $this->get_all_fonts_downloads( 'otf-2', 'academic' );
				$downloads = array_merge( $downloads, $academic_downloads );
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

	public function downloads_table_buttons() {
		?>
		<div class="fontimator-buttons">
			<button class="fontimator-bulk-download button alt" type="submit" disabled><?php _e( 'Download Selected', 'fontimator' ); ?></button>
			<button class="fontimator-select-all button alt" type="button"><?php _e( 'Select All', 'fontimator' ); ?></button>
			<button class="fontimator-unselect-all button alt" type="button"><?php _e( 'Unselect All', 'fontimator' ); ?></button>
		</div>
		<?php
	}

	public function add_edit_account_to_edit_address() {
		WC_Shortcode_My_Account::edit_address( false );
	}

}
