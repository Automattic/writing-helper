<ul id="helpers"<?php if ( isset( $_GET['cap'] ) || isset( $_GET['requestfeedback'] ) ) echo ' style="display:none"'; ?>>
	<li class="copyapost">
		<div class="iconbox">
			<img src="<?php echo esc_url( WritingHelper()->plugin_url . 'i/pencilhelper.png' ); ?>" alt="" />
		</div>

		<div class="helper-text">
			<a href="#copyapost"><?php echo esc_html( $cap_strings['title'] ); ?></a>
			<h4><?php echo esc_html( $cap_strings['title'] ); ?></h4>
			<p><?php echo esc_html( $cap_strings['details'] ); ?></p>
		</div>
		<div class="clear"></div>
	</li>
<?php if ( $show_feedback_button ): ?>
	<li class="requestfeedback">
		<div class="border-box">
			<div class="iconbox">
				<img src="<?php echo esc_url( WritingHelper()->plugin_url . 'i/requestfeedback.png' ); ?>" alt="" />
			</div>

			<div class="helper-text">
				<a href="#requestfeedback"><?php _e( 'Request Feedback' ) ?></a>
				<h4><?php _e( 'Request Feedback' ) ?></h4>
				<p><?php _e( 'Get feedback on this draft before publishing.' ); ?></p>
			</div>
		</div>
	</li>
<?php endif; ?>
</ul>
