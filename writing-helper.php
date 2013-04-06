<?php
/*
Plugin Name: Writing Helper
Plugin URI: http://wordpress.org/extend/plugins/writing-helper/
Description: Helps you write your posts
Author: Nikolay Bachiyski, Daniel Bachhuber, Automattic
Version: 1.0-alpha
Author URI: http://automattic.com/
*/

$writing_helper = new WritingHelper();
add_action( 'init', array( &$writing_helper, 'init' ) );
foreach( glob( dirname(__FILE__). '/writing-helper/class-*.php' ) as $php_file_name ) {
	require $php_file_name;
}

class WritingHelper {
	public $helpers = array();
	
	function init() {
		if ( is_admin() ) {
			add_action( 'add_meta_boxes', array( &$this, 'add_meta_box' ) );
		}
	}

	function add_meta_box() {
		add_meta_box( 
			'writing_helper_meta_box',
			__( 'Writing Helper' ),
			array( &$this, 'meta_box_content' ),
			'post',
			'normal',
			'high'
		);
		add_meta_box( 
			'writing_helper_meta_box',
			__( 'Writing Helper' ),
			array( &$this, 'meta_box_content' ),
			'page',
			'normal',
			'high'
		);
		wp_enqueue_style(
			'writing_helper_style',
			'/wp-content/mu-plugins/writing-helper/writing-helper.css',
			array(),
			'06242011'
		);
		WritingHelper::enqueue_script();
	}

	static function enqueue_script() {
		wp_enqueue_script(
			'writing_helper_script',
			'/wp-content/mu-plugins/writing-helper/script.js',
			array( 'jquery' ),
			'21032012',
			true
		);
	}

	function meta_box_content() {
		global $post_id, $current_user, $post, $screen_layout_columns;
		wp_localize_script( 'writing_helper_script', 'WritingHelperBox', array( 'nonce' => wp_create_nonce( 'writing_helper_nonce' ) ) );
		$df       = $this->helpers['draft_feedback'];
		$requests = $df->get_requests( $post_id, $sort = true );
		$show_feedback_button = ( !empty( $requests ) || ( is_object( $post ) && 'publish' != $post->post_status ) );
		require_once( dirname( __FILE__ ) . '/writing-helper/meta-box-content.php' );
	}

	function add_helper( $helper_name, $helper_obj ) {
		$this->helpers[ $helper_name ] = $helper_obj;	
	}
}
