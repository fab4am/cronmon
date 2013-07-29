<?php

require_once(__DIR__.'/../basecheck.php');
class devnull extends basecheck {

	var $desc = "redirection to /dev/null";
	var $alert = "This cron redirects output to /dev/null.";
	function check($object, $param=null) {
		if (strpos($object->command, '/dev/null')) return false;
		else return true;
	}
}

?>
