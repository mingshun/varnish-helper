<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
  exit ();
}

unregister_sidebar('varnish-esi-sidebar');
?>