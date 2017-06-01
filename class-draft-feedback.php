<?php

Writing_Helper()->add_helper( 'draft_feedback', new Writing_Helper_Draft_Feedback() );

/**
 * Handle the "Share a Draft" and commenting.
 *
 * Loosely modelled after {@link http://wordpress.org/extend/plugins/shareadraft/}
 * but it's mechanism is different.
 *
 * Most of the data for this is stored in post_meta for the post.
 *
 * Stats:
 * - writinghelper-feedbackrequest: clicks on share a draft in writing helper
 * - feedbackrequest-post-request: number of posts which have used shareadraft feature
 * - feedbackrequest-request-sent: a request for sharing e-mailed
 * - feedbackrequest-pageview: someone has viewed a post and feedback form
 * - feedbackrequest-feedback-received: a feedback has been submitted
 */
class Writing_Helper_Draft_Feedback {
	/**
	 * Temporary holder of post object if user is permed to view a draft
	 */
	public $shared_post;
	/**
	 * Post meta key for feedback on post
	 */
	const feedback_metakey = 'draft_feedback';
	/**
	 * Post meta key for who draft has been shared with (and access control)
	 *
	 * This contains an array indexed by e-mail, with the following hash:
	 * - key: the key to access the post
	 * - time: the time the request was created
	 * - user_id: the person who made the request (writer)
	 */
	const requests_metakey = 'draftfeedback_requests';

	/**
	 * Minimum feedback text length
	 */
	const MIN_FEEDBACK_LENGTH = 5;

	function init() {
		// should work even if not logged in (for testing)
		if ( isset( $_REQUEST['shareadraft'] ) ) {
			add_filter( 'the_posts', array( $this, 'the_posts_intercept' ) );
			add_filter( 'posts_results', array( $this, 'posts_results_intercept' ) );
			add_action( 'wp_ajax_add_feedback', array( $this, 'add_feedback_ajax_endpoint' ) );
			add_action( 'wp_ajax_nopriv_add_feedback', array( &$this, 'add_feedback_ajax_endpoint' ) );
		}

		add_action( 'wp_ajax_request_feedback', array( $this, 'add_request_ajax_endpoint' ) );
		add_action( 'wp_ajax_revoke_draft_access', array( $this, 'revoke_draft_access_ajax_endpoint' ) );
		add_action( 'wp_ajax_get_draft_link', array( $this, 'get_draft_link_ajax_endpoint' ) );
		add_action( 'transition_post_status', array( $this, 'post_status_change' ), 10, 3 );
	}

	function can_mail( $post_id ) {
		return current_user_can( 'edit_post', $post_id );
	}

	function post_status_change( $new_status, $old_status, $post ) {
		if ( $new_status != 'publish' || $new_status == $old_status ) {
			return;
		}
		if ( ! $requests = $this->get_requests( $post->ID ) ) {
			return;
		}
		foreach ( $requests as $email => $request ) {
			if ( ! isset( $request['revoked'] ) && is_email( $email ) ) {
				$this->email_post_published( $email, $post, $request );
			}
		}
	}

	private function email_headers( &$user ) {
		$headers = 'Reply-To: ' . $user->display_name . ' <' . $user->user_email . ">\r\n";
		$headers .= "From: " . $user->display_name . " <donotreply@wordpress.com>\r\n";

		/**
		 * Filter the headers included in the outgoing feedback request email.
		 *
		 * @since 1.0.0
		 *
		 * @param string  $headers The "from" and "reply-to" headers.
		 * @param WP_User $user    WP_User object of user requesting feedback.
		 */
		return apply_filters( 'writing_helper_feedback_email_headers', $headers, $user );
	}

	private function email_post_published( $email, $post, $request ) {
		$sender = get_userdata( $request['user_id'] );
		$subject = sprintf(
			__( '%1$s’s draft titled "%2$s" has been published', 'writing-helper' ),
			$sender->display_name,
			$post->post_title
		);
		$body = sprintf( __(
			'Howdy!

Recently you were kind enough to give feedback on my draft "%1$s".

It is now published! Thanks so much for your help.

Here’s the published version, and please share if you wish:
%2$s

Regards,
%3$s', 'writing-helper' ), $post->post_title, get_permalink( $post->ID ), $sender->display_name );

		wp_mail( $email, $subject, $body, $this->email_headers( $sender ) );
	}

	/**
	 * Generates a secret link that gives universal access to an
	 * unpublished post (or draft).
	 *
	 * Note: You have to save this unique ID somewhere else to
	 * handle access control. Also, you want to generate a
	 * different uniqueid() for each e-mail that requests
	 * so that we can reverse it to figure out which e-mail has
	 * given the feedback.
	 */
	function generate_secret_link( $post_id, $secret ) {
		$query_args = array( 'p' => $post_id, 'shareadraft' => $secret );
		if ( 'post' !== get_post_type( $post_id ) ) {
			$query_args['post_type'] = get_post_type( $post_id );
		}
		return add_query_arg( $query_args, home_url() );
	}

	/**
	 * This adds a feedback to the post
	 *
	 * This is stored in post_meta.
	 *
	 */
	function add_feedback( $post_id, $email, $feedback_text ) {
		$feedbacks = get_post_meta( $post_id, self::feedback_metakey, true );

		if ( ! is_array( $feedbacks ) ) {
			$feedbacks = array();
		}

		if ( ! isset( $feedbacks[ $email ] ) || ! is_array( $feedbacks[ $email ] ) ) {
			$feedbacks[ $email ] = array();
		}

		$feedbacks[ $email ][] = array( 'time' => time(), 'content' => $feedback_text );

		if ( get_post_meta( $post_id, self::feedback_metakey, true ) ) {
			update_post_meta( $post_id, self::feedback_metakey, self::addslashes_deep( $feedbacks ) );
		} else { add_post_meta( $post_id, self::feedback_metakey, self::addslashes_deep( $feedbacks ) );
		}
	}

	function add_feedback_ajax_endpoint() {
		$_REQUEST = stripslashes_deep( $_REQUEST );
		$post_id = isset( $_REQUEST['post_ID'] )? (int) $_REQUEST['post_ID'] : 0;
		$feedback = isset( $_REQUEST['feedback'] )? $_REQUEST['feedback'] : '';
		$callback = isset( $_REQUEST['callback'] )? $_REQUEST['callback'] : '';

		if ( mb_strlen( $feedback ) < self::MIN_FEEDBACK_LENGTH ) {
			$this->jsonp_die_with_error(
				sprintf(
					_n(
						'The feedback text should be at least %d character long.',
						'The feedback text should be at least %d characters long.',
						self::MIN_FEEDBACK_LENGTH,
						'writing-helper'
					),
					self::MIN_FEEDBACK_LENGTH
				),
				$callback
			);
		}

		$secret = isset( $_REQUEST['shareadraft'] )? $_REQUEST['shareadraft'] : '';

		check_ajax_referer(
			'add_feedback_nonce_'
				. get_current_blog_id()
				. '_'
				. $post_id
				. '_'
				. $secret,
			'nonce'
		);

		if ( $this->can_view( $post_id ) ) {
			$this->shared_post = get_post( $post_id );
			$this->add_feedback( $post_id, $this->request_email, $feedback );
			$this->email_feedback_received( $feedback );
		} else {
			$this->jsonp_die_with_error(
				__( "Sorry, you can't post feedbacks here.", 'writing-helper' ),
				$callback
			);
		}
		Writing_Helper::jsonp_return( array(),  $callback );
	}

	function get_feedbacks( $post_id ) {
		return get_post_meta( $post_id, self::feedback_metakey, true );
	}

	function get_user_feedbacks( $post_id, $email ) {
		$feedbacks = $this->get_feedbacks( $post_id );
		if ( ! empty( $feedbacks[ $email ] ) ) {
			return $feedbacks[ $email ];
		}
	}

	function time_to_date( $timestamp ) {
		$df = get_option( 'date_format' );
		$tf = ''; // get_option( 'time_format' );
		return date_i18n( "$df $tf", $timestamp );
	}

	/**
	 * Does the work to handle the request to share a draft
	 *
	 * @param $email text
	 * @return success or failure
	 */
	function add_request( $post_id, $emails, $email_text ) {
		global $current_user;
		if ( ! $emails || ! is_array( $emails ) ) {
			return false;
		}
		$requests = $this->get_requests( $post_id );
		do_action( 'wh_draftfeedback_existing_requests', $requests );

		foreach ( $emails as $email ) {
			$email = $this->_normalize_email( $email );
			if ( ! isset( $requests[ $email ] ) ) {
				$requests[ $email ] = array(
					'key'		=> uniqid(),
					'time'		=> time(),
					'user_id' 	=> $current_user->ID,
				);
				$res = $this->save_requests( $post_id, $requests );
				if ( ! $res ) { return false;
				}
			} else {
				$requests[ $email ]['user_id'] = $current_user->ID;
			}
			$this->email_feedback_request( $post_id, $email, $email_text, $requests[ $email ] );
		}
		return true;
	}

	function save_requests( $post_id, $requests ) {
		if ( get_post_meta( $post_id, self::requests_metakey, true ) ) {
			return update_post_meta( $post_id, self::requests_metakey, self::addslashes_deep( $requests ) );
		} else { return add_post_meta( $post_id, self::requests_metakey, self::addslashes_deep( $requests ) );
		}
	}

	private function rsort_requests( $a, $b ) {
		$a = $a['last_feedback'];
		$b = $b['last_feedback'];
		if ( $a == $b ) {
			return 0;
		} else { return ( $a > $b ) ? -1 : 1;
		}
	}

	function get_requests( $post_id, $sort = false ) {
		$requests = get_post_meta( $post_id, self::requests_metakey, true );

		// order by last received feedback, reverse chronological
		if ( $sort && $requests && is_array( $requests ) ) {
			$feedbacks = $this->get_feedbacks( $post_id );
			foreach ( $requests as $email => $values ) {
				if ( isset( $feedbacks[ $email ] ) ) {
					$last_feedback = end( $feedbacks[ $email ] );
					$requests[ $email ]['last_feedback'] = $last_feedback['time'];
				} else {
					$requests[ $email ]['last_feedback'] = $requests[ $email ]['time'];
				}
			}
			uasort( $requests, array( &$this, 'rsort_requests' ) );
		}
		if ( ! $requests ) { $requests = array();
		}
		return $requests;
	}

	/**
	 * Send an e-mail to request feedback from the user
	 */
	function email_feedback_request( $post_id, $email, $email_text, $request ) {
		global $current_user;
		$email_text = str_replace( '[feedback-link]', ' ' . $this->generate_secret_link( $post_id, $request['key'] ) . ' ', $email_text );
		$post = get_post( $post_id );
		$subject = sprintf(
			__( '%1$s asked you for feedback on a new draft: "%2$s"', 'writing-helper' ),
			$current_user->display_name,
			$post->post_title
		);
		wp_mail( $email, $subject, $email_text, $this->email_headers( $current_user ) );
		do_action( 'wh_draftfeedback_sent_request' );
		return true;
	}

	/**
	 * Was this post (url) designed to be shared?
	 */
	function can_view( $post_id ) {
		$requests = $this->get_requests( $post_id );
		if ( ! isset( $_REQUEST['shareadraft'] ) || ! $requests ) {
			return false;
		}
		foreach ( $requests as $email => $request ) {
			if ( $request['key'] == $_REQUEST['shareadraft'] && ! isset( $request['revoked'] ) ) {
				$this->request_email = $email;
				return true;
			}
		}
		return false;
	}

	/**
	 * Used to determine post results
	 *
	 * If you shared this post it stores the post locally.
	 */
	function posts_results_intercept( $posts ) {
		if ( 1 != count( $posts ) ) { return $posts;
		}
		$post = &$posts[0];
		/* Don't use get_post_status(), because it generates a DB query,
		 * which messes up with FOUND_ROWS(): https://wpcom.trac.automattic.com/ticket/2165
		 * In this case we don't need the extra get_post_status() functionality, because only
		 * posts can be shared.
		 * */
		$status = $post->post_status;
		if ( 'publish' != $status && $this->can_view( $post->ID ) ) {
			$this->shared_post = & $post;
			add_filter( 'comments_open', '__return_false' );
		} else if ( $this->can_view( $post->ID ) ) {
			add_action( 'wp_footer', array( &$this, 'inject_published_notice' ) );
		}

		return $posts;
	}

	/**
	 * Used in the loop to render post
	 *
	 * If the post was stored locally, it returns it for rendering.
	 */
	function the_posts_intercept( $posts ) {
		if ( ! empty( $posts ) && ( isset( $_GET['nux'] ) && $_GET['nux'] == 'nuts' ) ) {
			// site admins always have a post
			$overwrite_post = true;
		} else if ( ! is_null( $this->shared_post ) ) {
			$overwrite_post = true;
		} else {
			$overwrite_post = false;
		}
		if ( $overwrite_post ) {
			do_action( 'wh_draftfeedback_load_feedback_form' );
			Writing_Helper()->enqueue_front_end_scripts();
			wp_localize_script( 'writing_helper_script', 'DraftFeedback', array(
				/* Use scheme of current page, instead of obeying force_ssl_admin().
				 * Otherwise we might end up with Ajax request to a URL with a different scheme, which is not allowed by browsers
				 */
				'ajaxurl' => admin_url( 'admin-ajax.php', is_ssl()? 'https' : 'http' ),
				'post_ID' => $this->shared_post->ID,
				'shareadraft' => esc_attr( $_GET['shareadraft'] ),
				'nonce' => wp_create_nonce(
					'add_feedback_nonce_'
						. get_current_blog_id()
						. '_'
						. $this->shared_post->ID
						. '_'
						. $_GET['shareadraft']
				),
				'handheld_media_query' => Writing_Helper::HANDHELD_MEDIA_QUERY,
				'minimum_feedback_length' => self::MIN_FEEDBACK_LENGTH,
				'i18n' => array(
					'error_minimum_feedback_length' => sprintf(
						_n(
							'The feedback text should be at least %d character long.',
							'The feedback text should be at least %d characters long.',
							self::MIN_FEEDBACK_LENGTH,
							'writing-helper'
						),
						self::MIN_FEEDBACK_LENGTH
					),
					'error_message' => sprintf(
						__( 'Internal Server Error: %s', 'writing-helper' ), '{error}'
					),
					'button_send_feedback' => __( 'Send Feedback', 'writing-helper' ),
					'button_sending_feedback' => __(
						'Sending Feedback...',
						'writing-helper'
					),
				),
			) );
			add_action( 'wp_footer', array( $this, 'inject_feedback_form' ) );
			return array( &$this->shared_post );
		} else {
			$this->shared_post = null;
			return $posts;
		}
	}

	/**
	 * Notify post owner of feedback received
	 */
	private function email_feedback_received( $feedback ) {
		global $current_user;
		if ( $current_user && ! empty( $current_user->display_name ) ) {
			$reviewer = $current_user->display_name;
		} else if ( is_email( $this->request_email ) ) {
			$reviewer = $this->request_email;
		} else {
			$reviewer = '';
		}

		$post_author = get_userdata( $this->shared_post->post_author );

		if ( ! empty( $reviewer ) ) {
			$subject = sprintf(
				__( 'Feedback received from %1$s for "%2$s"', 'writing-helper' ),
				$reviewer,
				$this->shared_post->post_title
			);
		} else {
			$subject = sprintf(
				__( 'Feedback received from your friend for "%2$s"', 'writing-helper' ),
				$reviewer,
				$this->shared_post->post_title
			);
		}
		// Note: Keep in one string for easier i18n.
		$body = sprintf( __(
			'Hi %1$s,

Your friend %2$s has read your draft titled "%3$s" and provided feedback for you to read:

%4$s

You can also see their feedback here:
%5$s

Thanks for flying with WordPress.com', 'writing-helper' ),
			$post_author->display_name,
			$reviewer,
			$this->shared_post->post_title,
			$feedback,
			home_url( '/wp-admin/post.php?post=' . $this->shared_post->ID . '&action=edit&requestfeedback=1#requestfeedback' )
		);
		do_action( 'wh_draftfeedback_sent_feedback' );
		wp_mail( $post_author->user_email, $subject, $body, $this->email_headers( $post_author ) );
	}

	function inject_feedback_form() {
		include( dirname( __FILE__ ) . '/templates/feedback-form.tpl.php' );
	}

	function inject_published_notice() {
		include( dirname( __FILE__ ) . '/templates/post-published.tpl.php' );
	}

	private function _do_template( $file, $template_vars = array() ) {
		extract( $template_vars );
		ob_start();
		include( $file );
		return ob_get_clean();
	}

	function json_die_with_error( $message ) {
		Writing_Helper::json_return( array( 'error' => $message ) );
	}

	function jsonp_die_with_error( $message, $callback ) {
		if ( ! $callback ) { $this->json_die_with_error( $message );
		}

		Writing_Helper::jsonp_return( array( 'error' => $message ), $callback );
	}

	function add_request_ajax_endpoint() {
		$_REQUEST = stripslashes_deep( $_REQUEST );
		$post_id = isset( $_REQUEST['post_id'] )? (int) $_REQUEST['post_id'] : 0;

		check_ajax_referer(
			'writing_helper_nonce_' . get_current_blog_id() . '_' . $post_id,
			'nonce'
		);

		if ( ! $this->can_mail( $post_id ) ) {
			$this->json_die_with_error( __( 'Access denied', 'writing-helper' ) );
		}

		$emails = isset( $_REQUEST['emails'] )? trim( $_REQUEST['emails'] ) : '';
		if ( ! $emails ) {
			$this->json_die_with_error(
				__( 'You need to enter an email address for someone you know before sending.', 'writing-helper' )
			);
		}

		$email_text = isset( $_REQUEST['email_text'] )? trim( $_REQUEST['email_text'] ) : '';

		$single_emails = preg_split( '/[,\s]+/', $emails );
		foreach ( $single_emails as $email ) {
			$email = trim( $email );
			if ( ! $email ) {
				continue;
			}
			if ( ! is_email( $email ) ) {
				$this->json_die_with_error(
					__( 'Invalid email address', 'writing-helper' ) . ' ' . $email
				);
			}
		}
		if ( ! $email_text ) {
			$this->json_die_with_error(
				__( 'E-mail text cannot be empty', 'writing-helper' )
			);
		}
		if ( strpos( $email_text, '[feedback-link]' ) === false ) {
			$this->json_die_with_error(
				__( 'You must include [feedback-link] in the e-mail text', 'writing-helper' )
			);
		}
		$res = $this->add_request( $post_id, $single_emails, $email_text );
		if ( ! $res ) {
			$this->json_die_with_error(
				__( 'Error in adding the request', 'writing-helper' )
			);
		}

		Writing_Helper::json_return(
			array(
				'response' => $this->_get_feedback_table_content( $post_id ),
			)
		);
	}

	/**
	 * Normalize an e-mail address.
	 */
	private function _normalize_email( $email ) {
		// TODO: add more sanitization functions here
		return strtolower( trim( $email ) ); // light sanitization
	}

	/**
	 * Returns a part of the meta box HTML for the table that contains all
	 * feedback requests for the specified post ID and all available feedback.
	 *
	 * @param Integer $post_id
	 * @return String an html string
	 */
	private function _get_feedback_table_content( $post_id ) {
		ob_start();
		Writing_Helper()->meta_box_content(
			$post_id,
			null,
			array(
				'show_helper_selector' => false,
				'show_copy_block' => false,
				'show_feedback_block' => true,
				'wrap_feedback_table' => false,
			)
		);
		$response = ob_get_contents();
		ob_end_clean();
		return $response;
	}

	/**
	 * Toggle revoke/grant access
	 */
	function revoke_draft_access_ajax_endpoint() {
		$_REQUEST = stripslashes_deep( $_REQUEST );
		$post_id = isset( $_REQUEST['post_id'] )? (int) $_REQUEST['post_id'] : 0;

		check_ajax_referer(
			'writing_helper_nonce_' . get_current_blog_id() . '_' . $post_id,
			'nonce'
		);

		if ( ! $this->can_mail( $post_id ) ) {
			$this->json_die_with_error( __( 'Access denied', 'writing-helper' ) );
		}

		$revoke_email = ( isset( $_REQUEST['email'] ) ) ? $this->_normalize_email( $_REQUEST['email'] ) : '';
		$requests = $this->get_requests( $post_id );
		$res = false;
		foreach ( $requests as $email => $request ) {
			if ( $email == $revoke_email ) {
				if ( isset( $requests[ $email ]['revoked'] ) ) {
					unset( $requests[ $email ]['revoked'] );
				} else { $requests[ $email ]['revoked'] = true;
				}

				$res = $this->save_requests( $post_id, $requests );
				break;
			}
		}

		if ( ! $res ) {
			$this->json_die_with_error( __( 'Action failed', 'writing-helper' ) );
		}
	}

	function get_draft_link_ajax_endpoint() {
		$post_id = isset( $_REQUEST['post_id'] )? (int) $_REQUEST['post_id'] : 0;

		check_ajax_referer(
			'writing_helper_nonce_' . get_current_blog_id() . '_' . $post_id,
			'nonce'
		);

		if ( ! $post_id || ! $this->can_mail( $post_id ) ) {
			$this->json_die_with_error( __( 'Access denied', 'writing-helper' ) );
		}

		$key = uniqid();
		$requests = $this->get_requests( $post_id );
		$requests[ $key ] = array(
					'key'		=> $key,
					'time'		=> time(),
					'user_id' 	=> get_current_user_id(),
				);
		$this->save_requests( $post_id, $requests );
		do_action( 'wh_draftfeedback_generate_link' );

		Writing_Helper::json_return(
			array( 'response' => $this->_get_feedback_table_content( $post_id ) )
		);
	}

	static function array_map_deep( $value, $function ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $key => $data ) {
				$value[ $key ] = self::array_map_deep( $data, $function );
			}
		} elseif ( is_object( $value ) ) {
			$vars = get_object_vars( $value );
			foreach ( $vars as $key => $data ) {
				$value->{$key} = self::array_map_deep( $data, $function );
			}
		} else {
			$value = $function( $value );
		}
		return $value;
	}

	static function addslashes_deep( $value ) {
		return self::array_map_deep( $value, 'addslashes' );
	}
}

