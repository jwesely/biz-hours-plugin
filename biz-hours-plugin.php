<?php
/*
Plugin Name: Business Hours Plugin
Plugin URI: none
Description: Use GooglePlaces to display information about your stores
Version: 0.3 BETA
Author: StoneFin Technologies
Author URI: https://www.stonefin.com
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}



//include admin settings
require_once ("admin/biz-hours-plugin-admin-settings.php");

//store location functionality
require_once ("store-locations.php");

// Add Menu Item for Store locations
add_action( 'admin_menu', 'wpLocationMenuItem' );


// Install or Update the Table
register_activation_hook( __FILE__, 'wpLocationInstall' );

// Add Admin Menu Tab
function wpLocationMenuItem() {
  // add Store Location Main Menu
  add_menu_page( 'Store Locations', 'Store Locations', 'manage_options', 'wp-location', 'wp_locations_view' );

  // Add "Add Location" SubMenu
  add_submenu_page(
    "wp-location",
    "Add New Location",
    "Add Location",
    "manage_options",
    "wp-location-add",
    "wp_locations_add"
  );

  // add "Edit Location" page, WITHOUT menu Item
  add_submenu_page(
    null,
    "Edit Location",
    "Edit Location",
    "manage_options",
    "wp-location-edit",
    "wp_locations_edit"
  );
}


function wpLocationInstall() {
  global $wpdb;
  global $wp_location_db_version;

  $table_name      = $wpdb->prefix . WP_LOCATION_TABLE;
  $charset_collate = $wpdb->get_charset_collate();

  $sql = "CREATE TABLE $table_name (
	id INT(10) NOT NULL AUTO_INCREMENT,
	place_id TEXT NULL,
	alt_ids TEXT NULL,
	name VARCHAR(255) NOT NULL,
	latitude DECIMAL(20,10) NULL,
	longitude DECIMAL(20,10) NULL,
	address1 VARCHAR(255) NOT NULL,
	address2 VARCHAR(255) NULL DEFAULT NULL,
	city VARCHAR(255) NOT NULL,
	province VARCHAR(255) NOT NULL,
	country VARCHAR(255) NOT NULL DEFAULT 'United States',
	postal_code VARCHAR(255) NOT NULL,
	created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY  (id)
) $charset_collate;
";

  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  dbDelta( $sql );
  add_option( 'wp_location_db_version', $wp_location_db_version );
  flush_rewrite_rules();
}





?>