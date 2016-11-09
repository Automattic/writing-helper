jQuery(function( $ ) {
	var $requestfeedback = $( '#requestfeedback' ),
		$textareaCustom = $( 'textarea.customize', $requestfeedback ),
		$textareaInvite = $( '#invitelist', $requestfeedback ),
		$linkCancel = $( 'a.cancel', $requestfeedback ),
		$linkCustomize = $( 'a.customize', $requestfeedback ),
		$sectionModify = $( '#modify-email', $requestfeedback ),
		$sectionSent = $( '#add-request-sent', $requestfeedback ),
		$sectionInvite = $( '#invitetoshare', $requestfeedback ),
		$blockMetaBox = $( '#writing_helper_meta_box' ),
		$blockHelpers = $( '#helpers' ),
		$buttonAdd = $( '#add-request', $requestfeedback ),
		$buttonAddCustom = $( '#add-request-custom', $requestfeedback ),
		defaultEmailText = $textareaCustom.val(),
		$postContent = $( '#content' ),
		displayError,
		publishNewRequests;

	$.fn.replace_placeholders = function() {
		return this.each(function() {
			var $this = $( this );
			var text, excerpt;

			if ( $this.data( 'replaced-placeholders' ) ) {
				return;
			}
			text = $this.val();
			excerpt = $postContent.text();
			excerpt = $( '<div>' + excerpt + '</div>' ).text().replace( /\n+/g, ' ' );
			if ( excerpt.length > 300 ) {
				excerpt = excerpt.substr( 0, 300 ) + '...';
			}
			text = text.replace( /\[title\]/g, $( '#title' ).val() );
			text = text.replace( /\[excerpt\]/g, excerpt );
			$this.val( text );
			$this.data( 'replaced-placeholders', true );
		});
	};

	displayError = function( id, notice ) {
		$( id ).after( '<div id="draft-error" class="error"><p>' + notice + '</p></div>' );
		$( '#draft-error' ).delay( 4000 ).fadeOut( 'slow' );
	};

	publishNewRequests = function( data ) {
		var $firstRow, backgroundColor;
		$( '#requests-list' ).replaceWith( data.response );

		// Getting the newly inserted requests list's first row
		$firstRow = $( '#requests-list tr:first' );
		backgroundColor = $firstRow.css( 'background-color' );

		// Highlighting the first row
		$firstRow.animate({ 'background-color': '#78dcfa' }).promise().done(function() {
			$firstRow.animate({ 'background-color': backgroundColor });
		});
	};

	$buttonAdd.add( $buttonAddCustom ).click(function() {
		$textareaCustom.replace_placeholders();
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'request_feedback',
				emails: $textareaInvite.val(),
				email_text: $textareaCustom.val(),
				nonce: WritingHelperBox.post_nonce,
				post_id: $( '#post_ID' ).val()
			},
			dataType: 'json',
			success: function( data, status, xhr ) {
				if ( data.error ) {
					displayError( $sectionInvite, data.error );
				} else {
					publishNewRequests( data );
					$textareaInvite.val( '' ).triggerHandler( 'keyup' );
					$linkCancel.triggerHandler( 'click' );
					$sectionInvite.hide();
					$sectionSent.show();
				}
			},
			error: function( xhr, status, error ) {
				displayError(
					WritingHelperBox.i18n.error_message.replace( '{error}', error )
				);
			}
		});
		return false;
	});
	$linkCustomize.click(function() {
		$textareaCustom.replace_placeholders().data( 'replaced-placeholders', false );
		$sectionModify.show();
		$buttonAdd.hide();
		$( this ).hide();
		return false;
	});
	$linkCancel.click(function() {
		$textareaCustom
			.val( defaultEmailText )
			.data( 'replaced-placeholders', false );
		$sectionModify.hide();
		$buttonAdd.show();
		$linkCustomize.show();
		$( '.first-focus', $requestfeedback ).focus();
		return false;
	});
	$textareaInvite.keyup(function() {
		var i, parts,
			emails = $( this ).val(),
			to = $linkCustomize;

		emails = emails.replace( /^\s+/, '' ).replace( /\s+$/, '' );
		parts = emails.split( /\s*[,\n]\s*/ );
		for ( i = 0; i < parts.length; ++i ) {
			if ( ! parts[i] ) {
				parts.splice( i, 1 );
			}
		}
		if ( 0 == parts.length || ! emails ) {
			to.html( WritingHelperBox.i18n.customize_message );
		} else if ( 1 == parts.length ) {
			to.text( WritingHelperBox.i18n.customize_message_single.replace( '{whom}', parts[0] ) );
		} else {
			to.text(
				WritingHelperBox.i18n.customize_message_multiple
						.replace( '{whom}', parts[0] )
						.replace( '{number}', parts.length - 1 )
			);
		}
	});

	// Making the link get selected automatically on click
	$requestfeedback.on( 'click', 'input.link', function() {
		this.select();
	});

	// Reverting changes to the link field
	$requestfeedback.on( 'change', 'input.link', function() {
		$( this ).val( this.defaultValue );
	});

	$( 'ol.feedbacks-list li a', $requestfeedback ).click(function() {
		$( this ).parents( 'li' ).children( '.full,.truncated' ).toggle();
		return false;
	});

	/* Get a link without sending an email */
	$( '#df-share-link' ).on( 'click', function( event ) {
		var elements = $( '#df-share-link,#df-getting-link' );
		event.preventDefault();

		elements.toggle();
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'get_draft_link',
				nonce: WritingHelperBox.post_nonce,
				post_id: $( this ).data( 'post-id' )
			},
			dataType: 'json',
			success: function( data ) {
				if ( ! data.error ) {
					publishNewRequests( data );
				}
			},
			complete: function() {
				elements.toggle();
			}
		});
	});

	/* JS to hide/show helper boxes */
	$blockHelpers.on( 'click', 'li', function( e ) {
		var helperContainer, helperName;

		e.preventDefault();

		$blockHelpers.hide();
		helperContainer = $( $( 'a', this ).attr( 'href' ) ).show();
		helperContainer.find( '.first-focus' ).focus();

		// Ping stats
		helperName = $( 'a', this ).attr( 'href' ).substr( 1 ); // Remove the #

		if ( WritingHelperBox.tracking_image ) {
			new Image().src = WritingHelperBox
				.tracking_image
						.replace( '{helperName}', helperName )
						.replace( '{random}', Math.random() );
		}
	});
	$blockMetaBox.on( 'click', '.back', function( event ) {
		event.preventDefault();
		$blockHelpers.show();
		$( '.helper' ).hide();
	});
	$blockMetaBox.on( 'click', '.back, #add-request-sent a', function( event ) {
		event.preventDefault();
		$sectionInvite.show().find( '.first-focus' ).focus();
		$sectionSent.hide();
	});
});

/* Toggle Revoke/Grant Access */
function DraftRevokeAccess( $, postId, email, linkId ) {
	$.ajax({
		type: 'POST',
		url: ajaxurl,
		data: {
			action: 'revoke_draft_access',
			email: email,
			nonce: WritingHelperBox.post_nonce,
			post_id: postId
		},
		dataType: 'json',
		success: function( data, status, xhr ) {
			var $link;
			if ( ! data.error ) {
				$link = $( linkId );
				$( '.revoke,.unrevoke', $link ).toggle();
			}
		},
		error: function() {
		}
	});
}

jQuery( function( $ ) {
	var $containerHelper = $( '#copyapost' ),
		$blockPosts = $( '.copy-posts', $containerHelper ),
		$blockConfirm = $( '.confirm-copy', $containerHelper ),
		$blockSearch = $( '.search-posts', $containerHelper ),
		$inputSearch = $( 'input', $blockSearch ),
		postSearchTimeout = null;

	$( 'ul', $blockPosts ).on( 'click', 'input[type=button]', function() {
		$( this ).addClass( 'selected' );
		$( 'input', $blockPosts ).prop( 'disabled', true );
		$( 'li', $blockPosts ).not( $( this ).parent( 'li' ) ).animate({ 'opacity': 0.3 }, 'fast' );

		$blockConfirm.slideDown( 'fast' );

		if ( '' === $( 'input#title' ).val() ) {
			$( 'p.copying', $blockConfirm ).show();
			$( 'p.confirm', $blockConfirm ).hide();
			copyPost();
		}
	});

	$blockConfirm.on( 'click', 'input#cancel-copy', function() {
		$( 'li input.selected', $blockPosts ).removeClass( 'selected' );
		$( 'input', $blockPosts ).prop( 'disabled', false );
		$( 'li', $blockPosts ).animate({ 'opacity': 1 }, 'fast' );
		$blockConfirm.slideUp( 'fast' );
	});

	$blockConfirm.on( 'click', 'input#confirm-copy', function() {
		$( 'p.confirm', $blockConfirm ).fadeOut( 'fast', function() {
			$( 'p.copying', $blockConfirm ).fadeIn( 'fast' );
		});
		copyPost();
	});

	$inputSearch.unbind( 'keyup' ).bind( 'keyup', function() {
		searchPosts( $( this ) );
	});

	$blockSearch.on( 'click', 'input', function( e ) {
		var offset = $( this ).offset();

		if ( e.pageX > offset.left + $( this ).width() - 16 ) {
			searchPosts( $( this ) );
		}
	});

	// Disable the enter key in the search posts box so ppl don't publish posts by mistake.
	$( window ).on( 'keydown', function( e ) {
		if ( 13 === e.keyCode && $inputSearch.is( ':focus' ) ) {
			return false;
		}
	});

	// Populating the posts search results
	searchPosts( $inputSearch, true );

	function searchPosts( el, immediately ) {
		var searchTerm;

		// If there is no search input, we assume that the whole form is missing and
		// don't do anything
		if ( ! $inputSearch.length ) {
			return;
		}

		searchTerm = $inputSearch.val();

		$blockPosts.scrollTo( 0, 'fast' );

		if ( searchTerm.trim() ) {
			$( 'ul#s-posts' ).slideUp( 'fast' );
		}

		$( 'li', $blockPosts ).not( el.parent( 'li' ) ).animate({ 'opacity': 0.3 }, 'fast' );
		$( '.loading', $blockPosts ).fadeIn( 'fast' );

		clearTimeout( postSearchTimeout );
		postSearchTimeout = setTimeout( function() {
			$.post( ajaxurl, {
				'action': 'helper_searchPosts',
				'search': $inputSearch.val(),
				'post_type': typenow,
				'nonce': WritingHelperBox.blog_nonce
			}, function( posts ) {
				var $lPosts = $( '#l-posts', $blockPosts );
				$lPosts.find( 'li' ).remove();

				$.each( posts, function( i, post ) {
					var excerpt, title, $li;

					// Strip tags: Doesn't have to be perfect. Just has to be not terrible.
					title = post.post_title.replace( /<[^>]*>/g, '' );
					excerpt = post.post_content.substr( 0, 400 ).replace( /<[^>]*>/g, '' ).substr( 0, 200 );

					$li = $( '<li />' ).
						append( $( '<input type="button" value="Copy" class="button-secondary" />' ).attr( 'id', 'cp-' + post.ID ) ).
						append( ' &nbsp;' ).
						append( $( '<span class="title">' ).text( title ) ).
						append( $( '<span class="excerpt">' ).text( excerpt ) );

					$lPosts.append( $li );
				} );

				$( 'li', $blockPosts ).css( { 'opacity': 0 } ).animate({ 'opacity': 1 }, 'fast' );
				$( '.loading', $blockPosts ).fadeOut( 'fast' );

				if ( '' === $inputSearch.val() ) {
					$( '#s-posts' ).slideDown( 'fast' );
				}
			}, 'json' );
		}, immediately ? 0 : 600 );
	}

	function copyPost( callback ) {
		var postId = $( 'li input.selected', $blockPosts )
				.attr( 'id' )
				.substr( 3, $( 'div.copy-posts li input.selected' ).attr( 'id' ).length ),
			isSwitchable = 'undefined' !== typeof switchEditors;

		if ( isSwitchable ) {
			switchEditors.go( 'content', 'html' );
		}

		$.post( ajaxurl, {
			'action': 'helper_get_post',
			'post_id': postId,
			'nonce': WritingHelperBox.blog_nonce
		}, function( post ) {

			$( 'input', $blockPosts ).prop( 'disabled', false );
			$( 'li', $blockPosts ).animate({ 'opacity': 1 }, 'fast' );
			$blockConfirm.slideUp( 'fast', function() {
				$( 'p.copying', $blockConfirm ).hide();
				$( 'p.confirm', $blockConfirm ).show();
			} );
			$( 'li input.selected', $blockPosts ).removeClass( 'selected' );

			// Title
			$( 'input#title' ).val( post.post_title );
			$( '#titlewrap label' ).hide();

			// Content
			$( 'textarea#content' ).val( post.post_content );
			if ( isSwitchable ) {
				switchEditors.go( 'content', 'tinymce' );
			}

			// Tags
			$( 'div.taghint' ).hide();
			$( 'input#new-tag-post_tag' ).val( post.post_tags );

			// Categories
			$.each( $( 'ul#categorychecklist input[type=checkbox]' ), function() {
				$( this ).prop( 'checked', false );
			} );
			$.each( post.post_categories, function( i, cat ) {
				if ( '' !== cat.cat_ID ) {
					$( 'input#in-category-' + cat.cat_ID ).prop( 'checked', true );
				}
			});
		}, 'json' );

		// Add the post to the list of recently copied posts to stick at the top.
		$.post( ajaxurl, {
			'action': 'helper_stick_post',
			'post_id': postId,
			'nonce': WritingHelperBox.blog_nonce
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
