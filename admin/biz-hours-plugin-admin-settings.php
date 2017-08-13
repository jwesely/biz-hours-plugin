<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

add_action('admin_menu', 'biz_hours_plugin_settings_page');
add_action('load-settings_page_biz-hours-plugin-options', 'biz_hours_plugin_settings_page_save_options' );


function biz_hours_plugin_settings_page(){
  add_submenu_page(
    'options-general.php',
    'Biz Hours Plugin Settings',
    'Biz Hours Plugin Settings',
    'manage_options',
    'biz-hours-plugin-options',
    'biz_hours_plugin_settings_page_render'
  );
}

function biz_hours_plugin_settings_page_render(){
  include 'templates/biz-hours-plugin-admin-settings-options.php';
}

function biz_hours_plugin_settings_page_save_options(){

  $action = 'biz-hours-plugin-settings-page-save';
  $nonce = 'biz-hours-plugin-settings-page-save-nonce';

  // Prevent people from changing settings if they aren't supposed to be here
  if ( !biz_hours_plugin_user_can_save( $action, $nonce ) ){
    return;
  }

  if ( isset( $_POST['googlemaps_api_key'] ) ){
    update_option( 'googlemaps_api_key', $_POST['googlemaps_api_key'] );
  }
}

function biz_hours_plugin_user_can_save( $action, $nonce ) {
  $is_nonce_set = isset( $_POST[$nonce] );
  $is_valid_nonce = false;

  if( $is_nonce_set ){
    $is_valid_nonce = wp_verify_nonce( $_POST[$nonce], $action );
  }

  return ( $is_nonce_set && $is_valid_nonce );
}