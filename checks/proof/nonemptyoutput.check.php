<?php
require_once(__DIR__.'/../basecheck.php');
class nonemptyoutput extends basecheck {

	var $desc = "Non empty output";
	var $alert = "This log should be empty.";
	function check($proof, $param=null) {
		if (strlen($proof->log) != 0) return False;
		else return True;
	}
}

?>
