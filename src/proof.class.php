<?php

class Proof {

	var $id;
	var $exec;
	var $datetime;
	var $type;
	var $log;
	var $error;
	var $status; // 0 = not checked yet, 1 = checked and OK, 2 = check and not OK

	function Proof($id=0) {
		$this->id = $id;
		if ($this->id != 0) {
			$sql = "SELECT * FROM proof WHERE id = $this->id";
			$result = mysql_query($sql);
			if ($row = mysql_fetch_object($result)) {
				$this->datetime = new Datetime($row->datetime);
				$this->type	= $row->type;
				$this->exec	= new Execution($row->exec_id);
				$this->log	= $row->log;
				$this->status   = $row->status;
				$this->error   = $row->error;
			}
		}
	}

	function save() {
		$sql = "INSERT INTO proof (
				datetime, 
				type, 
				log, 
				exec_id 
			) VALUES (
				'".$this->datetime->format("Y-m-d H:i:s")."', 
				'$this->type', 
				'".mysql_real_escape_string($this->log)."',
				".$this->exec->id."
			)";
		$req = mysql_query($sql) or die($sql." - ".mysql_error());
		$this->id = mysql_insert_id();
		return $this->id;
	}

	function delete() {
		$sql = "DELETE FROM proof WHERE id = $this->id";
		mysql_query($sql);
	}

	function check($mark=true) {
		$options = $this->exec->cron->getOptions();
		if (!in_array('proof', array_keys($options))) return true;
		$result = true;
		$this->error = '';
		if ($mark) $this->status = 1;
		foreach($options['proof'] as $check) {
			if (!$check->result($this)) {
				$result = false;
				$this->error .= $check->check->alert."<br />";
				if ($mark) $this->status = 2;
			}
		}
		if ($mark) {
			$sql = "UPDATE proof SET status=$this->status, error='".mysql_real_escape_string($this->error)."' WHERE id=$this->id";
			mysql_query($sql);
		}
		return $result;
	}

	function acknowledge() {
		$sql = "UPDATE proof SET status=3 WHERE status=2 AND id=$this->id";
		mysql_query($sql);
	}

}

?>
