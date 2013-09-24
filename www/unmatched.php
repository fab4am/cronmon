<?php
include('header.php');


if (!empty($_GET['delete'])) {
	$ids = explode(' ', $_GET['delete']);
	foreach ($ids as $id) {
		$id = (int) $id;
		if ($id != 0) {
			$uproof = new UProof($id);
			$uproof->delete();
		}
	}
} elseif (!empty($_GET['register'])) {
	$ids = explode(' ', $_GET['register']);

	$p = new UProof($ids[0]);
	$cron = new Cronjob();
	$cron->name = "";
	$cron->command = $p->command;
	$cron->user = $p->fromuser;
	$cron->host = $p->fromhost;
	$cron->schedule = "";

	$cron_id = $cron->save();

	foreach ($ids as $id) {
		$id = (int) $id;
		if ($id != 0) {
			$uproof = new UProof($id);
			$uproof->matchto($cron_id);
		}
	}
	header("Location: /crons.php?cronid=".$cron_id);

}

?>

<?php
$sql = "SELECT fromuser, fromhost, command, count(*) as nb
	FROM `unmatched_proof`
	GROUP BY `command` , `fromuser` , `fromhost`";
$result = mysql_query($sql);
while ($row = mysql_fetch_object($result)) {
	$sql2 = "SELECT id, datetime, content FROM unmatched_proof 
		WHERE fromuser = '$row->fromuser' 
		AND fromhost = '$row->fromhost' 
		AND command = '".mysql_real_escape_string($row->command)."';";
	$result2 = mysql_query($sql2) or die(mysql_error());
	$details = "";
	$ids = "";
	while ($row2 = mysql_fetch_object($result2)) {
		$details .= "<tr><td colspan='2'>$row2->datetime</td><td colspan='2'>$row2->content</td><td><a href='".$_SERVER['PHP_SELF']."?delete=$row2->id' title='Delete' class='btn btn-danger btn-mini'><i class='icon-white icon-remove'></i></a></td></tr>\n";
		$ids .= $row2->id."+";
	}

	echo "
	<table class='table table-bordered table-striped table-hover table-condensed' >
	  <tr><th>User</th><th>Host</th><th>command</th><th>Occurence</th><th>Actions</th></tr>
		<tr>
		<td>$row->fromuser</td>
		<td>$row->fromhost</td>
		<td>$row->command</td>
		<td>$row->nb</td>
		<td><a href='".$webpath.basename(__FILE__)."?delete=$ids' title='Delete all' class='btn btn-inverse btn-mini'><i class='icon-white icon-remove'></i></a>
		<a href='".$webpath.basename(__FILE__)."?register=$ids' title='Register as regular job'  class='btn btn-success btn-mini'><i class='icon-white icon-ok'></i></a>
		</td></tr>";
	echo $details;

	echo "</table><br />";
}
?>

<?php
include('footer.php');
?>
