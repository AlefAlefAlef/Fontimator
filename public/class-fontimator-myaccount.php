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

			$zipomator_base_url = Zipomator::zipomator_url();
			wp_localize_script( 'fontimator-my-account', 'FontimatorDownloadCheckboxesButtons', array(
				'zipomatorBaseURL' => $zipomator_base_url,
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
			'download-file'      => __( 'Download', 'fontimator' ),
			'download-actions'   => '&nbsp;',
		);
	}

	public function prepend_icon_to_download_name( $download ) {
		$order_id = $download['order_id'];

		$membership = new Fontimator_Membership( $order_id );
		if ( $membership && 'active' === $membership->get_status() ) {
			$membership_variation = new Fontimator_Font_Variation( $membership->get_variation_id() );
			echo '<a href="' . $membership_variation->get_permalink() . '" title="' . $membership_variation->get_title() . '"><i class="icon" data-icon="א"></i></a>';
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

	public function font_weight_for_download( $download ) {
		$weight_attribute = FTM_WEIGHT_ATTRIBUTE;
		echo $this->get_font_attribute_for_download( $download, $weight_attribute );
	}

	public function font_license_for_download( $download ) {
		$license_attribute = FTM_LICENSE_ATTRIBUTE;
		echo $this->get_font_attribute_for_download( $download, $license_attribute );
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
			'download_url'        => $variation_download->get_file(),
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
					$font_variation_for_membership = $font->get_variation_for_membership( $membership_license );

					if ( ! $font_variation_for_membership || ! $font_variation_for_membership->is_downloadable() ) {
						continue;
					} else {
						$downloads[] = $this->get_new_download_obj( $font_variation_for_membership, $membership_id );
					}
				}
			}
		}
		return $downloads;
	}

	public function wsms_add_gifts_downloads_table( $downloads ) {
		global $wsms_instance;

		if ( class_exists( 'WSMS' ) ) {
			if ( ! $wsms_instance ) {
				$wsms_instance = new WSMS;
			}

			$merge_fields = $wsms_instance->get_user_merge_fields();
			$font_gifts = (array) get_field( 'mailchimp_font_gifts', 'options' );

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
		$variation = new Fontimator_Font_Variation( $download['product_id'] );
		if ( ! $variation->get_weight() ) {
			return; // No checkbox for memberships
		}
		$family = $variation->get_family();
		$weight = Zipomator::get_clean_weight( $variation->get_weight() );
		$license = $variation->get_license();
		$item_string = sprintf( '{%s,%s,%s}', $family, $weight, $license );
		?>

		<input type="checkbox" name="fontimator-downloads" value="<?php echo $item_string; ?>" id="fontimator_download_<?php echo $download['product_id']; ?>" />

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

}
