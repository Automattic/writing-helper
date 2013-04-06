<?php
/*
Plugin Name: Writing Helper
Plugin URI: http://wordpress.org/extend/plugins/writing-helper/
Description: Helps you write your posts
Author: Nikolay Bachiyski, Daniel Bachhuber, Automattic
Version: 1.0-alpha
Author URI: http://automattic.com/
*/

foreach( glob( dirname(__FILE__). '/class-*.php' ) as $wh_php_file_name ) {
	require $wh_php_file_name;
}

class WritingHelper {

	public $helpers = array();

	public $plugin_url;

	private static $instance;

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WritingHelper;
			self::$instance->setup_actions();
			self::$instance->setup_globals();
		}
		return self::$instance;
	}

	private function setup_actions() {

		add_action( 'init', array( $this, 'action_init' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'action_admin_enqueue_scripts' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
	}

	private function setup_globals() {

		$this->plugin_url = plugins_url( '/', __FILE__ );
	}

	public function action_init() {

		// Helpers each have an init() method
		foreach( $this->helpers as $helper ) {
			if ( method_exists( $helper, 'init' ) )
				$helper->init();
		}
	}

	function add_meta_box() {

		add_meta_box( 
			'writing_helper_meta_box',
			__( 'Writing Helper' ),
			array( $this, 'meta_box_content' ),
			'post',
			'normal',
			'high'
		);
		add_meta_box( 
			'writing_helper_meta_box',
			__( 'Writing Helper' ),
			array( $this, 'meta_box_content' ),
			'page',
			'normal',
			'high'
		);
		wp_enqueue_style(
			'writing_helper_style',
			$this->plugin_url . 'writing-helper.css',
			array(),
			'06242011'
		);
	}

	public function action_admin_enqueue_scripts() {
		wp_enqueue_script(
			'writing_helper_script',
			$this->plugin_url . 'script.js',
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
		require_once( dirname( __FILE__ ) . '/meta-box-content.php' );
	}

	function add_helper( $helper_name, $helper_obj ) {
		$this->helpers[ $helper_name ] = $helper_obj;	
	}
}

function WritingHelper() {
	return WritingHelper::instance();
}
add_action( 'plugins_loaded', 'WritingHelper' );