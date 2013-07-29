<?php

class basenotif {

	var $desc = "Base notification";
	var $title;
	var $message;
	var $destinataire;
	var $sent = False;
	var $err;

	function basenotif($title=null, $message=null, $destinataire=null) {
		$this->title = $title;
		$this->message = $message;
		$this->destinataire = $destinataire;
	}

	function send() {
	}
}

?>
