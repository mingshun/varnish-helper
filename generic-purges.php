<?php
/**
 * Generic purge functions.
 *
 * @since 1.0
 */


/**
 * Send PURGE request to the given $uri.
 *
 * @since 1.0
 */
function vh_purge($uri) {
  return wp_remote_request(get_bloginfo('url') . $uri, array('method' => 'PURGE'));
}


/**
 * Send BAN request to the given $uri.
 *
 * @since 1.0
 */
function vh_ban($uri) {
  return wp_remote_request(get_bloginfo('url') . $uri, array('method' => 'BAN'));
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
    $uri = str_replace(get_bloginfo('url'), '', $permalink);
    vh_purge($uri);
    vh_purge($uri . 'feed/');
  }
}


/**
 * Purge pages of the archive that the post of the given post id within.
 *
 * @since 1.0
 */
function vh_purge_archive($post_id) {
  $slug = get_the_time('/Y/m', $post_id);
  $uri = $slug . '/.*';
  vh_ban($uri);
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
  $uri = str_replace(get_bloginfo('url'), '', $author_posts_link) . '.*';
  vh_ban($uri);
}


/**
 * Purge pages of the category of the post with the given post id.
 *
 * @since 1.0
 */
function vh_purge_category($post_id) {
  $categories = get_the_category($post_id);
  foreach ($categories as $category) {
    $uri = '/category/' . $category->slug . '/.*';
    vh_ban($uri);
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
    $uri = '/tag/' . $tag->slug . '/.*';
    vh_ban($uri);
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
 * Purge ESI sidebar.
 *
 * @since 1.0
 */
function vh_purge_esi_sidebar() {
  $url = plugin_dir_url(__FILE__) . 'esi-sidebar.php';
  $uri = str_replace(get_bloginfo('url'), '', $url);
  vh_purge($uri);
}


/**
 * Purge the whole site.
 *
 * @since 1.0
 */
function vh_purge_all() {
  vh_ban('/.*');
}
?>