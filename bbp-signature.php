<?php
/*
Plugin Name: bbP Signature
Plugin URI: http://arjunsk.in/bbp-signature/
Description: This plugin allows users to add a signature below their topics and replies with support for BuddyPress.
Author: Arjun S Kumar
Version: 1.1
Author URI: http://arjunsk.in/
*/

/**
 * @todo add max length feature at admin panel.
 *
*/

function bbp_user_signature( $user_id = 0 ) {
	echo bbp_get_user_signature( $user_id );
}

	function bbp_get_user_signature( $user_id = 0 ) {
		$signature = get_user_meta( $user_id, '_bbp_signature', true );
		return apply_filters( 'bbp_get_user_signature', $signature, $user_id );
	}
	
add_action( 'bbp_user_edit_after_about', 'bbp_edit_user_signature'  );	
add_filter( 'bbp_get_user_signature',    'wptexturize',        3    );
add_filter( 'bbp_get_user_signature',    'html_entity_decode', 7    );
add_filter( 'bbp_get_user_signature',    'convert_chars',      5    );
add_filter( 'bbp_get_user_signature',    'make_clickable',     9    );
add_filter( 'bbp_get_user_signature',    'capital_P_dangit',   10   );
add_filter( 'bbp_get_user_signature',    'force_balance_tags', 25   );
add_filter( 'bbp_get_user_signature',    'convert_smilies',    20   );
add_filter( 'bbp_get_user_signature',    'wpautop',            30   );
	
function bbp_edit_user_signature() {
	
	// Get the displayed users signature
	$signature = bbp_get_displayed_user_field( '_bbp_signature' ); ?>

		<div>
			<label for="_bbp_signature"><?php _e( 'Signature', 'bbpress' ); ?></label>
			<textarea name="_bbp_signature" id="_bbp_signature" rows="5" cols="30" onkeypress="return imposeMaxLength(this, 499);"><?php echo $signature; ?></textarea>
			<span class="description"><?php _e( 'This will be shown publicly below your topics and replies.', 'bbpress' ); ?></span>
		</div>
<?php
}	
add_action( 'wp_print_styles', 'bbp_signature_css' );
function bbp_signature_css( ) {
	wp_enqueue_style('bbp-signature', plugins_url('bbp-signature/bbp-signature.css'), false, '0.1', 'all');
}

add_action( 'wp_enqueue_scripts', 'bbp_signature_max_length_js' );
function bbp_signature_max_length_js( ) {
	?>
    <script language="javascript" type="text/javascript">
		<!--
		function imposeMaxLength(Object, MaxLen)
		{
  		return (Object.value.length <= MaxLen);
		}
		-->
	</script>
    <?php	
}

function bbp_reply_content_append_user_signature( $content = '', $reply_id = 0, $args = array() ) {
	// Default arguments
	$defaults = array(
		'separator' => '<hr />',
		'before'    => '<div class="bbp-reply-signature">',
		'after'     => '</div>'
	);
	$r = wp_parse_args( $args, $defaults );
	extract( $r );

	// Verify topic id, get author id, and potential signature
	$reply_id  = bbp_get_reply_id       ( $reply_id );
	$user_id   = bbp_get_reply_author_id( $reply_id );
	if(function_exists('bp_has_groups')) {
		$signature = xprofile_get_field_data( 'Signature', $user_id );
	}
	else {
		$signature = bbp_get_user_signature ( $user_id  );
	}

	// If signature exists, adjust the content accordingly
	if ( !empty( $signature ) )
		$content = $content . $separator . $before . $signature . $after;

	return apply_filters( 'bbp_reply_content_append_signature', $content, $reply_id, $separator );
}

if ( !is_admin() ) {
	add_filter( 'bbp_get_reply_content', 'bbp_reply_content_append_user_signature', 1, 2 );
	add_filter( 'bbp_get_reply_content', 'bbp_reply_content_append_revisions',      1, 4 );
}

function bbp_topic_content_append_user_signature( $content = '', $topic_id = 0, $args = array() ) {

	// Default arguments
	$defaults = array(
		'separator' => '<hr />',
		'before'    => '<div class="bbp-topic-signature">',
		'after'     => '</div>'
	);
	$r = wp_parse_args( $args, $defaults );
	extract( $r );

	// Verify topic id, get author id, and potential signature
	$topic_id  = bbp_get_topic_id       ( $topic_id );
	$user_id   = bbp_get_topic_author_id( $topic_id );
	if(function_exists('bp_has_groups')) {
		$signature = xprofile_get_field_data( 'Signature', $user_id );
	}
	else {
		$signature = bbp_get_user_signature ( $user_id  );
	}
	
	// If signature exists, adjust the content accordingly
	if ( !empty( $signature ) )
		$content = $content . $separator . $before . $signature . $after;

	return apply_filters( 'bbp_topic_content_append_signature', $content, $topic_id, $separator );
}

if ( !is_admin() ) {
	add_filter( 'bbp_get_topic_content', 'bbp_topic_content_append_user_signature', 1, 2 );
	add_filter( 'bbp_get_topic_content', 'bbp_topic_content_append_revisions',      1, 4 );
}

function bbp_edit_user_signature_handler( $user_id ) {

	// Sanitize the user signature
	$signature = !empty( $_POST['_bbp_signature'] ) ? $_POST['_bbp_signature'] : '';
	$signature = apply_filters( 'bbp_edit_user_signature_handler', $signature );

	// Update signature user meta
	if ( !empty( $signature ) )
		update_user_meta( $user_id, '_bbp_signature', $signature );

	// Delete signature user meta
	else
		delete_user_meta( $user_id, '_bbp_signature' );
}
add_action( 'personal_options_update',         'bbp_edit_user_signature_handler' );
add_action( 'edit_user_profile_update',        'bbp_edit_user_signature_handler' );
add_filter( 'bbp_edit_user_signature_handler', 'trim'                            );
add_filter( 'bbp_edit_user_signature_handler', 'wp_filter_kses'                  );
add_filter( 'bbp_edit_user_signature_handler', 'force_balance_tags'              );
add_filter( 'bbp_edit_user_signature_handler', '_wp_specialchars'                );

	
?>