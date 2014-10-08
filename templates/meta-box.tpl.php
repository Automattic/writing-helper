<?php

$post_type = get_post_type();
$post_type_obj = get_post_type_object( $post_type );

$cap_strings = array();

switch ( $post_type ) {
	case 'post':
		$cap_strings['title'] = __( 'Copy a Post', 'writing-helper' );
		$cap_strings['details'] = __(
			'Use an existing post as a template.', 'writing-helper'
		);
		$cap_strings['instructions'] = __(
			"Pick a post and we'll copy the title, content, tags and categories. Recent posts are listed below. Search by title to find older posts. You can mark any post to keep it at the top.",
			'writing-helper'
		);
		$cap_strings['search'] = __(
			'Search for a post by title', 'writing-helper'
		);
		$cap_strings['confirm'] = __(
			'Replace the current post with the selected post?', 'writing-helper'
		);
		$cap_strings['copying'] = __( 'Copying post...', 'writing-helper' );
		break;
	case 'page':
		$cap_strings['title'] = __( 'Copy a Page', 'writing-helper' );
		$cap_strings['details'] = __(
			'Use an existing page as a template.', 'writing-helper'
		);
		$cap_strings['instructions'] = __(
			"Pick a post and we'll copy the title and content. Recent pages are listed below. Search by title to find older pages. You can mark any page to keep it at the top.",
			'writing-helper'
		);
		$cap_strings['search'] = __(
			'Search for a page by title', 'writing-helper'
		);
		$cap_strings['confirm'] = __(
			'Replace the current page with the selected page?', 'writing-helper'
		);
		$cap_strings['copying'] = __( 'Copying page...', 'writing-helper' );
		break;
	default:
		$cap_strings['title'] = sprintf(
			_x( 'Copy a %s', 'Copy a {post_type}', 'writing-helper' ),
			$post_type_obj->labels->singular_name
		);
		$cap_strings['details'] = sprintf(
			_x(
				'Use an existing %s as a template.',
				'Use an existing {post_type} as a template',
				'writing-helper'
			),
			strtolower( $post_type_obj->labels->singular_name )
		);
		$cap_strings['instructions'] = sprintf(
			_x(
				'Pick a %1$s and we&#8217;ll copy the title and content. Recent %2$s are listed below. Search by title to find older %3$s. You can mark any %4$s to keep it at the top.',
				'Copy a {post_type}. First and fourth variables are single, second and third are plurals',
				'writing-helper'
			),
			strtolower( $post_type_obj->labels->singular_name ),
			strtolower( $post_type_obj->labels->name ),
			strtolower( $post_type_obj->labels->name ),
			strtolower( $post_type_obj->labels->singular_name )
		);
		$cap_strings['search'] = sprintf(
			_x(
				'Search for %s by title',
				'Search for {post_type} by title',
				'writing-helper'
			),
			strtolower( $post_type_obj->labels->name )
		);
		$cap_strings['confirm'] = sprintf(
			_x(
				'Replace the current %s with the selected %s?',
				'Replace the current {post_type} with the selected {post_type}',
				'writing-helper'
			),
			strtolower( $post_type_obj->labels->singular_name ),
			strtolower( $post_type_obj->labels->singular_name )
		);
		$cap_strings['copying'] = sprintf(
			_x( 'Copying %s...', 'copying {post_type}', 'writing-helper' ),
			$post_type_obj->labels->singular_name
		);
}

if ( $parameters['show_helper_selector'] ) {
	require( dirname( __FILE__ ) . '/partials/feedback-form-helpers.tpl.php' );
}

if ( $parameters['show_feedback_block'] ) {
	require( dirname( __FILE__ ) . '/partials/feedback-form-feedback.tpl.php' );
}

if ( $parameters['show_copy_block'] ) {
	require( dirname( __FILE__ ) . '/partials/feedback-form-copy.tpl.php' );
}
?>

<div class="clear"></div>
