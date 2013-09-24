<?php

class Option {

	var $id;
	var $cron_id;
	var $matching;
	var $check_type;
	var $check_name;
	var $param;

	function Option($id=0) {
		$this->id = $id;
		if ($this->id != 0) {
			$sql = "SELECT id, cron_id, matching, check_type, check_name, param FROM check_options WHERE id = $this->id";
			$result = mysql_query($sql);
			if ($row = mysql_fetch_object($result)) {
				$this->cron_id	= $row->cron_id;
				$this->matching	= $row->matching;
				$this->check_type	= $row->check_type;
				$this->check_name	= $row->check_name;
				$this->param	= $row->param;
			}
		}
	}

	function add() {
		$sql = "INSERT INTO check_options(cron_id, matching, check_type, check_name, param) VALUES('$this->cron_id', '".mysql_real_escape_string($this->matching)."', '$this->check_type', '$this->check_name', '".mysql_real_escape_string($this->param)."');";
		mysql_query($sql) or die($sql." - ".mysql_error());
	}

	function save() {
		$sql = "UPDATE check_options SET matching='".mysql_real_escape_string($this->matching)."', check_type='$this->check_type', check_name='$this->check_name', param='".mysql_real_escape_string($this->param)."' WHERE id=$this->id;";
		mysql_query($sql) or die($sql." - ".mysql_error());
	}

	function remove() {
		$sql = "DELETE FROM check_options WHERE id=$this->id;";
		mysql_query($sql) or die($sql." - ".mysql_error());
	}


	public static function all($forcron=False, $withglobal=False, $withmatching=False) {
		$all = array();
		$sql = "SELECT id FROM check_options ";
		if ($forcron) {
			$sql .= "WHERE cron_id=$forcron ";
			if ($withglobal) $sql .= "  OR (cron_id=0 AND matching='') ";

			if ($withmatching) {
				$cron = new Cronjob($forcron);
				$sqlchecksmatching = "SELECT id, matching FROM check_options WHERE matching != '';";
				$result = mysql_query($sqlchecksmatching) or die($sql." - ".mysql_error());
				while ($row = mysql_fetch_object($result)) {
					if (preg_match($row->matching, $cron->command)) {
						$all[$row->id] = new Option($row->id);
					}
				}
			}

		} else {
		  $sql .= "WHERE cron_id = 0 ";
		  if (!$withmatching) $sql .= " AND matching = ''";
		}
		$result = mysql_query($sql) or die($sql." - ".mysql_error());
		while ($row = mysql_fetch_object($result)) {
			$all[$row->id] = new Option($row->id);
		}
		return $all;
	}
}

?>
