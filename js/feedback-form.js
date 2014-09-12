jQuery(document).ready(function($) {

	var display_error = function(id, notice) {
		$(id).after('<div id="draft-error" class="error"><p>' + notice + '</p></div>');
		$('#draft-error').delay(4000).fadeOut('slow');
	}

	$('#feedbackform').submit(function( e ) {
		e.preventDefault();

		// Don't send empty feedback
		if ( '' == $('#feedback-text').val() ) {
			display_error( '#feedback-text', 'The feedback text can not be blank.' );
			return false;
		}

		$.ajax({
			type: 'GET',
			url: DraftFeedback.ajaxurl,
			data: {
				action: 'add_feedback',
				feedback: $('#feedback-text').val(),
				shareadraft: DraftFeedback.shareadraft,
				nonce: DraftFeedback.nonce,
				post_ID: DraftFeedback.post_ID
			},
			dataType: 'jsonp',
			beforeSend: function() {
				// Disable the button so it can't be clicked more than once to submit
				$( '#feedbackform input:submit' ).val( 'Sending Feedback...' ).attr( 'disabled', true );
			},
			success: function(data, status, xhr) {
				var promise;

				if (data['error']) {
					display_error('#feedback-text', data['error']);
				} else {

					// Starting to fade out the first screen of the feedback interface
					promise = $( '.draftfeedback-first-screen' ).fadeOut( 400 ).promise();

					promise.done(function(){

						// Fading is done, resetting the form and fading in the thank you screen
						$( '#feedback-text' ).val( '' );
						$( '#feedbackform input:submit' )
							.val( 'Send Feedback' )
							.removeAttr( 'disabled ');
						$( '.draftfeedback-second-screen' ).fadeIn( 400 );
					});
				}
			},
			error: function(xhr, status, error) {
				display_error( '#feedback-text', "Internal Server Error: " + error );
				$( '#feedbackform input:submit' ).val( 'Send Feedback' ).removeAttr( 'disabled' );
			}
		});
	});
	$( '#feedback-more' ).on( 'click', function( event ) {
		var promise = $( '.draftfeedback-second-screen' ).fadeOut( 400 ).promise();

		promise.done(function() {

			// When the second screen is completely faded away, we start fading the first screen in
			$( '.draftfeedback-first-screen' ).fadeIn( 400 ).promise().done(function() {

				// After the first screen is visible, we focus on the textarea
				$('#feedback-text').focus();
			});
		});
	});
	$( '#draftfeedback-activate' ).on( 'click', function( event ) {
		event.preventDefault();
		$( '.draftfeedback-first-screen' ).show();
		$( '.draftfeedback-second-screen' ).hide();
		$( 'body' ).removeClass( 'draftfeedback-closed' ).addClass( 'draftfeedback-open' );
		$( window ).triggerHandler( 'resize' );
	});
	$( '.draftfeedback-feedback-form' ).on( 'click', '.draftfeedback-deactivate', function( event ) {
		event.preventDefault();
		$( 'body' ).removeClass( 'draftfeedback-open' ).addClass( 'draftfeedback-closed' );
	});
	$('#feedback-text').focus();


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
