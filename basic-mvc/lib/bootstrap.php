<?php

if (!defined('E_DEPRECATED')) {
	define('E_DEPRECATED', 8192);
}
#error_reporting(E_ALL & ~E_DEPRECATED);
error_reporting(E_ALL | E_STRICT);

session_start();

require CORE_PATH . 'lib' . DS . 'functions' . DS . 'core.php';
require CORE_PATH . 'lib' . DS . 'classes' . DS . 'core.class.php';
require CORE_PATH . 'lib' . DS . 'classes' . DS . 'dispatcher.class.php';
require CORE_PATH . 'lib' . DS . 'classes' . DS . 'controller.class.php';
require CORE_PATH . 'lib' . DS . 'classes' . DS . 'model.class.php';
require CORE_PATH . 'lib' . DS . 'classes' . DS . 'view.class.php';
require CORE_PATH . 'lib' . DS . 'classes' . DS . 'config.class.php';
Config::addSessionConfigs();
require CORE_PATH . 'lib' . DS . 'classes' . DS . 'database.class.php';