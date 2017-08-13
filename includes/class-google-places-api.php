<?php

/**
 * Created by PhpStorm.
 * User: jwesely
 * Date: 1/4/2017
 * Time: 12:13 PM
 */
class google_places_api {
	public static $place_endpoint = "https://maps.googleapis.com/maps/api/place/details/json?";
	public static $map_endpoint = "https://maps.googleapis.com/maps/api/place/details/json?";
	protected static $version = "3.exp";

	public static function include_js_script() {
		wp_enqueue_script( 'google-maps-api', "https://maps.googleapis.com/maps/api/js?key=" . get_option('googlemaps_api_key'). "&v=" . self::$version );
	}

	public static function geocode( $address ) {
		// url encode the address
		$address = urlencode( $address );

		// google map geocode api url
		$url = "http://maps.google.com/maps/api/geocode/json?address={$address}";

		// get the json response
		$resp_json = file_get_contents( $url );

		// decode the json
		$resp = json_decode( $resp_json, true );

		// response status will be 'OK', if able to geocode given address
		if ( $resp['status'] == 'OK' ) {

			// get the important data
			$lati              = $resp['results'][0]['geometry']['location']['lat'];
			$longi             = $resp['results'][0]['geometry']['location']['lng'];
			$place_id          = $resp['results'][0]['place_id'];
			$formatted_address = $resp['results'][0]['formatted_address'];

			// verify if data is complete
			if ( $lati && $longi && $formatted_address ) {

				// put the data in the array
				$data_arr              = array();
				$data_arr['latitude']  = $lati;
				$data_arr['longitude'] = $longi;
				$data_arr['formatted'] = $formatted_address;
				$data_arr['place_id']  = $place_id;

				return $data_arr;
			}
		}

		return null;
	}

	public static function get_place_hours( $place_id ) {
		$current_time = new DateTime( "now" );
		$current_time->setTimezone( new DateTimeZone( 'America/Chicago' ) );
		$json = wp_remote_get( self::$place_endpoint . 'placeid=' . $place_id . '&key=' . get_option('googlemaps_api_key') );

		try {
			$place_object = json_decode( $json['body'] );
			// print_r( $place_object );

			return $place_object->result->opening_hours;
		} catch ( Exception $e ) {
			return null;
		}
	}

	public static function condense_weekday_text( $hours ) {
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
				if ( self::sameSpan( $grouping, $times ) ) {
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

	protected static function sameSpan( $span1, $span2 ) {
		return ( ( $span1['close'] === $span2['close'] ) && ( $span1['open'] === $span2['open'] ) );
	}
}
