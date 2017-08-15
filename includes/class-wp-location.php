<?php

class WP_Location {
	public $id;
    public $place_id;
    public $alt_ids;
    public $name;
	public $latitude;
    public $longitude;
    public $address1;
    public $address2;
    // City
    public $city;
    // State
    public $province;
    // Country
    public $country="United States";
    // Zip
    public $postalCode;
    public $opening_hours;
	public $permanently_closed;

	public static function get_location_by_id($id){
	  $obj = new WP_Location();
	  $obj->id = $id;
	  $location = get_post_meta($id, 'location', true);
	  if(empty($location)){
	    return false;
    }
	  $obj = (object)((array)$location + (array)$obj); // merge location data into obj while giving location priority
    return $obj;
  }
}
