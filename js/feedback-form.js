jQuery( document ).ready(function( $ ) {
	var displayError, hideError,
		$textareaFeedback = $( '#feedback-text' ),
		$firstScreen = $( '.draftfeedback-first-screen' ),
		$secondScreen = $( '.draftfeedback-second-screen' );

	displayError = function( id, notice, hideOnKeydown ) {
		var errorContainer = $( '#draft-error' );
		if ( errorContainer.length ) {
			hideError( false, true ).done( function() {
				displayError( id, notice, hideOnKeydown );
			});
			return;
		} else {
			errorContainer = '<div id="draft-error" class="error"><p>' + notice + '</p></div>';
		}

		$( id ).after( errorContainer );
		if ( hideOnKeydown ) {
			$( hideOnKeydown ).on( 'keydown', function() {
				hideError( false );
			});
		} else {
			hideError( true );
		}
	};

	hideError = function( delayed, fast ) {
		var errorContainer = $( '#draft-error' );

		if ( delayed ) {
			errorContainer = errorContainer.delay( 4000 );
		}

		return errorContainer
			.fadeOut( fast ? 'fast' : 'slow' )
			.promise()
			.done( function() {
				$( '#draft-error' ).remove();
			});
	};

	$( '#feedbackform' ).submit(function( e ) {
		e.preventDefault();

		// Don't send empty feedback
		if ( $textareaFeedback.val().length < DraftFeedback.minimum_feedback_length ) {
			displayError(
				'#feedback-text',
				DraftFeedback.i18n.error_minimum_feedback_length,
				'#feedback-text'
			);
			return false;
		}

		$.ajax({
			type: 'GET',
			url: DraftFeedback.ajaxurl,
			data: {
				action: 'add_feedback',
				feedback: $textareaFeedback.val(),
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
			success: function( data, status, xhr ) {
				var promise;

				if ( data.error ) {
					displayError( '#feedback-text', data.error );
				} else {

					// Starting to fade out the first screen of the feedback interface
					promise = $firstScreen.fadeOut( 400 ).promise();

					promise.done(function() {

						// Fading is done, resetting the form and fading in the thank you screen
						$textareaFeedback.val( '' );
						$secondScreen.fadeIn( 400 );
					});
				}
			},
			error: function( xhr, status, error ) {
				displayError(
					'#feedback-text',
					DraftFeedback.i18n.error_message.replace( '{error}', error )
				);
			},
			complete: function() {
				$( '#feedbackform input:submit' )
					.val( DraftFeedback.i18n.button_send_feedback )
					.removeAttr( 'disabled ' );
			}
		});
	});
	$( '#feedback-more' ).on( 'click', function( event ) {
		var promise = $secondScreen.fadeOut( 400 ).promise();

		promise.done(function() {

			// When the second screen is completely faded away, we start fading the first screen in
			$firstScreen.fadeIn( 400 ).promise().done(function() {

				// After the first screen is visible, we focus on the textarea
				$textareaFeedback.focus();
			});
		});
	});
	$textareaFeedback.focus();
});

/**
 * Feedback form interface controls
 */
jQuery( document ).ready(function( $ ) {
	var feedbackTextarea = $( '#feedbackform textarea' ),
		$buttonActivate = $( '#draftfeedback-activate' ),
		$blockFloater = $( '#feedback-floater' ),
		$blockIntro = $( '#draftfeedback-intro' ),
		$buttonsReturn = $( '.draftfeedback-return' ),
		scrollTopOffset,
		matcher,
		resizeHandler,
		sidebarHeight,
		introHeight,
		textareaHeight;

	$( 'body' ).addClass( 'draftfeedback' );

	// If the body width matches this media selector, the feedback helper
	// will start in minimized state
	matcher = window.matchMedia( DraftFeedback.handheld_media_query );

	resizeHandler = function() {

		// For smaller screens the feedback textarea is located below the
		// post, so no action is required.
		if ( matcher.matches ) {
			return;
		}

		sidebarHeight = $( '.draftfeedback-feedback-form' ).height();
		introHeight = $( '.draftfeedback-intro' ).height();
		textareaHeight = sidebarHeight - introHeight;
		feedbackTextarea.css( 'height', ( textareaHeight - 130 ) + 'px' );
	};

	$( window ).resize( resizeHandler );
	resizeHandler();

	// Clicking the activate button should take us to the form
	$buttonActivate.on( 'click', function( event ) {
		scrollTopOffset = $( window ).scrollTop();
		$buttonsReturn.show();
		$( 'html, body' ).animate({
			scrollTop: $( '#draftfeedback-intro' ).offset().top
		}, 400 );
	});

	// Clicking the return button should take us to where we were
	$buttonsReturn.on( 'click', function( event ) {
		$( 'html, body' ).animate({
			scrollTop: scrollTopOffset
		}, 400 );
	});

	// The feedback floater block should get hidden when the
	// window is scrolled down to the form
	$( window ).on( 'scroll resize', function() {
		var offset;

		if ( ! matcher.matches ) {
			return;
		}

		offset = $blockIntro.offset().top - window.innerHeight;

		if ( $( window ).scrollTop() > offset ) {
			$blockFloater.fadeOut( 'fast' );
		} else {
			$blockFloater.fadeIn( 'fast' );
		}
	});
});

/**
 * The code below includes only the matchMedia method polyfill, and doesn't
 * include the addListener code, you can see the entire repository here:
 * {@link https://github.com/paulirish/matchMedia.js}
 */

/*! matchMedia() polyfill - Test a CSS media type/query in JS. Authors & copyright (c) 2012: Scott Jehl, Paul Irish, Nicholas Zakas, David Knight. Dual MIT/BSD license */

window.matchMedia||(window.matchMedia=function(){var c=window.styleMedia||window.media;if(!c){var a=document.createElement("style"),d=document.getElementsByTagName("script")[0],e=null;a.type="text/css";a.id="matchmediajs-test";d.parentNode.insertBefore(a,d);e="getComputedStyle"in window&&window.getComputedStyle(a,null)||a.currentStyle;c={matchMedium:function(b){b="@media "+b+"{ #matchmediajs-test { width: 1px; } }";a.styleSheet?a.styleSheet.cssText=b:a.textContent=b;return"1px"===e.width}}}return function(a){return{matches:c.matchMedium(a|| "all"),media:a||"all"}}}());
