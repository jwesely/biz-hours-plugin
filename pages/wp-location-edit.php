<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die(
		'<h1>' . __( 'Cheatin&#8217; uh?' ) . '</h1>' .
		'<p>' . __( 'Sorry, you are not allowed to list users.' ) . '</p>',
		403
	);
}

#global $wpdb;
// get current Location
if ( empty( $_GET['id'] ) ) {
	wp_redirect( admin_url( "admin.php?page=wp-location-new" ) );
}

$id = intval( $_GET['id'] );
if ( $id <= 0 ) {
	wp_redirect( admin_url( "admin.php?page=wp-location-new" ) );
}
global $wpdb;
$location = $wpdb->get_row( "Select * from " . $wpdb->prefix . WP_LOCATION_TABLE . " where id = " . $id . " LIMIT 1", ARRAY_A );

$states     = array(
	"Alabama",
	"Alaska",
	"Arizona",
	"Arkansas",
	"California",
	"Colorado",
	"Connecticut",
	"Delaware",
	"Florida",
	"Georgia",
	"Hawaii",
	"Idaho",
	"Illinois",
	"Indiana",
	"Iowa",
	"Kansas",
	"Kentucky",
	"Louisiana",
	"Maine",
	"Maryland",
	"Massachusetts",
	"Michigan",
	"Minnesota",
	"Mississippi",
	"Missouri",
	"Montana",
	"Nebraska",
	"Nevada",
	"New Hampshire",
	"New Jersey",
	"New Mexico",
	"New York",
	"North Carolina",
	"North Dakota",
	"Ohio",
	"Oklahoma",
	"Oregon",
	"Pennsylvania",
	"Rhode Island",
	"South Carolina",
	"South Dakota",
	"Tennessee",
	"Texas",
	"Utah",
	"Vermont",
	"Virginia",
	"Washington",
	"West Virginia",
	"Wisconsin",
	"Wyoming"
);
$labelClass = "";
$inputClass = "";
?>
<div class="wrap">
    <h2>Edit Location</h2>
	<?php settings_errors(); ?>
    <form method="post" action="admin-post.php">
        <input type="hidden" name="action" value="wp_locations_save"/>
        <input type="hidden" name="location[id]" value="<?php echo $location['id']; ?>"/>

		<?php wp_nonce_field( 'wp_location_verify' ); ?>
        <fieldset>
            <table class="form-table">
                <tbody>
                <tr class="form-field form-required">
                    <th scope="row">
                        <label for="location[name]">Location Name: <span class="description">(required)</span></label>
                    </th>
                    <td>
                        <input type="text" name="location[name]" value="<?php echo $location['name']; ?>"
                               placeholder="Dr. Muffin's Residence"/>
                    </td>
                </tr>
                <tr class="form-field form-required">
                    <th scope="row">
                        <label for="location[address1]">Address: <span class="description">(required)</span></label>
                    </th>
                    <td>
                        <input type="text" name="location[address1]" value="<?php echo $location['address1']; ?>"
                               placeholder="123 Drury Lane"/>
                    </td>
                </tr>
                <tr class="form-field">
                    <th scope="row">
                        <label for="location[address2]">Address2:</label>
                    </th>
                    <td>
                        <input type="text" name="location[address2]" value="<?php echo $location['address2']; ?>"
                               placeholder=""/>
                    </td>
                </tr>
                <tr class="form-field form-required">
                    <th scope="row">
                        <label for="location[city]">City:<span class="description">(required)</span></label>
                    </th>
                    <td>
                        <input type="text" name="location[city]" value="<?php echo $location['city']; ?>"
                               placeholder="Lincoln"/>
                    </td>
                </tr>
                <tr class="form-field form-required">
                    <th scope="row">
                        <label for="location[province]">State:<span class="description">(required)</span></label>
                    </th>
                    <td>
                        <select name="location[province]">
							<?php
							foreach ( $states as $state ) {
								?>
                                <option
									<?php if ( $location['province'] == $state ) {
										echo 'selected="selected"';
									} ?>
                                        value="<?php echo $state; ?>"><?php echo $state; ?></option>
								<?php
							}
							?>
                        </select>
                    </td>
                </tr>
                <tr class="form-field">
                    <th scope="row">
                        <label for="location[country]">Country:</label>
                    </th>
                    <td>
                        <input type="text" name="location[country]" value="<?php echo $location['country']; ?>"/>
                    </td>
                </tr>
                <tr class="form-field form-required">
                    <th scope="row">
                        <label for="location[postal_code]">Postal Code: <span
                                    class="description">(required)</span></label>
                    </th>
                    <td>
                        <input type="text" name="location[postal_code]" value="<?php echo $location['postal_code']; ?>" placeholder="68508"/>
                    </td>
                </tr>
                <tr class="form-field">
                    <th scope="row">
                        <label for="location[latitude]">Latitude:</label>
                    </th>
                    <td>
                        <input type="text" name="location[latitude]" value="<?php echo $location['latitude']; ?>" placeholder=""/>
                    </td>
                </tr>
                <tr class="form-field">
                    <th scope="row">
                        <label for="location[longitude]">Longitude:</label>
                    </th>
                    <td>
                        <input type="text" name="location[longitude]" value="<?php echo $location['longitude']; ?>" placeholder=""/>
                    </td>
                </tr>
                <tr class="form-field">
                    <th scope="row">
                        <label for="location[place_id]">Google Place Id:</label>
                    </th>
                    <td>
                        <input type="text" name="location[place_id]" value="<?php echo $location['place_id']; ?>" placeholder=""/>
                    </td>
                </tr>
                <tr class="form-field form-required">
                    <td colspan="2">
                        <span name="blah"><i>Find Place ID at <a href="https://developers.google.com/places/place-id">this</a> location</i></span>
                    </td>
                </tr>
                <tr class="form-field form-required">
                    <td colspan="2">
                        <button value="Save Location" type="submit">Save Location</button>
                        <button value="Cancel" type="button" onClick="window.location='<?php echo admin_url("admin.php?page=wp-location"); ?>'">Cancel</button>
                    </td>
                </tr>
                </tbody>
            </table>
        </fieldset>
    </form>
</div>