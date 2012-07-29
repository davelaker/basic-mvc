<?php
/**
 * App ENV can be set in a htaccess file or in http setup (httpd.conf or individual vhost)
 * If none set then production is assumed
 *
 * Example: SetEnv APP_ENV "development"
 */
if (!defined('APP_ENV')) {
  if (!empty($_SERVER['APP_ENV'])) {
    define('APP_ENV', $_SERVER['APP_ENV']);
  } else {
    define('APP_ENV', 'production');
  }
}

if (!defined('DS')) {
  define('DS', DIRECTORY_SEPARATOR);
}

if (!defined('ROOT')) {
  define('ROOT', dirname(dirname(__FILE__)));
}

if (!defined('APP_DIR')) {
  define('APP_DIR', basename(dirname(__FILE__)));
}

/**
 * START EDITABLE AREA
 *
 * Add user defined constants and variables below
 *
 */
switch (APP_ENV) {
  case 'dev_dave':
    define('CORE_INCLUDES_PATH', ROOT . DS . 'basic-mvc');
    define('SITE_ROOT', 'http://localhost/basic-mvc/');
    break;
  case 'production':
  case 'development':
  default:
    define('CORE_INCLUDES_PATH', ROOT . DS . 'basic-mvc');
    define('SITE_ROOT', 'http://REMOTEURL/');
    break;
}

#echo CORE_INCLUDES_PATH . DS . '<br />' . ROOT . DS . APP_DIR . DS . '<br />' . ROOT . DS . '<br />' . ini_get('include_path');
/**
 * END EDITABLE AREA
 */

/**
 * Do not edit below this line
 */

if (function_exists('ini_set')) {
  ini_set('include_path', CORE_INCLUDES_PATH . DS . PATH_SEPARATOR . ROOT . DS . APP_DIR . DS . PATH_SEPARATOR . ROOT . DS . PATH_SEPARATOR . ini_get('include_path'));
  define('APP_PATH', null);
  define('CORE_PATH', null);
}
else {
  define('APP_PATH', ROOT . DS . APP_DIR . DS);
  define('CORE_PATH', CORE_INCLUDES_PATH . DS);
}

if (!include(CORE_PATH . 'lib' . DS . 'bootstrap.php')) {
  trigger_error("Could not locate required setup files", E_USER_ERROR);
}

Config::write('_mvc_page_start_time', getMicrotime());

if (isset($_GET['url']) && $_GET['url'] === 'favicon.ico') {
  return;
} else {
  $Dispatcher = new Dispatcher();
  $url = (!empty($_GET['url'])) ? $_GET['url'] : '';
  #@todo clean up url to only allow url chars
  $Dispatcher->generatePage($url);
}

if(Config::read('debug') == true) {
  Config::write('_mvc_page_end_time', getMicrotime());
  ?>
<style type="text/css">
  #_mvc_toggle_debug {
    position:absolute;
    top: 5px;
    right: 5px;
    z-index: 10000;
    padding: 2px 5px 5px 4px;
    cursor: pointer;
    border: 1px solid #000;
    border-radius: 6px;
    box-shadow: 3px 3px 15px #888;
    background: #700;
    color: #eee;

  }
  #_mvc_errorBox {
    z-index: 9999;
    position:absolute;
    top: 5px;
    left: 5px;
    margin-right: 5px;
    border: 1px solid #000;
    border-radius: 6px;
    box-shadow: 3px 3px 15px #888;
    background: #eee;
    padding: 10px;
    font-size: 12px;
    font-family: helvetica, arial, sans-serif;
    width:auto;
  }
  #_mvc_errorBox h1 {
    font-size: 16px;
    text-shadow: 2px 2px 6px #000;
  }
  #_mvc_errorBox table {
    border: 0px;
  }
  #_mvc_errorBox table td {
    font-size: 11px;
    padding:2px 3px 0 0;
  }
</style>
<script type="text/javascript">
  function toggleDebug () {
    var toggleElement = document.getElementById('_mvc_toggle_debug');
    var debugElement = document.getElementById('_mvc_errorBox');

    if(toggleElement.className == 'open') {
      debugElement.style.display = 'none';
      toggleElement.className = 'closed';
      toggleElement.innerHTML = '[ + ]';
    }
    else {
      debugElement.style.display = '';
      toggleElement.className = 'open';
      toggleElement.innerHTML = '[ - ]';
    }
  }
</script>
<div id="_mvc_toggle_debug" class="open" onclick="toggleDebug();">
  [ - ]
</div>
<div id="_mvc_errorBox">
<p>
  Page Load Time: <?php echo round(Config::read('_mvc_page_end_time') - Config::read('_mvc_page_start_time'), 2); ?>
  <?php $errors = Config::read('_mvc_errors');
  if(!empty($errors)) : ?>
    <h1>Errors: </h1>
    <ul>
      <?php foreach($errors as $error) : ?>
      <li><?php echo $error; ?></li>
      <?php endforeach; ?>
    </ul>
    <?php endif;
  if(Config::read('db_name') != '') :
    $db = Database::getInstance();
    ?>
    <h1>Queries: </h1>
    <p>There were a total of <?php echo $db->getQueriesCount(); ?> queries (<?php echo $db->getTotalExecutionTime(); ?>s)</p>
    <?php if($db->getQueriesCount() > 0) : ?>
    <table>
      <thead>
      <tr>
        <td>Query</td>
        <td>Time Taken</td>
      </tr>
      </thead>
      <tbody>
        <?php
        $queries = $db->getQueries();
        foreach($queries as $query) : ?>
        <tr>
          <td><?php echo $query['query']; ?></td>
          <td><?php echo sprintf("%01.6f", $query['time']); ?>s</td>
        </tr>
          <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif;
  endif; ?>
  </p>
</div>
<?php
}