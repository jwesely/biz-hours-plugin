<div class="wrap">
  <?php settings_errors();

// notify the user of save success
  if($_GET['saved_api_key']){
    $class = 'notice notice-success';
    $message = __( 'Successfully saved settings', 'sample-text-domain' );
    printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
    unset($_GET['saved_api_key']);
  }
  ?>

  <h1>Google API Settings</h1>

  <form method="post" action="">
    <table class="form-table">
      <tbody>
      <tr>
        <th scope="row">
          GoogleMaps API Key
        </th>
        <td>
          <input class="widefat" id="googlemaps_api_key" name="googlemaps_api_key" type="text" value="<?php echo get_option('googlemaps_api_key')?>"/>
            <p>Don't have an API key? register to get one <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">here!</a></p>
        </td>
      </tr>

<!--      <tr>-->
<!--          <th scope="row">-->
<!--              GoogleMaps Endpoint Url-->
<!--          </th>-->
<!--          <td>-->
<!--              <input class="widefat" id="googlemaps_api_endpoint" name="googlemaps_api_endpoint" type="text" value="--><?php //echo get_option('googlemaps_api_endpoint')?><!--"/>-->
<!--          </td>-->
<!--      </tr>-->
<!--      <tr>-->
<!--          <th scope="row">-->
<!--              GoogleMaps API Version-->
<!--          </th>-->
<!--          <td>-->
<!--              <input class="widefat" id="googlemaps_api_version" name="googlemaps_api_version" type="text" value="--><?php //echo get_option('googlemaps_api_version')?><!--"/>-->
<!--          </td>-->
<!--      </tr>-->
      </tbody>
    </table>
    <?php submit_button(); ?>
    <?php wp_nonce_field( 'biz-hours-plugin-settings-page-save', 'biz-hours-plugin-settings-page-save-nonce'); ?>
  </form>
</div>