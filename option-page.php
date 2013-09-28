<?php
/**
 * Plugin options page.
 *
 * @since 1.0
 */


require_once('generic-purges.php');

define('VH_OPTION_SLUG', 'varnish-helper');
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
      $current = 'auto';
    }
  }
  $tabs = array(
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
  } else if (isset($_GET['del'])) {
    vh_handle_del_auto_clean();
  }

  vh_option_tabs();
    settings_errors();

  if (isset($_GET['tab'])) {
    $current = $_GET['tab'];
    switch ($current) {
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
    vh_page_auto_clean_render();
  }

?>
</div>
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
  <h3>添加自动清洗</h3>
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
          <input type="text" class="regular-text" id="purge_uri" name="purge_uri" value="/">
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
    <input type="submit" name="submit" id="submit" class="button button-primary" value="添加自动清洗" />
  </p>
</form>
<form method="post" action="">
  <h3>管理自动清洗</h3>
<?php
  $list = vh_get_auto_clean_task_list();

  if (count($list) == 0) {
    echo '<i>没有自动清洗任务</i>';
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

  return $time;
}


/**
 * Return auto clean last clean status.
 *
 * @since 1.0
 */
function vh_get_auto_clean_last_status_render($status) {
  if (!$status) {
    return '-';
  }

  if ($status == 'wp error') {
    return '<span style="color: red;">清洗失败</span>';
  }

  if ($status['code'] >= 300) {
    return '<span style="color: red;">清洗失败(' . $status['message'] . ')</span>';
  } else {
    return '<span style="color: green;">清洗成功</span>';
  }
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

  $head_title = '<tr><th id="cb" class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all-1"></th>';
  $head_title .= '<th>序号</th><th>清洗方法</th><th>URI</th><th>清洗时机</th><th>最后清洗时间</th><th>最后清洗结果</th><th>操作</th></tr>';

  $foot_title = '<tr><th id="cb" class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all-2"></th>';
  $foot_title .= '<th>序号</th><th>清洗方法</th><th>URI</th><th>清洗时机</th><th>最后清洗时间</th><th>最后清洗结果</th><th>操作</th></tr>';

  $result = '';
  $result .= '<table class="widefat fixed">';
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
    $result .= '<td class="column-status">' . vh_get_auto_clean_last_status_render($list[$i]['last_status']) . '</td>';
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
    <input type="submit" class="button button-primary" value="删除选中的清洗" />
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
          <input type="text" class="regular-text" id="purge_uri" name="purge_uri" value="/">
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
 * Handle manual purge.
 *
 * since 1.0
 */
function vh_handle_manual_clean() {
  $method = $_POST['purge_method'];
  $uri = $_POST['purge_uri'];

  if (!strcasecmp($method, 'PURGE')) {
    $result = vh_purge($uri);
    if (is_wp_error($result)) {
      add_settings_error('varnish-helper-settings', 'manual', $result->get_error_message(), 'error');
    } else {
      if ($result['response']['code'] == 200) {
        add_settings_error('varnish-helper-settings', 'manual', '清洗“' . $uri . '”执行成功。', 'updated');
      } else {
        add_settings_error('varnish-helper-settings', 'manual', '清洗“' . $uri . '”执行失败，原因：' . $result['response']['message'] . '。', 'error');
      }
    }

  } else if (!strcasecmp($method, 'BAN')) {
    $uri_suffix = '.*';
    $uri .= (substr($uri, -strlen($uri_suffix)) == $uri_suffix) ? '' : $uri_suffix;
    $result = vh_ban($uri);
    if (is_wp_error($result)) {
      add_settings_error('varnish-helper-settings', 'manual', $result->get_error_message(), 'error');
    } else {
      if ($result['response']['code'] == 200) {
        add_settings_error('varnish-helper-settings', 'manual', '批量清洗“' . $uri . '”执行成功。', 'updated');
      } else {
        add_settings_error('varnish-helper-settings', 'manual', '批量清洗“' . $uri . '”执行失败，原因：' . $result['response']['message'] . '。', 'error');
      }
    }

  } else {
    add_settings_error('varnish-helper-settings', 'manual', '无效的清洗方法！', 'error');
  }
}


/**
 * Add auto clean task.
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
    add_settings_error('varnish-helper-settings', 'auto', '添加自动清洗失败！', 'error');
    return;
  }
  add_settings_error('varnish-helper-settings', 'auto', '添加自动清洗成功！', 'updated');
}


/**
 * Handle remove auto clean.
 *
 * @since 1.0
 */
function vh_handle_del_auto_clean() {
  $uuid = $_GET['del'];

  if (!vh_del_auto_clean_task($uuid)) {
    add_settings_error('varnish-helper-settings', 'auto', '删除自动清洗失败！', 'error');
    return;
  }
  add_settings_error('varnish-helper-settings', 'auto', '删除自动清洗成功！', 'updated');
}


/**
 * Handle manage auto clean.
 *
 * @since 1.0
 */
function vh_handle_manage_auto_clean() {
  $selected = $_POST['selected'];
  foreach ($selected as $element) {
    if (!vh_del_auto_clean_task($element)) {
      add_settings_error('varnish-helper-settings', 'auto', '删除自动清洗失败！', 'error');
      return;
    }
  }
  add_settings_error('varnish-helper-settings', 'auto', '删除自动清洗成功！', 'updated');
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


/**
 * Do clean.
 *
 * @since 1.0
 */
function vh_do_clean($timing) {
  $list = vh_get_auto_clean_task_list();
  $count = count($list);
  $done = false;

  for($i = 0; $i < $count; ++$i) {
    if ($list[$i]['timing'] == $timing) {

      if ($list[$i]['method'] == 'purge') {
        $result = vh_purge($list[$i]['uri']);

        $list[$i]['last_clean'] = current_time('mysql');
        if (is_wp_error($result)) {
          $list[$i]['last_status'] = 'wp error';

        } else {
          $list[$i]['last_status'] = array(
            'code' => $result['response']['code'],
            'message' => $result['response']['message']
          );
        }

        $done = true;

      } else if ($list[$i]['method'] == 'ban') {
        $result = vh_ban($list[$i]['uri']);

        $list[$i]['last_clean'] = current_time('mysql');
        if (is_wp_error($result)) {
          $list[$i]['last_status'] = 'wp error';
          
        } else {
          $list[$i]['last_status'] = array(
            'code' => $result['response']['code'],
            'message' => $result['response']['message']
          );
        }

        $done = true;
      }
    }
  }

  if ($done) {
    vh_update_auto_clean_task_list($list);
  }
}


/**
 * Do clean when post status changed.
 *
 * @since 1.0
 */
function vh_do_clean_when_post_status_changed($new_status, $old_status, $post) {
  if ($new_status == 'publish' || $old_status == 'publish') {
    vh_do_clean('at_post_change');
  }
}


/**
 * Do clean when comment posted.
 *
 * @since 1.0
 */
function vh_do_clean_when_comment_posted($comment_id) {
  vh_do_clean('at_comment_change');
}


/**
 * Do clean when comment status changed.
 *
 * @since 1.0
 */
function vh_do_clean_when_comment_status_changed($new_status, $old_status, $comment) {
  if ($new_status == 'approved' || $old_status == 'approved') {
    vh_do_clean('at_comment_change');
  }
}


/**
 * Do clean when theme switched.
 *
 * @since 1.0
 */
function vh_do_clean_when_theme_switched() {
  vh_do_clean('at_theme_switch');
}


// Purge when post status changed.
add_action('transition_post_status', 'vh_do_clean_when_post_status_changed', 99, 3);

// Purge when there is a comment or the existing comment status changes.
add_action('comment_post', 'vh_do_clean_when_comment_posted', 99);
add_action('transition_comment_status', 'vh_do_clean_when_comment_status_changed', 99, 3);

// Purge when switching theme.
add_action('switch_theme', 'vh_do_clean_when_theme_switched', 99);
?>