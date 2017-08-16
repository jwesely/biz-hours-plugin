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

// Keep shortcode calls grouped up here for easy viewing
add_shortcode( 'wp_location_hours', 'wp_location_hours_shortcode' );
add_shortcode( 'wp_location_hours_long', 'wp_location_hours_long_shortcode' );
add_shortcode( 'wp_location_hours_short', 'wp_location_hours_short_shortcode' );
add_shortcode( 'wp_location_hours_today', 'wp_location_hours_today_shortcode' );

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

// Format address
function wp_location_format_address( $location ) {
  $formatted = "";
  if ( is_array( $location ) ) {
    $formatted = $location['name']. " " .$location['address1'] . " " . $location['address2'] . ", " . $location['city'] . " " . $location['province'] . " " . $location['postal_code'];
    if ( ! empty( $location['country'] ) ) {
      $formatted .= ", " . $location['country'];
    }
  } elseif ( is_object( $location ) ) {
    $formatted = $location->name. " " .$location->address1 . " " . $location->address2 . ", " . $location->city . " " . $location->province . " " . $location->postal_code;
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

// Shortcode to Output building hours
function wp_location_hours_shortcode( $atts = [] ) {
  if ( empty( $atts ) ) {
    // no atts given
    return;
  }

  // include the styling for this plugin
  wp_enqueue_style( "wp-location-css", plugins_url( "wp-location/css/wp_location.css" ) );

  $location = null;
if ( array_key_exists( "id", $atts ) ) {
    // load location by Id
    $location = WP_Location::get_location_by_id( $atts['id'] );
  } else {
    // no atts given
    return;
  }

  if ( empty( $location ) || empty( $location->place_id ) ) {
    return;
  }

  $defaulted_atts = shortcode_atts( array(
    "type"  => "long",
    "style" => "",
    "class" => ""
  ), $atts );

  $style = $defaulted_atts["style"];
  $class = $defaulted_atts["class"];


  $hours = google_places_api::get_place_hours( $location->place_id );
  if ( empty( $hours ) ) {
    ob_start();
    try {
      ?>
        <div class="wp-location-hours-container">
            <div class="wp-location-hours-status">
                <p>Failed to Load Open Hours for Location</p>
            </div>
        </div>
      <?php
      return ob_get_contents();
    } finally {
      ob_end_clean();
    }
  }

  $html = "";
  switch ( $defaulted_atts['type'] ) {
    case 'long':
      $html = wp_location_hours_display_long( $hours, $class, $style );
      break;
    case 'short':
      $html = wp_location_hours_display_short( $hours, $class, $style );
      break;
    case 'today':
      $html = wp_location_hours_display_today( $hours, $class, $style );
      break;
  }

  return $html;
}

// Shortcode to Output condensed location hours
function wp_location_hours_short_shortcode( $atts = [] ) {
  if ( array_key_exists( "name", $atts ) || array_key_exists( "id", $atts ) ) {
    $atts["type"] = "short";

    return wp_location_hours_shortcode( $atts );
  }
}

// Shortcode to Output location hours without condensing hours that are the same
function wp_location_hours_long_shortcode( $atts = [] ) {
  if ( array_key_exists( "name", $atts ) || array_key_exists( "id", $atts ) ) {
    $atts["type"] = "long";

    return wp_location_hours_shortcode( $atts );
  }
}

// Shortcode to Output location hours for the current day
function wp_location_hours_today_shortcode( $atts = [] ) {
  if ( array_key_exists( "name", $atts ) || array_key_exists( "id", $atts ) ) {
    $atts["type"] = "today";

    return wp_location_hours_shortcode( $atts );
  }
}

// Output location hours without condensing hours that are the same
function wp_location_hours_display_long( $hours, $class = "", $style = "" ) {
  if ( empty( $hours ) ) {
    return;
  }

  ob_start();
  try {
    ?>
      <div class="wp-location-hours-container <?php echo $class; ?>" style="<?php echo $style; ?>">
          <div class="wp-location-hours-status">
              <p>Doors are: <?php echo( $hours->open_now ? "Open" : "Closed" ); ?></p>
          </div>
          <div class='wp-location-hours-table'>
            <?php
            foreach ( $hours->weekday_text as $key => $value ) {
              ?>
                <div class="wp-location-hours-table-row">
                    <span class="wp-location-hours-table-column"><?php echo $value; ?></span>
                </div>
              <?php
            }
            ?>
          </div>
      </div>
    <?php
    return ob_get_contents();
  } finally {
    // no matter what, make sure to kill the output buffering that we started
    ob_end_clean();
  }
}

// Output condensed location hours
function wp_location_hours_display_short( $hours, $class = "", $style = "" ) {
  if ( empty( $hours ) ) {
    return;
  }

  // group days by consistent hours
  $condensed_text = condense_weekday_text( $hours );
  ob_start();
  try {
    ?>
      <div class="wp-location-hours-container <?php echo $class; ?>" style="<?php echo $style; ?>">
          <div class="wp-location-hours-status">
              <p>Doors are: <?php echo( $hours->open_now ? "Open" : "Closed" ); ?></p>
          </div>
          <div class='wp-location-hours-table'>
            <?php
            foreach ( $condensed_text as $value ) {
              ?>
                <div class="wp-location-hours-table-row">
                    <span class="wp-location-hours-table-column"><?php echo $value; ?></span>
                </div>
              <?php
            }
            ?>
          </div>
      </div>
    <?php
    return ob_get_contents();
  } finally {
    ob_end_clean();
  }
}

// Output location hours for the current day
function wp_location_hours_display_today( $hours, $class = "", $style = "" ) {
  if ( empty( $hours ) ) {
    return;
  }
  // because googlePlaces API doesn't understand how to make things the same...
  $day_conversion_table = array( 0 => 5, 1 => 0, 2 => 1, 3 => 2, 4 => 3, 5 => 4, 6 => 6 );
  ob_start();
  try {
    ?>
      <div class="wp-location-hours-container <?php echo $class; ?>" style="<?php echo $style; ?>">
          <div class="wp-location-hours-status">
              <p>Doors are: <?php echo( $hours->open_now ? "Open" : "Closed" ); ?></p>
          </div>
          <div class='wp-location-hours-table'>
              <div class="wp-location-hours-table-row">
                  <span class="wp-location-hours-table-column"><?php echo $hours->weekday_text[ $day_conversion_table[ date( 'w' ) ] ] ?></span>
              </div>
          </div>
      </div>
    <?php
    return ob_get_contents();
  } finally {
    ob_end_clean();
  }
}

// Condense Hours information So it doesn't duplicate as much
function condense_weekday_text( $hours ) {
  $day_conversion_table = array(
    0 => "Sun",
    1 => "Mon",
    2 => "Tue",
    3 => "Wed",
    4 => "Thu",
    5 => "Fri",
    6 => "Sat"
  );

  $days_to_hours          = array();
  $condensed_weekday_text = array();
  foreach ( $hours->periods as $day => $business_hours ) {
    foreach ( $business_hours as $type => $info ) {
      $days_to_hours[ $info->day ][ $type ] = $info->time;
    }
  }

  $timespans_to_days = array();

  foreach ( $days_to_hours as $day => $times ) {
    $matched = false;

    foreach ( $timespans_to_days as $groupID => $grouping ) {
      if ( sameSpan( $grouping, $times ) ) {
        $timespans_to_days[ $groupID ]['days'][] = $day;
        $matched                                 = true;

        break;
      }
    }

    if ( ! $matched ) {
      $new                 = $times;
      $new['days'][]       = $day;
      $timespans_to_days[] = $new;
    }
  }

  $day_to_group = array();

  foreach ( $timespans_to_days as $groupingID => $group ) {
    foreach ( $group['days'] as $key => $day ) {
      $day_to_group[ $day ] = $groupingID;
    }
  }

  $start_day       = false;
  $end_day         = false;
  $groupingID      = false;
  $arraySize       = sizeof( $day_to_group );
  $separate_sunday = false;
  for ( $i = 1; $i < $arraySize; $i ++ ) {
    if ( ! $start_day ) {
      $start_day  = $i;
      $groupingID = $day_to_group[ $start_day ];
    }

    if ( $i + 1 != $arraySize ) {
      if ( $day_to_group[ $i + 1 ] != $groupingID ) {
        $end_day = $i;
      }
    } else {
      if ( $day_to_group[0] != $groupingID ) {
        $end_day         = $i;
        $separate_sunday = true;
      } else {
        $end_day = 0;
      }
    }

    if ( $end_day ) {
      // Print the sections
      $time_string = date( "g:i A", strtotime( $timespans_to_days[ $groupingID ]['open'] ) ) . " - " . date( "g:i A", strtotime( $timespans_to_days[ $groupingID ]['close'] ) );
      if ( $start_day == $end_day ) {
        $date_string = $day_conversion_table[ $start_day ] . ": " . $time_string;
      } else {
        $date_string = $day_conversion_table[ $start_day ] . "-" . $day_conversion_table[ $end_day ] . ": " . $time_string;
      }

      $condensed_weekday_text[] = $date_string;

      if ( $separate_sunday ) {
        $sundayGroupID            = $day_to_group[0];
        $sunday_time_string       = date( "g:i A", strtotime( $timespans_to_days[ $sundayGroupID ]['open'] ) ) . " - " . date( "g:i A", strtotime( $timespans_to_days[ $sundayGroupID ]['close'] ) );
        $date_string              = $day_conversion_table[0] . ": " . $sunday_time_string;
        $condensed_weekday_text[] = $date_string;
      }

      // Reset variables
      $start_day  = false;
      $end_day    = false;
      $groupingID = false;
    }
  }

  return $condensed_weekday_text;
}

// check if two spans are the same
function sameSpan( $span1, $span2 ) {
  return ( ( $span1['close'] === $span2['close'] ) && ( $span1['open'] === $span2['open'] ) );
}