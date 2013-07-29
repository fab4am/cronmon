<?php
require_once(__DIR__.'/../basecheck.php');
class hasword extends basecheck {

	var $desc = "Defined word in log message";
	var $alert = "The word '' has been detected in this log.";
	var $param;
	var $withparam = True;
	function check($proof, $param) {
		$this->param = $param;
		$this->alert = "The word '$this->param' has been detected in this log.";
		if (strstr($proof->log, $param)) return False;
		else return True;
	}
}

?>
