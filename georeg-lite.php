<?php
/*
Plugin Name: GeoReg Lite
Plugin URI: https://wordpress.org/plugins/georeg-lite/
Description: A plugin that magically captures the IP address, country name, and country code of new users who register on your site. A full-blown <a href="http://marketplace.appthemes.com/plugins/georeg/" target="_blank">GeoReg plugin</a> is available in the <a href="http://marketplace.appthemes.com/" target="_blank">AppThemes Marketplace</a>.

Version: 1.1
Author: AppThemes
Author URI: http://www.appthemes.com
Text Domain: georeg-lite
*/


/**
 * Setup plugin when full version not installed.
 *
 * @return void
 */
function at_georeg_lite_setup() {
	// do not load when full version installed
	if ( class_exists( 'AT_GeoReg' ) ) {
		return;
	}

	// load file with class
	if ( ! class_exists( 'AT_GeoReg_Lite' ) ) {
		require_once( dirname( __FILE__ ) . '/class-georeg-lite.php' );
	}

	new AT_GeoReg_Lite();
}
add_action( 'plugins_loaded', 'at_georeg_lite_setup', 11 );
