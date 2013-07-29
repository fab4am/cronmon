<?php
include('header.php');

print '<script src="//s.ntl.nc/js/sorttable/2/sorttable.js"></script>'."\n";
print '<script src="//s.ntl.nc/js/dragtable/1.0/dragtable.js"></script>'."\n";
print '<script src="//s.ntl.nc/js/jquery.uitablefilter/1/jquery.uitablefilter.js"></script>'."\n";

	echo '<script>$(function() { 
  var theTable = $(".filterable")

  theTable.find("tbody > tr").find("td:eq(1)").mousedown(function(){
    $(this).prev().find(":checkbox").click()
  });

  $("#filter").keyup(function() {
    $.uiTableFilter( theTable, this.value );
  })

  $("#filter-form").submit(function(){
    theTable.find("tbody > tr:visible > td:eq(1)").mousedown();
    return false;
  }).focus(); //Give focus to input field
});  
</script>';
	
	$table = '<div align="right"><form id="filter-form"><input id="filter" type="text" size="30" maxlength="30" value="" name="filter" placeholder="Filtrer"></input></form></div>';
	$table .= "<table class='table table-bordered table-striped table-hover table-condensed filterable sortable draggable'>";
	$table .= "<tr><th>&nbsp;</th><th>User</th><th>Host</th><th>Command</th><th>Actions</th></tr>";
	ob_flush();

	$count = 0;

foreach(Cronjob::all() as $cron) {
	$problem = false;
	if (!$cron->check()) $problem = true;
        foreach($cron->getExecutions(null, null, 'all', 2) as $exec) {
		if ($exec->status == 2) $problem = true;
                foreach($exec->getProofs('2') as $proof) {
			if ($proof->status == 2) $problem = true;
                }
	}

	if ($problem) {
		$count++;
		$table .= "<tr>
			<td><span class='badge badge-important'>!</span></td>
			<td>$cron->user</td>
			<td>$cron->host</td>
			<td>$cron->command</td>
			<td>
			  <a href='".$webpath."crons.php?cronid=$cron->id' title='Cron options & execution log'><i class='icon-list'></i><i class='icon-wrench' title='Cron options'></i></a>
			</td>
		</tr>\n";
		$messages = array();
		$messages[] = $cron->error;
		foreach($cron->getExecutions(null, null, 'all', 2) as $exec) {
			if ($exec->status == 2) {
				foreach(explode('<br />', $exec->error) as $line) $messages[] = $line;
			}
			foreach($exec->getProofs('2') as $proof) {
				if ($proof->status == 2) {
					foreach(explode('<br />', $proof->error) as $line) $messages[] = $line;
				}
			}
		}
		$messages = array_unique($messages);
		$table .= "<tr><td colspan='5'><strong>Detected problems : </strong><ul>";
		foreach ($messages as $m) {
			if ($m != '') $table .= "<li>$m</li>";
		}

		$table .= "</ul></td></tr>\n";
		flush();
		ob_flush();
	}
}
$table .= "</table>";

echo "<div class='alert alert-error'>
    <button type='button' class='close' data-dismiss='alert'>&times;</button>
    <h2>$count</h2>
    That's the number of crons you have to fix right now.
    </div>";

echo $table;
include('footer.php');
?>
