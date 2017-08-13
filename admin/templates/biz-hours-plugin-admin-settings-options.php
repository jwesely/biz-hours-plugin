<div class="wrap">
  <?php settings_errors(); ?>

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