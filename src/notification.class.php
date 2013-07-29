<?php

class notification {
	var $id;
	var $name;
	var $title;
	var $message;
	var $destinataire;
	var $plugin;

	function notification($id=null) {
		if ($id != null) {
			$sql = "SELECT * from notification WHERE id=$id;";
			$result = mysql_query($sql) or die($sql." - ".mysql_error());
			$row = mysql_fetch_object($result);
			$this->id = $row->id;
			$this->name = $row->name;
			$this->title = $row->title;
			$this->message = $row->message;
			$this->destinataire = $row->destinataire;
			if (!file_exists(__DIR__."/../notifications/$row->name.notif.php")) return false;
			require_once(__DIR__."/../notifications/$this->name.notif.php");
			$name = $this->name;
			$this->plugin = new $name();
		}
	}

	function setVariables($error) {
		foreach(array_keys(get_object_vars($error)) as $var) {
			$this->title = str_replace("[%$var%]", $error->$var, $this->title);
			$this->message = str_replace("[%$var%]", $error->$var, $this->message);
		}
	}

	function send() {
		return $this->plugin->send($this->title, $this->message, $this->destinataire);
	}

	function add() {
		if (!file_exists(__DIR__."/../notifications/$this->name.notif.php")) return false;
		$sql = "INSERT INTO notification(name, title, message, destinataire) VALUES('$this->name', '".mysql_real_escape_string($this->title)."', '".mysql_real_escape_string($this->message)."', '".mysql_real_escape_string($this->destinataire)."');";
		mysql_query($sql) or die($sql." - ".mysql_error());
	}

	function del() {
		$sql = "DELETE FROM notification WHERE id=$this->id";
		mysql_query($sql)or die($sql." - ".mysql_error());
	}

	public static function getAllNotifications() {
		$sql = "SELECT id from notification";
		$result = mysql_query($sql) or die($sql." - ".mysql_error());
		$notifs = array();
		while ($row = mysql_fetch_object($result)) {
			$notifs[] = new notification($row->id);
		}
		return $notifs;
	}


	public static function getAllNotificationPlugins() {
		$notifs = array();
		$files = scandir(__DIR__."/../notifications/");
		foreach($files as $file) {
			if ((substr($file, -10) == ".notif.php") && (is_file(__DIR__."/../notifications/$file"))) {
				require_once(__DIR__."/../notifications/$file");
				$class = substr($file, 0, -10);
				$notifs[$class] = new $class();
			}
		}
		return $notifs;
	}

	public static function sendAll($error) {
		foreach(notification::getAllNotifications() as $notification) {
			$notification->setVariables($error);
			$notification->send();
		}
	}
}

?>
