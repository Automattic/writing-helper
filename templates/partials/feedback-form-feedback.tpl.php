<?php if ( $parameters['wrap_feedback_table'] ): ?>

	<div
			id="requestfeedback"
			class="helper"
			<?php if ( isset( $_GET['requestfeedback'] ) ): ?>
				style="display:block"
			<?php endif; ?> >
		<div class="helper-header">
			<a href="" class="back"><?php _e( 'Back', 'writing-helper' ) ?><span></span></a>
			<h5><?php _e( 'Request Feedback', 'writing-helper' ) ?></h5>
		</div>

		<div class="inside helper-content">

			<?php if ( $show_feedback_button ): ?>
				<div id="invitetoshare">
					<p>
						<strong>
							<?php _e( 'Get feedback on this draft before publishing', 'writing-helper' ); ?>
						</strong>
					</p>

					<p class="invitetext">
						<label for="invitelist">
							<?php _e( 'Enter email addresses of people you would like to get feedback from:', 'writing-helper' ) ?>
						</label>
					</p>
					<textarea
						id="invitelist"
						rows="2"
						placeholder="bob@example.org, sarah@example.org"
						class="first-focus"
					></textarea>

					<input type="submit" id="add-request" value="Send Requests" class="button-secondary" />
					<a class="customize" href=""><?php _e( 'Customize the message', 'writing-helper' ) ?></a>

					<div id="modify-email" style="display: none;">
						<textarea class="customize" cols="80" rows="8">
<?php // Note: Keep in one string for easier i18n.
	printf( __( 'Hi,

I started writing a new draft titled "%1$s" and would love to get your feedback. I plan on publishing it shortly.

Please leave your feedback here:
%2$s

Title: %3$s
Beginning: %4$s
Read more: %5$s
Thanks,
%6$s', 'writing-helper' ),
	'[title]',
	'[feedback-link]',
	'[title]',
	'[excerpt]',
	'[feedback-link]',
	$current_user->display_name
); ?>
						</textarea>
						<br />
						<input
								type="submit"
								id="add-request-custom"
								value="<?php echo esc_attr( __( 'Send Requests', 'writing-helper' ) ); ?>"
								class="button-secondary" />
						&nbsp;
						<a class="cancel" href="#"><?php _e( 'Cancel', 'writing-helper' ); ?></a>
					</div>

					<div id="df-share-link-p">
						<p><?php _e( 'or get a share link without sending an email', 'writing-helper' ) ?></p>
						<div id="df-getting-link" style="display:none">
							<img
									src="<?php echo esc_url( Writing_Helper()->plugin_url . 'i/ajax-loader.gif' ); ?>"
									alt="<?php esc_attr_e( 'Loading', 'writing-helper' ); ?>" />
							<?php _e( 'Getting a link...', 'writing-helper' ) ?>
						</div>
						<a
								class="button"
								id="df-share-link"
								data-post-id="<?php the_ID() ?>">
							<?php _e( 'Get a link', 'writing-helper' ) ?>
						</a>
					</div>
				</div>
			<?php else: ?>
				<p>
					<strong>
						<?php _e( 'This post has already been published, so you can not request any more feedback, but you can see the feedback that has been provided before that. ', 'writing-helper' ); ?>
					</strong>
				</p>
			<?php endif; ?>
			<div id="add-request-sent" class="add-request-message" style="display:none">
				<h4><?php _e( "Requests sent.", 'writing-helper' ); ?></h4>
				<p>
					<?php _e( "When your friends read your draft and give feedback, you'll get an email and the feedback will appear below.", 'writing-helper' ); ?>
				</p>
				<p><a href="#sendmore"><?php _e( "Send more requests.", 'writing-helper' );?></a></p>
			</div>

			<?php require( dirname( __FILE__ ) . '/feedback-form-feedback-table.tpl.php' ); ?>

		</div>
	</div>

<?php else: ?>

	<?php require( dirname( __FILE__ ) . '/feedback-form-feedback-table.tpl.php' ); ?>

<?php endif; ?>
