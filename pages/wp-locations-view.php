<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! current_user_can( 'list_users' ) ) {
	wp_die(
		'<h1>' . __( 'Cheatin&#8217; uh?' ) . '</h1>' .
		'<p>' . __( 'Sorry, you are not allowed to list users.' ) . '</p>',
		403
	);
}

require_once plugin_dir_path( __FILE__ )."../includes/class-wp-locations-list-table.php";

$wp_list_table = new wp_locations_list_table();
$pagenum       = $wp_list_table->get_pagenum();
$title         = __( 'Locations' );
// $parent_file = 'users.php';

add_screen_option( 'per_page' );

// contextual help - choose Help on the top right of admin panel to preview this.
get_current_screen()->add_help_tab( array(
	'id'      => 'overview',
	'title'   => __( 'Overview' ),
	'content' => '<p>' . __( 'This screen lists all the existing users for your site. Each user has one of five defined roles as set by the site admin: Site Administrator, Editor, Author, Contributor, or Subscriber. Users with roles other than Administrator will see fewer options in the dashboard navigation when they are logged in, based on their role.' ) . '</p>' .
	             '<p>' . __( 'To add a new user for your site, click the Add New button at the top of the screen or Add New in the Users menu section.' ) . '</p>'
) );

get_current_screen()->add_help_tab( array(
	'id'      => 'screen-display',
	'title'   => __( 'Screen Display' ),
	'content' => '<p>' . __( 'You can customize the display of this screen in a number of ways:' ) . '</p>' .
	             '<ul>' .
	             '<li>' . __( 'You can hide/display columns based on your needs and decide how many users to list per screen using the Screen Options tab.' ) . '</li>' .
	             '<li>' . __( 'You can filter the list of users by User Role using the text links above the users list to show All, Administrator, Editor, Author, Contributor, or Subscriber. The default view is to show all users. Unused User Roles are not listed.' ) . '</li>' .
	             '<li>' . __( 'You can view all posts made by a user by clicking on the number under the Posts column.' ) . '</li>' .
	             '</ul>'
) );

$help = '<p>' . __( 'Hovering over a row in the users list will display action links that allow you to manage users. You can perform the following actions:' ) . '</p>' .
        '<ul>' .
        '<li>' . __( 'Edit takes you to the editable profile screen for that user. You can also reach that screen by clicking on the username.' ) . '</li>';

if ( is_multisite() ) {
	$help .= '<li>' . __( 'Remove allows you to remove a user from your site. It does not delete their content. You can also remove multiple users at once by using Bulk Actions.' ) . '</li>';
} else {
	$help .= '<li>' . __( 'Delete brings you to the Delete Users screen for confirmation, where you can permanently remove a user from your site and delete their content. You can also delete multiple users at once by using Bulk Actions.' ) . '</li>';
}
$help .= '</ul>';

get_current_screen()->add_help_tab( array(
	'id'      => 'actions',
	'title'   => __( 'Actions' ),
	'content' => $help,
) );
unset( $help );

get_current_screen()->set_help_sidebar(
	'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
	'<p>' . __( '<a href="https://codex.wordpress.org/Users_Screen">Documentation on Managing Users</a>' ) . '</p>' .
	'<p>' . __( '<a href="https://codex.wordpress.org/Roles_and_Capabilities">Descriptions of Roles and Capabilities</a>' ) . '</p>' .
	'<p>' . __( '<a href="https://wordpress.org/support/">Support Forums</a>' ) . '</p>'
);

get_current_screen()->set_screen_reader_content( array(
	'heading_views'      => __( 'Filter users list' ),
	'heading_pagination' => __( 'Users list navigation' ),
	'heading_list'       => __( 'Users list' ),
) );

if ( empty( $_REQUEST ) ) {
	$referer = '<input type="hidden" name="wp_http_referer" value="' . esc_attr( wp_unslash( $_SERVER['REQUEST_URI'] ) ) . '" />';
} elseif ( isset( $_REQUEST['wp_http_referer'] ) ) {
	$redirect = remove_query_arg( array(
		'wp_http_referer',
		'updated',
		'delete_count'
	), wp_unslash( $_REQUEST['wp_http_referer'] ) );
	$referer  = '<input type="hidden" name="wp_http_referer" value="' . esc_attr( $redirect ) . '" />';
} else {
	$redirect = 'users.php';
	$referer  = '';
}

$update = '';

switch ( $wp_list_table->current_action() ) {

	/* Bulk Dropdown menu Role changes */
	case 'promote':
		check_admin_referer( 'bulk-users' );

		if ( ! current_user_can( 'promote_users' ) ) {
			wp_die( __( 'Sorry, you are not allowed to edit this user.' ) );
		}
		exit();

	case 'dodelete':
		if ( is_multisite() ) {
			wp_die( __( 'User deletion is not allowed from this screen.' ) );
		}
		check_admin_referer( 'delete-users' );

		if ( empty( $_REQUEST['users'] ) ) {
			wp_redirect( $redirect );
			exit();
		}
		exit();

	case 'delete':
		if ( is_multisite() ) {
			wp_die( __( 'User deletion is not allowed from this screen.' ) );
		}

		check_admin_referer( 'bulk-users' );

		if ( empty( $_REQUEST['users'] ) && empty( $_REQUEST['user'] ) ) {
			wp_redirect( $redirect );
			exit();
		}

		break;

	case 'doremove':
		check_admin_referer( 'remove-users' );

		if ( ! is_multisite() ) {
			wp_die( __( 'You can&#8217;t remove users.' ) );
		}

		if ( empty( $_REQUEST['users'] ) ) {
			wp_redirect( $redirect );
			exit;
		}

	case 'remove':

		check_admin_referer( 'bulk-users' );

		if ( ! is_multisite() ) {
			wp_die( __( 'You can&#8217;t remove users.' ) );
		}

		if ( empty( $_REQUEST['users'] ) && empty( $_REQUEST['user'] ) ) {
			wp_redirect( $redirect );
			exit();
		}
		break;

	default:

		if ( ! empty( $_GET['_wp_http_referer'] ) ) {
			wp_redirect( remove_query_arg( array(
				'_wp_http_referer',
				'_wpnonce'
			), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
			exit;
		}

		if ( $wp_list_table->current_action() && ! empty( $_REQUEST['users'] ) ) {
			$userids  = $_REQUEST['users'];
			$sendback = wp_get_referer();

			/** This action is documented in wp-admin/edit-comments.php */
			$sendback = apply_filters( 'handle_bulk_actions-' . get_current_screen()->id, $sendback, $wp_list_table->current_action(), $userids );

			wp_safe_redirect( $sendback );
			exit;
		}

		$wp_list_table->prepare_items();
		$total_pages = $wp_list_table->get_pagination_arg( 'total_pages' );
		if ( $pagenum > $total_pages && $total_pages > 0 ) {
			wp_redirect( add_query_arg( 'paged', $total_pages ) );
			exit;
		}

		include_once( ABSPATH . 'wp-admin/admin-header.php' );

		$messages = array();
		if ( isset( $_GET['update'] ) ) :
			switch ( $_GET['update'] ) {
				case 'del':
				case 'del_many':
					$delete_count = isset( $_GET['delete_count'] ) ? (int) $_GET['delete_count'] : 0;
					if ( 1 == $delete_count ) {
						$message = __( 'User deleted.' );
					} else {
						$message = _n( '%s user deleted.', '%s users deleted.', $delete_count );
					}
					$messages[] = '<div id="message" class="updated notice is-dismissible"><p>' . sprintf( $message, number_format_i18n( $delete_count ) ) . '</p></div>';
					break;
				case 'add':
					if ( isset( $_GET['id'] ) && ( $user_id = $_GET['id'] ) && current_user_can( 'edit_user', $user_id ) ) {
						/* translators: %s: edit page url */
						$messages[] = '<div id="message" class="updated notice is-dismissible"><p>' . sprintf( __( 'New user created. <a href="%s">Edit user</a>' ),
								esc_url( add_query_arg( 'wp_http_referer', urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ),
									self_admin_url( 'user-edit.php?user_id=' . $user_id ) ) ) ) . '</p></div>';
					} else {
						$messages[] = '<div id="message" class="updated notice is-dismissible"><p>' . __( 'New user created.' ) . '</p></div>';
					}
					break;
				case 'promote':
					$messages[] = '<div id="message" class="updated notice is-dismissible"><p>' . __( 'Changed roles.' ) . '</p></div>';
					break;
				case 'err_admin_role':
					$messages[] = '<div id="message" class="error notice is-dismissible"><p>' . __( 'The current user&#8217;s role must have user editing capabilities.' ) . '</p></div>';
					$messages[] = '<div id="message" class="updated notice is-dismissible"><p>' . __( 'Other user roles have been changed.' ) . '</p></div>';
					break;
				case 'err_admin_del':
					$messages[] = '<div id="message" class="error notice is-dismissible"><p>' . __( 'You can&#8217;t delete the current user.' ) . '</p></div>';
					$messages[] = '<div id="message" class="updated notice is-dismissible"><p>' . __( 'Other users have been deleted.' ) . '</p></div>';
					break;
				case 'remove':
					$messages[] = '<div id="message" class="updated notice is-dismissible fade"><p>' . __( 'User removed from this site.' ) . '</p></div>';
					break;
				case 'err_admin_remove':
					$messages[] = '<div id="message" class="error notice is-dismissible"><p>' . __( "You can't remove the current user." ) . '</p></div>';
					$messages[] = '<div id="message" class="updated notice is-dismissible fade"><p>' . __( 'Other users have been removed.' ) . '</p></div>';
					break;
			}
		endif; ?>

		<?php if ( isset( $errors ) && is_wp_error( $errors ) ) : ?>
        <div class="error">
            <ul>
				<?php
				foreach ( $errors->get_error_messages() as $err ) {
					echo "<li>$err</li>\n";
				}
				?>
            </ul>
        </div>
	<?php endif;

		if ( ! empty( $messages ) ) {
			foreach ( $messages as $msg ) {
				echo $msg;
			}
		} ?>

        <div class="wrap">
            <h1 class="wp-heading-inline"><?php
				echo esc_html( $title );
				?></h1>

			<?php
			if ( current_user_can( 'create_users' ) )
			{
			    ?>
                <a href="<?php echo admin_url( 'admin.php?page=wp-location-add','https' ); ?>" class="page-title-action"><?php echo esc_html_x( 'Add New', 'location' ); ?></a>
			<?php
			}

			if ( !empty( $locationsearch ) ) {
				/* translators: %s: search keywords */
				printf( '<span class="subtitle">' . __( 'Search results for &#8220;%s&#8221;' ) . '</span>', esc_html( $locationsearch ) );
			}
			?>

            <hr class="wp-header-end">

			<?php $wp_list_table->views(); ?>

            <form method="get">

				<?php $wp_list_table->search_box( __( 'Search Locations' ), 'location' ); ?>

				<?php if ( ! empty( $_REQUEST['role'] ) ) { ?>
                    <input type="hidden" name="role" value="<?php echo esc_attr( $_REQUEST['role'] ); ?>"/>
				<?php } ?>

				<?php $wp_list_table->display(); ?>
            </form>

            <br class="clear"/>
        </div>
		<?php
		break;

} // end of the $doaction switch

include_once( ABSPATH . 'wp-admin/admin-footer.php' );
