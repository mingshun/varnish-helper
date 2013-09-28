<?php
/*
Plugin Name: Varnish Helper
Plugin URI: https://github.com/mingshun/varnish-helper
Description: Varnish helper for WordPress in purge and ESI.
Author: mingshun
Author URI: https://github.com/mingshun
Version: 1.0
*/


require_once('generic-purges.php');

require_once('esi-widget.php');

require_once('option-page.php');


/**
 * Purge the post whose status changed.
 *
 * @since 1.0
 */
function vh_purge_when_post_status_changed($new_status, $old_status, $post) {
  if ($new_status == 'publish' || $old_status == 'publish') {
    vh_purge_post($post->ID);
    vh_purge_common($post->ID);
    vh_purge_post_related_pages($post->ID);
    vh_purge_esi_sidebar();
  }
}


/**
 * Purge the post which has new comment.
 *
 * @since 1.0
 */
function vh_purge_comment($comment_id) {
  $comment = get_comment($comment_id);
  $post_id = $comment->comment_post_ID;
  vh_purge_post($post_id);
}

/**
 * Purge the post containing the comment whose status changed.
 *
 * @since 1.0
 */
function vh_purge_when_comment_status_changed($new_status, $old_status, $comment) {
  if ($new_status == 'approved' || $old_status == 'approved') {
    $post_id = $comment->comment_post_ID;
    vh_purge_post($post_id);
    vh_purge_esi_sidebar();
  }
}



// Purge when post status changed.
add_action('transition_post_status', 'vh_purge_when_post_status_changed', 99, 3);

// Purge when there is a comment or the existing comment status changes.
add_action('comment_post', 'vh_purge_comment', 99);
add_action('transition_comment_status', 'vh_purge_when_comment_status_changed', 99, 3);

// Purge when there is a xml-rpc call.
add_action('xmlrpc_call', 'vh_purge_all', 99);

// Purge when switching theme.
add_action('switch_theme', 'vh_purge_all', 99);
?>