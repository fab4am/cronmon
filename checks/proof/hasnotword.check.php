<?php
require_once(__DIR__.'/../basecheck.php');
class hasnotword extends basecheck {

	var $desc = "Defined word not in log message";
	var $alert = "The word '' has not been detected in this log.";
	var $param;
	var $withparam = True;
	function check($proof, $param) {
		$this->param = $param;
		$this->alert = "The word '$this->param' has not been detected in this log.";
		if (!strstr($proof->log, $param)) return False;
		else return True;
	}
}

?>
