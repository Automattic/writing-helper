<script type="text/javascript">
jQuery(document).ready(function($) {
	$('#draft-post-published').delay(8000).fadeOut('slow');
});
</script>
<style>
#draft-post-published {
position: absolute;
width: 100%;
height: 40px;
top: 0;
left: 0;
background: #ffffe3;
border-bottom: 1px solid #f6f6d0;
z-index: 999999;
text-align: center;
line-height: 40px;
font-size: 14px;
font-family: Helvetica, Arial, sans-serif;
}
</style>
<div id="draft-post-published">
<?php _e("The draft you're looking for has been published. Please, leave me a comment!"); ?>
</div>
