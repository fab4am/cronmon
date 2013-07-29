<?php

class Execution {

	var $id;
	var $datetime;
	var $cronjob;
	var $status;
	var $error;
	var $proofs = array();

	function Execution($id=0) {
		$this->id = $id;
		if ($this->id != 0) {
			$sql = "SELECT * FROM execution WHERE id = $this->id";
			$result = mysql_query($sql);
			if ($row = mysql_fetch_object($result)) {
				$this->datetime = new Datetime($row->datetime);
				$this->cron	= new Cronjob($row->cron_id);
				$this->status	= $row->status;
				$this->error	= $row->error;
			}
		}
	}

	function save() {
		$sql = "INSERT INTO execution (
				datetime, 
				cron_id, 
				status 
			) VALUES (
				'".$this->datetime->format("Y-m-d H:i:s")."', 
				'".$this->cron->id."', 
				'$this->status' 
			)";
		$req = mysql_query($sql) or die($sql." - ".mysql_error());
		$this->id = mysql_insert_id();
		return $this->id;
	}

	function delete() {
		$sql = "DELETE FROM execution WHERE id = $this->id";
		mysql_query($sql);

	}

	function addProof($date, $type, $content) {
		$proof = new Proof();
		$proof->exec = $this;
		$proof->datetime = $date;
		$proof->type = $type;
		$proof->log = $content;
		$proof->save();
	}

	function getProofs($status='all') {
		$sql = "SELECT id FROM proof WHERE exec_id=$this->id";
		if ($status != 'all') $sql .= " AND status=$status";
		$result = mysql_query($sql) or die($sql." - ".mysql_error());
		$this->proofs = array();
		while ($row = mysql_fetch_object($result)) {
			$this->proofs[] = new Proof($row->id);
		}
		return $this->proofs;
	}

	function check($mark=true) {
		$options = $this->cron->getOptions();
		$result = true;
		$this->error = '';
		if ($mark) $this->status = 1;
		if (in_array('exec', array_keys($options))) {
			foreach($options['exec'] as $check) {
				if (!$check->result($this)) {
					$result = false;
					$this->error .= $check->check->alert."<br />";
					if ($mark) $this->status = 2;
				}
			}
		}
		/*
		if (in_array('proof', array_keys($options))) {
			foreach($this->getProofs() as $proof) {
				if (!$proof->check()) return false;
			}
		}
		*/
		if ($mark) {
			$sql = "UPDATE execution SET status=$this->status, error='".mysql_real_escape_string($this->error)."' WHERE id=$this->id";
			mysql_query($sql) or die($sql." - ".mysql_error());
		}
		return $result;
	}

	function acknowledge($recursively=true) {
		$sql = "UPDATE execution SET status=3 WHERE status=2 AND id=$this->id";
		mysql_query($sql);
		if ($recursively) {
			foreach($this->getProofs(2) as $p) $p->acknowledge();
		}
	}

	public static function all($cron_id=0, $after=null, $before=null, $status='all', $OnlyWithProofsStatus='all') {
		$all = array();
		$sql = "SELECT id FROM execution ";
		$sql .= "WHERE 1=1 ";
		if ($cron_id != 0) $sql .= " AND cron_id = $cron_id ";
		if ($after != null) $sql .= " AND datetime >= '".$after->format("Y-m-d H:i:s")."' ";
		if ($before != null) $sql .= " AND datetime <= '".$before->format("Y-m-d H:i:s")."' ";
		if ($status != 'all') $sql .= " AND status = $status ";
		if ($OnlyWithProofsStatus != 'all') $sql .= " AND id IN (SELECT DISTINCT exec_id FROM proof WHERE status=$OnlyWithProofsStatus)";
		$sql .= " ORDER BY datetime DESC;";
		$result = mysql_query($sql) or die($sql." - ".mysql_error());
		while ($row = mysql_fetch_object($result)) {
			$all[] = new Execution($row->id);
		}
		return $all;
	}

}

?>
