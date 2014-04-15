<?php

/**
 * ESI widget control.
 *
 * @since 1.0
 */
function esi_widget_control() {
  echo '<p>Place widgets in Varnish ESI Sidebar.</p>';
}


/**
 * ESI sidebar widget function.
 *
 * @since 1.0
 */
function esi_widget($args) {
  $esi_server_https_state = $_SERVER['HTTPS'];
  echo $before_widget;
  echo '<!--esi ';
  $_SERVER['HTTPS'] = 'off';
  echo '<esi:include src="'. plugin_dir_url(__FILE__) . 'esi-sidebar.php" />';
  $_SERVER['HTTPS'] = $esi_server_https_state;
  echo ' -->';
  echo $after_widget;
}


/**
 * Set up ESI widget sidebar.
 *
 * @since 1.0
 */
function esi_widget_setup() {
  if (!function_exists('wp_register_sidebar_widget') ||
      !function_exists('wp_register_widget_control')) {
    return;
  }

  wp_register_sidebar_widget('esi-sidebar', 'Varnish ESI Widget', 'esi_widget');
  wp_register_widget_control('esi-sidebar', 'Varnish ESI Widget', 'esi_widget_control');

  if (function_exists('register_sidebar')) {
    register_sidebar(array(
      'name'            => 'Varnish ESI Sidebar',
      'id'              => 'esi-sidebar',
      'description'     => 'Varnish Edge Side Include Sidebar',
      'class'           => '',
      'before_widget'   => '<div class="well widget-box clearfix">',
      'after_widget'    => '</div>',
      'before_title'    => '<h4 class="widget-title">',
      'after_title'     => '</h4>'
    ));
  }
}

add_action('after_setup_theme', 'esi_widget_setup');
?>