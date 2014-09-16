<div id="copyapost" class="helper"<?php if ( isset( $_GET['cap'] ) ) echo ' style="display:block"'; ?>>
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
			<input type="search" name="search" id="search-posts" value="<?php echo esc_attr( $cap_strings['search'] ); ?>" onfocus="if ( this.value == '<?php echo esc_js( $cap_strings['search'] ); ?>' ) this.value = '';" onblur="if ( this.value == '' ) this.value = '<?php echo esc_js( $cap_strings['search'] ) ?>';" />
		</div>

		<div class="confirm-copy" style="display: none;">
			<p class="confirm"><?php echo esc_html( $cap_strings['confirm']  ); ?> &nbsp;<input type="button" class="button-secondary" value="<?php esc_attr_e( 'Cancel', 'writing-helper' ) ?>" id="cancel-copy" /> <input type="button" class="button-primary" value="<?php esc_attr_e( 'Confirm Copy', 'writing-helper' ) ?>" id="confirm-copy" /></p>
			<p class="copying"><img src="<?php echo esc_url( WritingHelper()->plugin_url . 'i/ajax-loader.gif' ); ?>" alt="Loading" />  <?php echo esc_html( $cap_strings['copying'] ); ?></p>
		</div>

		<div class="copy-posts">
			<?php $tmp_post = $post; ?>
			<?php $sticky_posts = get_option( 'copy_a_post_sticky_posts' ); ?>
			<?php if ( !empty( $sticky_posts ) ) : ?>
			<ul id="s-posts">
				<?php
					$stickies_args = array(
										'posts_per_page'		=> 3,
										'ignore_sticky_posts'	=> 1,
										'post__in'				=> (array) $sticky_posts,
										'post_type'				=> $post_type,
									);

					$stickies = new WP_Query( $stickies_args );
				?>
				<?php while( $stickies->have_posts() ) : $stickies->the_post(); ?>
					<li>
						<input type="button" value="<?php esc_attr_e( 'Copy', 'writing-helper' ) ?>" class="button-secondary" id="cp-<?php the_ID() ?>" /> &nbsp;
						<span class="title"><?php the_title() ?></span>
						<?php if ( strlen( $post->post_content ) > MB_IN_BYTES / 5 ) : ?>
							<span class="excerpt"><?php esc_html_e( 'Excerpt cannot be retrieved.', 'writing-helper' ); ?></span>
						<?php else: ?>
							<span class="excerpt"><?php echo strip_tags( get_the_excerpt() ) ?></span>
						<?php endif; ?>
					</li>
				<?php endwhile; ?>
			</ul>
			<?php endif; ?>

			<ul id="l-posts">
				<?php
					$latest_posts_args = array(
											'posts_per_page'	=> 20,
											'posts__not_in'		=> (array) $sticky_posts,
											'post_status'		=> 'any',
											'post_type'			=> $post_type,
										);

					$latest_posts = new WP_Query( $latest_posts_args );
				?>
				<?php while( $latest_posts->have_posts() ) : $latest_posts->the_post(); ?>
					<li>
						<input type="button" value="<?php esc_attr_e( 'Copy', 'writing-helper' ) ?>" class="button-secondary" id="cp-<?php the_ID() ?>" /> &nbsp;
						<span class="title"><?php the_title() ?></span>
						<?php if ( strlen( $post->post_content ) > MB_IN_BYTES / 5 ) : ?>
							<span class="excerpt"><?php esc_html_e( 'Excerpt cannot be retrieved.', 'writing-helper' ); ?></span>
						<?php else: ?>
							<span class="excerpt"><?php echo strip_tags( get_the_excerpt() ) ?></span>
						<?php endif; ?>
					</li>
				<?php endwhile; ?>
				<?php wp_reset_query(); $post = $tmp_post; ?>
			</ul>
			<div class="loading"><img src="<?php echo esc_url( WritingHelper()->plugin_url . 'i/ajax-loader.gif' ); ?>" alt="<?php esc_attr_e( 'Loading', 'writing-helper' ); ?>" /> <?php _e( 'Searching&hellip;', 'writing-helper' ) ?></div>
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
