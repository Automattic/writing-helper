<ul
	id="helpers"
	<?php if ( isset( $_GET['cap'] ) || isset( $_GET['requestfeedback'] ) ): ?>
		style="display:none"
	<?php endif; ?>>
	<li class="copyapost">
		<div class="iconbox">
			<img src="<?php echo esc_url( Writing_Helper()->plugin_url . 'i/pencilhelper.png' ); ?>" alt="" />
		</div>

		<div class="helper-text">
			<a href="#copyapost"><?php echo esc_html( $cap_strings['title'] ); ?></a>
			<h4><?php echo esc_html( $cap_strings['title'] ); ?></h4>
			<p><?php echo esc_html( $cap_strings['details'] ); ?></p>
		</div>
		<div class="clear"></div>
	</li>
	<?php if ( $show_feedback_button || ( is_array( $requests ) && !empty( $requests ) ) ): ?>
		<li class="requestfeedback">
			<div class="border-box">
				<div class="iconbox">
					<img src="<?php echo esc_url( Writing_Helper()->plugin_url . 'i/requestfeedback.png' ); ?>" alt="" />
				</div>
	
				<div class="helper-text">
					<a href="#requestfeedback"><?php _e( 'Request Feedback', 'writing-helper' ) ?></a>
					<h4><?php _e( 'Request Feedback', 'writing-helper' ) ?></h4>
					<?php if ( $show_feedback_button ): ?>
						<p>
							<?php _e( 'Get feedback on this draft before publishing.', 'writing-helper' ); ?>
						</p>
					<?php else: ?>
						<p>
							<?php _e( 'See the feedback provided for a draft of this post.', 'writing-helper' ); ?>
						</p>
					<?php endif; ?>
				</div>
			</div>
		</li>
	<?php endif; ?>
</ul>
