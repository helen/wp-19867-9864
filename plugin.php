<?php
/*
 Plugin Name: wp-19867-9864
 Plugin URI: https://github.com/helenhousandi/wp-19867-9864
 Description: Plugin-to-patch development for #19867 and #9864.
 Author: helen
 Version: 0.1
 Author URI: http://helenhousandi.com
 */

// This is really a namespacing class, deal.
class WP_19867 {
	public function __construct() {
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 9999 );
		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'wp_ajax_get_users', array( $this, 'get_users' ) );
	}

	public function admin_bar_menu() {
		global $wp_admin_bar;

		/* Add the main siteadmin menu item */
		$wp_admin_bar->add_menu( array(
			'id' => 'wp-19867',
			'parent' => 'top-secondary',
			'title' => 'Patch: #19867 (Select2)',
			'href' => 'https://core.trac.wordpress.org/ticket/19867',
		) );
	}

	public function admin_head() {
?>
<style type="text/css">
#wpadminbar ul li#wp-admin-bar-wp-19867 {
	background-color: yellow;
	background-image: repeating-linear-gradient(135deg, yellow, yellow 10px, #ccc 10px, #ccc 20px);
}

#wpadminbar ul li#wp-admin-bar-wp-19867:hover a {
	background-color: yellow;
	background-image: repeating-linear-gradient(135deg, #ccc, #ccc 10px, yellow 10px, yellow 20px);
}

#wpadminbar ul li#wp-admin-bar-wp-19867 a {
	color: black;
}
</style>
<?php
	}

	public function add_meta_boxes() {
		remove_meta_box( 'authordiv', 'post', 'normal' );
		add_meta_box( 'authordiv-test', 'Author (Select2)', array( $this, 'display_meta_box' ), 'post', 'side' );
	}

	public function display_meta_box( $post ) {
		global $user_ID;
?>
<label class="screen-reader-text" for="post_author_override"><?php _e('Author'); ?></label>
<?php
		// forked version of this function for the purposes of the patch
		$this->wp_dropdown_users( array(
			'who' => 'authors',
			'name' => 'post_author_override',
			'selected' => empty( $post->ID ) ? $user_ID : $post->post_author,
			'include_selected' => true
		) );
	}

	/**
	 * Create dropdown HTML content of users.
	 *
	 * The content can either be displayed, which it is by default or retrieved by
	 * setting the 'echo' argument. The 'include' and 'exclude' arguments do not
	 * need to be used; all users will be displayed in that case. Only one can be
	 * used, either 'include' or 'exclude', but not both.
	 *
	 * The available arguments are as follows:
	 *
	 * @since 2.3.0
	 *
	 * @global wpdb $wpdb WordPress database object for queries.
	 *
	 * @param array|string $args {
	 *     Optional. Array or string of arguments to generate a drop-down of users.
	 *     {@see WP_User_Query::prepare_query() for additional available arguments.
	 *
	 *     @type string       $show_option_all         Text to show as the drop-down default (all).
	 *                                                 Default empty.
	 *     @type string       $show_option_none        Text to show as the drop-down default when no
	 *                                                 users were found. Default empty.
	 *     @type int|string   $option_none_value       Value to use for $show_option_non when no users
	 *                                                 were found. Default -1.
	 *     @type string       $hide_if_only_one_author Whether to skip generating the drop-down
	 *                                                 if only one user was found. Default empty.
	 *     @type string       $orderby                 Field to order found users by. Accepts user fields.
	 *                                                 Default 'display_name'.
	 *     @type string       $order                   Whether to order users in ascending or descending
	 *                                                 order. Accepts 'ASC' (ascending) or 'DESC' (descending).
	 *                                                 Default 'ASC'.
	 *     @type array|string $include                 Array or comma-separated list of user IDs to include.
	 *                                                 Default empty.
	 *     @type array|string $exclude                 Array or comma-separated list of user IDs to exclude.
	 *                                                 Default empty.
	 *     @type bool|int     $multi                   Whether to skip the ID attribute on the 'select' element.
	 *                                                 Accepts 1|true or 0|false. Default 0|false.
	 *     @type string       $show                    User table column to display. If the selected item is empty
	 *                                                 then the 'user_login' will be displayed in parentheses.
	 *                                                 Accepts user fields. Default 'display_name'.
	 *     @type int|bool     $echo                    Whether to echo or return the drop-down. Accepts 1|true (echo)
	 *                                                 or 0|false (return). Default 1|true.
	 *     @type int          $selected                Which user ID should be selected. Default 0.
	 *     @type bool         $include_selected        Whether to always include the selected user ID in the drop-
	 *                                                 down. Default false.
	 *     @type string       $name                    Name attribute of select element. Default 'user'.
	 *     @type string       $id                      ID attribute of the select element. Default is the value of $name.
	 *     @type string       $class                   Class attribute of the select element. Default empty.
	 *     @type int          $blog_id                 ID of blog (Multisite only). Default is ID of the current blog.
	 *     @type string       $who                     Which type of users to query. Accepts only an empty string or
	 *                                                 'authors'. Default empty.
	 * }
	 * @return string|null Null on display. String of HTML content on retrieve.
	 */
	public function wp_dropdown_users( $args = '' ) {
		/**
		 * @todo localize settings separately for each instance of the dropdown,
		 * including the $show param passed into $args, which we'll pass off to
		 * the AJAX users() call, which will pass to the WP_User_Query?
		 */
		$this->enqueue_scripts();
		$defaults = array(
			'show_option_all' => '', 'show_option_none' => '', 'hide_if_only_one_author' => '',
			'orderby' => 'display_name', 'order' => 'ASC',
			'include' => '', 'exclude' => '', 'multi' => 0,
			'show' => 'display_name', 'echo' => 1,
			'selected' => 0, 'name' => 'user', 'class' => '', 'id' => '',
			'blog_id' => $GLOBALS['blog_id'], 'who' => '', 'include_selected' => false,
			'option_none_value' => -1
		);

		$defaults['selected'] = is_author() ? get_query_var( 'author' ) : 0;

		$r = wp_parse_args( $args, $defaults );
		$show = $r['show'];
		$show_option_all = $r['show_option_all'];
		$show_option_none = $r['show_option_none'];
		$option_none_value = $r['option_none_value'];

		$query_args = wp_array_slice_assoc( $r, array( 'blog_id', 'include', 'exclude', 'orderby', 'order', 'who' ) );
		$query_args['fields'] = array( 'ID', 'user_login', $show );
		$users = get_users( $query_args );

		$name = esc_attr( $r['name'] );
		if ( $r['multi'] && ! $r['id'] ) {
			$id = '';
		} else {
			$id = $r['id'] ? "id='" . esc_attr( $r['id'] ) . "'" : "id='$name'";
		}


		if ( $r['selected'] ) {
			$user = get_userdata( $r['selected'] );
			$selected_show = $user->get( $r['show'] );
		}

		$output = sprintf( "<input name='%s' %s class='%s' data-placeholder='%s' %s type='hidden' %s>",
			$name,
			$id,
			$r['class'],
			__( 'Select a user' ),
			$r['selected'] ? sprintf( "value='%s'", $r['selected'] ) : '',
			$r['selected'] ? sprintf( "data-selected-show='%s'", $selected_show ) : '' );

		/**
		 * Filter the wp_dropdown_users() HTML output.
		 *
		 * @since 2.3.0
		 *
		 * @param string $output HTML output generated by wp_dropdown_users().
		 */
		$html = apply_filters( 'wp_dropdown_users', $output );

		if ( $r['echo'] ) {
			echo $html;
		}
		return $html;
	}

	/**
	 * Enqueue Select2 and the implementation script.
	 */
	public function enqueue_scripts() {
		wp_enqueue_style( 'select2', plugins_url( 'includes/vendor/select2-3.5.1/select2.css', __FILE__ ) );
		wp_enqueue_script( 'select2', plugins_url( 'includes/vendor/select2-3.5.1/select2.js', __FILE__ ), array( 'jquery' ) );
		wp_enqueue_script( 'wp-select2', plugins_url( 'includes/js/wp-select2.js', __FILE__ ), array( 'jquery', 'select2', 'wp-util' ) );
	}

	/**
	 * AJAX endpoint handler for querying users.
	 */
	public function get_users() {
		// @todo consider proper capability check.
		if ( ! current_user_can( 'read' ) )
			wp_send_json_error();

		$args = array(
			'orderby' => 'display_name',
			'number' => '10'
		);

		if ( ! empty( $_REQUEST['search'] ) ) {
			$args['search'] = $_REQUEST['search'] . '*';
			$args['search_columns'] = array( 'ID', 'user_login', 'user_email', 'user_url', 'user_nicename' );
		}

		$page = intval( $_REQUEST['page'] ) - 1;
		if ( $page )
			$args['offset'] = $page * 10;

		$query = new WP_User_Query( $args );
		$users = $query->get_results();
		$_users = array();
		if ( ! empty( $users ) ) {
			foreach ( $users as $user ) {
				$_users[] = array(
					'id' => $user->data->ID,
					// @todo support wp_dropdown_users( array( 'show' => 'login' ) )
					'text' => $user->data->display_name
				);
			}
		}
		$count_users = count_users();

		wp_send_json_success( array( 'users' => $_users, 'total' => $count_users['total_users'] ) );
	}
}

$wp19867 = new WP_19867;
