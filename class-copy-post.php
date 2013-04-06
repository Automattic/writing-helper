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

	function add_submenu_page() {
	
		add_posts_page( __( 'Copy a Post' ), __( 'Copy a Post' ), 'edit_posts', '/post-new.php?&cap#cap' );
		add_pages_page( __( 'Copy a Page' ), __( 'Copy a Page' ), 'edit_pages', '/post-new.php?post_type=page&cap#cap' );
	}

	function add_ajax_search_posts_endpoint() {
		global $wpdb;

		check_ajax_referer( 'writing_helper_nonce', 'nonce' );

		$_REQUEST = stripslashes_deep( $_REQUEST );
		$search_terms = $_REQUEST['search'];

		$posts = false;
		$post_type = 'post';
		if ( isset( $_REQUEST['post_type'] ) && $_REQUEST['post_type'] == 'page' )
			$post_type = 'page';

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

		die( '1' );
	}
}

