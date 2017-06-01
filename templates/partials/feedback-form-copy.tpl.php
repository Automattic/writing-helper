<div id="copyapost" class="helper"<?php if ( isset( $_GET['cap'] ) ) { echo ' style="display:block"';} ?>>
	<div class="helper-header" id="cap">
		<a href="" class="back"><?php _e( 'Back', 'writing-helper' ) ?><span></span></a>
		<h5><?php echo esc_html( $cap_strings['title'] ); ?></h5>
	</div>

	<div class="inside helper-content">
		<p>
			<strong><?php echo esc_html( $cap_strings['details'] ); ?></strong>
			<?php echo $cap_strings['instructions']; ?>
		</p>

		<div class="search-posts">
			<!--[if lt IE 10]]><label for="search-posts"><?php echo $cap_strings['search']; ?></label><![endif]-->
			<input
					type="search"
					name="search"
					id="search-posts"
					placeholder="<?php echo esc_attr( $cap_strings['search'] ); ?>" />
		</div>

		<div class="confirm-copy" style="display: none;">
			<p class="confirm">
				<?php echo esc_html( $cap_strings['confirm'] ); ?>
				&nbsp;
				<input
						type="button"
						class="button-secondary"
						value="<?php esc_attr_e( 'Cancel', 'writing-helper' ) ?>"
						id="cancel-copy" />
				<input
						type="button"
						class="button-primary"
						value="<?php esc_attr_e( 'Confirm Copy', 'writing-helper' ) ?>"
						id="confirm-copy" />
			</p>
			<p class="copying">
				<img
						src="<?php echo esc_url( Writing_Helper()->plugin_url . 'i/ajax-loader.gif' ); ?>"
						alt="<?php esc_attr_e( 'Loading', 'writing-helper' ); ?>" />
				<?php echo esc_html( $cap_strings['copying'] ); ?>
			</p>
		</div>

		<div class="copy-posts">
			<?php $stickies = Writer_Helper_Copy_Post::get_candidate_posts( $post_type, '', true ); ?>
			<?php if ( ! empty( $stickies ) ) : ?>
			<ul id="s-posts">
				<?php foreach ( $stickies as $sticky_post ) : ?>
					<?php setup_postdata( $sticky_post ); ?>
					<li>
						<input
								type="button"
								value="<?php esc_attr_e( 'Copy', 'writing-helper' ) ?>"
								class="button-secondary"
								id="cp-<?php the_ID() ?>" />
						&nbsp;
						<span class="title"><?php echo $sticky_post->post_title ?></span>
						<?php if ( strlen( $sticky_post->post_content ) > MB_IN_BYTES / 5 ) : ?>
							<span class="excerpt">
								<?php esc_html_e( 'Excerpt cannot be retrieved.', 'writing-helper' ); ?>
							</span>
						<?php else : ?>
							<span class="excerpt"><?php echo strip_tags( get_the_excerpt() ) ?></span>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
				<?php wp_reset_postdata(); ?>
			</ul>
			<?php endif; ?>

			<ul id="l-posts">
			</ul>
			<div class="loading">
				<img
						src="<?php echo esc_url( Writing_Helper()->plugin_url . 'i/ajax-loader.gif' ); ?>"
						alt="<?php esc_attr_e( 'Loading', 'writing-helper' ); ?>" />
				<?php _e( 'Searching&hellip;', 'writing-helper' ) ?>
			</div>
		</div>

	</div>

	<span class="explain" style="display: none;"><?php _e( 'Stick to Top', 'writing-helper' ) ?></span>

	<?php if ( isset( $_GET['cap'] ) ) : ?>
		<script type="text/javascript">
			jQuery( function() {
				jQuery( 'li#menu-posts li, li#menu-posts li a' ).removeClass( 'current' );
				jQuery( 'li#menu-posts li a[href="post-new.php?cap#cap"]' ).addClass('current').parent('li').addClass('current');
				jQuery.post( ajaxurl, { 'action': 'helper_record_stat', 'stat': 'menu_click' } );
			});
		</script>
	<?php endif; ?>
</div>
