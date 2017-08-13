<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

if ( ! defined( "wpLocationTable" ) ) {
  require_once "includes/constants.php";
}

if ( ! class_exists( "wp_location" ) ) {
  require_once "includes/class-wp-location.php";
}

add_action( 'admin_post_wp_locations_save', 'wp_locations_save' );

function wp_locations_view() {
  try {
    require_once( "pages/wp-locations-view.php" );
  } catch ( Exception $e ) {
    var_dump( $e );
  }
}

function wp_locations_add() {
  try {
    // dont use once,just incase this method gets called twice
    require( "pages/wp-location-new.php" );
  } catch ( Exception $e ) {
    var_dump( $e );
  }
}

function wp_locations_edit( $location_id ) {
  try {
    if ( ! defined( "wpLocationTable" ) ) {
      include_once "includes/constants.php";
    }

    require( "pages/wp-location-edit.php" );
  } catch ( Exception $e ) {
    var_dump( $e );
  }
}

function wp_locations_save() {
  global $wpdb;
  if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'You are not allowed to be on this page.' );
  }

  // Check that nonce field
  check_admin_referer( 'wp_location_verify' );
  $location = $_POST['location'];
  if ( ! empty( $location ) ) {
    // disabling alt_id for now
    try {
      if ( empty( $location['alt_ids'] ) ) {
        $location['alt_ids'] = null;
      }
      if ( empty( $location['address2'] ) ) {
        $location['address2'] = null;
      }

      // make sure that the ID key Exists so that the format array is aligned
      if ( ! array_key_exists( "id", $location ) ) {
        $location['id'] = null;
      }

      // sort the array by Key, so that our format lines up, otherwise dont use a format at all
      $format = null;
      if ( ksort( $location ) ) {
        $format                = array();
        $format['address1']    = "%s";
        $format['address2']    = "%s";
        $format['alt_ids']     = "%s";
        $format['city']        = "%s";
        $format['country']     = "%s";
        $format['id']          = "%d";
        $format['latitude']    = "%f";
        $format['longitude']   = "%f";
        $format['name']        = "%s";
        $format['province']    = "%s";
        $format['place_id']    = "%s";
        $format['postal_code'] = "%s";

        // just in case i f*ck up something above
        ksort( $format );
      }

      $wpdb->replace( $wpdb->prefix . WP_LOCATION_TABLE, $location, $format );
      // now that we've saved redirect back to the List
      wp_redirect( "/wp-admin/admin.php?page=wp-location" );
      add_action( 'admin_notices', 'wp_locations_save_success' );
    } catch ( Exception $e ) {
      // something Failed
      // add error to Admin Page
      add_action( 'admin_notices', 'wp_locations_save_failure' );
      wp_redirect( "/wp-admin/admin.php?page=wp-location-add" );
    }
  }
}

function wp_location_format_address( $location ) {
  $formatted = "";
  if ( is_array( $location ) ) {
    $formatted = $location['address1'] . " " . $location['address2'] . ", " . $location['city'] . " " . $location['province'] . " " . $location['postal_code'];
    if ( ! empty( $location['country'] ) ) {
      $formatted .= ", " . $location['country'];
    }
  } elseif ( is_object( $location ) ) {
    $formatted = $location->address1 . " " . $location->address2 . ", " . $location->city . " " . $location->province . " " . $location->postal_code;
    if ( ! empty( $location->country ) ) {
      $formatted .= ", " . $location->country;
    }
  }

  return $formatted;
}

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


// function to geocode address, it will return NULL if unable to geocode address
function wp_location_geocode( $address ) {
  return google_places_api::geocode( $address );
}