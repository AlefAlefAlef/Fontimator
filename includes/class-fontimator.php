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
		if ( defined( 'PLUGIN_NAME_VERSION' ) ) {
			$this->version = PLUGIN_NAME_VERSION;
		} else {
			$this->version = '2.0.0';
		}
		$this->plugin_name = 'fontimator';

		$this->load_includes();
		$this->define_constants();
		$this->define_plugin_dependencies();
		$this->set_locale();
		$this->define_global_hooks();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_myaccount_hooks();

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

		$this->loader = new Fontimator_Loader();
		$this->acf = new Fontimator_ACF();

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
		$fonts_directory = $this->acf->get_field( 'fonts_path', 'options' );
		$site_prefix = $this->acf->get_field( 'site_prefix', 'options' );
		$site_name = $this->acf->get_field( 'site_name', 'options' );
		$license_attribute = $this->acf->get_field( 'license_attribute', 'options' );
		$weight_attribute = $this->acf->get_field( 'weight_attribute', 'options' );

		define( 'FTM_FONTS_PATH', WP_CONTENT_DIR . '/' . $fonts_directory . '/' );
		define( 'FTM_FONTS_URL', content_url( $fonts_directory ) );
		define( 'FTM_SITE_PREFIX', $site_prefix );
		define( 'FTM_SITE_NAME', $site_name );
		define( 'FTM_LICENSE_ATTRIBUTE', $license_attribute );
		define( 'FTM_WEIGHT_ATTRIBUTE', $weight_attribute );
	}

	private function define_plugin_dependencies() {

		$this->loader->add_dependency( 'plugin', 'woocommerce/woocommerce.php' );
		$this->loader->add_dependency( 'plugin', 'woocommerce-subscriptions/woocommerce-subscriptions.php' );
		$this->loader->add_dependency( 'class', 'acf' );
	}

	private function define_global_hooks() {
		$catalog_filename_prefix = $this->acf->get_field( 'catalog_filename_prefix', 'options' );
		$zipomator = new Zipomator( $catalog_filename_prefix );
		$zipomator->add_rewrite_rules();
		$this->loader->add_filter( 'query_vars', $zipomator, 'query_vars', 30, 2 );
		$this->loader->add_action( 'parse_request', $zipomator, 'parse_request' );

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

		// WooCommerce Product Page
		$this->loader->add_filter( 'woocommerce_dropdown_variation_attribute_options_args', $plugin_public, 'hide_dead_weights_from_dropdown' );
		$this->loader->add_filter( 'woocommerce_variation_option_name', $plugin_public, 'add_family_calculation_to_dropdown' );
		$this->loader->add_filter( 'woocommerce_dropdown_variation_attribute_options_html', $plugin_public, 'group_licenses_dropdown', 100, 2 );

		// Shortcodes
		$this->loader->add_shortcode( 'fontimator-zip-table', $plugin_public, 'shortcode_zip_table' );
		$this->loader->add_shortcode( 'fontimator-eula', $plugin_public, 'shortcode_eula' );

	}

	/**
	 * Register all of the hooks related to the WooCommerce My Account Dashboard.
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function define_myaccount_hooks() {
		$myaccount = new Fontimator_MyAccount( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'wp_enqueue_scripts', $myaccount, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $myaccount, 'enqueue_scripts' );
		
		// Adds the edit address section to edit-account. Disabled because we don't need it.
		// $this->loader->add_action( 'woocommerce_after_edit_account_form', $myaccount, 'add_edit_account_to_edit_address' );
		

		// Add columns to download table
		$this->loader->add_filter( 'woocommerce_account_downloads_columns', $myaccount, 'add_columns_to_downloads_table' );
		$this->loader->add_filter( 'woocommerce_email_downloads_columns', $myaccount, 'add_columns_to_downloads_table' );

		$this->loader->add_action( 'woocommerce_account_downloads_column_download-product', $myaccount, 'prepend_icon_to_download_name' );
		$this->loader->add_action( 'woocommerce_email_downloads_column_download-product', $myaccount, 'prepend_icon_to_download_name' );

		$this->loader->add_action( 'woocommerce_account_downloads_column_download-font-version', $myaccount, 'font_version_for_download' );
		$this->loader->add_action( 'woocommerce_email_downloads_column_download-font-version', $myaccount, 'font_version_for_download' );
		$this->loader->add_action( 'woocommerce_account_downloads_column_download-font-weight', $myaccount, 'font_weight_for_download' );
		$this->loader->add_action( 'woocommerce_email_downloads_column_download-font-weight', $myaccount, 'font_weight_for_download' );
		$this->loader->add_action( 'woocommerce_account_downloads_column_download-font-license', $myaccount, 'font_license_for_download' );
		$this->loader->add_action( 'woocommerce_email_downloads_column_download-font-license', $myaccount, 'font_license_for_download' );

		$this->loader->add_action( 'woocommerce_account_downloads_column_download-select', $myaccount, 'checkbox_for_download' );

		$this->loader->add_action( 'woocommerce_before_available_downloads', $myaccount, 'downloads_table_buttons' );
		$this->loader->add_action( 'woocommerce_after_available_downloads', $myaccount, 'downloads_table_buttons' );

		// Add dynamic downloads
		$this->loader->add_filter( 'woocommerce_customer_get_downloadable_products', $myaccount, 'membership_add_all_fonts_downloads_table' );
		$this->loader->add_filter( 'woocommerce_customer_get_downloadable_products', $myaccount, 'wsms_add_gifts_downloads_table' );
		$this->loader->add_filter( 'woocommerce_customer_get_downloadable_products', $myaccount, 'wsms_add_academic_downloads_table' );

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
		$this->acf->config();
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

}
