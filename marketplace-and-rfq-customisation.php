<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.fiverr.com/gemini002
 * @since             1.0.0
 * @package           Marketplace_And_Rfq_Customisation
 *
 * @wordpress-plugin
 * Plugin Name:       Marketplace and RFQ Customisation Part 3
 * Plugin URI:        https://extended.eatr.ph/
 * Description:       This plugin is for managing custom customization related to Marketplace and RFQ
 * Version:           1.0.0
 * Author:            Noor
 * Author URI:        https://www.fiverr.com/gemini002
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       marketplace-and-rfq-customisation
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'MARKETPLACE_AND_RFQ_CUSTOMISATION_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-marketplace-and-rfq-customisation-activator.php
 */
function activate_marketplace_and_rfq_customisation() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-marketplace-and-rfq-customisation-activator.php';
	Marketplace_And_Rfq_Customisation_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-marketplace-and-rfq-customisation-deactivator.php
 */
function deactivate_marketplace_and_rfq_customisation() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-marketplace-and-rfq-customisation-deactivator.php';
	Marketplace_And_Rfq_Customisation_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_marketplace_and_rfq_customisation' );
register_deactivation_hook( __FILE__, 'deactivate_marketplace_and_rfq_customisation' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-marketplace-and-rfq-customisation.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_marketplace_and_rfq_customisation() {

	$plugin = new Marketplace_And_Rfq_Customisation();
	$plugin->run();

}
run_marketplace_and_rfq_customisation();
