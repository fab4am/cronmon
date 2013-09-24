<?php

include('boot.php');

foreach(Cronjob::all() as $cron) {
	#echo "$cron->id : $cron->command";
	foreach($cron->getExecutions(null, null, 'all', 0) as $exec) {
		foreach($exec->getProofs('0') as $proof) {
			#echo "proof $proof->id\n";
			// check each proof
			if (!$proof->check()) {
				$error = new error();
				$error->errortype = "Content with errors";
				$error->errordesc = "The content received failed one or more checks : \n".$proof->error;
				$error->originalcontent = $proof->log;
				$error->datetime = $proof->datetime->format('c');
				$error->cronuser = $cron->user;
				$error->cronhost = $cron->host;
				$error->croncommand = $cron->command;
				notification::sendAll($error);
			}
		}
	}
	foreach($cron->getExecutions(null, null, '0') as $exec) {
		#echo "exec $exec->id\n";
		// check execution
		if (!$exec->check()) {
			$error = new error();
			$error->errortype = "Execution errors";
			$error->errordesc = "Execution failed one or more checks : \n".$exec->error;
			$error->originalcontent = "";
			$error->datetime = $exec->datetime->format('c');
			$error->cronuser = $cron->user;
			$error->cronhost = $cron->host;
			$error->croncommand = $cron->command;
			notification::sendAll($error);
		}
	}
	#echo "cron $cron->id\n";
	// check cron
	if (!$cron->check()) {
		$now = new Datetime();
		$error = new error();
		$error->errortype = "Cron error";
		$error->errordesc = "Cron failed one or more checks : \n".$cron->error;
		$error->originalcontent = "";
		$error->datetime = $now->format('c');
		$error->cronuser = $cron->user;
		$error->cronhost = $cron->host;
		$error->croncommand = $cron->command;
		notification::sendAll($error);
	}
}

?>
