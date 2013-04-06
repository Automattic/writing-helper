jQuery(function($) {
	var $requestfeedback = $('#requestfeedback');
	var default_email_text = $('textarea.customize', $requestfeedback).val();
	$.fn.replace_placeholders = function() {
		return this.each(function() {
			var $this = $(this);
			if ($this.data('replaced-placeholders')) return;
			var text = $this.val();
			var excerpt = $('#content').val();
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
			to.html('Customize the message to {whom}'.replace('{whom}', parts[0]));
		} else {
			to.html('Customize the message to {whom} and {number} more'.replace('{whom}', parts[0]).replace('{number}', parts.length - 1));
		}
	});
	$('ol.feedbacks-list li a', $requestfeedback).click(function() {
		$(this).parents('li').children('.full,.truncated').toggle();
		return false;
	});
	/* JS for the new feedback */
	$('#feedbackform').submit(function() {
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
			success: function(data, status, xhr) {
				if (data['error'])
					display_error('#feedback-text', data['error']);
				else {
					$('#draftfeedback-intro').hide();
					$('#feedback-text').val('');
					$('#draftfeedback-thanks').show();
				}
				$('#feedback-text').focus();
			},
			error: function(xhr, status, error) {
				display_error("Internal Server Error: "+status);
			}
		});
		return false;
	});
	$('#feedback-text').focus();
});

/* JS to hide/show helper boxes */
jQuery(document).ready(function($) {
	$( '#helpers li' ).click(function() {

		$('#helpers').hide();
		$( $( 'a', this).attr('href') ).show();
		// ping stats
		var helper_name = $( 'a', this).attr('href').substr(1); // remove the #
		new Image().src = document.location.protocol+'//stats.wordpress.com/g.gif?v=wpcom-no-pv&x_writinghelper='+helper_name+'&baba='+Math.random();

		return false;
	});
	$('.back').click(function(e) {
		e.preventDefault();
		$('#helpers').show();
		$('.helper').hide();
	});
	$('#add-request-sent a, .back').click( function() {
			$('#invitetoshare').show();
			$('#add-request-sent').hide();
			return false;
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

	$( 'div.copy-posts li input[type=button]' ).live( 'click', function() {
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

	$( 'input#cancel-copy' ).bind( 'click', function() {
		$( 'div.copy-posts li input.selected' ).removeClass( 'selected' );
		$( 'div.copy-posts input' ).prop( 'disabled', false );
		$( 'div.copy-posts li' ).animate({ 'opacity': 1 }, 'fast' );
		$( 'div.confirm-copy' ).slideUp( 'fast' );
	});

	$( 'input#confirm-copy' ).bind( 'click', function() {
		$( '.confirm-copy p.confirm' ).fadeOut( 'fast', function() { $( '.confirm-copy p.copying' ).fadeIn( 'fast' ); });
		copy_post();
	});

	$( '.search-posts input' ).unbind( 'keyup' ).bind( 'keyup', function() {
		search_posts( $(this) );
	});

	$( '.search-posts input' ).bind( 'click', function(e) {
  		var offset = $(this).offset();

  		if (e.pageX > offset.left + $(this).width() - 16 )
 			search_posts( $(this) );
	});

	// Disable the enter key in the search posts box so ppl don't publish posts by mistake.
	$(window).bind( "keydown", function(e) {
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
		switchEditors.go('content', 'html');
		var post_id = $( 'div.copy-posts li input.selected' ).attr( 'id' ).substr( 3, $( 'div.copy-posts li input.selected' ).attr('id').length );

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

// ScrollTo Plugin 1.4.2 | Copyright (c) 2007-2009 Ariel Flesler | GPL/MIT License
;(function(d){var k=d.scrollTo=function(a,i,e){d(window).scrollTo(a,i,e)};k.defaults={axis:'xy',duration:parseFloat(d.fn.jquery)>=1.3?0:1};k.window=function(a){return d(window)._scrollable()};d.fn._scrollable=function(){return this.map(function(){var a=this,i=!a.nodeName||d.inArray(a.nodeName.toLowerCase(),['iframe','#document','html','body'])!=-1;if(!i)return a;var e=(a.contentWindow||a).document||a.ownerDocument||a;return d.browser.safari||e.compatMode=='BackCompat'?e.body:e.documentElement})};d.fn.scrollTo=function(n,j,b){if(typeof j=='object'){b=j;j=0}if(typeof b=='function')b={onAfter:b};if(n=='max')n=9e9;b=d.extend({},k.defaults,b);j=j||b.speed||b.duration;b.queue=b.queue&&b.axis.length>1;if(b.queue)j/=2;b.offset=p(b.offset);b.over=p(b.over);return this._scrollable().each(function(){var q=this,r=d(q),f=n,s,g={},u=r.is('html,body');switch(typeof f){case'number':case'string':if(/^([+-]=)?\d+(\.\d+)?(px|%)?$/.test(f)){f=p(f);break}f=d(f,this);case'object':if(f.is||f.style)s=(f=d(f)).offset()}d.each(b.axis.split(''),function(a,i){var e=i=='x'?'Left':'Top',h=e.toLowerCase(),c='scroll'+e,l=q[c],m=k.max(q,i);if(s){g[c]=s[h]+(u?0:l-r.offset()[h]);if(b.margin){g[c]-=parseInt(f.css('margin'+e))||0;g[c]-=parseInt(f.css('border'+e+'Width'))||0}g[c]+=b.offset[h]||0;if(b.over[h])g[c]+=f[i=='x'?'width':'height']()*b.over[h]}else{var o=f[h];g[c]=o.slice&&o.slice(-1)=='%'?parseFloat(o)/100*m:o}if(/^\d+$/.test(g[c]))g[c]=g[c]<=0?0:Math.min(g[c],m);if(!a&&b.queue){if(l!=g[c])t(b.onAfterFirst);delete g[c]}});t(b.onAfter);function t(a){r.animate(g,j,b.easing,a&&function(){a.call(this,n,b)})}}).end()};k.max=function(a,i){var e=i=='x'?'Width':'Height',h='scroll'+e;if(!d(a).is('html,body'))return a[h]-d(a)[e.toLowerCase()]();var c='client'+e,l=a.ownerDocument.documentElement,m=a.ownerDocument.body;return Math.max(l[h],m[h])-Math.min(l[c],m[c])};function p(a){return typeof a=='object'?a:{top:a,left:a}}})(jQuery);
