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
    <h2>Add Location</h2>
	<?php settings_errors(); ?>
    <form method="post" action="admin-post.php">
        <input type="hidden" name="action" value="wp_locations_save"/>
		<?php wp_nonce_field( 'wp_location_verify' ); ?>
        <fieldset>
            <table class="form-table">
                <tbody>
                <tr class="form-field">
                    <th scope="row">
                        <label for="location[place_id]">Google Place Id:</label>
                    </th>
                    <td>
                        <input type="text" name="location[place_id]" value="" placeholder=""/>
                    </td>
                </tr>
                <tr class="form-field">
                    <td colspan="2">
                        <button type="button" onClick="alert('Feature not Complete');">
                            <span>Fill Form With Google Place Info</span>
                        </button>
                    </td>
                </tr>
                <tr class="form-field form-required">
                    <td colspan="2">
                        <span name="blah"><i>Find Place ID at <a href="https://developers.google.com/places/place-id">this</a> location</i></span>
                    </td>
                </tr>
                <tr class="form-field form-required">
                    <th scope="row">
                        <label for="location[name]">Location Name: <span class="description">(required)</span></label>
                    </th>
                    <td>
                        <input type="text" name="location[name]" value="" placeholder="Dr. Muffin's Residence"/>
                    </td>
                </tr>
                <tr class="form-field form-required">
                    <th scope="row">
                        <label for="location[address1]">Address: <span class="description">(required)</span></label>
                    </th>
                    <td>
                        <input type="text" name="location[address1]" value="" placeholder="123 Drury Lane"/>
                    </td>
                </tr>
                <tr class="form-field">
                    <th scope="row">
                        <label for="location[address2]">Address2:</label>
                    </th>
                    <td>
                        <input type="text" name="location[address2]" value="" placeholder=""/>
                    </td>
                </tr>
                <tr class="form-field form-required">
                    <th scope="row">
                        <label for="location[city]">City:<span class="description">(required)</span></label>
                    </th>
                    <td>
                        <input type="text" name="location[city]" value="" placeholder="Lincoln"/>
                    </td>
                </tr>
                <tr class="form-field form-required">
                    <th scope="row">
                        <label for="location[province]">State:<span class="description">(required)</span></label>
                    </th>
                    <td>
                        <select name="location[province]">
							<?php
							foreach ( $states as $key => $value ) {
								echo '<option value="' . $value . '">' . $value . '</option>';
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
                        <input type="text" name="location[country]" value="United States"/>
                    </td>
                </tr>
                <tr class="form-field form-required">
                    <th scope="row">
                        <label for="location[postal_code]">Postal Code: <span
                                    class="description">(required)</span></label>
                    </th>
                    <td>
                        <input type="text" name="location[postal_code]" value="" placeholder="68508"/>
                    </td>
                </tr>
                <tr class="form-field">
                    <th scope="row">
                        <label for="location[latitude]">Latitude:</label>
                    </th>
                    <td>
                        <input type="text" name="location[latitude]" value="" placeholder="40.810556"/>
                    </td>
                </tr>
                <tr class="form-field">
                    <th scope="row">
                        <label for="location[longitude]">Longitude:</label>
                    </th>
                    <td>
                        <input type="text" name="location[longitude]" value="" placeholder=" -96.680278"/>
                    </td>
                </tr>
                <tr class="form-field form-required">
                    <td colspan="2">
                        <button value="Save Location" type="submit">Save Location</button>
                    </td>
                </tr>
                </tbody>
            </table>
        </fieldset>
    </form>
</div>