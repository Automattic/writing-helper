<?php if ( is_array( $requests ) && !empty( $requests ) ): ?>
<table
		cellspacing="0"
		cellpadding="0"
		id="requests-list"
		<?php if ( 1 == $screen_layout_columns ): ?>
			class="wide"
		<?php endif; ?> >
	<?php if ( 1 == $screen_layout_columns ): ?>
		<thead>
			<tr>
				<th class="user"><?php _e( 'User', 'writing-helper' ); ?></th>
				<th class="feedback"><?php _e( 'Feedback', 'writing-helper' ); ?></th>
			</tr>
		</thead>
	<?php endif; ?>
	<tbody>
		<?php $i = 0; ?>

		<?php foreach( $requests as $email => $data ): ?>
			<?php
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
								title="<?php esc_attr__( 'The secret link this person received in order to see and give feedback on your draft', 'writing-helper' ); ?>" />
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
								<?php _e( 'Revoke Access', 'writing-helper' ); ?>
							</span>
							<span
									class="unrevoke"
									style="display: <?php echo $unrevoke_display; ?>">
								<?php _e( 'Give Back Access', 'writing-helper' ); ?>
							</span>
						</a>
					</div>
					<p class="avatar <?php echo $avatar_class ?>">
						<?php if ( 'anonymous' != $avatar_class ): ?>
							<?php echo get_avatar( $email, 24 ); ?>
						<?php endif; ?>
						<span class="name"><?php echo $display_name ?></span>
						<span class="added">
							<?php printf(
								 _x(
									'added on %s',
									'added on {creation_date}',
									'writing-helper'
								),
								esc_html( $requested_on )
							); ?>
						</span>
					</p>
			
					<?php if ( 1 == $screen_layout_columns ): ?>
						</td>
						<td>
					<?php endif; ?>
			
					<?php if ( is_array( $feedbacks ) && !empty( $feedbacks ) ):
						$multi_items_css = ( count( $feedbacks ) > 1 ) ? 'multiple' : ''; ?>
						<ol class="feedbacks-list <?php echo esc_attr( $multi_items_css ) ?>">
			
							<?php foreach ( $feedbacks as $feedback ): ?>
								<?php
								$feedback_content = wpautop( esc_html( $feedback['content'] ) );
								$feedback_content_truncated = ( mb_strlen( $feedback_content ) > 70 ) ?
									 wp_html_excerpt( $feedback_content, 70 ) . '&hellip;' : $feedback_content;
								$feedback_date_info = esc_attr(
									sprintf(
										__( 'Submitted on %s', 'writing-helper' ),
										$df->time_to_date( $feedback['time'] )
									)
								);
								?>
								<li>
									<?php if ( $feedback_content_truncated != $feedback_content ) : ?>
										<a
												href="#"
												title="<?php echo esc_attr( $feedback_date_info ); ?>"
												class="truncated">
											[+] <?php echo $feedback_content_truncated; ?>
										</a>
										<div
												title="<?php echo esc_attr( $feedback_date_info ); ?>"
												class="full">
											<?php echo $feedback_content; ?>
											<a href="#"><?php _e( '[-] Collapse', 'writing-helper' ); ?></a>
										</div>
									<?php else: ?>
										<?php echo $feedback_content; ?>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
						</ol>
					<?php elseif ( empty( $feedbacks ) ): ?>
						<span class="no-feedback">
							<?php _e( 'No feedback has been given yet ', 'writing-helper' ); ?>
						</span>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php else: ?>
<input type="hidden" id="requests-list" />
<?php endif; ?>