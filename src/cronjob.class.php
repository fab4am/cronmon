<?php

class Cronjob {

	var $id;
	var $name;
	var $user;
	var $host;
	var $command;
	var $schedule;
	var $options = array();
	var $executions;
	var $error = "";

	function Cronjob($id=0) {
		$this->id = $id;
		if ($this->id != 0) {
			$sql = "SELECT name, command, user, host, schedule FROM cronjob WHERE id = $this->id";
			$result = mysql_query($sql);
			if ($row = mysql_fetch_object($result)) {
				$this->user	= $row->user;
				$this->host	= $row->host;
				$this->command	= $row->command;
				$this->name	= $row->name;
				$this->schedule	= $row->schedule;
			}
		} else {
			$this->user = "default";
			$this->host = "default";
			$this->command = "default";
			$this->name = "default";
		}
	}

	function save() {
		$sql = "INSERT INTO cronjob (
				name,
				user,
				host,
				command,
				schedule
			) VALUES (
				'".mysql_real_escape_string($this->name)."',
				'".mysql_real_escape_string($this->user)."',
				'".mysql_real_escape_string($this->host)."',
				'".mysql_real_escape_string($this->command)."',
				'".mysql_real_escape_string($this->schedule)."'
			)";
		$req = mysql_query($sql) or die($sql." - ".mysql_error());
		$this->id = mysql_insert_id();
		return $this->id;
	}

	function update() {
		$sql = "REPLACE INTO cronjob (
				id,
				name,
				user,
				host,
				command,
				schedule
			) VALUES (
				$this->id,
				'".mysql_real_escape_string($this->name)."',
				'".mysql_real_escape_string($this->user)."',
				'".mysql_real_escape_string($this->host)."',
				'".mysql_real_escape_string($this->command)."',
				'".mysql_real_escape_string($this->schedule)."'
			)";
		mysql_query($sql) or die($sql." - ".mysql_error());
	}

	function delete() {
		$sql = "DELETE FROM cronjob WHERE id = $this->id";
		mysql_query($sql);

	}

	function addExecution($date, $type, $content) {
		$exec = new Execution();
		$exec->cron = $this;
		$exec->datetime = $date;
		$exec->save();
		$exec->addProof($date, $type, $content);
	}

	function setOption($type, $name, $param=null) {
		$sql = "REPLACE INTO check_options VALUES($this->id, '$type', '$name', '$param');";
		mysql_query($sql) or die($sql." - ".mysql_error());
	}

	function delOption($type, $name, $param=null) {
		$sql = "DELETE FROM check_options WHERE cron_id=$this->id AND check_type='$type' AND check_name='$name' AND param='$param';";
		mysql_query($sql)or die($sql." - ".mysql_error());
	}

	function getOptions($mode=1) { // 1 = cron options + default options, 2 = cron options only, 3 = default options only
		if ($mode == 1) $sql = "SELECT check_type, check_name, param FROM check_options WHERE cron_id=$this->id OR cron_id=0;";
		elseif ($mode==2) $sql = "SELECT check_type, check_name, param FROM check_options WHERE cron_id=$this->id;";
		elseif ($mode==3) $sql = "SELECT check_type, check_name, param FROM check_options WHERE cron_id=0;";
		$result = mysql_query($sql) or die($sql." - ".mysql_error());
		$this->options = array();
		while ($row = mysql_fetch_object($result)) {
			$this->options[$row->check_type][] = new check($row->check_type, $row->check_name, $row->param);
		}
		return $this->options;
	}

	function check() {
		$options = $this->getOptions();
		if (in_array('cron', array_keys($options))) {
			foreach($options['cron'] as $check) {
				if (!$check->result($this)) {
					$this->error .= $check->check->alert;
					return false;
				}
			}
		}
		/*
		if ((in_array('exec', array_keys($options)))||(in_array('proof', array_keys($options)))) {
			foreach($this->getExecutions() as $proof) {
				if (!$proof->check()) return false;
			}
		}
		*/
		return true;
	}

	function getExecutions($after=null, $before=null, $status='all', $OnlyWithProofsStatus='all') {
		return $this->executions = Execution::all($this->id, $after, $before, $status, $OnlyWithProofsStatus);
	}

	function acknowledge($until=null, $recursively=true) {
		if ($until == null) $until = new Datetime();
		foreach($this->getExecutions(null, $until) as $e) $e->acknowledge($recursively);
	}

	function stats($what) {
		if ($what == 'percent_success') {
			$execs = $this->getExecutions();
			$total = count($execs);
			$sql = "SELECT count(*) as nb FROM `execution` WHERE cron_id=$this->id AND status NOT IN (2,3) AND id NOT IN (SELECT DISTINCT exec_id FROM proof WHERE status IN (2,3))";
			$result = mysql_query($sql);
			$row = mysql_fetch_object($result);
			$success = $row->nb;

			return round($success/$total)*100;
		}
	}

	public static function match($command, $user, $host) {
		$sql = "SELECT id FROM cronjob
			WHERE command = '$command'
			AND user = '$user'
			AND host = '$host';
		";
		$result = mysql_query($sql);
		if (mysql_num_rows($result) > 0) {
			$cron_id = mysql_fetch_object($result);
			return $cron_id->id;
		} else return false;
	}

	public static function all() {
		$all = array();
		$sql = "SELECT id FROM cronjob ORDER BY host, user;";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_object($result)) {
			$all[] = new Cronjob($row->id);
		}
		return $all;
	}
}

?>
