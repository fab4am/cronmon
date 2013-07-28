<?php

$config = array();
include(dirname(__FILE__).'/config.php');
include(dirname(__FILE__).'/src/proof.class.php');
include(dirname(__FILE__).'/src/unmatched.class.php');
include(dirname(__FILE__).'/src/execution.class.php');
include(dirname(__FILE__).'/src/cronjob.class.php');
include(dirname(__FILE__).'/src/check.class.php');
include(dirname(__FILE__).'/src/notification.class.php');
include(dirname(__FILE__).'/src/error.class.php');

// mysql 
$db = mysql_connect($config['db_host'], $config['db_username'], $config['db_password']);
mysql_select_db($config['db_base'] ,$db);


?>
