<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

//Our class extends the WP_List_Table class, so we need to make sure that it's there
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
//Our class extends the WP_List_Table class, so we need to make sure that it's there
if ( ! class_exists( 'wp_location' ) ) {
	require_once( 'class-wp-location.php' );
}

if ( ! defined( "wpLocationTable" ) ) {
	include_once "constants.php";
}

class wp_locations_list_table extends WP_List_Table {
	/**
	 * Constructor, we override the parent to pass our own arguments
	 * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
	 */
	public function __construct( $args = array() ) {
		parent::__construct( array(
			'singular' => 'wp_location', //Singular label
			'plural'   => 'wp_locations', //plural label, also this well be one of the table css class
			'ajax'     => false, //We won't support Ajax for this table,
			'screen'   => isset( $args['screen'] ) ? $args['screen'] : null,
		) );
	}

	/**
	 * Define the columns that are going to be used in the table
	 * @return array $columns, the array of columns to use with the table
	 */
	public function get_columns() {
		$columns = array(
			'id'       => __( 'ID' ),
			'name'     => __( 'Name' ),
			'address1' => __( 'Address1' ),
			'address2' => __( 'Address2' ),
			'city'     => __( 'City' ),
			'province' => __( 'State' ),
			'country'  => __( 'Country' ),
			'postal_code'   => __( 'Postal Code' ),
			'latitude' => __( 'Latitude' ),
			'longitude' => __( 'Longitude' ),
			'edit'=>('Edit')
		);

		return $columns;
	}

	/**
	 * Decide which columns to activate the sorting functionality on
	 * @return array $sortable, the array of columns that can be sorted by the user
	 */
	public function get_sortable_columns() {
		$sortable = array(
			'id'       => 'id',
			'name'     => 'name',
			'city'     => 'city',
			'province' => 'province'
		);

		return $sortable;
	}

	/**
	 * Get a list of all, hidden and sortable columns, with filter applied
	 *
	 * @return array
	 */
	public function get_column_info() {
		if ( ! isset( $this->_column_headers ) ) {
			$columns = $this->get_columns();
			$hidden = get_hidden_columns( $this->screen );

			$sortable_columns = $this->get_sortable_columns();
			/**
			 * Filters the list table sortable columns for a specific screen.
			 *
			 * The dynamic portion of the hook name, `$this->screen->id`, refers
			 * to the ID of the current screen, usually a string.
			 *
			 * @since 3.5.0
			 *
			 * @param array $sortable_columns An array of sortable columns.
			 */
			$_sortable = apply_filters( "manage_{$this->screen->id}_sortable_columns", $sortable_columns );

			$sortable = array();
			foreach ( $_sortable as $id => $data ) {
				if ( empty( $data ) )
					continue;

				$data = (array) $data;
				if ( !isset( $data[1] ) )
					$data[1] = false;

				$sortable[$id] = $data;
			}

			$primary = $this->get_primary_column_name();
			$this->_column_headers = array( $columns, $hidden, $sortable, $primary );
		}

		return $this->_column_headers;
	}
	/**
	 * Prepare the table with different parameters, pagination, columns and table elements
	 */
	public function prepare_items() {
		global $wpdb;
	//	$screen = get_current_screen();

		/* -- Preparing your query -- */
		$query = "SELECT  id, place_id,  alt_ids,  name, latitude, longitude, address1,  address2,  city,  province,  country,  postal_code FROM " . $wpdb->prefix . WP_LOCATION_TABLE;

		/* -- Ordering parameters -- */
		//Parameters that are going to be used to order the result
		$orderby = ! empty( $_GET["orderby"] ) ? $wpdb->_escape( $_GET["orderby"] ) : 'ASC';
		$order   = ! empty( $_GET["order"] ) ? $wpdb->_escape( ( $_GET["order"] ) ) : '';
		if ( ! empty( $orderby ) & ! empty( $order ) ) {
			$query .= ' ORDER BY ' . $orderby . ' ' . $order;
		}

		/* -- Pagination parameters -- */
		//Number of elements in your table?
		$totalitems = $wpdb->query( $query ); //return the total number of affected rows

		//How many to display per page?
		$perpage = 10;

		//Which page is this?
		$paged = ! empty( $_GET["paged"] ) ? $wpdb->_escape( ( $_GET["paged"] ) ) : '';

		//Page Number
		if ( empty( $paged ) || ! is_numeric( $paged ) || $paged <= 0 ) {
			$paged = 1;
		}

		//How many pages do we have in total?
		$totalpages = ceil( $totalitems / $perpage );

		//adjust the query to take pagination into account
		if ( ! empty( $paged ) && ! empty( $perpage ) ) {
			$offset = ( $paged - 1 ) * $perpage;
			$query  .= ' LIMIT ' . (int) $offset . ',' . (int) $perpage;
		}
		/* -- Register the pagination -- */
		$this->set_pagination_args( array(
			"total_items" => $totalitems,
			"total_pages" => $totalpages,
			"per_page"    => $perpage,
		) );
		//The pagination links are automatically built according to those parameters

		/* -- Register the Columns -- */
	//	 $columns                           = $this->get_columns();
	//	 $_wp_column_headers[ $screen->id ] = $columns;

		/* -- Fetch the items -- */
		$this->items = $wpdb->get_results( $query );
	}

	public function no_items() {
		_e( 'No Locations found.' );
	}

	/**
	 * Display the rows of records in the table
	 * @return string, echo the markup of the rows
	 */
	public function display_rows() {

		//Get the records registered in the prepare_items method
		$records = $this->items;

		// list( $columns, $hidden ) = $this->get_column_info();

		//Loop for each record
		if ( ! empty( $records ) ) {
			foreach ( $records as $recId => $rec ) {
				echo PHP_EOL . "\t" . $this->single_row( $rec );

			}
		}
	}

	protected function get_default_primary_column_name() {
		return 'id';
	}

	public function single_row( $location_object, $style = '', $role = '', $numposts = 0 ) {
		if ( ! ( $location_object instanceof wp_location ) ) {
			$location_object = $this->get_location( $location_object );
		}

		//Open the line
		$r = "<tr id='location-$location_object->id'>";

		//Get the columns registered in the get_columns and get_sortable_columns methods
		list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

		foreach ( $columns as $column_name => $column_display_name ) {
			$classes = "$column_name column-$column_name";
			if ( $primary === $column_name ) {
				$classes .= ' has-row-actions column-primary';
			}
			if ( 'posts' === $column_name ) {
				$classes .= ' num'; // Special case for that column
			}

			if ( in_array( $column_name, $hidden ) ) {
				$classes .= ' hidden';
			}

			$data = 'data-colname="' . wp_strip_all_tags( $column_display_name ) . '"';

			$attributes = "class='$classes' $data";

				$r .= "<td $attributes>";
				switch ( $column_name ) {
					case 'id':
						$r .= $location_object->id;
						break;
					case 'name':
						$r .= $location_object->name;
						break;
					case 'address1':
						$r .= $location_object->address1;
						break;
					case 'address2':
						$r .= $location_object->address2;
						break;
					case 'city':
						$r .= $location_object->city;
						break;
					case 'province':
						$r .= $location_object->province;
						break;
					case 'country':
						$r .= $location_object->country;
						break;
					case 'postal_code':
						$r .= $location_object->postal_code;
						break;
					case 'place_id':
						$r .= $location_object->place_id;
						break;
						//latitude, longitude
					case 'latitude':
						$r .= $location_object->latitude;
						break;
						case 'longitude':
					$r .= $location_object->longitude;
					break;
					case 'alt_ids':
						$r .= $location_object->alt_ids;
						break;
					case 'edit':
						$r .= '<a href="'.admin_url(sprintf("admin.php?page=wp-location-edit&id=%d",$location_object->id),'https').'">Edit Location</a>';
						break;
					default:

				}

				if ( $primary === $column_name ) {
					//	$r .= $this->row_actions( $actions );
				}
				$r .= "</td>";

		}
		$r .= '</tr>';

		return $r;
	}

	protected function get_location( $object ) {
		if ( $object instanceof wp_location ) {
			return $object;
		}

		$gp = new wp_location();
		if ( is_array( $object ) ) {
			if ( array_key_exists( 'id', $object ) ) {
				$gp->id       = $object["id"];
				$gp->place_id = $object["place_id"];
				$gp->alt_ids  = $object["alt_ids"];
				$gp->name     = $object["name"];
				$gp->latitude = $object["latitude"];
				$gp->longitude = $object["longitude"];
				$gp->address1 = $object["address1"];
				$gp->address2 = $object["address2"];
				$gp->city     = $object["city"];
				$gp->province = $object["province"];
				$gp->country  = $object["country"];
				$gp->postal_code   = $object["postal_code"];
			} else {
				//  id, place_id,  alt_ids,  name, latitude, longitude, address1,  address2,  city,  province,  country,  postal_code
				$gp->id       = $object[0];
				$gp->place_id = $object[1];
				$gp->alt_ids  = $object[2];
				$gp->name     = $object[3];
				$gp->latitude = $object[4];
				$gp->longitude = $object[5];
				$gp->address1 = $object[6];
				$gp->address2 = $object[7];
				$gp->city     = $object[8];
				$gp->province = $object[9];
				$gp->country  = $object[10];
				$gp->postal_code   = $object[11];
			}
		}

		if ( is_object( $object ) ) {
			$gp->id       = $object->id;
			$gp->place_id = $object->place_id;
			$gp->alt_ids  = $object->alt_ids;
			$gp->name     = $object->name;
			$gp->latitude = $object->latitude;
			$gp->longitude = $object->longitude;
			$gp->address1 = $object->address1;
			$gp->address2 = $object->address2;
			$gp->city     = $object->city;
			$gp->province = $object->province;
			$gp->country  = $object->country;
			$gp->postal_code   = $object->postal_code;
		}

		return $gp;
	}

	protected function get_bulk_actions() {
		$actions = array();

//		if ( is_multisite() ) {
//			if ( current_user_can( 'remove_users' ) ) {
//				$actions['remove'] = __( 'Remove' );
//			}
//		} else {
//			if ( current_user_can( 'delete_users' ) ) {
//				$actions['delete'] = __( 'Delete' );
//			}
//		}

		return $actions;
	}
}