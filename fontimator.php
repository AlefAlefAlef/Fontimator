<?php
/**
 * The Fontimator
 *
 * Based on https://github.com/DevinVinson/WordPress-Plugin-Boilerplate
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://alefalefalef.co.il
 * @package           Fontimator
 *
 * @wordpress-plugin
 * Plugin Name:       The Fontimator
 * Plugin URI:        http://reuven.rocks
 * Description:       The famous Fontimator (FKA 'Zipomator'), which does everything here, basically. Developed for AlefAlefAlef and Fontimonim.
 * Version:           2.4.80
 * Author:            Reuven Karasik
 * Author URI:        https://alefalefalef.co.il/
 * License:           CC-NC-ND
 * License URI:       https://creativecommons.org/licenses/by-nc-nd/3.0/
 * Text Domain:       fontimator
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Using SemVer - https://semver.org
 */
define( 'FONTIMATOR_VERSION', '2.4.80' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-fontimator-activator.php
 */
function activate_fontimator() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-fontimator-activator.php';
	Fontimator_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-fontimator-deactivator.php
 */
function deactivate_fontimator() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-fontimator-deactivator.php';
	Fontimator_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_fontimator' );
register_deactivation_hook( __FILE__, 'deactivate_fontimator' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-fontimator.php';


/**
 * Load the plugin update checker
 */
require plugin_dir_path( __FILE__ ) . 'includes/lib/plugin-update-checker/plugin-update-checker.php';

 /**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    2.0.0
 */
function run_fontimator() {

	$plugin = Fontimator::get_instance();
	$plugin->run();

	// Plugin Update Checker
	$updateChecker = Puc_v4_Factory::buildUpdateChecker(
		'https://github.com/AlefAlefAlef/Fontimator/',
		__FILE__,
		'fontimator'
	);
	$updateChecker->setBranch('master');
}

/**
 * after_setup_theme here is to proceed the acf/init hook and the MC4WP integration checking
 * @since 2.4.2
 */
add_action( 'after_setup_theme', 'run_fontimator', 1 );
