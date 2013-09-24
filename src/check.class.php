<?php

class check {
	var $name;
	var $matching;
	var $type;
	var $check;
	var $param;

	function check($type, $name, $param=null, $matching=null) {
		if (!file_exists(__DIR__."/../checks/$type/$name.check.php")) return false;
		$this->name = $name;
		$this->type = $type;
		$this->param = $param;
		$this->matching = $matching;
	}

	function result($object) {
		require_once(__DIR__."/../checks/$this->type/$this->name.check.php");
		$name = $this->name;
		$this->check = new $name();
		return $this->check->check($object, json_decode($this->param));
	}

	public static function getAllOptions() {
		$checks = array();
		$dirs = scandir(__DIR__."/../checks/");
		foreach($dirs as $dir) {
			if ((is_dir(__DIR__."/../checks/$dir"))&&(substr($dir, 0, 1) != ".")) {
				#$checks[$dir] = array();
				$files = scandir(__DIR__."/../checks/$dir");
				foreach($files as $file) {
					if ((substr($file, -10) == ".check.php") && (is_file(__DIR__."/../checks/$dir/$file"))) {
						require_once(__DIR__."/../checks/$dir/$file");
						$class = substr($file, 0, -10);
						$checks[$dir][$class] = new $class();
					}
				}
			}
		}
		return $checks;
	}
}

?>
