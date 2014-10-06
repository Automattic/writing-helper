<?php
/*
Plugin Name: Writing Helper
Plugin URI: http://wordpress.org/extend/plugins/writing-helper/
Description: Helps you write your posts
Author: Nikolay Bachiyski, Daniel Bachhuber, Prasath Nadarajah, Automattic
Version: 1.0-rc1
Author URI: http://automattic.com/
Text Domain: writing-helper
*/

define( 'WH_VERSION', '1.0-rc1' );

foreach( glob( dirname(__FILE__). '/class-*.php' ) as $wh_php_file_name ) {
	require $wh_php_file_name;
}

class Writing_Helper {

	public $helpers = array();

	public $plugin_url;

	const HANDHELD_MEDIA_QUERY = '(min-width : 320px) and (max-width : 720px)';

	private static $instance;

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Writing_Helper;
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

		add_post_type_support( 'post', 'writing-helper' );
		add_post_type_support( 'page', 'writing-helper' );
	}

	public function action_init() {

		// Helpers each have an init() method
		foreach( $this->helpers as $helper ) {
			if ( method_exists( $helper, 'init' ) )
				$helper->init();
		}
	}

	function add_meta_box() {
		$post_type = get_post_type();
		if( post_type_supports( $post_type, 'writing-helper' ) ) {
			add_meta_box( 'writing_helper_meta_box', __( 'Writing Helper' ), array( $this, 'meta_box_content' ), $post_type, 'normal', 'high' );
		}
	}

	public function action_admin_enqueue_scripts() {

		$screen = get_current_screen();
		if ( 'post' != $screen->base || ! post_type_supports( $screen->post_type, 'writing-helper' ) )
			return;
		self::enqueue_admin_scripts();
	}

	public static function enqueue_admin_scripts() {
		wp_enqueue_style(
			'writing_helper_style',
			Writing_Helper()->plugin_url . 'css/writing-helper.css',
			array(),
			WH_VERSION
		);
		wp_enqueue_script(
			'writing_helper_script',
			Writing_Helper()->plugin_url . 'js/writing-helper.js',
			array( 'jquery' ),
			WH_VERSION,
			true
		);
	}

	public static function enqueue_front_end_scripts() {
		wp_enqueue_style(
			'writing_helper_feedback_form',
			Writing_Helper()->plugin_url . 'css/feedback-form.css',
			array(),
			WH_VERSION
		);
		wp_enqueue_style(
			'writing_helper_feedback_form_handheld',
			Writing_Helper()->plugin_url . 'css/feedback-form-handheld.css',
			array( 'writing_helper_feedback_form' ),
			WH_VERSION,
			Writing_Helper::HANDHELD_MEDIA_QUERY
		);

		wp_enqueue_script(
			'writing_helper_script',
			Writing_Helper()->plugin_url . 'js/feedback-form.js',
			array( 'jquery' ),
			WH_VERSION,
			true
		);
	}

	function meta_box_content(
			$entry = NULL, $metabox = NULL, $parameters = array() ) {
		global $post_id, $current_user, $post, $screen_layout_columns;

		// If the function is called with a post ID
		if( !is_object( $entry ) && isset( $entry ) && ! isset( $post_id ) ) {
			$post_id = $entry;
		}

		$object_values = array(
			'nonce' => wp_create_nonce( 'writing_helper_nonce' ),
			'i18n' => array (
				'error_message' => sprintf(
					__( 'Internal Server Error: %s', 'writing-helper' ), '{error}'
				),
				'customize_message' => __( 'Customize the message', 'writing-helper' ),
				'customize_message_single' => sprintf(
					__( 'Customize the message to %s', 'writing-helper' ), '{whom}'
				),
				'customize_message_multiple' => sprintf(
					__( 'Customize the message to %s and %s more', 'writing-helper' ),
					'{whom}',
					'{number}'
				)
			)
		);

		/**
		 * The tracking image URL can be defined as a string constant.
		 * The string must contain two placeholder substrings:
		 *		* {helper_name} - the helper identifier
		 *		* {random} - a random number
		 * The specified substrings will be replaced in the client side code
		 */
		if ( defined ( 'WH_TRACKING_IMAGE' ) ) {
			$object_values['tracking_image'] = WH_TRACKING_IMAGE;
		}

		wp_localize_script(
			'writing_helper_script',
			'WritingHelperBox',
			$object_values
		);
		$df       = $this->helpers['draft_feedback'];
		$requests = $df->get_requests( $post_id, $sort = true );
		$show_feedback_button = ( is_object( $post ) && 'publish' != $post->post_status );

		$parameters = array_merge(
			array(
				'show_helper_selector' => true,
				'show_copy_block' => true,
				'show_feedback_block' => true,
				'wrap_feedback_table' => true
			),
			$parameters
		);

		require_once( dirname( __FILE__ ) . '/templates/meta-box.tpl.php' );
	}

	function add_helper( $helper_name, $helper_obj ) {
		$this->helpers[ $helper_name ] = $helper_obj;
	}
}

function Writing_Helper() {
	return Writing_Helper::instance();
}
add_action( 'plugins_loaded', 'Writing_Helper' );
