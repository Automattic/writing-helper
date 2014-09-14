<?php
/**
 * Template file for the Draft Feedback Form
 *
 * @todo style this (and such)
 */
?>
<style>
<?php require( dirname(__FILE__) . '/../css/feedback-form.css' ); ?>
</style>

<script type="text/javascript">
<?php require( dirname(__FILE__) . '/../js/feedback-form.js' ); ?>
</script>

<div class="draftfeedback-container draftfeedback-feedback-pulldown">
	<h6><?php the_author(); ?> <?php _e( 'would like your feedback.' ); ?></h6>
	<input type="button" id="draftfeedback-activate" value="<?php _e( 'I am ready!' ); ?>" />
</div>
<div class="draftfeedback-container draftfeedback-feedback-form">

	<div class="draftfeedback-thanks draftfeedback-second-screen" id="draftfeedback-thanks">
		<h3><?php _e( 'Thank you for your feedback!' ); ?></h3>
		<p><?php _e( "Feel free to close this page and we'll email you when the draft is published for everyone to see. If you want to send anything else, press the button below." ); ?></p>
		<input type="button" id="feedback-more"  value="<?php _e( 'Send More Feedback' ); ?>" />
		<input type="button" class="draftfeedback-deactivate" value="<?php _e( 'Back to post' ); ?>" />
	</div>
	<div class="draftfeedback-intro draftfeedback-first-screen" id="draftfeedback-intro">
		<h3><?php the_author(); ?> <?php _e( 'would like your feedback.' ); ?></h3>

		<p><?php _e( 'This is a private, unpublished draft. Please review it and leave your feedback in the box below.' ); ?></p>

		<p><?php _e( 'Note any typos you find, suggestions you have, or links to recommend.' ); ?></p>
	</div>
	<form class="draftfeedback-first-screen" id="feedbackform" method="post">
		<textarea name="feedback" rows="4" id="feedback-text"></textarea>
		<input type="submit" name="Send Feedback" value="<?php _e( 'Send Feedback' ); ?>" />
		<input type="button" class="draftfeedback-deactivate" value="<?php _e( 'Back to post' ); ?>" />
	</form>
</div>