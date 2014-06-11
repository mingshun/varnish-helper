<?php
/**
 * Plugin options page.
 *
 * @since 1.0
 */


require_once('generic-purges.php');

define('VH_OPTION_SLUG', 'varnish-helper');
define('VH_EDGE_NODES', 'varnish_helper_edge_nodes');
define('VH_AUTO_CLEAN_TASKS', 'varnish_helper_auto_clean_tasks');


/**
 * Create option menu for Varnish Helper.
 *
 * @since  1.0
 */
function vh_create_option_menu() {
  $page_hook_suffix = add_submenu_page('options-general.php', 'Varnish 助手', 'Varnish 助手', 'administrator', VH_OPTION_SLUG, 'vh_setting_page');
  add_action('load-' . $page_hook_suffix, 'vh_admin_styles');
  add_action('admin_print_scripts-' . $page_hook_suffix, 'vh_admin_scripts');

  add_action('admin_init', 'vh_register_settings');
}
add_action('admin_menu', 'vh_create_option_menu');


/**
 * Register plugin settings.
 *
 * @since 1.0
 */
function vh_register_settings() {
  register_setting('varnish-helper-settings', VH_AUTO_CLEAN_TASKS);
  if (!get_option(VH_AUTO_CLEAN_TASKS)) {
    $list = vh_get_default_auto_clean_task_list();
    $json = json_encode($list);
    add_option(VH_AUTO_CLEAN_TASKS, $json);
  }

  register_setting('varnish-helper-settings', VH_EDGE_NODES);
  if (!get_option(VH_EDGE_NODES)) {
    $list = vh_get_default_edge_node_list();
    $json = json_encode($list);
    add_option(VH_EDGE_NODES, $json);
  }
}


/**
 * Return edge node list.
 *
 * @since 2.0
 */
function vh_get_edge_node_list() {
  $json = get_option(VH_EDGE_NODES);
  $list = json_decode($json, true);
  return $list;
}


/**
 * Update edge node list.
 *
 * @since 2.0
 */
function vh_update_edge_node_list($list) {
  $json = json_encode($list);
  update_option(VH_EDGE_NODES, $json);
}


/**
 * Add edge node.
 *
 * @since 2.0
 */
function vh_add_edge_node($node) {
  $json = get_option(VH_EDGE_NODES);
  $nodes = json_decode($json, true);
  array_push($nodes, $node);
  $json = json_encode($nodes);
  return update_option(VH_EDGE_NODES, $json);
}


/**
 * Delete edge node.
 *
 * @since 2.0
 */
function vh_del_edge_node($uuid) {
  $json = get_option(VH_EDGE_NODES);
  $nodes = json_decode($json, true);
  for ($i = 0; $i < count($nodes); ++$i) {
    if ($nodes[$i]['uuid'] == $uuid) {
      array_splice($nodes, $i, 1);
      break;
    }
  }
  $json = json_encode($nodes);
  return update_option(VH_EDGE_NODES, $json);
}


/**
 * Return default node list.
 *
 * @since 2.0
 */
function vh_get_default_edge_node_list() {
  $list = array();
  return $list;
}


/**
 * Return auto clean task list.
 *
 * @since 1.0
 */
function vh_get_auto_clean_task_list() {
  $json = get_option(VH_AUTO_CLEAN_TASKS);
  $list = json_decode($json, true);
  return $list;
}


/**
 * Update auto clean task list.
 *
 * @since 1.0
 */
function vh_update_auto_clean_task_list($list) {
  $json = json_encode($list);
  update_option(VH_AUTO_CLEAN_TASKS, $json);
}


/**
 * Add auto clean task.
 *
 * @since 1.0
 */
function vh_add_auto_clean_task($task) {
  $json = get_option(VH_AUTO_CLEAN_TASKS);
  $list = json_decode($json, true);
  array_push($list, $task);
  $json = json_encode($list);
  return update_option(VH_AUTO_CLEAN_TASKS, $json);
}


/**
 * Delete auto clean task.
 *
 * @since 1.0
 */
function vh_del_auto_clean_task($uuid) {
  $json = get_option(VH_AUTO_CLEAN_TASKS);
  $list = json_decode($json, true);
  for ($i = 0; $i < count($list); ++$i) {
    if ($list[$i]['uuid'] == $uuid) {
      array_splice($list, $i, 1);
      break;
    }
  }
  $json = json_encode($list);
  return update_option(VH_AUTO_CLEAN_TASKS, $json);
}


/**
 * Return default auto clean task list.
 *
 * @since 1.0
 */
function vh_get_default_auto_clean_task_list() {
  $list = array();
  return $list;
}


/**
 * Load stylesheets of setting page.
 *
 * @since 1.0
 */

function vh_admin_styles() {
  wp_enqueue_style('vh-style', plugins_url('vh-style.css', __FILE__), array(), '1.0.0', 'all');
}


/**
 * Load javascripts of setting page.
 *
 * @since 1.0
 */
function vh_admin_scripts() {
  wp_enqueue_script('vh-script', plugins_url('vh-script.js', __FILE__ ), 'jquery', '1.0.0', true);
}


/**
 * Display option tabs.
 *
 * @since 1.0
 */
function vh_option_tabs($current = 0) {
  global $wp_db_version;

  if ($current == 0) {
    if (isset($_GET['tab'])) {
      $current = $_GET['tab'];
    } else {
      $current = 'nodes';
    }
  }
  $tabs = array(
    'nodes' => 'Varnish 节点',
    'auto' => '自动清洗',
    'manual' => '手动清洗'
  );

  $links = array();
  foreach ($tabs as $tab => $name) {
    if ($current == $tab) {
      $links[] = '<a class="nav-tab nav-tab-active" href="?page=' . VH_OPTION_SLUG . '&tab=' . $tab . '">' . $name . '</a>';
    } else {
      $links[] = '<a class="nav-tab" href="?page=' . VH_OPTION_SLUG . '&tab=' . $tab . '">' . $name . '</a>';
    }
  }

  if ($wp_db_version >= 15477) {
    echo '<div id="nav"><h2 class="themes-php">';
    echo implode("", $links);
    echo '</h2></div>';
  } else {
    echo  implode(" | ", $links);
  }
}


/**
 * Display plugin settings page.
 *
 * @since 1.0
 */
function vh_setting_page() {
?>
<a name="top"></a>
<div class="wrap">
<?php screen_icon(); ?>
<h2>Varnish 助手</h2>
<?php
  if (isset($_POST['op'])) {
    $op = $_POST['op'];
    switch ($op) {
      case 'add_node':
        vh_handle_add_node();
        break;

      case 'manage_node':
        vh_handle_manage_node();
        break;

      case 'request_clean':
        vh_handle_manual_clean();
        break;

      case 'add_clean':
        vh_handle_add_auto_clean();
        break;

      case 'manage_clean':
        vh_handle_manage_auto_clean();
        break;

      default:
        break;
    }
  } else if (isset($_GET['op'])) {
    $op = $_GET['op'];
    switch ($op) {
    case 'delete_node':
      vh_handle_del_node();
      break;

    case 'delete_clean':
      vh_handle_del_auto_clean();
      break;

    default:
        break;
    }
  }

  vh_option_tabs();
    settings_errors();

  if (isset($_GET['tab'])) {
    $current = $_GET['tab'];
    switch ($current) {
      case 'nodes':
        vh_page_edge_node_render();
        break;

      case 'auto':
        vh_page_auto_clean_render();
        break;

      case 'manual':
        vh_page_manual_clean_render();
        break;

      default:
        return;
    }
  } else {
    vh_page_edge_node_render();
  }

?>
</div>
<?php
}


/**
 * Render setting page of edge node.
 *
 * @since 2.0
 */
function vh_page_edge_node_render() {
?>
<form method="post" action="">
  <h3>添加节点</h3>
  <table class="form-table">
    <tbody>
      <tr valign="top">
        <th scope="row">
          <label for="row_node_name">名称</label>
        </th>
        <td id="row_node_name">
             <input type="text" class="regular-text" id="node_name" name="node_name" value="新节点" required>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">
          <label for="row_node_host">主机</label>
        </th>
        <td id="row_node_host">
             <input type="text" class="regular-text code" id="node_host" name="node_host" value="127.0.0.1" required>
        </td>
      </tr>
    </tbody>
    <input type="hidden" name="op" value="add_node">
  </table>
  <p class="submit desc">
    <input type="submit" name="submit" id="submit" class="button button-primary" value="添加节点" />
  </p>
</form>
<form method="post" action="">
  <h3>管理节点</h3>
<?php
  $nodes = vh_get_edge_node_list();

  if (count($nodes) == 0) {
    echo '<i>还没有添加任何节点</i>';
    return;
  }

  vh_edge_node_form_render();
?>
</form>
<?php
}


/**
 * Return edge node manage links render.
 *
 * @since 2.0
 */
function vh_get_edge_node_manage_links_render($id) {
  if (!$id) {
    return false;
  }

  $url = add_query_arg(array(
    'op' => 'delete_node',
    'del' => $id
  ));

  $link = '<a class="button button-primary" href="' . $url . '">删除</a>';
  return $link;
}


/**
 * Render table of edge node.
 *
 * @since 2.0
 */
function vh_edge_node_table_render() {
  $nodes = vh_get_edge_node_list();

  $title_items = '<th class="column-index">序号</th>';
  $title_items .= '<th class="column-name">名称</th>';
  $title_items .= '<th class="column-host">主机</th>';
  $title_items .= '<th class="column-actions">操作</th>';

  $head_title = '<tr><th id="cb" class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all-1"></th>';
  $head_title .= $title_items . '</tr>';

  $foot_title = '<tr><th id="cb" class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all-2"></th>';
  $foot_title .= $title_items . '</tr>';

  $result = '';
  $result .= '<table class="widefat fixed" id="edge-node-table">';
  $result .= '<thead>' . $head_title . '</thead>';
  $result .= '<tfoot>' . $foot_title . '</tfoot>';
  $result .= '<tbody>';

  $count = count($nodes);
  for ($i = 0; $i < $count; ++$i) {
    $result .= '<tr' . ($i % 2 == 0 ? ' class="alternate"' : '') . '>';
    $result .= '<th class="check-column"><input id="cb-select-' . $nodes[$i]['uuid'] . '" type="checkbox" name="selected[]" value="' . $nodes[$i]['uuid'] . '"><div class="locked-indicator"></div></th>';
    $result .= '<td class="column-index">' . ($i + 1) . '</td>';
    $result .= '<td class="column-name">' . $nodes[$i]['name'] . '</td>';
    $result .= '<td class="column-host"><code>' . $nodes[$i]['host'] . '</code></td>';
    $result .= '<td class="column-actions">' . vh_get_edge_node_manage_links_render($nodes[$i]['uuid']) . '</td>';
    $result .= '</tr>';
  }

  $result .= '</tbody>';
  $result .= '</table>';

  echo $result;
}


/**
 * Render form of edge node.
 *
 * @since 2.0
 */
function vh_edge_node_form_render() {
?>
  <table class="form-table">
    <tbody>
      <?php vh_edge_node_table_render(); ?>
    </tbody>
  </table>
  <input type="hidden" name="op" value="manage_node">
  <p class="submit">
    <input type="submit" class="button button-primary" value="删除选中的节点" />
  </p>
<?php 
}


/**
 * Render setting page of auto clean.
 *
 * @since 1.0
 */
function vh_page_auto_clean_render() {
?>
<form method="post" action="">
  <h3>添加清洗任务</h3>
  <table class="form-table">
    <tbody>
      <tr valign="top">
        <th scope="row">
          <label for="row_info">清洗内容</label>
        </th>
        <td id="row_info">
          <select id="purge_method" name="purge_method">
            <option value="purge">单一清洗</option>
            <option value="ban">批量清洗</option>
          </select>
          &nbsp;&nbsp;&nbsp;
          <code><?php bloginfo('url'); ?></code>
          <input type="text" class="regular-text code" id="purge_uri" name="purge_uri" value="/" required>
          <code id="ban-wildcard">.*</code>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">
          <label for="row_timing">清洗时机</label>
        </th>
        <td id="row_timing">
          <select id="purge_timing" name="purge_timing">
            <option value="at_post_change">文章/页面状态变更</option>
            <option value="at_comment_change">有新评论/评论状态变更</option>
            <option value="at_theme_switch">主题切换</option>
          </select>
        </td>
      </tr>
    </tbody>
    <input type="hidden" name="op" value="add_clean">
  </table>
  <p class="submit desc">
    <input type="submit" name="submit" id="submit" class="button button-primary" value="添加清洗任务" />
  </p>
</form>
<form method="post" action="">
  <h3>管理清洗任务</h3>
<?php
  $list = vh_get_auto_clean_task_list();

  if (count($list) == 0) {
    echo '<i>还没有添加任何自动清洗任务</i>';
    return;
  }

  vh_auto_clean_form_render();
?>
</form>
<?php
}


/**
 * Return auto clean method render.
 *
 * @since 1.0
 */
function vh_get_auto_clean_method_render($method) {
  if (!strcasecmp($method, 'PURGE')) {
    return '单一清洗';
  } else if (!strcasecmp($method, 'BAN')) {
    return '批量清洗';
  }
  return false;
}


/**
 * Return auto clean timing render.
 *
 * @since 1.0
 */
function vh_get_auto_clean_timing_render($timing) {
  switch ($timing) {
    case 'at_post_change':
      return '文章/页面状态变更';

    case 'at_comment_change':
      return '有新评论/评论状态变更';

    case 'at_theme_switch':
      return '主题切换';

    default:
      return false;
  }
}


/**
 * Return auto clean last clean time.
 *
 * @since 1.0
 */
function vh_get_auto_clean_last_clean_render($time) {
  if (!$time) {
    return '<i>从不</i>';
  }

  return strftime('%Y-%m-%d %H-%M-%S', $time);
}


/**
 * Return auto clean manage links render.
 *
 * @since 1.0
 */
function vh_get_auto_clean_manage_links_render($id) {
  if (!$id) {
    return false;
  }

  $url = add_query_arg(array(
    'op' =>'delete_clean',
    'del' => $id
  ));

  $link = '<a class="button button-primary" href="' . $url . '">删除</a>';
  return $link;
}


/**
 * Render task table of auto clean.
 *
 * @since 1.0
 */
function vh_auto_clean_table_render() {
  $list = vh_get_auto_clean_task_list();

  $title_items = '<th class="column-index">序号</th>';
  $title_items .= '<th class="column-method">清洗方法</th>';
  $title_items .= '<th class="column-uri">URI</th>';
  $title_items .= '<th class="column-timing">清洗时机</th>';
  $title_items .= '<th class="column-time">最后清洗时间</th>';
  $title_items .= '<th class="column-status">最后清洗结果</th>';
  $title_items .= '<th class="column-actions">操作</th>';

  $head_title = '<tr><th id="cb" class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all-1"></th>';
  $head_title .= $title_items . '</tr>';

  $foot_title = '<tr><th id="cb" class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all-2"></th>';
  $foot_title .= $title_items . '</tr>';

  $result = '';
  $result .= '<table class="widefat fixed" id="auto-clean-table">';
  $result .= '<thead>' . $head_title . '</thead>';
  $result .= '<tfoot>' . $foot_title . '</tfoot>';
  $result .= '<tbody>';

  $count = count($list);
  for ($i = 0; $i < $count; ++$i) {
    $result .= '<tr' . ($i % 2 == 0 ? ' class="alternate"' : '') . '>';
    $result .= '<th class="check-column"><input id="cb-select-' . $list[$i]['uuid'] . '" type="checkbox" name="selected[]" value="' . $list[$i]['uuid'] . '"><div class="locked-indicator"></div></th>';
    $result .= '<td class="column-index">' . ($i + 1) . '</td>';
    $result .= '<td class="column-method">' . vh_get_auto_clean_method_render($list[$i]['method']) . '</td>';
    $result .= '<td class="column-uri"><code>' . $list[$i]['uri'] . '</code></td>';
    $result .= '<td class="column-timing">' . vh_get_auto_clean_timing_render($list[$i]['timing']) . '</td>';
    $result .= '<td class="column-time">' . vh_get_auto_clean_last_clean_render($list[$i]['last_clean']) . '</td>';
    $result .= '<td class="column-status">' . $list[$i]['last_status'] . '</td>';
    $result .= '<td class="column-actions">' . vh_get_auto_clean_manage_links_render($list[$i]['uuid']) . '</td>';
    $result .= '</tr>';
  }

  $result .= '</tbody>';
  $result .= '</table>';

  echo $result;
}


/**
 * Render task form of auto clean.
 *
 * @since 1.0
 */
function vh_auto_clean_form_render() {
?>
  <table class="form-table">
    <tbody>
      <?php vh_auto_clean_table_render(); ?>
    </tbody>
  </table>
  <input type="hidden" name="op" value="manage_clean">
  <p class="submit">
    <input type="submit" class="button button-primary" value="删除选中的清洗任务" />
  </p>
<?php 
}


/**
 * Render setting page of manual clean.
 *
 * @since 1.0
 */
function vh_page_manual_clean_render() {
?>
<form method="post" action="" id="purge-request-form">
  <table class="form-table">
    <tbody>
      <tr valign="top">
        <th scope="row">
          <label for="purge_info">清洗内容</label>
        </th>
        <td id="purge_info">
          <select id="purge_method" name="purge_method">
            <option value="purge">单一清洗</option>
            <option value="ban">批量清洗</option>
          </select>
          &nbsp;&nbsp;&nbsp;
          <code><?php bloginfo('url'); ?></code>
          <input type="text" class="regular-text code" id="purge_uri" name="purge_uri" value="/" required>
          <code id="ban-wildcard">.*</code>
        </td>
      </tr>
    </tbody>
    <input type="hidden" name="op" value="request_clean">
  </table>
  <p class="submit">
    <input type="submit" class="button button-primary" value="立即清洗" />
  </p>
</form>
<?php
}


/**
 * Handle adding edge node.
 *
 * @since 2.0
 */
function vh_handle_add_node() {
  $node_name = $_POST['node_name'];
  $node_host = $_POST['node_host'];

  if (!$node_name) {
    add_settings_error('varnish-helper-settings', 'auto', '无效名称', 'error');
    return;
  }

  $host_regex = array();
  array_push($host_regex, '/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/');
  array_push($host_regex, '/^([a-zA-Z0-9][a-zA-Z0-9\-\_]+[a-zA-Z0-9]\.)?[a-zA-Z0-9][a-zA-Z0-9\-\_]+[a-zA-Z0-9]\.[a-z]{2,7}$/');
  array_push($host_regex, '/^localhost$/');

  $host_validate = false;
  for ($i = 0; $i < count($host_regex); ++$i) {
    if (preg_match($host_regex[$i], $node_host)) {
      $host_validate = TRUE;
      break;
    }
  }
  if (!$host_validate) {
    add_settings_error('varnish-helper-settings', 'auto', '无效主机：<span class="code">' . $node_host . '</span>，必须为有效的 IP 或域名！', 'error');
    return;
  }
  

  $edge_node = array(
    'uuid' => uniqid('vh_edge_node_'),
    'name' => $node_name,
    'host' => $node_host
  );

  if (!vh_add_edge_node($edge_node)) {
    add_settings_error('varnish-helper-settings', 'auto', '节点添加失败！', 'error');
  }
  add_settings_error('varnish-helper-settings', 'auto', '节点添加成功！', 'updated');
}


/**
 * Handle removing edge node.
 *
 * @since 2.0
 */
function vh_handle_del_node() {
  $uuid = $_GET['del'];

  if (!vh_del_edge_node($uuid)) {
    add_settings_error('varnish-helper-settings', 'auto', '节点删除失败！', 'error');
    return;
  }
  add_settings_error('varnish-helper-settings', 'auto', '节点删除成功！', 'updated');
}


/**
 * Handle managing edge node.
 *
 * @since 2.0
 */
function vh_handle_manage_node() {
  $selected = $_POST['selected'];
  foreach ($selected as $element) {
    if (!vh_del_edge_node($element)) {
      add_settings_error('varnish-helper-settings', 'auto', '节点删除失败！', 'error');
      return;
    }
  }
  add_settings_error('varnish-helper-settings', 'auto', '节点删除成功！', 'updated');
}


/**
 * Show manual clean results.
 *
 * @since 2.0
 */
function vh_show_manual_clean_results($action, $uri, $results) {
  $prefix = $action . '<code>' . $uri . '</code>';
  if (!$results) {
    add_settings_error('varnish-helper-settings', 'manual', $prefix . '没有执行结果！', 'error');
    return;

  } else {
    $prefix = $action . '<code>' . $uri;
  }

  for ($i = 0; $i < count($results); ++$i) {
    $item = $results[$i];
    $message = $prefix;
    $message .= '@' . $item['host'] . '</code> -> ';
    if (is_wp_error($item['result'])) {
      $message .= '执行失败，原因：' . $item['result']->get_error_message();
      add_settings_error('varnish-helper-settings', 'manual', $message, 'error');
      
    } else {
      if ($item['result']['response']['code'] == 200) {
        $message .= '执行成功';
        add_settings_error('varnish-helper-settings', 'manual', $message, 'updated');

      } else {
        $message .= '执行失败，原因：' . $item['result']['response']['message'];
        add_settings_error('varnish-helper-settings', 'manual', $message, 'error');
      }
    }
  }
}


/**
 * Handle manual purge.
 *
 * since 1.0
 */
function vh_handle_manual_clean() {
  $method = $_POST['purge_method'];
  $uri = $_POST['purge_uri'];

  if (!strcasecmp($method, 'PURGE')) {
    $results = vh_purge($uri);
    vh_show_manual_clean_results('清洗', $uri, $results);

  } else if (!strcasecmp($method, 'BAN')) {
    $uri_suffix = '.*';
    $uri .= (substr($uri, -strlen($uri_suffix)) == $uri_suffix) ? '' : $uri_suffix;
    $results = vh_ban($uri);
    vh_show_manual_clean_results('批量清洗', $uri, $results);

  } else {
    add_settings_error('varnish-helper-settings', 'manual', '无效的清洗方法！', 'error');
  }
}


/**
 * Handle adding auto clean task.
 *
 * @since 1.0
 */
function vh_handle_add_auto_clean() {
  $method = $_POST['purge_method'];
  $uri = $_POST['purge_uri'];
  $timing = $_POST['purge_timing'];

  if (!strcasecmp($method, 'PURGE')) {
    $method = 'purge';

  } else if (!strcasecmp($method, 'BAN')) {
    $method = 'ban';
    $uri_suffix = '.*';
    $uri .= (substr($uri, -strlen($uri_suffix)) == $uri_suffix) ? '' : $uri_suffix;

  } else {
    add_settings_error('varnish-helper-settings', 'auto', '无效的清洗方法！', 'error');
    return;
  }

  if (!vh_get_auto_clean_timing_render($timing)) {
    add_settings_error('varnish-helper-settings', 'auto', '无效的清洗时机！', 'error');
    return;
  }

  $clean_task = array(
    'uuid' => uniqid('vh_auto_clean_'),
    'method' => $method,
    'uri' => $uri,
    'timing' => $timing,
    'last_clean' => false,
    'last_status' => false
  );

  if (!vh_add_auto_clean_task($clean_task)) {
    add_settings_error('varnish-helper-settings', 'auto', '清洗任务添加失败！', 'error');
    return;
  }
  add_settings_error('varnish-helper-settings', 'auto', '清洗任务添加成功！', 'updated');
}


/**
 * Handle removing auto clean.
 *
 * @since 1.0
 */
function vh_handle_del_auto_clean() {
  $uuid = $_GET['del'];

  if (!vh_del_auto_clean_task($uuid)) {
    add_settings_error('varnish-helper-settings', 'auto', '清洗任务删除失败！', 'error');
    return;
  }
  add_settings_error('varnish-helper-settings', 'auto', '清洗任务删除成功！', 'updated');
}


/**
 * Handle managing auto clean.
 *
 * @since 1.0
 */
function vh_handle_manage_auto_clean() {
  $selected = $_POST['selected'];
  foreach ($selected as $element) {
    if (!vh_del_auto_clean_task($element)) {
      add_settings_error('varnish-helper-settings', 'auto', '清洗任务删除失败！', 'error');
      return;
    }
  }
  add_settings_error('varnish-helper-settings', 'auto', '清洗任务删除成功！', 'updated');
}


/**
 * Unregister setting while deactivating plugin.
 *
 * @since 1.0
 */
function vh_unregister_settings() {
  unregister_setting('varnish-helper-settings', 'varnish_helper_custom_tasks');
}
register_deactivation_hook( __FILE__, 'vh_unregister_settings');
?>