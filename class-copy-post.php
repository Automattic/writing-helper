<?php

WritingHelper()->add_helper( 'copy_post', new WH_CopyPost() );

class WH_CopyPost {

	function init() {

		add_action( 'wp_ajax_helper_search_posts', array( $this, 'add_ajax_search_posts_endpoint' ) );
		add_action( 'wp_ajax_helper_get_post', array( $this, 'add_ajax_get_post_endpoint' ) );
		add_action( 'wp_ajax_helper_stick_post', array( $this, 'add_ajax_stick_post_endpoint' ) );
		add_action( 'wp_ajax_helper_record_stat', array( $this, 'add_ajax_record_stat_endpoint' ) );

		// Add "Copy a Post" to the Posts menu.
		add_action( 'admin_menu', array( $this, 'add_submenu_page' ) );
	}

	/**
	 * Add submenu links for each supported post type
	 */
	function add_submenu_page() {
		$post_types = get_post_types();
		foreach( $post_types as $post_type ) {
			if( post_type_supports( $post_type, 'writing-helper' ) ) {
				$post_type_obj = get_post_type_object( $post_type );

				$submenu_page = 'edit.php';
				if ( 'post' != $post_type ) {
					$submenu_page .= '?post_type=' . $post_type;
				}

				if ( $post_type == 'post' ) {
					$submenu_page_label = __( 'Copy a Post' );
				} else if ( $post_type == 'page' ) {
					$submenu_page_label = __( 'Copy a Page' );
				} else {
					$submenu_page_label =  sprintf( _x( 'Copy a %s', 'Copy a {post_type}' ), $post_type_obj->labels->singular_name );
				}

				$submenu_page_link = add_query_arg( 'cap#cap', '', str_replace( 'edit.php', '/post-new.php', $submenu_page ) );

				add_submenu_page( $submenu_page, $submenu_page_label, $submenu_page_label, $post_type_obj->cap->edit_posts, $submenu_page_link );
			}
		}
	}

	function add_ajax_search_posts_endpoint() {
		global $wpdb;

		check_ajax_referer( 'writing_helper_nonce', 'nonce' );

		$_REQUEST = stripslashes_deep( $_REQUEST );
		$search_terms = $_REQUEST['search'];

		$posts = false;
		$post_type = ! empty( $_REQUEST['post_type'] ) ? sanitize_key( $_REQUEST['post_type'] ) : 'post';

		if ( empty( $search_terms ) || __( 'Search for posts by title' ) == $search_terms ) {
			$sticky_posts = get_option( 'copy_a_post_sticky_posts' );
			$args = array(
					'post_type'   => $post_type,
					'post_status' => 'any',
					'numberposts' => 20,
					'exclude' => implode( ',', (array) $sticky_posts ),
				);
			die( json_encode( get_posts( $args ) ) );
		}

		$like = like_escape( $search_terms );
		$post_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_title LIKE %s AND post_type = %s LIMIT 20", "%$like%", $post_type ) );

		if ( !empty( $post_ids ) ) {
			$post_ids = implode( ',', (array) $post_ids );
			$args = array(
					'post_type'   => $post_type,
					'post_status' => 'any',
					'include' => $post_ids,
				);
			$posts = get_posts( $args );
		}

		do_action( 'wh_copypost_searched_posts' );

		die( json_encode( $posts ) );
	}

	function add_ajax_get_post_endpoint() {
		global $wpdb, $current_blog;

		check_ajax_referer( 'writing_helper_nonce', 'nonce' );

		$_REQUEST = stripslashes_deep( $_REQUEST );
		$post_id = (int) $_REQUEST['post_id'];

		if ( empty( $post_id ) )
			die( '-1' );

		$post = get_post( $post_id );

		if ( 'post' == $post->post_type ) {
			$post->post_tags = implode( ', ', (array) $wpdb->get_col( $wpdb->prepare( "SELECT slug FROM {$wpdb->terms} AS t INNER JOIN {$wpdb->term_taxonomy} AS tt ON tt.term_id = t.term_id INNER JOIN {$wpdb->term_relationships} AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE tt.taxonomy IN ( 'post_tag' ) AND tr.object_id = %d", $post_id ) ) );
			$post->post_categories = get_the_category( $post_id );
		}

		do_action( 'wh_copypost_copied_post', $post );

		die( json_encode( $post ) );
	}

	function add_ajax_stick_post_endpoint() {
		check_ajax_referer( 'writing_helper_nonce', 'nonce' );

		$_REQUEST = stripslashes_deep( $_REQUEST );
		$post_id = (int) $_REQUEST['post_id'];

		if ( empty( $post_id ) )
			die( '-1' );

		// Get sticky posts for the blog.
		$sticky_posts = (array) get_option( 'copy_a_post_sticky_posts' );

		$existing = array_search( $post_id, $sticky_posts );
		if ( false !== $existing ) {
			unset( $sticky_posts[$existing] );
		} else if ( count( $sticky_posts ) > 2 ) {
			array_pop( $sticky_posts );
		}

		array_unshift( $sticky_posts, $post_id );
        update_option( 'copy_a_post_sticky_posts', $sticky_posts );

        die( '1' );
    }

	function add_ajax_record_stat_endpoint() {
		$_REQUEST = stripslashes_deep( $_REQUEST );
		$stat = $_REQUEST['stat'];

		if ( empty( $stat ) )
			die( '-1' );

		do_action( 'wh_copypost_ajax_stat', $stat );

		die( '1' );
	}
}