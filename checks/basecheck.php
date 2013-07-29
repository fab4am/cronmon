<?php

class basecheck {

	var $desc = "Base check";
	var $alert = "";
	var $param;
	var $withparam = False;
	function check($object, $param) {
		return true; // check passed - no problem detected
		return false; // check failed problem detected
	}
}

?>
