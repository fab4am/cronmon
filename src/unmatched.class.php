<?php

class UProof {

	var $id;
	var $datetime;
	var $type;
	var $fromuser;
	var $fromhost;
	var $senderIP;
	var $command;
	var $content;


	function UProof($id=0) {
		$this->id = $id;
		if ($this->id != 0) {
			$sql = "SELECT datetime, type, content, fromuser, fromhost, senderIP, command FROM unmatched_proof WHERE id = $this->id";
			$result = mysql_query($sql);
			if ($row = mysql_fetch_object($result)) {
				$this->datetime = new Datetime($row->datetime);
				$this->type	= $row->type;
				$this->content	= $row->content;
				$this->fromuser	= $row->fromuser;
				$this->fromhost	= $row->fromhost;
				$this->senderIP	= $row->senderIP;
				$this->command	= $row->command;
			}
		}
	}

	function save() {
		$sql = "INSERT INTO unmatched_proof (
				datetime, 
				type, 
				content, 
				fromuser, 
				fromhost, 
				senderIP, 
				command
			) VALUES (
				'".$this->datetime->format("Y-m-d H:i:s")."', 
				'$this->type', 
				'".mysql_real_escape_string($this->content)."',
				'".mysql_real_escape_string($this->fromuser)."',
				'".mysql_real_escape_string($this->fromhost)."',
				'".mysql_real_escape_string($this->senderIP)."',
				'".mysql_real_escape_string($this->command)."'
			)";
		$req = mysql_query($sql) or die($sql." - ".mysql_error());
		$this->id = mysql_insert_id();
		return $this->id;
	}

	function delete() {
		$sql = "DELETE FROM unmatched_proof WHERE id = $this->id";
		mysql_query($sql);

	}


	function matchto($cron_id) {
		$exec = new Execution();
		$exec->cron = new Cronjob($cron_id);
		$exec->datetime = $this->datetime;
		$exec->save();

		$proof = new Proof();
		$proof->exec = $exec;
		$proof->datetime = $this->datetime;
		$proof->type = $this->type;
		$proof->log = $this->content;

		$proof->save();
		$this->delete();
	}

}

?>
