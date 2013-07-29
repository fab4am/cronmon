<?php

require_once('basenotif.php');

class mail extends basenotif {

	var $desc = "Mail";
	var $title;
	var $message;
	var $destinataire;
	var $sent = False;
	var $err;

	function send($title=null, $message=null, $destinataire=null) {
		$this->title = $title;
		$this->message = $message;
		$this->destinataire = $destinataire;
		$headers = 'From: cronmon@nautile.nc' . "\r\n" .
		'Reply-To: cronmon@nautile.nc'. "\r\n";

		if (mail($this->destinataire, $this->title, $this->message, $headers)) $this->sent = True;
		else {
			$this->sent = False;
			$this->err = "Mail failed to be sent.";
		}

	}
}

?>
