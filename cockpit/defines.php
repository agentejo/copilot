<?php
// load custom config
$_sitedir    = dirname(__DIR__);
$_configpath = $_sitedir."/config/config.".(file_exists($_sitedir."/config/config.php") ? 'php':'yaml');

define('COCKPIT_CONFIG_PATH', $_configpath);
define('COCKPIT_STORAGE_FOLDER' , dirname(__DIR__) . '/storage');