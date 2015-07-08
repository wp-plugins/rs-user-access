<?php
/**
 * Plugin Name:       RS User Access Levels
 * Plugin URI:        http://www.rstandley.co.uk/rs-user-access-levels
 * Description:       A plugin allowing you to specify which menu items are available to which users
 * Version:           1.0.0
 * Author:            Rory Standley
 * Author URI:        http://www.rstandley.co.uk
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


require_once( plugin_dir_path( __FILE__ ) . 'public/class-admin-pages.php' );
add_action( 'plugins_loaded', array('Admin_Pages', 'get_instance' ) );

register_activation_hook( __FILE__, array( 'Admin_Pages', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Admin_Pages', 'deactivate' ) );

if(is_admin()){
	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-admin-pages-admin.php' );
	add_action( 'plugins_loaded', array( 'Admin_Pages_Admin', 'get_instance' ) );
}