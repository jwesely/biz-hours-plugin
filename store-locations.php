<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

if ( ! class_exists( "wp_location" ) ) {
  require_once "includes/class-wp-location.php";
}


if ( ! class_exists( "google_places_api" ) ) {
  require_once( "includes/class-google-places-api.php" );
}

// Keep add_action calls grouped up here for easy viewing
add_action( 'init', 'wp_location_custom_post_type' );
add_action( 'save_post', 'wp_location_box_save' );
add_action( 'admin_notices', 'wp_location_save_notice__error' );

// Keep add_filter calls grouped up here for easy viewing


// Register a custom post type with WordPress
function wp_location_custom_post_type() {

  // $labels contains configuration for names
  $labels = array(
    'name'                => _x( 'Locations', 'Store Locations' ),
    'singular_name'       => _x( 'Location', 'Store Location' ),
    'add_new'             => _x( 'Add New', 'book' ),
    'add_new_item'        => __( 'Add New Location' ),
    'edit_item'           => __( 'Edit Location' ),
    'new_item'            => __( 'New Location' ),
    'all_items'           => __( 'All Locations' ),
    'view_item'           => __( 'View Locations' ),
    'search_items'        => __( 'Search Locations' ),
    'not_found'           => __( 'No locations found' ),
    'not_found_in_trash'  => __( 'No locations found in the Trash' ),
    'parent_item_colon'   => '',
    'menu_name'           => 'Locations'
  );

  // $args contains configuration options for the new custom post type
  $args = array(
    'labels'              => $labels,
    'description'         => 'Holds location data',
    'public'              => true,
    'menu_position'       => 5,
    'supports'            => array('title', 'thumbnail'),
    'has_archive'         => true,
    'publicly_queryable'  => false,
    'register_meta_box_cb'=> 'wp_location_fields'
  );

  // Register the new post type with WordPress using given options
  register_post_type( 'wp-location', $args);
}

// Register a meta_box to hold custom fields
function wp_location_fields(){
    add_meta_box(
            'wp_location_data_box',
            __('Location Data', 'wp_location'),
            'wp_location_box_content',
            'wp-location', // In our case screen should be the same as the newly registered post_type
            'normal',
            'high'
    );
}

// Provide content for the wp_location meta_box
function wp_location_box_content( $post ){
  include( 'pages/wp-location-page.php' );
}

// Handling wp-location post save
function wp_location_box_save( $post_id ){

  // Don't save anything if the user didn't save it
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
    return;

  // Make sure the nonce is valid to help prevent malicious access
  if ( !wp_verify_nonce( $_POST['wp_location_box_content_nonce'], 'test9000' ))
    return;

  if ( 'page' == $_POST['post_type'] ) {
    if ( !current_user_can( 'edit_page', $post_id ) )
      return;
  } else {
    if ( !current_user_can( 'edit_post', $post_id ) )
      return;
  }

  $location = $_POST['location']; // get the data that was posted

  // We need these fields to be populated if we want to pull Google Places information from them
  if(empty($location['address1']) || empty($location['name']) || empty($location['city']) || empty($location['province']) || empty($location['postal_code']) ){
    add_filter( 'redirect_post_location', 'add_notice_query_var', 99); // que up a warning for missing fields but still let them update
    $missing_required_fields = true;
  }

  // Lets only try to generate lat/long if we have the required fields
  if(!isset($missing_required_fields)){
    if ( empty( $location['longitude'] ) || empty( $location['latitude'] ) || empty( $location['place_id'] ) ) {
      // try and get the Geometry from Google
      $formatted = wp_location_format_address( $location );
      $temp      = wp_location_geocode( $formatted );
    }

    if($temp != null){
      // are our coordinates empty
      if ( ( empty( $location['latitude'] ) || empty( $location['longitude'] ) ) ) {
        $location['latitude']  = floatval( $temp['latitude'] );
        $location['longitude'] = floatval( $temp['longitude'] );
      }

      // dont overwrite manually entered place_id
      if ( empty( $location['place_id'] ) ) {
        $location['place_id'] = ! empty( $temp['place_id'] ) ? $temp['place_id'] : null;
      }
    }
  }

  update_post_meta( $post_id, 'location', $location );
}

//Add query args we can use to notify user of data that couldn't be saved
function add_notice_query_var( $redirect ) {
  remove_filter( 'redirect_post_location','add_notice_query_var', 99 );
  $location = $_POST['location'];
  $args = array();
  if(empty($location['address1'])){
    $args['missing_address'] = 'true';
  }
  if( empty($location['name']) ){
    $args['missing_name'] = 'true';
  }
  if( empty($location['city']) ){
    $args['missing_city'] = 'true';
  }
  if( empty($location['province']) ){
    $args['missing_province'] = 'true';
  }
  if( empty($location['postal_code']) ){
    $args['missing_postal_code'] = 'true';
  }
  return add_query_arg( $args, $redirect );
}


// Create error notices for missing fields
function wp_location_save_notice__error() {
  $class = 'notice notice-error';

  if($_GET['missing_address']){
    $message = __( 'Please enter Address', 'sample-text-domain' );
    printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
  }
  if($_GET['missing_name']){
    $message = __( 'Please enter Location', 'sample-text-domain' );
    printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
  }
  if($_GET['missing_city']){
    $message = __( 'Please enter City', 'sample-text-domain' );
    printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
  }
  if($_GET['missing_province']){
    $message = __( 'Please select a State', 'sample-text-domain' );
    printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
  }
  if($_GET['missing_postal_code']){
    $message = __( 'Please enter a Postal Code', 'sample-text-domain' );
    printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
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

// function to geocode address, it will return NULL if unable to geocode address
function wp_location_geocode( $address ) {
  return google_places_api::geocode( $address );
}