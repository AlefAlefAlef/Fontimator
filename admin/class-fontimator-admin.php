<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://alefalefalef.co.il
 * @since      2.0.0
 *
 * @package    Fontimator
 * @subpackage Fontimator/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Fontimator
 * @subpackage Fontimator/admin
 * @author     Reuven Karasik <rubik@karasik.org>
 */
class Fontimator_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    2.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Fontimator_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Fontimator_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		// wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/fontimator-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    2.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Fontimator_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Fontimator_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		// wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/fontimator-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function can_edit_orders() {
		return true;
	}

	public function maybe_generate_complete_family_eligible_emails_list ( $screen ) {
		if ( ! strpos($screen->id, "fontimator-config") == true || ! isset( $_GET['generate_complete_family_eligible_list'] ) ) {
			return;
		}

		// Settings for long requests
		ini_set('max_execution_time', 6000); // 6000 seconds = 100 minutes
		ob_end_flush();
		ob_implicit_flush( true );
		ob_end_flush();

		// Use file
		$time = date('d-m-Y_His');
		$emails_file = fopen("/var/www/output/family_eligible_emails_$time.txt", 'a') or die("Unable to open file!");//opens file in append mode

		$acf = Fontimator::acf();
		$limited_to_fonts = $acf->get_acf_field( 'complete_family_limit_fonts', 'options' );
		$limit_days = $acf->get_acf_field( 'complete_family_limit_days_from_purchase', 'options' );


		$all_users = get_users( array( 
			'fields' => array( 'ID' ),
			// 'number' => 500,
			// 'offset' => 500 * ( $_GET['half_thousand'] ?: 0 )
		) );

		foreach ( $all_users as $user ) {
			$customer_id = $user->ID;

			// Skip if has subscription
			$subscription = wcs_user_has_subscription( $customer_id, '', 'active' );
			if ( $subscription ) {
				continue;
			}

			// Skip if not subscribed
			$userdata = get_userdata($customer_id);
			$user_email = $userdata->user_email;
			if ( Fontimator::mc()->enabled() ) {
				if ( ! Fontimator::mc()->is_user_subscribed( $user_email ) ) {
					continue;
				}
			}


			$downloads = wc_get_customer_available_downloads( $customer_id );
			$fonts = [];
			foreach ( $downloads as $download ) {
				$variation = new Fontimator_Font_Variation( $download['product_id'] );
				if ( ! $variation 
					|| $variation->get_license_type() !== 'otf' 
					|| strpos( $variation->get_family(), 'membership') !== false
					|| $variation->get_family() === 'membership-reseller' ) {
					continue;
				}
				$family_name = $variation->get_family();

				// Check if font is in banner whitelist
				if ( $limited_to_fonts && ! in_array( $variation->get_parent_id(), $limited_to_fonts ) ) {
					continue;
				}

				if ( ! isset( $fonts[$family_name] ) ) {
					// Check if font was purchased long enough ago, if this is first weight of font
					if ( $limit_days ) {
						$first_order = wc_get_order( $download['order_id'] );
						$date_created       = $first_order->get_date_created();
						$timestamp_created	= $date_created->getTimestamp();

						$datetime_now       = new WC_DateTime(); // Get now datetime (from Woocommerce datetime object)
						$timestamp_now      = $datetime_now->getTimestamp(); // Get now timestamp

						$time_delta         = $timestamp_now - $timestamp_created; // Difference in seconds
						$days_in_seconds    = $limit_days * 24 * 60 * 60; // x days in seconds
						
						if ( $time_delta < $days_in_seconds ) {
							continue; // Skip fonts purchased too recently.
						}
					}
					$fonts[$family_name] = [];
				}

				$fonts[$family_name][] = $variation->get_weight();

				if ( $variation->get_weight() === '000-familybasic' ) {
					$font = new Fontimator_Font( $variation->get_parent_id() );
					$fonts[$family_name] = array_unique( array_merge( $fonts[$family_name], $font->get_familybasic_weights( 'slug' ) ) );
				}
			} // Downloads loop

			// Now, after we went over all the downloads, let's check every font for missing weights
			foreach ( $fonts as $font_name => $purchased_weights ) {
				if ( ! $font_id = get_page_by_path( $family_name, OBJECT, 'product' )->ID ) {
					continue;
				}
				$font = new Fontimator_Font( $font_id );
				$visible_weights = $font->get_visible_weights( 'slug' );
				if ( ! $visible_weights ) {
					continue;
				}
				$not_purchased_weights = array_diff( $visible_weights, $purchased_weights, [ '000-variable', '000-familybasic' ] );
				if ( count( $purchased_weights ) // There are desktop weights, not just web/app
					&& count( $not_purchased_weights ) // There are weights not yet purchased
					&& ! in_array( '000-family', $purchased_weights ) // Person doesn't have both the entire family...
					&& ! in_array( '000-variable', $purchased_weights ) // and the variable font.
				) {	
					fwrite($emails_file, $user_email . ",\n");
					break;
				}
			}
		} // Customers loop

		fwrite($emails_file, "#### DONE ####\n");
		fclose($emails_file);
		die();
	}

	public function maybe_sync_retroactively_all_subscriptions ( $screen ) {
		if ( ! strpos($screen->id, "fontimator-config") == true || ! isset( $_GET['sync_retroactively_all_subscriptions'] ) ) {
			return;
		}
		
		// Settings for long requests
		ini_set('max_execution_time', 6000); // 6000 seconds = 100 minutes
		ob_end_flush();
		ob_implicit_flush( true );
		ob_end_flush();

		// Use file
		$time = date('d-m-Y_His');
		$log_file = fopen("/var/www/output/sync_retroactively_all_subscriptions_$time.txt", 'a') or die("Unable to open file!");//opens file in append mode

		$members_to_update = array();
		$inactives = array();

		$args = array(
			'subscriptions_per_page' => -1,
		);
		$subscriptions = wcs_get_subscriptions( $args );
		foreach ( $subscriptions as $subscription ) {
			$email = $subscription->get_billing_email();
			if ( ! $email ) {
				continue; // Apparently that can happen
			}

			$status = $subscription->get_status();

			if ( 'active' === $status ) {
				$members_to_update[ $email ] = true;
			} else {
				$inactives[] = $email;
			}
			
		}
		
		foreach ( $inactives as $inactive ) {
			// If a user has both active and inactive subscriptions, we want to add him but not remove him.
			if ( ! in_array( $inactive, array_keys( $members_to_update ) ) ) {
				$members_to_update[ $inactive ] = false;
			}
		}

		$group_id = Fontimator::mc()->subscription_sync_group;
		$api_results = Fontimator::mc()->bulk_update_group_subscribers( $group_id, $members_to_update );

		echo '<h1>Note to self: read the response, GET `/batches/{batch_id}`, take the URL from _links field and GET it with the API key using POSTMAN or something. There there is a ZIP file you can download with all the responses.</h1>';
		var_dump( $api_results );
		
		fwrite($log_file, "API Response:\n" . var_export( $api_results, true ));
		fwrite($log_file, "#### DONE ####\n");
		fclose($log_file);
		die();
	}

	public function add_delete_user_link($user_object) {
		if ( ! is_multisite() && get_current_user_id() != $user_object->ID && current_user_can( 'delete_user', $user_object->ID ) ) {
			echo "<a class='button button-link-delete' href='" . wp_nonce_url( "users.php?action=delete&amp;user=$user_object->ID", 'bulk-users' ) . "'>" . 
				sprintf( __( 'Delete &#8220;%s&#8221; permanently' ), $user_object->display_name ) . '</a>';
		}
	}

	public function add_reseller_domains_meta_box() {
		add_meta_box(
      'ftm-reseller-domains',
      _x( 'Domains registered for this subscription', 'Meta box title of reseller domains in admin area', 'fontimator' ),
      array( $this, 'print_reseller_domains_meta_box' ),
      array( 'shop_subscription' )
    );
	}

	public function print_reseller_domains_meta_box( $post ) {
		$subscription = new WC_Subscription( $post );
		if ( ! Fontimator_WooCommerce::is_subscription_of_type( $subscription, 'membership-reseller' ) ) {
			return false;
		}

		$domains = $subscription->get_meta( 'ftm_reseller_domains' ) ?: array();
		?>
		<div class="woocommerce_subscriptions_related_orders"> <!-- copying the classname since it's already styled, feel free to remove in the future -->
			<table class="table">
				<?php if ( !empty( $domains ) ) { ?>
					<thead>
						<tr>
							<th class="domain-added"><?php _ex( 'Added on', 'Column in the reseller domains table', 'fontimator' ); ?></th>
							<th class="domain-name"><?php _e( 'Domain Name', 'fontimator' ); ?></th>
							<th class="domain-families"><?php _ex( 'Font/s', 'Column in the reseller domains table', 'fontimator' ); ?></th>
						</tr>
					</thead>
				<?php } ?>

				<tbody class="reseller-domains-list">
					<?php foreach ( (array) $domains as $domain => $meta ) {
						$families_ids = $meta['families'] ?: array();
						?>
						<tr>
							<td class="domain-added" title="<?php echo esc_attr( date_i18n( 'Y-m-d h:i:s', $meta['timestamp'] ?: 0 ) ); ?>"><?php echo esc_html( $meta['timestamp'] ? date_i18n( get_option( 'date_format' ), $meta['timestamp'] ) : '-' ); ?></td>
							<td class="domain-name" scope="row"><a href="//<?php echo $domain; ?>" target="_blank"><?php echo esc_html( $domain ); ?></a></td>
							<td class="domain-families" scope="row"><?php
							foreach ( $families_ids as $font_id ) {
								?>
								<a href="<?php echo esc_attr( get_edit_post_link( $font_id ) ); ?>"><?php echo esc_html( wc_get_product( $font_id )->get_name() ); ?></a>
								<?php
							}
							?></td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
