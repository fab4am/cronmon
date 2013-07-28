<?php

include('boot.php');

$connection = imap_open($config['imap_server'], $config['imap_username'], $config['imap_password']);
$count = imap_num_msg($connection);
for($msgno = 1; $msgno <= $count; $msgno++) {

    $headers = imap_headerinfo($connection, $msgno);
    echo $msgno.". ".$headers->subject." ";

    $command = preg_replace('/Cron <[^@>]+@[^>]+> (.+)/i', "$1", $headers->subject);
    $user = $headers->from[0]->mailbox;
    $host = $headers->from[0]->host;
    $date = new Datetime($headers->date);
    $type = "mail";
    $content = imap_body($connection, $msgno);

    if ($cron_id = Cronjob::match($command, $user, $host)) {
	    $cron = new Cronjob($cron_id);
	    $cron->addExecution($date, $type, $content);
	    echo " -> match cron $cron->id\n";
    } else {
	    $proof = new UProof();
	    $proof->datetime = $date;
	    $proof->type = $type;
	    $proof->fromuser = $user;
	    $proof->fromhost = $host;
	    $proof->senderIP = "";
	    $proof->command = $command;
	    $proof->content = $content;

	    $proof->save();
	    echo " -> unmatched\n";
    }
    imap_delete($connection, $msgno);
   
}

imap_expunge($connection);

?>
