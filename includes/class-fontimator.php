<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://alefalefalef.co.il
 * @since      2.0.0
 *
 * @package    Fontimator
 * @subpackage Fontimator/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      2.0.0
 * @package    Fontimator
 * @subpackage Fontimator/includes
 * @author     Reuven Karasik <rubik@karasik.org>
 */
class Fontimator {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var      Fontimator_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The ACF instance.
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var      Fontimator_ACF    $acf
	 */
	protected $acf;
	
	/**
	 * The MailChimp instance.
	 *
	 * @since    2.4.0
	 * @access   protected
	 * @var      Fontimator_MC    $mc
	 */
	protected $mc;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	protected static $instance = null;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    2.0.0
	 */
	public function __construct() {
		if ( defined( 'FONTIMATOR_VERSION' ) ) {
			$this->version = FONTIMATOR_VERSION;
		} else {
			$this->version = '2.0.0';
		}
		$this->plugin_name = 'fontimator';

		if ( ! class_exists('WooCommerce') || ! class_exists( 'WC_Subscription' ) ) {
			return;
		}

		$this->load_includes();

		$this->loader = new Fontimator_Loader();
		$this->acf = new Fontimator_ACF();
		$this->mc = new Fontimator_MC();

		$this->define_constants();
		$this->define_plugin_dependencies();
		$this->set_locale();
		$this->define_global_hooks();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_myaccount_hooks();
		
		// Must run after define_constants()
		$this->acf->config();
	}

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Fontimator_Loader. Orchestrates the hooks of the plugin.
	 * - Fontimator_I18n. Defines internationalization functionality.
	 * - Fontimator_Admin. Defines all hooks for the admin area.
	 * - Fontimator_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function load_includes() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fontimator-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fontimator-i18n.php';

		/**
		 * The class responsible for defining all ACF fields and defaults.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fontimator-acf.php';
		
		/**
		 * The class responsible for all MailChimp interactions and integrations.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fontimator-mc.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-mc4wp-woocommerce-integration.php';

		/**
		 * The classes that extend WooCommerce functionality.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fontimator-font.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fontimator-font-variation.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fontimator-membership.php';

		/**
		 * The Zipomator classes.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-zipomator-font-package.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-zip-file.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-pdf-file.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-zipomator-eula.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-zipomator.php';

		/**
		 * The class responsible for WooCommerce actions and overrides.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fontimator-woocommerce.php';

		/**
		 * The helper class with Product Queries.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fontimator-query.php';

		/**
		 * The class for free downloads.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fontimator-free-download.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-fontimator-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-fontimator-variations-helper.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-fontimator-public.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-fontimator-myaccount.php';

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Fontimator_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Fontimator_I18n();
		$plugin_i18n->load_plugin_textdomain();

	}

	private function define_constants() {
		$fonts_directory = $this->acf->get_default( 'fonts_directory', 'options' );
		$site_prefix = $this->acf->get_default( 'site_prefix', 'options' );
		$site_name = $this->acf->get_default( 'site_name', 'options' );
		$license_attribute = $this->acf->get_default( 'license_attribute', 'options' );
		$weight_attribute = $this->acf->get_default( 'weight_attribute', 'options' );

		if ( ! defined( 'FTM_FONTS_PATH' ) ) {
			define( 'FTM_FONTS_PATH', WP_CONTENT_DIR . '/' . $fonts_directory . '/' );
		}
		if ( ! defined( 'FTM_FONTS_URL' ) ) {
			define( 'FTM_FONTS_URL', trailingslashit( content_url( $fonts_directory ) ) );
		}
		if ( ! defined( 'FTM_SITE_PREFIX' ) ) {
			define( 'FTM_SITE_PREFIX', $site_prefix );
		}
		if ( ! defined( 'FTM_SITE_NAME' ) ) {
			define( 'FTM_SITE_NAME', $site_name );
		}
		if ( ! defined( 'FTM_LICENSE_ATTRIBUTE' ) ) {
			define( 'FTM_LICENSE_ATTRIBUTE', $license_attribute );
		}
		if ( ! defined( 'FTM_WEIGHT_ATTRIBUTE' ) ) {
			define( 'FTM_WEIGHT_ATTRIBUTE', $weight_attribute );
		}
	}

	private function define_plugin_dependencies() {

		$this->loader->add_dependency( 'plugin', 'woocommerce/woocommerce.php' );
		$this->loader->add_dependency( 'plugin', 'woocommerce-subscriptions/woocommerce-subscriptions.php' );
		$this->loader->add_dependency( 'class', 'acf' );
	}

	private function define_global_hooks() {
		// Zipomator
		$zipomator = new Zipomator();
		$zipomator->add_rewrite_rules();
		$this->loader->add_filter( 'query_vars', $zipomator, 'query_vars', 30, 2 );
		$this->loader->add_action( 'parse_request', $zipomator, 'parse_request' );
		
		// MailChimp
		if ($this->mc->enabled()) {
			// ACF fields
			$this->loader->add_action( 'acf/init', $this->mc, 'set_private_config' );
			$this->loader->add_filter( 'acf/load_field/name=ftm_main_list', 		$this->mc, 'populate_acf_field_with_mailchimp_lists' );
			$this->loader->add_filter( 'acf/load_field/name=ftm_academic_group', $this->mc, 'populate_acf_field_with_mailchimp_group_categories' );
			$this->loader->add_filter( 'acf/load_field/name=ftm_freefonts_group', $this->mc, 'populate_acf_field_with_mailchimp_groups' );
			$this->loader->add_filter( 'acf/load_field/name=ftm_interest_group', $this->mc, 'populate_acf_field_with_mailchimp_groups' );
			$this->loader->add_filter( 'acf/load_field/name=ftm_gender_merge_field', 			$this->mc, 'populate_acf_field_with_mailchimp_merge_fields' );
			$this->loader->add_filter( 'acf/load_field/name=ftm_subscription_sync_group', 	$this->mc, 'populate_acf_field_with_mailchimp_groups' );
			$this->loader->add_filter( 'acf/load_field/name=ftm_subscribe_groups', 	$this->mc, 'populate_acf_field_with_mailchimp_groups' );

			// Subscribe to the correct group
			$this->loader->add_filter( 'mc4wp_subscriber_data', $this->mc, 'add_subscriber_to_groups' );

			// Sync subscriptions
			$this->loader->add_action( 'woocommerce_subscription_status_updated', $this->mc, 'update_subscription_status', 10, 3 );
		}

		// WooCommerce
		$fontimator_woocommerce = new Fontimator_WooCommerce();

		// Allow automatic updates
		$this->loader->add_filter( 'automatic_updates_is_vcs_checkout', $this, '_return_false' );

		// Add variations from ftm-add-to-cart
		$this->loader->add_filter( 'wp_loaded', $fontimator_woocommerce, 'add_variations_from_url' );
		$this->loader->add_action( 'woocommerce_before_calculate_totals', $fontimator_woocommerce, 'apply_session_discounts', 90 );

		// Remove the quantity field from WooCommerce Product
		$this->loader->add_filter( 'woocommerce_is_sold_individually', $this, '_return_true' );

		// Forgot password hack to sign up users on the fly
		if ( $this->mc->enabled() ) {
			remove_action( 'wp_loaded', array( 'WC_Form_Handler', 'process_lost_password' ), 20 ); // Replace with ours
			$this->loader->add_action( 'wp_loaded', $fontimator_woocommerce, 'process_lost_password', 20 );
			$this->loader->add_filter( 'authenticate', $fontimator_woocommerce, 'check_email_mailchimp_on_login', 90, 3 );
		}

		// Timeout limit
		$this->loader->add_filter( 'woocommerce_json_search_limit', $fontimator_woocommerce, 'woocommerce_json_search_limit' );

		// Trim zeros in price decimals
		$this->loader->add_filter( 'woocommerce_price_trim_zeros', $this, '_return_true' );
		$this->loader->add_filter( 'formatted_woocommerce_price', $fontimator_woocommerce, 'limit_decimals_to_two', 10, 5 );
		$this->loader->add_filter( 'wc_price', $fontimator_woocommerce, 'add_span_to_decimals_in_price' );

		// Remove billing fields
		$this->loader->add_filter( 'woocommerce_billing_fields', $fontimator_woocommerce, 'custom_billing_fields' );
		$this->loader->add_filter( 'woocommerce_checkout_fields', $fontimator_woocommerce, 'custom_billing_fields' );

		// Variations in font name
		$this->loader->add_filter( 'woocommerce_product_variation_title_include_attributes', $this, '_return_false' );
		$this->loader->add_filter( 'woocommerce_is_attribute_in_product_name', $this, '_return_false' );

		// Email columns and quantity
		$this->loader->add_filter( 'woocommerce_email_order_item_quantity', $this, '_return_empty' );
		$this->loader->add_filter( 'woocommerce_email_downloads_columns', $fontimator_woocommerce, 'remove_expires_head_from_email' );
		$this->loader->add_filter( 'woocommerce_email_downloads_column_download-expires', $this, '_return_false' );

		// When user has no subscriptions, link to a subscription in their dashboard
		$this->loader->add_filter( 'woocommerce_subscriptions_message_store_url', $fontimator_woocommerce, 'subscription_message_store_url_to_product' );

		// Price Ranges
		$this->loader->add_filter( 'woocommerce_get_price_html_from_text', $fontimator_woocommerce, 'woocommerce_get_price_html_from_text' );
		$this->loader->add_filter( 'woocommerce_variable_price_html', $fontimator_woocommerce, 'woocommerce_variable_price_html', 10, 2 );
		$this->loader->add_filter( 'woocommerce_variable_sale_price_html', $fontimator_woocommerce, 'woocommerce_variable_price_html', 10, 2 );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$this->loader->add_action( 'plugins_loaded', $this->loader, 'check_dependencies' );

		$plugin_admin = new Fontimator_Admin( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_filter( 'wc_order_is_editable', $plugin_admin, 'can_edit_orders' );

		// Custom actions in admin
		$this->loader->add_action( 'current_screen', $plugin_admin, 'maybe_generate_complete_family_eligible_emails_list' );
		$this->loader->add_action( 'current_screen', $plugin_admin, 'maybe_sync_retroactively_all_subscriptions' );

		$variations_helper = new Fontimator_Variations_Helper( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_enqueue_scripts', $variations_helper, 'enqueue_scripts' );
		$this->loader->add_action( 'woocommerce_variable_product_before_variations', $variations_helper, 'variable_product_toolbar' );
		$this->loader->add_action( 'woocommerce_product_options_attributes', $variations_helper, 'product_options_update_attributes_notice' );
		$this->loader->add_action( 'wp_ajax_fontimator_setup_variations', $variations_helper, 'ajax_setup_variations' );
		$this->loader->add_action( 'admin_head-edit.php', $variations_helper, 'http_setup_variations' );
		$this->loader->add_action( 'admin_head-edit.php', $variations_helper, 'http_generate_variations' );
		$this->loader->add_filter( 'post_row_actions', $variations_helper, 'add_product_row_actions', 30, 2 );
		$this->loader->add_action( 'woocommerce_variation_options', $variations_helper, 'print_ignore_checkbox', 10, 3 );
		$this->loader->add_action( 'woocommerce_save_product_variation', $variations_helper, 'save_ignore_checkbox_value' );

		$this->loader->add_action( 'edit_user_profile', $variations_helper, 'add_delete_user_link', 90 );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new Fontimator_Public( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		// DevTools banner
		$this->loader->add_action( 'wp_footer', $plugin_public, 'devtools_detect_notice' );

		// WooCommerce Product Archive Page
		$this->loader->add_filter( 'woocommerce_catalog_orderby', $plugin_public, 'hide_sorting_options_from_dropdown' );

		// WooCommerce Product Page
		$this->loader->add_filter( 'woocommerce_dropdown_variation_attribute_options_args', $plugin_public, 'hide_dead_weights_from_dropdown' );
		$this->loader->add_filter( 'woocommerce_variation_option_name', $plugin_public, 'add_family_calculation_to_dropdown' );
		$this->loader->add_filter( 'woocommerce_dropdown_variation_attribute_options_html', $plugin_public, 'group_licenses_dropdown', 100, 2 );
		$this->loader->add_action( 'woocommerce_single_product_summary', $plugin_public, 'show_already_bought_notice' );

		// LicenseApp Field
		$this->loader->add_action( 'woocommerce_before_add_to_cart_button', $plugin_public, 'display_licenseapp_field' );
		$this->loader->add_filter( 'woocommerce_add_to_cart_validation', $plugin_public, 'validate_licenseapp_field', 10, 4 );
		$this->loader->add_filter( 'woocommerce_add_cart_item_data', $plugin_public, 'add_licenseapp_cart_data', 10, 4 );
		$this->loader->add_filter( 'woocommerce_get_item_data', $plugin_public, 'add_licenseapp_get_item_data', 10, 2 );
		$this->loader->add_filter( 'woocommerce_checkout_create_order_line_item', $plugin_public, 'add_licenseapp_to_order_line_item', 10, 4 );

		// Discount Field
		$this->loader->add_filter( 'woocommerce_cart_item_price', $plugin_public, 'add_discount_price_to_cart', 10, 3 );

		// WooCommerce Cart Page
		$this->loader->add_action( 'woocommerce_cart_actions', $plugin_public, 'display_share_cart_url' );
		
		// WooCommerce Checkout Page
		$this->loader->add_filter( 'woocommerce_get_terms_and_conditions_checkbox_text', $plugin_public, 'terms_and_conditions_checkbox_text' );
		$this->loader->add_filter( 'woocommerce_registration_error_email_exists', $plugin_public, 'returning_customers_custom_message', 10, 2 );

		// Shortcodes
		$this->loader->add_shortcode( 'fontimator-zip-table', $plugin_public, 'shortcode_zip_table' );
		$this->loader->add_shortcode( 'fontimator-eula', $plugin_public, 'shortcode_eula' );
		$this->loader->add_shortcode( 'fontimator-sale-products', $plugin_public, 'shortcode_sale_products' );
		$this->loader->add_shortcode( 'fontimator-free-download', $plugin_public, 'shortcode_free_download' );
		$this->loader->add_shortcode( 'fontimator-genderize', $plugin_public, 'shortcode_genderize' );

	}

	/**
	 * Register all of the hooks related to the WooCommerce My Account Dashboard.
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function define_myaccount_hooks() {		
		// Resource: https://businessbloomer.com/woocommerce-visual-hook-guide-account-pages/

		$myaccount = new Fontimator_MyAccount( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'wp_enqueue_scripts', $myaccount, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $myaccount, 'enqueue_scripts' );

		$this->loader->add_action( 'woocommerce_account_content', $myaccount, 'add_myaccount_notice_not_subscribed', 10 );

		// Adds the edit address section to edit-account. Disabled because we don't need it.
		// $this->loader->add_action( 'woocommerce_after_edit_account_form', $myaccount, 'add_edit_account_to_edit_address' );

		// Override templates
		$this->loader->add_filter( 'woocommerce_locate_template', $myaccount, 'locate_template', 10, 3 );

		// Override downloads table
		remove_action( 'woocommerce_available_downloads', 'woocommerce_order_downloads_table', 10 );
		$this->loader->add_action( 'woocommerce_available_downloads', $myaccount, 'get_myaccount_template', 10 );

		// Add columns to download table
		$this->loader->add_filter( 'woocommerce_account_downloads_columns', $myaccount, 'add_columns_to_downloads_table' );
		$this->loader->add_filter( 'woocommerce_email_downloads_columns', $myaccount, 'add_columns_to_downloads_table' );

		$this->loader->add_action( 'woocommerce_account_downloads_column_download-product', $myaccount, 'prepend_icon_to_download_name', 10 );
		$this->loader->add_action( 'woocommerce_account_downloads_column_download-product', $myaccount, 'append_weight_to_download_name', 20 );

		$this->loader->add_action( 'woocommerce_account_downloads_column_download-font-version', $myaccount, 'font_version_for_download' );
		$this->loader->add_action( 'woocommerce_email_downloads_column_download-font-version', $myaccount, 'font_version_for_download' );
		$this->loader->add_action( 'woocommerce_account_downloads_column_download-font-license', $myaccount, 'font_license_for_download' );
		$this->loader->add_action( 'woocommerce_email_downloads_column_download-font-license', $myaccount, 'font_license_for_download' );

		$this->loader->add_action( 'woocommerce_account_downloads_column_download-select', $myaccount, 'checkbox_for_download' );

		$this->loader->add_action( 'woocommerce_before_account_downloads', $myaccount, 'reset_all_downloads' );
		$this->loader->add_action( 'woocommerce_after_available_downloads', $myaccount, 'downloads_table_buttons' );

		// Add dynamic downloads
		$this->loader->add_filter( 'woocommerce_customer_get_downloadable_products', $myaccount, 'membership_add_all_fonts_downloads_table', 20 );
		$this->loader->add_filter( 'woocommerce_customer_get_downloadable_products', $myaccount, 'mc4wp_add_academic_downloads_table', 40 );
		$this->loader->add_filter( 'woocommerce_customer_get_downloadable_products', $myaccount, 'mc4wp_add_gifts_downloads_table', 50 );
		$this->loader->add_filter( 'woocommerce_customer_get_downloadable_products', $myaccount, 'add_free_fonts_downloads_table', 60 );
		$this->loader->add_filter( 'woocommerce_customer_get_downloadable_products', $myaccount, 'sort_downloads_by_family', 100 );

		// Add complete-family banner
		$this->loader->add_action( 'ftm_account_downloads_after_group', $myaccount, 'complete_family_banner', 10, 2 );

		// Add footnotes
		$this->loader->add_action( 'woocommerce_after_account_downloads', $myaccount, 'add_message_after_downloads' );

		// Birthday messege on downloads page
		$this->loader->add_action( 'woocommerce_before_account_downloads', $myaccount, 'birthday_message_on_downlaods_page' );
		
		// Add gender to edit account page & MailChimp Tab
		if ( $this->mc->enabled() ) {
			// Gender & Bday Fields
			$this->loader->add_action( 'woocommerce_edit_account_form', $myaccount, 'add_gender_field_to_edit_account' );
			$this->loader->add_action( 'woocommerce_save_account_details', $myaccount, 'save_gender_field_on_edit_account' );
			
			// MailChimp Tab - source: https://businessbloomer.com/woocommerce-add-new-tab-account-page/
			$this->loader->add_action( 'init', $myaccount, 'add_email_preferences_tab_rewrite' );
			$this->loader->add_filter( 'query_vars', $myaccount, 'add_email_preferences_tab_query_var', 0 );
			$this->loader->add_filter( 'woocommerce_account_menu_items', $myaccount, 'add_email_preferences_tab_menu_item' );
			$this->loader->add_action( 'woocommerce_account_email-preferences_endpoint', $myaccount, 'email_preferences_tab_content' );
			$this->loader->add_action( 'template_redirect', $myaccount, 'save_email_preferences' );
		}

		// Disable cancelation & modificaton
		$this->loader->add_filter( 'wcs_view_subscription_actions', $myaccount, 'disable_subsciprion_cancellation', 10, 2 );
		$this->loader->add_filter( 'wcs_can_items_be_removed', $myaccount, 'remove_items_from_subscription' );
		$this->loader->add_filter( 'wcs_can_item_be_removed', $myaccount, 'remove_items_from_subscription' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    2.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     2.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     2.0.0
	 * @return    Fontimator_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * The reference to the class that deals with ACF.
	 *
	 * @since     2.0.0
	 * @return    Fontimator_ACF
	 */
	public function get_acf() {
		return $this->acf;
	}

	/**
	 * Alias to Fontimator->get_instance()->get_acf()
	 *
	 * @since     2.4.0
	 * @return Fontimator_ACF
	 */
	public static function acf() {
		return self::get_instance()->get_acf();
	}

	/**
	 * The reference to the class that deals with MailChimp.
	 *
	 * @since     2.4.0
	 * @return    Fontimator_MC
	 */
	public function get_mc() {
		return $this->mc;
	}
	
	/**
	 * Alias to Fontimator->get_instance()->get_mc()
	 *
	 * @since     2.4.0
	 * @return Fontimator_MC
	 */
	public static function mc() {
		return self::get_instance()->get_mc();
	}

	public function get_attr_name( $type ) {
		return $this->acf->get_field( $type . '_attribute', 'options' );
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     2.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	public function _return_true() {
		return true;
	}
	public function _return_false() {
		return false;
	}
	public function _return_empty() {
		return '';
	}

}
