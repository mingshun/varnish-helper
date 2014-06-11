<?php
/**
 * Generic purge functions.
 *
 * @since 1.0
 */

require_once('option-page.php');


define('VH_SCHEME_HTTP', 'http://');
define('VH_SCHEME_HTTPS', 'https://');


/**
 * Send request to the given $uri in the specified $method.
 *
 * @since 1.0
 */
function vh_generic_purge($uri, $method) {
  $hosts = vh_get_edge_node_list();
  $results = array();
  for ($i = 0; $i < count($hosts); ++$i) {
    $host = $hosts[$i]['host'];
    $url = vh_get_url($host . $uri);
    $result = wp_remote_request($url, array(
      'method' => $method,
      'headers' => array(
        'host' => vh_get_domain()
      ),
    ));
    array_push($results, array(
      'host' => $host,
      'uri' => $uri,
      'result' => $result
    ));
  }
  return $results;
}


/**
 * Send PURGE request to the given $uri.
 *
 * @since 1.0
 */
function vh_purge($uri) {
  return vh_generic_purge($uri, 'PURGE');
}


/**
 * Send BAN request to the given $uri.
 *
 * @since 1.0
 */
function vh_ban($uri) {
  return vh_generic_purge($uri, 'BAN');
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
    $uri = vh_get_uri_by_url($permalink);
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
  $author_posts_url = get_author_posts_url($author_id);
  $uri = vh_get_uri_by_url($author_posts_url);
  vh_ban($uri . '.*');
}


/**
 * Purge pages of the category of the post with the given post id.
 *
 * @since 1.0
 */
function vh_purge_category($post_id) {
  $categories = get_the_category($post_id);
  if ($categories) {
    foreach ($categories as $category) {
      $uri = '/category/' . $category->slug . '/.*';
      vh_ban($uri);
    }
  }
}


/**
 * Purge pages of the tag of the post with the given post id.
 *
 * @since 1.0
 */
function vh_purge_tag($post_id) {
  $tags = get_the_tags($post_id);
  if ($tags) {
    foreach ($tags as $tag) {
      $uri = '/tag/' . $tag->slug . '/.*';
      vh_ban($uri);
    }
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
  $uri = vh_get_uri_by_url($url);
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


/**
 * Purge custom uri while post status changes.
 *
 * @since 2.0
 */
function vh_purge_while_post_status_changes() {

}


/**
 * Purge custom uri while comment status changes.
 *
 * @since 2.0
 */
function vh_purge_while_comment_status_changes() {

}


/**
 * Purge custom uri while theme switches.
 *
 * @since 1.0
 */
function vh_purge_while_theme_switches() {

}


/**
 * Get blog domain.
 *
 * @since 2.0
 */
function vh_get_domain() {
  return vh_get_location_by_url(get_bloginfo('url'));
}


/**
 * Get blog url with ssl recognition.
 *
 * @since 2.0
 */
function vh_get_blog_url() {
  return vh_get_url(vh_get_domain());
}


/**
 * Get location by the given url.
 *
 * @since 2.0
 */
function vh_get_location_by_url($url) {
  if (stripos($url, VH_SCHEME_HTTP) == 0) {
    $scheme = VH_SCHEME_HTTP;
  } else if (stripos($url, VH_SCHEME_HTTPS) == 0) {
    $scheme = VH_SCHEME_HTTPS;
  } else {
    $scheme = NULL;
  }
  return str_ireplace($scheme, '', $url);
}


/**
 * Get url by the given location with ssl recognition.
 *
 * @since 2.0
 */
function vh_get_url($location) {
  return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? VH_SCHEME_HTTPS : VH_SCHEME_HTTP) . $location;
}


/**
 * Get uri by the given url with ssl recognition.
 *
 * @since 2.0
 */
function vh_get_uri_by_url($url) {
  $location = vh_get_location_by_url($url);
  $domain = vh_get_domain();
  return str_ireplace($domain, '', $location);
}
?>