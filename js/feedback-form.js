jQuery(document).ready(function($) {
	var display_error, hide_error,
		$textarea_feedback = $( '#feedback-text' ),
		$first_screen = $( '.draftfeedback-first-screen' ),
		$second_screen = $( '.draftfeedback-second-screen' );

	display_error = function( id, notice, hide_on_keydown ) {
		var error_container = $( '#draft-error' );
		if ( error_container.length) {
			hide_error( false, true ).done( function() {
				display_error( id, notice, hide_on_keydown );
			});
			return;
		} else {
			error_container = '<div id="draft-error" class="error"><p>' + notice + '</p></div>';
		}

		$(id).after(error_container);
		if ( hide_on_keydown ) {
			$( hide_on_keydown ).on( 'keydown', function() {
				hide_error( false );
			});
		} else {
			hide_error( true );
		}
	}

	hide_error = function( delayed, fast ) {
		var error_container = $('#draft-error');

		if ( delayed ) {
			error_container = error_container.delay( 4000 );
		}

		return error_container
			.fadeOut( fast ? 'fast' : 'slow' )
				.promise()
			.done( function() { $( '#draft-error' ).remove(); } );
	};

	$('#feedbackform').submit(function( e ) {
		e.preventDefault();

		// Don't send empty feedback
		if ( '' == $textarea_feedback.val() ) {
			display_error(
				'#feedback-text',
				DraftFeedback.i18n.error_empty_feedback,
				'#feedback-text'
			);
			return false;
		}

		$.ajax({
			type: 'GET',
			url: DraftFeedback.ajaxurl,
			data: {
				action: 'add_feedback',
				feedback: $textarea_feedback.val(),
				shareadraft: DraftFeedback.shareadraft,
				nonce: DraftFeedback.nonce,
				post_ID: DraftFeedback.post_ID
			},
			dataType: 'jsonp',
			beforeSend: function() {
				// Disable the button so it can't be clicked more than once to submit
				$( '#feedbackform input:submit' )
					.val( DraftFeedback.i18n.button_sending_feedback )
					.attr( 'disabled', true );
			},
			success: function(data, status, xhr) {
				var promise;

				if (data['error']) {
					display_error('#feedback-text', data['error']);
				} else {

					// Starting to fade out the first screen of the feedback interface
					promise = $first_screen.fadeOut( 400 ).promise();

					promise.done(function(){

						// Fading is done, resetting the form and fading in the thank you screen
						$textarea_feedback.val( '' );
						$second_screen.fadeIn( 400 );
					});
				}
			},
			error: function(xhr, status, error) {
				display_error(
					'#feedback-text',
					DraftFeedback.i18n.error_message.replace( '{error}', error )
				);
			},
			complete: function() {
				$( '#feedbackform input:submit' )
					.val( DraftFeedback.i18n.button_send_feedback )
					.removeAttr( 'disabled ');
			}
		});
	});
	$( '#feedback-more' ).on( 'click', function( event ) {
		var promise = $second_screen.fadeOut( 400 ).promise();

		promise.done(function() {

			// When the second screen is completely faded away, we start fading the first screen in
			$first_screen.fadeIn( 400 ).promise().done(function() {

				// After the first screen is visible, we focus on the textarea
				$textarea_feedback.focus();
			});
		});
	});
	$( '#draftfeedback-activate' ).on( 'click', function( event ) {
		event.preventDefault();
		$first_screen.show();
		$second_screen.hide();
		$( 'body' ).removeClass( 'draftfeedback-closed' ).addClass( 'draftfeedback-open' );
		$( window ).triggerHandler( 'resize' );
	});
	$( '.draftfeedback-feedback-form' ).on( 'click', '.draftfeedback-deactivate', function( event ) {
		event.preventDefault();
		$( 'body' ).removeClass( 'draftfeedback-open' ).addClass( 'draftfeedback-closed' );
	});
	$textarea_feedback.focus();


	// If the body inner width is less than this number, the feedback helper for
	// will start in minimized state
	var body_width_threshold = 720;
	var feedback_textarea = $('#feedbackform textarea');
	var resize_handler = function() {
		var sidebar_height = $( '.draftfeedback-feedback-form' ).height();
		var intro_height = $( '.draftfeedback-intro' ).height();
		var textarea_height = sidebar_height - intro_height;
		feedback_textarea.css( 'height', (textarea_height - 130) + 'px' );
	};

	if ( $( 'body' ).innerWidth() < body_width_threshold ) {
		$( 'body' ).addClass( 'draftfeedback-closed' );
	} else {

		// Hiding the buttons that minimize the helper - they are not needed on a large screen
		$( '.draftfeedback-deactivate' ).hide();
		$( 'body' ).addClass( 'draftfeedback-open' );
	}

	$( window ).resize( resize_handler );
	resize_handler();
});
