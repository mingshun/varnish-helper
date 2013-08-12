<?php
/*
Plugin Name: Varnish Helper
Plugin URI:
Description: Varnish helper for WordPress in purge and ESI.
Author: mingshun
Author URI: https://github.com/mingshun
Version: 1.0
*/


/**
	*	Send PURGE request to the given $url.
	*
	* @since 1.0
	*/
function vh_purge($url) {
	wp_remote_request(get_bloginfo('url') . $url, array('method' => 'PURGE'));
}


/**
 * Send BAN request to the given $url.
 *
 * @since 1.0
 */
function vh_ban($url) {
	wp_remote_request(get_bloginfo('url') . $url, array('method' => 'BAN'));
}


/**
 * Purge the common object: '/', '/feed/', '/feed/atom/', '/page/.*'.
 *
 * @since 1.0
 */
function vh_purge_common() {
	vh_purge('/');
	vh_purge('/feed/');
	vh_purge('/feed/atom/');
	vh_ban('/page/.*');
}


/**
 * Purge the post with the give post id.
 *
 * @since 1.0
 */
function vh_purge_post($post_id) {
	$post = get_post($post_id);
	if (!wp_is_post_revision($post)) {
		$permalink = get_permalink($post_id);
		$url = str_replace(get_bloginfo('url'), '', $permalink);
		vh_purge($url);
		vh_purge($url . 'feed/');
	}
}


/**
 * Purge pages of the archive that the post of the given post id within.
 *
 * @since 1.0
 */
function vh_purge_archive($post_id) {
	$slug = get_the_time('/Y/m', $post_id);
	$url = $slug . '/.*';
	vh_ban($url);
}


/**
 * Purge pages of the author of the post with the given post id.
 *
 * @since 1.0
 */
function vh_purge_author($post_id) {
	$post = get_post($post_id);
	$author_id = $post->post_author;
	$author_posts_link = get_author_posts_url($author_id);
	$url = str_replace(get_bloginfo('url'), '', $author_posts_link) . '.*';
	vh_ban($url);
}


/**
 * Purge pages of the category of the post with the given post id.
 *
 * @since 1.0
 */
function vh_purge_category($post_id) {
	$categories = get_the_category($post_id);
	foreach ($categories as $category) {
		$url = '/category/' . $category->slug . '/.*';
		vh_ban($url);
	}
}


/**
 * Purge pages of the tag of the post with the given post id.
 *
 * @since 1.0
 */
function vh_purge_tag($post_id) {
	$tags = get_the_tags($post_id);
	foreach ($tags as $tag) {
		$url = '/tag/' . $tag->slug . '/.*';
		vh_ban($url);
	}
}


/**
 * Purge the related pages of the post with the given post id.
 *
 * @since 1.0
 */
function vh_purge_post_related_pages($post_id) {
	vh_purge_archive($post_id);
	vh_purge_author($post_id);
	vh_purge_category($post_id);
	vh_purge_tag($post_id);
}


/**
 * Purge the whole site.
 *
 * @since 1.0
 */
function vh_purge_all() {
	vh_ban('/.*');
}


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