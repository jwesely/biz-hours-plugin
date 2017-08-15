<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

if ( ! class_exists( "wp_location" ) ) {
  require_once "includes/class-wp-location.php";
}


//      add_action( 'admin_notices', 'wp_locations_save_success' );
function wp_locations_save_success() {
  ?>
    <div class="notice notice-success is-dismissible">
        <p><?php _e( 'Successfully Saved Location!', 'wp-locations-textarea' ); ?></p>
    </div>
  <?php
}

function wp_locations_save_failure() {
  ?>
    <div class="notice notice-error">
        <p><?php _e( 'Failed to Save Location!', 'wp-locations-textarea' ); ?></p>
    </div>
  <?php
}
