<?php
require_once(__DIR__.'/../basecheck.php');
class traceback extends basecheck {

	var $desc = "Traceback in log";
	var $alert = "The word 'Traceback' has been detected in this log.";
	function check($proof, $param=null) {
		if (strstr($proof->log, 'Traceback')) return False;
		else return True;
	}
}

?>
