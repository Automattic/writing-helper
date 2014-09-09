<?php
/**
 * Template file for the Draft Feedback Form
 *
 * @todo style this (and such)
 */
?>
<style>
.draftfeedback-feedback-form {
	position: fixed;
	left: -1%;
	top: 0;
	z-index: 100;
	height: 100%;
	width: 26%;
	padding: 20px 1% 0px 2%;
	background: #f7f7f7;
	border-right: 1px solid #ccc;
}

.draftfeedback-feedback-form * {
	font-family: 'Lucida Grande', Verdana, Arial, 'Bitstream Vera Sans', sans-serif !important;
}

.draftfeedback-feedback-form label,
.draftfeedback-feedback-form h3,
.draftfeedback-feedback-form p,
.draftfeedback-feedback-form ol,
.draftfeedback-feedback-form li {
	color: #000;
	font-size: 12px;
	text-shadow: #fff 1px 1px 0px;
}

.draftfeedback-feedback-form h3 {
	margin-bottom: 12px;
	font-size: 16px;
	font-weight: bold;
	line-height: 1.2em;
	text-transform: none;
}

.draftfeedback-feedback-form li,
.draftfeedback-feedback-form p {
	margin-bottom: 1em;
	line-height: 1.5em;
}

.draftfeedback-feedback-form label {
	margin-bottom: 5px;
}

.draftfeedback-feedback-form textarea {
	width: 97%;
	min-height: 70px;
	line-height: 1.5em;
	margin-bottom: 10px;
	padding: 5px;
	background: #fff;
	color: #000;
	font-family: Georgia, serif !important;
	font-size: 14px;
}

.draftfeedback-feedback-form input[type="submit"] {
	padding: 0 30px;
	background: #21759B;
	border: 1px solid #298CBA;
	-webkit-border-radius: 20px;
	-moz-border-radius: 20px;
	border-radius: 20px;
	line-height: 30px;
	text-shadow: rgba(0, 0, 0, 0.3) 0 -1px 0;
	color: white;
	background-image: -webkit-gradient(
		linear,
		left bottom,
		left top,
		color-stop(1, rgb(41,140,186)),
		color-stop(0, rgb(31,107,142))
	);
	background-image: -moz-linear-gradient(
		center bottom,
		rgb(41,140,186) 100%,
		rgb(31,107,142) 0%
	);
}

.draftfeedback-feedback-form input[type="submit"]:hover {
	border-color: #13455B;
	cursor: pointer;
}

.draftfeedback-thanks {
	display: none;
}

body {
	margin-left: 30%;
}

#comments,
#respond {
	display: none;
}

@media screen and (max-width: 1024px ) {
	.draftfeedback-feedback-form {
		left: -5px;
		padding: 50px 5px 30px 10px;
		width: 230px;
	}
	.draftfeedback-feedback-form textarea {
		width: 220px;
	}
	body {
		margin-left: 250px;
	}
}
</style>

<script type="text/javascript">
jQuery(document).ready(function($) {
	var feedback_textarea = $('#feedbackform textarea');
	var resize_handler = function() {
		var sidebar_height = $( '.draftfeedback-feedback-form' ).height();
		var intro_height = $( '.draftfeedback-intro' ).height();
		var textarea_height = sidebar_height - intro_height;
		feedback_textarea.css( 'height', (textarea_height - 130) + 'px' );
	};

	$( window ).resize( resize_handler );
	resize_handler();
});
</script>
<div class="draftfeedback-feedback-form">

	<div class="draftfeedback-thanks" id="draftfeedback-thanks">
		<h3><?php _e( 'Thank you for your feedback!' ); ?></h3>
		<p><?php _e( "If you want to send anything else you can use the same form below, otherwise feel free to close this page and we'll email you when the draft is published for everyone to see." ); ?></p>
	</div>
	<div class="draftfeedback-intro" id="draftfeedback-intro">
		<h3><?php the_author(); ?> <?php _e( 'would like your feedback.' ); ?></h3>

		<p><?php _e( 'This is a private, unpublished draft. Please review it and leave your feedback in the box below.' ); ?></p>

		<p><?php _e( 'Note any typos you find, suggestions you have, or links to recommend.' ); ?></p>
	</div>
	<form id="feedbackform" method="post">
		<textarea name="feedback" rows="4" id="feedback-text"></textarea>
		<input type="submit" name="Send Feedback" value="Send Feedback" />
	</form>
</div>
