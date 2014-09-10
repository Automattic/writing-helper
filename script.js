jQuery(function($) {
	var $requestfeedback = $('#requestfeedback');
	var default_email_text = $('textarea.customize', $requestfeedback).val();
	$.fn.replace_placeholders = function() {
		return this.each(function() {
			var $this = $(this);
			if ($this.data('replaced-placeholders')) return;
			var text = $this.val();
			var excerpt = $('#content').text();
			excerpt = $('<div>'+excerpt+'</div>').text().replace(/\n+/g, ' ');
			if (excerpt.length > 300) {
				excerpt = excerpt.substr(0, 300)+'...';
			}
			text = text.replace(/\[title\]/g, $('#title').val());
			text = text.replace(/\[excerpt\]/g, excerpt);
			$this.val(text);
			$this.data('replaced-placeholders', true);
		});
	};
	var display_error = function(id, notice) {
		$(id).after('<div id="draft-error" class="error"><p>' + notice + '</p></div>');
		$('#draft-error').delay(4000).fadeOut('slow');
	}
	$('#add-request, #add-request-custom', $requestfeedback).click(function() {
		$('textarea.customize').replace_placeholders();
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'request_feedback',
				emails: $('#invitelist').val(),
				email_text: $('textarea.customize').val(),
				nonce: WritingHelperBox.nonce,
				post_id: $('#post_ID').val()
			},
			dataType: 'json',
			success: function(data, status, xhr) {
				if (data['error'])
					display_error('#invitetoshare', data['error']);
				else {
					$( '#invitelist' ).val( '' ).triggerHandler( 'keyup' );
					$( 'a.cancel', $requestfeedback ).triggerHandler( 'click' );
					$('#invitetoshare').hide();
					$('#add-request-sent').show();
				}
			},
			error: function(xhr, status, error) {
				display_error("Internal Server Error: "+status);
			}
		});
		return false;
	});
	$('a.customize', $requestfeedback).click(function() {
		$('textarea.customize').replace_placeholders().data('replaced-placeholders', false);
		$('#modify-email').show();
		$('#add-request').hide();
		$(this).hide();
		return false;
	});
	$('a.cancel', $requestfeedback).click(function() {
		$('textarea.customize', $requestfeedback).val(default_email_text);
		$('#modify-email').hide();
		$('#add-request').show();
		$('a.customize', $requestfeedback).show();
		$( '.first-focus', $requestfeedback ).focus();
		return false;
	});
	$('textarea#invitelist', $requestfeedback).keyup(function() {
		var emails = $(this).val();
		var to = $('a.customize', $requestfeedback);
		emails = emails.replace(/^\s+/, '').replace(/\s+$/, '');
		var parts = emails.split(/\s*[,\n]\s*/);
		for(var i=0; i<parts.length; ++i) {
			if (!parts[i]) parts.splice(i, 1);
		}
		if (0 == parts.length || !emails) {
			to.html('Customize the message');
		} else if (1 == parts.length) {
			to.text('Customize the message to {whom}'.replace('{whom}', parts[0]));
		} else {
			to.text('Customize the message to {whom} and {number} more'.replace('{whom}', parts[0]).replace('{number}', parts.length - 1));
		}
	});
	$('ol.feedbacks-list li a', $requestfeedback).click(function() {
		$(this).parents('li').children('.full,.truncated').toggle();
		return false;
	});
	/* JS for the new feedback */
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
	$('#feedback-text').focus();
});

/* JS to hide/show helper boxes */
jQuery(document).ready(function($) {
	$('#helpers').on( 'click', 'li', function(e) {
		var helper_container;
		e.preventDefault();

		$('#helpers').hide();
		helper_container = $( $( 'a', this).attr( 'href' ) ).show();
		helper_container.find( '.first-focus' ).focus();
		// ping stats
		var helper_name = $( 'a', this).attr('href').substr(1); // remove the #
		new Image().src = document.location.protocol+'//pixel.wp.com/g.gif?v=wpcom-no-pv&x_writinghelper='+helper_name+'&baba='+Math.random();
	});
	$('#writing_helper_meta_box').on( 'click', '.back', function(e) {
		e.preventDefault();
		$('#helpers').show();
		$('.helper').hide();
	});
	$( '#writing_helper_meta_box' ).on( 'click', '.back, #add-request-sent a', function( event ) {
		event.preventDefault();
		$( '#invitetoshare' )
			.show()
			.find( '.first-focus' )
		.focus();
		$( '#add-request-sent' ).hide();
	});
});

/* Toggle Revoke/Grant Access */
function DraftRevokeAccess($, post_id, email, link_id){
	$.ajax({
		type: 'POST',
		url: ajaxurl,
		data: {
			action: 'revoke_draft_access',
			email: email,
			nonce: WritingHelperBox.nonce,
			post_id: post_id
		},
		dataType: 'json',
		success: function(data, status, xhr) {
			if (!data['error']) {
				var $link = $(link_id);
				$('.revoke,.unrevoke', $link).toggle();
			}
		},
		error: function() {
		}
	});
}

/* Get a link without sending an email */
function DraftGetLink($, post_id) {
	$('#df-share-link,#df-getting-link').toggle();
	$.ajax({
		type: 'POST',
		url: ajaxurl,
		data: {
			action: 'get_draft_link',
			nonce: WritingHelperBox.nonce,
			post_id: post_id
		},
		dataType: 'json',
		success: function(data) {
			if (!data['error']) {
				$('#df-share-link-p').html('<a href="' + data['link']+ '">' + data['link'] + '</a>');
				$('#df-getting-link').hide();
			}
		},
		error: function(){
			$('#df-share-link,#df-getting-link').toggle();
		}
	});
}

jQuery( function( $ ) {
	var post_search_timeout = null;
	$( 'div.copy-posts ul' ).on( 'click', 'input[type=button]', function() {
		$( this ).addClass( 'selected' );
		$( 'div.copy-posts input' ).prop( 'disabled', true );
		$( 'div.copy-posts li' ).not( $(this).parent('li') ).animate({ 'opacity': 0.3 }, 'fast' );

		$( 'div.confirm-copy' ).slideDown( 'fast' );

		if ( $( 'input#title' ).val() == '' ) {
			$( '.confirm-copy p.copying' ).show();
			$( '.confirm-copy p.confirm' ).hide();
			copy_post();
		}
	});

	$( '.confirm-copy' ).on( 'click', 'input#cancel-copy', function() {
		$( 'div.copy-posts li input.selected' ).removeClass( 'selected' );
		$( 'div.copy-posts input' ).prop( 'disabled', false );
		$( 'div.copy-posts li' ).animate({ 'opacity': 1 }, 'fast' );
		$( 'div.confirm-copy' ).slideUp( 'fast' );
	});

	$( '.confirm-copy' ).on( 'click', 'input#confirm-copy', function() {
		$( '.confirm-copy p.confirm' ).fadeOut( 'fast', function() { $( '.confirm-copy p.copying' ).fadeIn( 'fast' ); });
		copy_post();
	});

	$( '.search-posts input' ).unbind( 'keyup' ).bind( 'keyup', function() {
		search_posts( $(this) );
	});

	$( '.search-posts' ).on( 'click', 'input', function(e) {
  		var offset = $(this).offset();

  		if (e.pageX > offset.left + $(this).width() - 16 )
 			search_posts( $(this) );
	});

	// Disable the enter key in the search posts box so ppl don't publish posts by mistake.
	$(window).on( "keydown", function(e) {
  		if (e.keyCode == 13 && $('.search-posts input').is(':focus') ) return false;
	});

	function search_posts(el) {
		$( '.copy-posts' ).scrollTo( 0, 'fast' );

		if ( $( '.search-posts input' ).val() != '' )
			$( 'ul#s-posts' ).slideUp('fast');

		$( 'div.copy-posts li' ).not( el.parent('li') ).animate({ 'opacity': 0.3 }, 'fast' );
		$( 'div.copy-posts .loading' ).fadeIn( 'fast' );

		clearTimeout( post_search_timeout );
		post_search_timeout = setTimeout( function() {
			$.post( ajaxurl, {
				'action': 'helper_search_posts',
				'search': $( '.search-posts input' ).val(),
				'post_type': typenow,
				'nonce': WritingHelperBox.nonce
			}, function( posts ) {
				$( 'div.copy-posts ul#l-posts li' ).remove();

				$.each( posts, function( i, post ) {
					$tmp = $('<div></div>')
					$tmp.html( post.post_content.substr(0,200) );
					$( 'div.copy-posts ul#l-posts' ).append( '\
						<li>\
							<input type="button" value="Copy" class="button-secondary" id="cp-' + post.ID + '" /> &nbsp;\
							<span class="title">' + post.post_title + '</span> \
							<span class="excerpt">' + $tmp.text() + '</span> \
						</li>\
					' );
				} );

				$( 'div.copy-posts li' ).css( {'opacity': 0} ).animate({ 'opacity': 1 }, 'fast' );
				$( 'div.copy-posts .loading' ).fadeOut( 'fast' );

				if ( $( '.search-posts input' ).val() == '' )
					$( 'ul#s-posts' ).slideDown('fast');
			}, 'json' );
		}, 600 );
	}

	function copy_post( callback ) {
		var post_id = $( 'div.copy-posts li input.selected' ).attr( 'id' ).substr( 3, $( 'div.copy-posts li input.selected' ).attr('id').length );
		var isSwitchable = typeof switchEditors !== 'undefined';

		if( isSwitchable )
			switchEditors.go('content', 'html');

		$.post( ajaxurl, {
			'action': 'helper_get_post',
			'post_id': post_id,
			'nonce': WritingHelperBox.nonce
		}, function( post ) {

			$( 'div.copy-posts input' ).prop( 'disabled', false );
			$( 'div.copy-posts li' ).animate({ 'opacity': 1 }, 'fast' );
			$( 'div.confirm-copy' ).slideUp( 'fast', function() {
				$( '.confirm-copy p.copying' ).hide();
				$( '.confirm-copy p.confirm' ).show();
			} );
			$( 'div.copy-posts li input.selected' ).removeClass('selected');

			// Title
			$( 'input#title' ).val( post.post_title );
			$( '#titlewrap label' ).hide();

			// Content
			$( 'textarea#content' ).val( post.post_content );
			if( isSwitchable )
				switchEditors.go('content', 'tinymce');

			// Tags
			$( 'div.taghint' ).hide();
			$( 'input#new-tag-post_tag' ).val( post.post_tags );

			// Categories
			$.each( $( 'ul#categorychecklist input[type=checkbox]' ), function() {
				$(this).prop( 'checked', false );
			} );
			$.each( post.post_categories, function( i, cat ) {
				if ( cat.cat_ID != '' )
					$( 'input#in-category-' + cat.cat_ID ).prop( 'checked', true );
			});
		}, 'json' );

		// Add the post to the list of recently copied posts to stick at the top.
		$.post( ajaxurl, {
			'action': 'helper_stick_post',
			'post_id': post_id,
			'nonce': WritingHelperBox.nonce
		}, function( result ) {});
	}
} );

/**
 * Copyright (c) 2007-2013 Ariel Flesler - aflesler<a>gmail<d>com | http://flesler.blogspot.com
 * Dual licensed under MIT and GPL.
 * @author Ariel Flesler
 * @version 1.4.5
 */
;(function($){var h=$.scrollTo=function(a,b,c){$(window).scrollTo(a,b,c)};h.defaults={axis:'xy',duration:parseFloat($.fn.jquery)>=1.3?0:1,limit:true};h.window=function(a){return $(window)._scrollable()};$.fn._scrollable=function(){return this.map(function(){var a=this,isWin=!a.nodeName||$.inArray(a.nodeName.toLowerCase(),['iframe','#document','html','body'])!=-1;if(!isWin)return a;var b=(a.contentWindow||a).document||a.ownerDocument||a;return/webkit/i.test(navigator.userAgent)||b.compatMode=='BackCompat'?b.body:b.documentElement})};$.fn.scrollTo=function(e,f,g){if(typeof f=='object'){g=f;f=0}if(typeof g=='function')g={onAfter:g};if(e=='max')e=9e9;g=$.extend({},h.defaults,g);f=f||g.duration;g.queue=g.queue&&g.axis.length>1;if(g.queue)f/=2;g.offset=both(g.offset);g.over=both(g.over);return this._scrollable().each(function(){if(e==null)return;var d=this,$elem=$(d),targ=e,toff,attr={},win=$elem.is('html,body');switch(typeof targ){case'number':case'string':if(/^([+-]=?)?\d+(\.\d+)?(px|%)?$/.test(targ)){targ=both(targ);break}targ=$(targ,this);if(!targ.length)return;case'object':if(targ.is||targ.style)toff=(targ=$(targ)).offset()}$.each(g.axis.split(''),function(i,a){var b=a=='x'?'Left':'Top',pos=b.toLowerCase(),key='scroll'+b,old=d[key],max=h.max(d,a);if(toff){attr[key]=toff[pos]+(win?0:old-$elem.offset()[pos]);if(g.margin){attr[key]-=parseInt(targ.css('margin'+b))||0;attr[key]-=parseInt(targ.css('border'+b+'Width'))||0}attr[key]+=g.offset[pos]||0;if(g.over[pos])attr[key]+=targ[a=='x'?'width':'height']()*g.over[pos]}else{var c=targ[pos];attr[key]=c.slice&&c.slice(-1)=='%'?parseFloat(c)/100*max:c}if(g.limit&&/^\d+$/.test(attr[key]))attr[key]=attr[key]<=0?0:Math.min(attr[key],max);if(!i&&g.queue){if(old!=attr[key])animate(g.onAfterFirst);delete attr[key]}});animate(g.onAfter);function animate(a){$elem.animate(attr,f,g.easing,a&&function(){a.call(this,e,g)})}}).end()};h.max=function(a,b){var c=b=='x'?'Width':'Height',scroll='scroll'+c;if(!$(a).is('html,body'))return a[scroll]-$(a)[c.toLowerCase()]();var d='client'+c,html=a.ownerDocument.documentElement,body=a.ownerDocument.body;return Math.max(html[scroll],body[scroll])-Math.min(html[d],body[d])};function both(a){return typeof a=='object'?a:{top:a,left:a}}})(jQuery);
