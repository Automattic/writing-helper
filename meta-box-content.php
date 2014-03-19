<?php
	$post_type = get_post_type();
	$post_type_obj = get_post_type_object( $post_type );

	$cap_strings = array();

	switch ( $post_type ) {
		case 'post':
			$cap_strings['title'] = __( 'Copy a Post' );
			$cap_strings['details'] = __( 'Use an existing post as a template.' );
			$cap_strings['instructions'] = __( "Pick a post and we'll copy the title, content, tags and categories. Recent posts are listed below. Search by title to find older posts. You can mark any post to keep it at the top." );
			$cap_strings['search'] = __( 'Search for a post by title' );
			$cap_strings['confirm'] = __( 'Replace the current post with the selected post?' );
			$cap_strings['copying'] = __( 'Copying post...' );
			break;
		case 'page':
			$cap_strings['title'] = __( 'Copy a Page' );
			$cap_strings['details'] = __( 'Use an existing page as a template.' );
			$cap_strings['instructions'] = __( "Pick a post and we'll copy the title and content. Recent pages are listed below. Search by title to find older pages. You can mark any page to keep it at the top." );
			$cap_strings['search'] = __( 'Search for a page by title' );
			$cap_strings['confirm'] = __( 'Replace the current page with the selected page?' );
			$cap_strings['copying'] = __( 'Copying page...' );
			break;
		default:
			$cap_strings['title'] = sprintf( _x( 'Copy a %s', 'Copy a {post_type}' ), $post_type_obj->labels->singular_name );
			$cap_strings['details'] = sprintf( _x( 'Use an existing %s as a template.', 'Use an existing {post_type} as a template' ), strtolower( $post_type_obj->labels->singular_name ) );
			$cap_strings['instructions'] = sprintf( _x( 'Pick a %1$s and we&#8217;ll copy the title and content. Recent %2$s are listed below. Search by title to find older %3$s. You can mark any %4$s to keep it at the top.', 'Copy a {post_type}. First and fourth variables are single, second and third are plurals' ), strtolower( $post_type_obj->labels->singular_name ), strtolower( $post_type_obj->labels->name ), strtolower( $post_type_obj->labels->name ), strtolower( $post_type_obj->labels->singular_name ) );
			$cap_strings['search'] = sprintf( _x( 'Search for %s by title', 'Search for {post_type} by title' ), strtolower( $post_type_obj->labels->name ) );
			$cap_strings['confirm'] = sprintf( _x( 'Replace the current %s with the selected %s?', 'Replace the current {post_type} with the selected {post_type}' ), strtolower( $post_type_obj->labels->singular_name ), strtolower( $post_type_obj->labels->singular_name ) );
			$cap_strings['copying'] = sprintf( _x( 'Copying %s...', 'copying {post_type}' ), $post_type_obj->labels->singular_name );
	}
?>

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
	<textarea id="invitelist" rows="2" placeholder="bob@example.org, sarah@example.org"></textarea>

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
  <table cellspacing="0" cellpadding="0" id="requests-list">
<?php if ( 1 == $screen_layout_columns ): ?>
    <thead>
      <tr>
	  <th class="name"><?php _e( 'Email' ); ?></th>
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
?>
	<tr>
		<td>
			<?php if ( is_email( $email ) ) : ?>
			<?php echo get_avatar( $email, 24 ); ?>
    		<?php echo esc_html( $email ); ?>
			<?php endif; ?>
			<div class="links">
				<?php echo esc_html( $requested_on  ); ?> |
				<a href="<?php echo esc_attr( $secret_url );  ?>" title="<?php esc_attr__( 'The secret link this person received in order to see and give feedback on your draft' ); ?>" target="_blank"><?php _e( 'Link' ); ?></a> |
				<a href="javascript:DraftRevokeAccess(jQuery, <?php echo esc_js( $post_id ); ?>, '<?php echo esc_js( $email ) ?>', '#revoke-<?php echo $i ?>')" id="revoke-<?php echo $i++; ?>">
					<span class="revoke" style="display: <?php echo $revoke_display; ?>"><?php _e( 'Revoke Access' ); ?></span>
					<span class="unrevoke" style="display: <?php echo $unrevoke_display; ?>"><?php _e( 'Give Back Access' ); ?></span>
				</a>
			</div>
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

<div id="copyapost" class="helper"<?php if ( isset( $_GET['cap'] ) ) echo ' style="display:block"'; ?>>
	<div class="helper-header" id="cap">
		<a href="" class="back"><?php _e( 'Back' ) ?><span></span></a>
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
			<p class="confirm"><?php echo esc_html( $cap_strings['confirm']  ); ?> &nbsp;<input type="button" class="button-secondary" value="<?php esc_attr_e( 'Cancel' ) ?>" id="cancel-copy" /> <input type="button" class="button-primary" value="<?php esc_attr_e( 'Confirm Copy' ) ?>" id="confirm-copy" /></p>
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
						<input type="button" value="<?php esc_attr_e( 'Copy' ) ?>" class="button-secondary" id="cp-<?php the_ID() ?>" /> &nbsp;
						<span class="title"><?php the_title() ?></span>
						<span class="excerpt"><?php echo strip_tags( get_the_excerpt() ) ?></span>
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
						<input type="button" value="<?php esc_attr_e( 'Copy' ) ?>" class="button-secondary" id="cp-<?php the_ID() ?>" /> &nbsp;
						<span class="title"><?php the_title() ?></span>
						<span class="excerpt"><?php echo strip_tags( get_the_excerpt() ) ?></span>
					</li>
				<?php endwhile; ?>
				<?php wp_reset_query(); $post = $tmp_post; ?>
			</ul>
			<div class="loading"><img src="<?php echo esc_url( WritingHelper()->plugin_url . 'i/ajax-loader.gif' ); ?>" alt="<?php esc_attr_e( 'Loading'); ?>" /> <?php _e( 'Searching&hellip;' ) ?></div>
		</div>

	</div>

	<span class="explain" style="display: none;"><?php _e( 'Stick to Top' ) ?></span>

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

<div class="clear"></div>
