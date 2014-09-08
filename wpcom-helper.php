<?php
/**
 * Stats tracking the WordPress.com usage of Writing Helper
 */
add_action( 'wh_copypost_searched_posts', function() {
	bump_stats_extras( 'copy_a_post', 'searched_posts' );
});
add_action( 'wh_copypost_copied_post', function() {
	bump_stats_extras( 'copy_a_post', 'copied_post' );
});
add_action( 'wh_copypost_ajax_stat', function( $stat ){
	if ( 'menu_click' == $stat )
		bump_stats_extras( 'copy_a_post', 'clicked_menu_link' );
});

add_action( 'wh_draftfeedback_existing_requests', function( $requests ){
	if ( empty( $requests ) )
		bump_stats_extras( 'feedbackrequest', 'post-request');
});
add_action( 'wh_draftfeedback_load_feedback_form', function() {
	bump_stats_extras( 'feedbackrequest', 'pageview' );
});
add_action( 'wh_draftfeedback_sent_request', function(){
	bump_stats_extras( 'feedbackrequest', 'request-sent' );
});
add_action( 'wh_draftfeedback_sent_feedback', function(){
	bump_stats_extras( 'feedbackrequest', 'feedback-received' );
});
add_action( 'wh_draftfeedback_generate_link', function(){
	bump_stats_extras( 'feedbackrequest', 'get-link');
});