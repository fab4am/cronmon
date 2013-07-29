<?php
require_once(__DIR__.'/../basecheck.php');
class emptylog extends basecheck {

	var $desc = "Empty output";
	var $alert = "This log shouldn't be empty.";
	function check($proof, $param=null) {
		if (strlen($proof->log) == 0) return False;
		else return True;
	}
}

?>
