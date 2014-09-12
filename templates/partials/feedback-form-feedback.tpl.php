<div id="requestfeedback" class="helper"<?php
	if ( isset( $_GET['requestfeedback'] ) )
		echo ' style="display:block"';
?>>
<div class="helper-header">
	<a href="" class="back"><?php _e( 'Back' ) ?><span></span></a>
	<h5><?php _e( 'Request Feedback' ) ?></h5>
</div>

<div class="inside helper-content">

<div id="invitetoshare">
	<p><strong><?php _e( 'Get feedback on this draft before publishing.' ); ?></strong></p>

	<p class="invitetext"><label for="invitelist"><?php _e( 'Enter email addresses of people you would like to get feedback from:' ) ?></label></p>
	<textarea id="invitelist" rows="2" placeholder="bob@example.org, sarah@example.org" class="first-focus"></textarea>

	<input type="submit" id="add-request" value="Send Requests" class="button-secondary" />

	&nbsp; <a class="customize" href=""><?php _e( 'Customize the message' ) ?></a>
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
%6$s' ),
	'[title]',
	'[feedback-link]',
	'[title]',
	'[excerpt]',
	'[feedback-link]',
	$current_user->display_name
); ?>
	</textarea><br />
	<input type="submit" id="add-request-custom" value="<?php echo esc_attr( __( 'Send Requests' ) ); ?>" class="button-secondary" /> &nbsp;<a class="cancel" href="#"><?php _e( 'Cancel' ); ?></a>
	</div>

	<div id="df-share-link-p">
		<div id="df-getting-link" style="display:none"><img src="<?php echo esc_url( WritingHelper()->plugin_url . 'i/ajax-loader.gif' ); ?>" alt="Loading" /> <?php _e( 'Getting a link...' ) ?></div>
		<a id="df-share-link" href="javascript:DraftGetLink(jQuery,<?php the_ID() ?>)"><?php _e( 'Get a share link without sending an email.' ) ?></a>
	</div>
</div>

<div id="add-request-sent" class="add-request-message" style="display:none">
<h4><?php _e( "Requests sent." ); ?></h4>
<p><?php _e( "When your friends read your draft and give feedback, you'll get an email and the feedback will appear below." ); ?></p>
<p><a href="#sendmore"><?php _e( "Send more requests." );?></a></p>
</div>
<?php
if ( $show_feedback_button && is_array( $requests ) && !empty( $requests ) ):
?>
  <table
		cellspacing="0"
		cellpadding="0"
		id="requests-list"
		<?php if ( 1 == $screen_layout_columns ): ?>
			class="wide"
		<?php endif; ?>
		>
<?php if ( 1 == $screen_layout_columns ): ?>
    <thead>
      <tr>
	  <th class="user"><?php _e( 'User' ); ?></th>
	  <th class="feedback"><?php _e( 'Feedback' ); ?></th>
      </tr>
    </thead>
<?php endif; ?>
    <tbody>
<?php
	$i = 0;

	foreach( $requests as $email => $data ):
		$feedbacks = $df->get_user_feedbacks( $post_id, $email );
		$requested_on = $df->time_to_date( $data['time'] );
		$secret_url = $df->generate_secret_link( $post_id, $data['key'] );
		$revoke_display = ! empty( $data['revoked'] ) ? 'none' : 'inline';
		$unrevoke_display = ! empty( $data['revoked'] ) ? 'inline' : 'none';
		$display_name = __( 'Anonymous link' );
		$avatar_class = is_email( $email ) ? '' : 'anonymous';
		if ( 'anonymous' != $avatar_class ) {
			$user = get_user_by( 'email', $email );
			$display_name = $user ? $user->display_name : $email;
		}
?>
	<tr>
		<td>
			<div class="links">
				<input
						class="input-small link"
						type="text"
						value="<?php echo esc_attr( $secret_url ); ?>"
						title="<?php esc_attr__( 'The secret link this person received in order to see and give feedback on your draft' ); ?>" />
				<a
						class="button button-small"
						href="javascript:DraftRevokeAccess(jQuery, <?php
								echo esc_js( $post_id );
							?>, '<?php
								echo esc_js( $email )
							?>', '#revoke-<?php echo $i ?>')"
						id="revoke-<?php echo $i++; ?>">
					<span
							class="revoke"
							style="display: <?php echo $revoke_display; ?>">
						<?php _e( 'Revoke Access' ); ?>
					</span>
					<span
							class="unrevoke"
							style="display: <?php echo $unrevoke_display; ?>">
						<?php _e( 'Give Back Access' ); ?>
					</span>
				</a>
			</div>
			<p class="avatar <?php echo $avatar_class ?>">
				<?php if ( 'anonymous' != $avatar_class ): ?>
					<?php echo get_avatar( $email, 24 ); ?>
				<?php endif; ?>
				<span class="name"><?php echo $display_name ?></span>
				<span class="added">
					<?php _ex( 'added on', 'Feedback request date prefix' ); ?>
					<?php echo esc_html( $requested_on ); ?>
				</span>
			</p>
<?php	if ( 1 == $screen_layout_columns ): ?>
		</td>
		<td>
<?php	endif;

		if ( is_array( $feedbacks ) && !empty( $feedbacks ) ):
			$multi_items_css = ( count( $feedbacks ) > 1 ) ? 'multiple' : '';
?>
			<ol class="feedbacks-list <?php echo esc_attr( $multi_items_css ) ?>">
<?php
				foreach ( $feedbacks as $feedback ):
					$feedback_content = wpautop( esc_html( $feedback['content'] ) );
					$feedback_content_truncated = ( mb_strlen( $feedback_content ) > 70 ) ? wp_html_excerpt( $feedback_content, 70 ) . '&hellip;' : $feedback_content;
					$feedback_date_info = esc_attr( sprintf( __( 'Submitted on %s' ), $df->time_to_date( $feedback['time'] ) ) );
?>
					<li>
						<?php if ( $feedback_content_truncated != $feedback_content ) : ?>
							<a href="#" title="<?php echo esc_attr( $feedback_date_info ); ?>" class="truncated">[+] <?php echo $feedback_content_truncated; ?></a>
							<div title="<?php echo esc_attr( $feedback_date_info ); ?>" class="full"><?php echo $feedback_content; ?><a href="#"><?php _e( '[-] Collapse' ); ?></a></div>
						<?php else: ?>
							<?php echo $feedback_content; ?>
						<?php endif; ?>
					</li>
<?php
				endforeach;
?>
			</ol>
<?php	elseif ( empty( $feedbacks ) ): ?>
			<span class="no-feedback">
				<?php _e( 'No feedback has been given yet '); ?>
			</span>
<?php
		endif;
?>
      </td>
    </tr>
<?php
	endforeach;
?>
	</tbody>
  </table>
<?php
endif;
?>
</div>
</div>
