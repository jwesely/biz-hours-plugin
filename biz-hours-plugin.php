<?php
/*
Plugin Name: Business Hours Plugin
Plugin URI: none
Description: Use GooglePlaces to display information about your stores
Version: 1.0
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





?>