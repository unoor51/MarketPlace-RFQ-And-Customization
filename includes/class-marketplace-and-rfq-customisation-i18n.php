<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.fiverr.com/gemini002
 * @since      1.0.0
 *
 * @package    Marketplace_And_Rfq_Customisation
 * @subpackage Marketplace_And_Rfq_Customisation/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Marketplace_And_Rfq_Customisation
 * @subpackage Marketplace_And_Rfq_Customisation/includes
 * @author     Noor <unoor51@gmail.com>
 */
class Marketplace_And_Rfq_Customisation_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'marketplace-and-rfq-customisation',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
