<?php
/**
 * Plugin Name: SO Clean Up Yoast SEO
 * Plugin URI:  http://so-wp.com/plugin/so-clean-up-wp-seo/
 * Description: Clean up several things that the Yoast SEO plugin adds to your WordPress Dashboard
 * Author:      SO WP
 * Author URI:  http://so-wp.com/plugins/
 * Version:     1.5
 * License:     GPL3+
 */

/**
 * Prevent direct access to files
 *
 * @since 1.0
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Version check; any WP version under 4.0 is not supported (if only to "force" users to stay up to date)
 * 
 * adapted from example by Thomas Scholz (@toscho) http://wordpress.stackexchange.com/a/95183/2015, Version: 2013.03.31, Licence: MIT (http://opensource.org/licenses/MIT)
 *
 * @since 1.1
 */

//Only do this when on the Plugins page.
if ( ! empty ( $GLOBALS['pagenow'] ) && 'plugins.php' === $GLOBALS['pagenow'] )

	/* so_cuws_ prefix is derived from [so] [c]lean [u]p [w]p [s]eo. */
	add_action( 'admin_notices', 'so_cuws_check_admin_notices', 0 );

function so_cuws_min_wp_version() {
	global $wp_version;
	$require_wp = '4.0';
	$update_url = get_admin_url( null, 'update-core.php' );

	$errors = array();

	if ( version_compare( $wp_version, $require_wp, '<' ) )

		$errors[] = "You have WordPress version $wp_version installed, but <b>this plugin requires at least WordPress $require_wp</b>. Please <a href='$update_url'>update your WordPress version</a>.";

	return $errors;
}

function so_cuws_check_admin_notices() {
	
	$errors = so_cuws_min_wp_version();

	if ( empty ( $errors ) )
		return;

	// Suppress "Plugin activated" notice.
	unset( $_GET['activate'] );

	// this plugin's name
	$name = get_file_data( __FILE__, array ( 'Plugin Name' ), 'plugin' );

	printf( __( '<div class="error"><p>%1$s</p><p><i>%2$s</i> has been deactivated.</p></div>', 'so-clean-up-wp-seo' ),
		join( '</p><p>', $errors ),
		$name[0]
	);
	deactivate_plugins( plugin_basename( __FILE__ ) );

}

/**
 * This function checks whether the WordPress SEO plugin is active (it needs to be active for SO Clean Up WP SEO to have any use)
 *
 * @since 1.1
 */

$plugins = get_option( 'active_plugins' );

$required_plugin = 'wordpress-seo/wp-seo.php';

// multisite throws the error message by default, because the plugin is installed on the network site, therefore check for multisite
if ( ! in_array( $required_plugin , $plugins ) && ! is_multisite() ) {

	add_action( 'admin_notices', 'so_cuws_warning' );

}

/**
 * Show warning if the WordPress SEO plugin has not been installed
 *
 * @since 1.1
 */

function so_cuws_warning() {

    // display the warning message
	echo '<div class="message error"><p>';

	_e( 'The <strong>SO Clean Up WP SEO plugin</strong> only works if you have the WordPress SEO plugin installed.', 'so-clean-up-wp-seo' );

	echo '</p></div>';

}

/**
 * if the WordPress SEO plugin has been installed, add the actions and filters that clean up the entire WP SEO experience
 *
 * @since 1.0
 */
if ( in_array( $required_plugin , $plugins ) ) {

	add_action( 'admin_head', 'so_cuws_hide_sidebar_ads' );

	add_action( 'admin_init', 'so_cuws_ignore_tour', 999 ); // since 1.4

	add_action( 'admin_bar_menu', 'so_cuws_remove_adminbar_settings', 999 ); // since 1.3
	
	add_action( 'wp_dashboard_setup', 'so_cuws_remove_wpseo_dashboard_overview_widget' ); // since 1.5

	add_filter( 'wpseo_use_page_analysis', '__return_false' );

}

// Remove irritating ads sidebar
// @since 1.3.1 remove tour option/introduction
// @since 1.4 remove updated nag (introduced with WordPress SEO version 2.2.1)
function so_cuws_hide_sidebar_ads() {
	echo '<style type="text/css">
	#wpseo-dismiss-about, #sidebar-container.wpseo_content_cell, .wpseotab.active > p:nth-child(6), .wpseotab.active > p:nth-child(7) {display:none;}
	</style>';
}

// @since 1.4 replaces previous so_cuws_remove_about_tour() function that has become redundant from WordPress SEO 2.2.1 onwards
function so_cuws_ignore_tour() {
	update_user_meta( get_current_user_id(), 'wpseo_ignore_tour', true );
}

// Remove Settings submenu in admin bar
// also shows how to remove other menus
// @since 1.3 - inspired by [Lee Rickler](https://profiles.wordpress.org/lee-rickler/)
function so_cuws_remove_adminbar_settings() {

	global $wp_admin_bar;

	// remove the entire menu
	//$wp_admin_bar->remove_node( 'wpseo-menu' );

	// remove WordPress SEO Settings
	$wp_admin_bar->remove_node( 'wpseo-settings' );

	// remove keyword research information
	//$wp_admin_bar->remove_node( 'wpseo-kwresearch' );

}

// Version 2.3 of Yoast SEO (formerly WordPress SEO) introduced a dashboard widget
// This function removes this widget
// @since v1.5
function so_cuws_remove_wpseo_dashboard_overview_widget() {
 	remove_meta_box( 'wpseo-dashboard-overview', 'dashboard', 'side' );
}


