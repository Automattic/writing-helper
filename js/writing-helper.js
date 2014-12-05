jQuery(function($) {
	var $requestfeedback = $( '#requestfeedback' ),
		$textarea_custom = $( 'textarea.customize', $requestfeedback ),
		$textarea_invite = $( '#invitelist', $requestfeedback ),
		$link_cancel = $( 'a.cancel', $requestfeedback ),
		$link_customize = $( 'a.customize', $requestfeedback ),
		$section_modify = $( '#modify-email', $requestfeedback ),
		$section_sent = $( '#add-request-sent', $requestfeedback ),
		$section_invite = $( '#invitetoshare', $requestfeedback ),
		$block_meta_box = $( '#writing_helper_meta_box' ),
		$block_helpers = $( '#helpers' ),
		$button_add = $( '#add-request', $requestfeedback),
		$button_add_custom = $( '#add-request-custom', $requestfeedback),
		default_email_text = $textarea_custom.val(),
		$post_content = $( '#content' );

	$.fn.replace_placeholders = function() {
		return this.each(function() {
			var $this = $(this);
			if ($this.data('replaced-placeholders')) return;
			var text = $this.val();
			var excerpt = $post_content.text();
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
	var publish_new_requests = function( data ) {
		var $first_row, background_color;
		$( '#requests-list' ).replaceWith( data.response );

		// Getting the newly inserted requests list's first row
		$first_row = $( '#requests-list tr:first' );
		background_color = $first_row.css('background-color');

		// Highlighting the first row
		$first_row.animate({ 'background-color': '#78dcfa' }).promise().done(function() {
			$first_row.animate({ 'background-color': background_color });
		});
	};
	$button_add.add( $button_add_custom ).click(function() {
		$textarea_custom.replace_placeholders();
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'request_feedback',
				emails: $textarea_invite.val(),
				email_text: $textarea_custom.val(),
				nonce: WritingHelperBox.nonce,
				post_id: $('#post_ID').val()
			},
			dataType: 'json',
			success: function(data, status, xhr) {
				if (data['error'])
					display_error( $section_invite, data['error']);
				else {
					publish_new_requests( data );
					$textarea_invite.val( '' ).triggerHandler( 'keyup' );
					$link_cancel.triggerHandler( 'click' );
					$section_invite.hide();
					$section_sent.show();
				}
			},
			error: function(xhr, status, error) {
				display_error(
					WritingHelperBox.i18n.error_message.replace( '{error}', error )
				);
			}
		});
		return false;
	});
	$link_customize.click(function() {
		$textarea_custom.replace_placeholders().data('replaced-placeholders', false);
		$section_modify.show();
		$button_add.hide();
		$(this).hide();
		return false;
	});
	$link_cancel.click(function() {
		$textarea_custom
			.val(default_email_text)
			.data('replaced-placeholders', false);
		$section_modify.hide();
		$button_add.show();
		$link_customize.show();
		$( '.first-focus', $requestfeedback ).focus();
		return false;
	});
	$textarea_invite.keyup(function() {
		var i, parts,
			emails = $(this).val(),
			to = $link_customize;

		emails = emails.replace(/^\s+/, '').replace(/\s+$/, '');
		parts = emails.split(/\s*[,\n]\s*/);
		for( i = 0; i < parts.length; ++i ) {
			if (!parts[i]) parts.splice(i, 1);
		}
		if (0 == parts.length || !emails) {
			to.html( WritingHelperBox.i18n.customize_message );
		} else if (1 == parts.length) {
			to.text( WritingHelperBox.i18n.customize_message_single.replace( '{whom}', parts[0] ) );
		} else {
			to.text(
				WritingHelperBox.i18n.customize_message_multiple
						.replace('{whom}', parts[0])
						.replace('{number}', parts.length - 1)
			);
		}
	});

	// Making the link get selected automatically on click
	$requestfeedback.on( 'click', 'input.link', function() {
		this.select();
	});

	// Reverting changes to the link field
	$requestfeedback.on( 'change', 'input.link', function() {
		$(this).val( this.defaultValue );
	});

	$('ol.feedbacks-list li a', $requestfeedback).click(function() {
		$(this).parents('li').children('.full,.truncated').toggle();
		return false;
	});

	/* Get a link without sending an email */
	$( '#df-share-link' ).on( 'click', function ( event ) {
		var elements = $( '#df-share-link,#df-getting-link' );
		event.preventDefault();

		elements.toggle();
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'get_draft_link',
				nonce: WritingHelperBox.nonce,
				post_id: $( this ).data('post-id')
			},
			dataType: 'json',
			success: function(data) {
				if (!data['error']) {
					publish_new_requests( data );
				}
			},
			complete: function(){
				elements.toggle();
			}
		});
	});

	/* JS to hide/show helper boxes */
	$block_helpers.on( 'click', 'li', function(e) {
		var helper_container;
		e.preventDefault();

		$block_helpers.hide();
		helper_container = $( $( 'a', this).attr( 'href' ) ).show();
		helper_container.find( '.first-focus' ).focus();
		// ping stats
		var helper_name = $( 'a', this).attr('href').substr(1); // remove the #

		if ( WritingHelperBox.tracking_image ) {
			new Image().src = WritingHelperBox
				.tracking_image
						.replace( '{helper_name}', helper_name )
						.replace( '{random}', Math.random() );
		}
	});
	$block_meta_box.on( 'click', '.back', function( event ) {
		event.preventDefault();
		$block_helpers.show();
		$('.helper').hide();
	});
	$block_meta_box.on( 'click', '.back, #add-request-sent a', function( event ) {
		event.preventDefault();
		$section_invite.show().find( '.first-focus' ).focus();
		$section_sent.hide();
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

jQuery( function( $ ) {
	var
		$container_helper = $( '#copyapost' ),
		$block_posts = $( '.copy-posts', $container_helper ),
		$block_confirm = $( '.confirm-copy', $container_helper ),
		$block_search = $( '.search-posts', $container_helper ),
		$input_search = $( 'input', $block_search ),
		post_search_timeout = null;

	$( 'ul', $block_posts ).on( 'click', 'input[type=button]', function() {
		$( this ).addClass( 'selected' );
		$( 'input', $block_posts ).prop( 'disabled', true );
		$( 'li', $block_posts ).not( $(this).parent('li') ).animate({ 'opacity': 0.3 }, 'fast' );

		$block_confirm.slideDown( 'fast' );

		if ( $( 'input#title' ).val() == '' ) {
			$( 'p.copying', $block_confirm ).show();
			$( 'p.confirm', $block_confirm ).hide();
			copy_post();
		}
	});

	$block_confirm.on( 'click', 'input#cancel-copy', function() {
		$( 'li input.selected', $block_posts ).removeClass( 'selected' );
		$( 'input', $block_posts ).prop( 'disabled', false );
		$( 'li', $block_posts ).animate({ 'opacity': 1 }, 'fast' );
		$block_confirm.slideUp( 'fast' );
	});

	$block_confirm.on( 'click', 'input#confirm-copy', function() {
		$( 'p.confirm', $block_confirm ).fadeOut( 'fast', function() {
			$( 'p.copying', $block_confirm ).fadeIn( 'fast' );
		});
		copy_post();
	});

	$input_search.unbind( 'keyup' ).bind( 'keyup', function() {
		search_posts( $(this) );
	});

	$block_search.on( 'click', 'input', function(e) {
		var offset = $(this).offset();

		if (e.pageX > offset.left + $(this).width() - 16 )
			search_posts( $(this) );
	});

	// Disable the enter key in the search posts box so ppl don't publish posts by mistake.
	$(window).on( "keydown", function(e) {
		if (e.keyCode == 13 && $input_search.is(':focus') ) return false;
	});

	function search_posts(el) {
		$block_posts.scrollTo( 0, 'fast' );

		if ( $input_search.val() != '' )
			$( 'ul#s-posts' ).slideUp('fast');

		$( 'li', $block_posts ).not( el.parent('li') ).animate({ 'opacity': 0.3 }, 'fast' );
		$( '.loading', $block_posts ).fadeIn( 'fast' );

		clearTimeout( post_search_timeout );
		post_search_timeout = setTimeout( function() {
			$.post( ajaxurl, {
				'action': 'helper_search_posts',
				'search': $input_search.val(),
				'post_type': typenow,
				'nonce': WritingHelperBox.nonce
			}, function( posts ) {
				var $l_posts = $( '#l-posts', $block_posts );
				$l_posts.find( 'li' ).remove();

				$.each( posts, function( i, post ) {
					var excerpt, title;

					// Strip tags: Doesn't have to be perfect. Just has to be not terrible.
					title = post.post_title.replace( /<[^>]*>/g, '' );
					excerpt = post.post_content.substr( 0, 400 ).replace( /<[^>]*>/g, '' ).substr( 0, 200 )

					var $li = $( '<li />' ).
						append( $( '<input type="button" value="Copy" class="button-secondary" />' ).attr( 'id', 'cp-' + post.ID ) ).
						append( ' &nbsp;' ).
						append( $( '<span class="title">' ).text( title ) ).
						append( $( '<span class="excerpt">' ).text( excerpt ) );

					$l_posts.append( $li );
				} );

				$( 'li', $block_posts ).css( {'opacity': 0} ).animate({ 'opacity': 1 }, 'fast' );
				$( '.loading', $block_posts ).fadeOut( 'fast' );

				if ( $input_search.val() == '' )
					$( '#s-posts' ).slideDown('fast');
			}, 'json' );
		}, 600 );
	}

	function copy_post( callback ) {
		var post_id = $( 'li input.selected', $block_posts )
				.attr( 'id' )
				.substr(3, $( 'div.copy-posts li input.selected' ).attr('id').length ),
			isSwitchable = typeof switchEditors !== 'undefined';

		if( isSwitchable )
			switchEditors.go('content', 'html');

		$.post( ajaxurl, {
			'action': 'helper_get_post',
			'post_id': post_id,
			'nonce': WritingHelperBox.nonce
		}, function( post ) {

			$( 'input', $block_posts ).prop( 'disabled', false );
			$( 'li', $block_posts ).animate({ 'opacity': 1 }, 'fast' );
			$block_confirm.slideUp( 'fast', function() {
				$( 'p.copying', $block_confirm ).hide();
				$( 'p.confirm', $block_confirm ).show();
			} );
			$( 'li input.selected', $block_posts ).removeClass('selected');

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
