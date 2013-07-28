<?php

require_once(__DIR__.'/../basecheck.php');
class always extends basecheck {

	var $desc = "Always";
	var $alert = "This cron always triggers notification.";
	function check($object, $param=null) {
		return false;
	}
}

?>
