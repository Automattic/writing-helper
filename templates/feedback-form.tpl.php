<?php
/**
 * Template file for the Draft Feedback Form
 */
?>

<div class="draftfeedback-container draftfeedback-feedback-pulldown" id="feedback-floater">
	<h3><?php the_author(); ?> <?php _e( 'would like your feedback.', 'writing-helper' ); ?></h3>
	<input
			type="button"
			class="button"
			id="draftfeedback-activate"
			value="<?php _e( 'I am ready!', 'writing-helper' ); ?>" />
</div>

<div
		class="draftfeedback-container
			draftfeedback-feedback-form
			<?php if ( is_admin_bar_showing() ) : ?>draftfeedback-admin-bar<?php endif; ?>">
	<div class="draftfeedback-thanks draftfeedback-second-screen" id="draftfeedback-thanks">
		<h3><?php _e( 'Thank you for your feedback!', 'writing-helper' ); ?></h3>
		<p>
			<?php _e( "Feel free to close this page and we'll email you when the draft is published for everyone to see. If you want to send anything else, press the button below.", 'writing-helper' ); ?>
		</p>
		<input
				type="button"
				class="button button-primary"
				id="feedback-more"
				value="<?php _e( 'Send More Feedback', 'writing-helper' ); ?>" />
		<input
				type="button"
				class="button draftfeedback-return"
				value="<?php _e( 'Back to post', 'writing-helper' ); ?>" />
	</div>
	<div class="draftfeedback-intro draftfeedback-first-screen" id="draftfeedback-intro">
		<h3><?php the_author(); ?> <?php _e( 'would like your feedback.', 'writing-helper' ); ?></h3>

		<p>
			<?php _e( 'This is a private, unpublished draft. Please review it and leave your feedback in the box below.', 'writing-helper' ); ?>
		</p>

		<p><?php _e( 'Note any typos you find, suggestions you have, or links to recommend.', 'writing-helper' ); ?></p>
	</div>
	<form class="draftfeedback-first-screen" id="feedbackform" method="post">
		<textarea name="feedback" rows="4" id="feedback-text"></textarea>
		<input
				type="submit"
				class="button button-primary"
				name="Send Feedback"
				value="<?php _e( 'Send Feedback', 'writing-helper' ); ?>" />
		<input
				type="button"
				class="button draftfeedback-return"
				value="<?php _e( 'Back to post', 'writing-helper' ); ?>" />
	</form>
</div>
