<?php
include('header.php');
ob_start();

if ((isset($_GET['deletecronid']))&&(!empty($_GET['deletecronid']))) {
	$cron = new Cronjob($_GET['deletecronid']);
	$cron->delete();
}


if (empty($_GET['cronid'])) {

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
	echo '<div align="right"><form id="filter-form"><input id="filter" type="text" size="30" maxlength="30" value="" name="filter" placeholder="Filtrer"></input></form></div>';
	echo "<table class='table table-bordered table-striped table-hover table-condensed filterable sortable draggable'>";
	echo "<tr><th>User</th><th>Host</th><th>Command</th><th>Nb of rules</th><th>% success</th><th>Actions</th></tr>";
	ob_flush();
	foreach(Cronjob::all() as $cron) {
		$nbrules = count($cron->getOptions(True, True));
		echo "<tr>
			<td>$cron->user</td>
			<td>$cron->host</td>
			<td>$cron->command</td>
			<td>$nbrules</td>
			<td>".$cron->stats('percent_success')."%</td>

			<td>
			  <a href='".$webpath.basename(__FILE__)."?cronid=$cron->id' title='Cron options & execution log'><i class='icon-list'></i><i class='icon-wrench' title='Cron options'></i></a>
			</td>
		</tr>\n";
		flush();
		ob_flush();
	}
	echo "</table>";
} else {

	$cron = new Cronjob($_GET['cronid']);
	$execs = $cron->getExecutions();

	if ((!empty($_POST))&&($_POST['submitted'] == 'notifications')) {

		if ($cron->schedule != $_POST['schedule']) {
			$cron->schedule = $_POST['schedule'];
			$cron->update();
		}

		$options = $cron->getOptions(False, False);
		foreach($options as $option) {
			if ( (!in_array($option->id, array_keys($_POST['modif']))) || (!in_array('onoff', array_keys($_POST['modif'][$option->id]))) ) {
				$option->remove();
				continue;
			}
			if (in_array('matching', array_keys($_POST['modif'][$option->id]))) $option->matching = $_POST['modif'][$option->id]['matching'];
			if (in_array('param', array_keys($_POST['modif'][$option->id]))) $option->param = $_POST['modif'][$option->id]['param'];
			$option->save();
		}
		foreach($_POST['new'] as $newoption) {
			if (!in_array('onoff', array_keys($newoption))) continue;
			$option = new Option();
			$option->cron_id = $cron->id;
			if (in_array('matching', array_keys($newoption))) $option->matching = $newoption['matching'];
			if (in_array('param', array_keys($newoption))) $option->param = $newoption['param'];
			$option->check_type = $newoption['type'];
			$option->check_name = $newoption['name'];
			$option->add();
		}
	}

	if (!empty($_GET['ackuntil'])) {
		$getid = function($exec) {
			return $exec->id;
		};
		if (in_array($_GET['ackuntil'], array_map($getid, $execs))) {
			$e = new execution($_GET['ackuntil']);
			$cron->acknowledge($e->datetime);
		}
	}

        echo '<a href="#divconfig" role="button" class="btn pull-right" data-toggle="modal">Cron config</a>';
	echo "<h2>$cron->user@$cron->host</h2><span class='muted'>$cron->command</span><br /><br />";
	?>


     
    <!-- Modal -->
    <div id="divconfig" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel">Cron Config</h3>
    </div>
    <div class="modal-body">
	<h4>Cron Info : </h4>
	<form method='POST' class="form-inline">
	<p><strong>User : </strong><?=$cron->user?><br />
	<strong>Host : </strong><?=$cron->host?><br />
	<strong>Command : </strong><?=$cron->command?><br />
	<strong>Schedule : </strong><input type='text' name='schedule' value='<?=$cron->schedule?>' /></p>

<?php
	echo "<h4>Notify on : </h4>";
	$options = $cron->getOptions(False, False);
	$checks = check::getAllOptions();
	$count=1;
	foreach($checks as $check_type => $check_type_data) {
		echo "<h5>$check_type</h5>";
		foreach ($check_type_data as $check_name => $check) {
			$done = false;
			foreach($options as $option) {
				if (($option->check_name == $check_name) && ($option->check_type == $check_type)) {
					if ($check->withparam) { 
						echo "<label class='checkbox'><input type='checkbox' name='modif[$option->id][onoff]' checked='checked'> $check->desc :&nbsp;</label>";
						echo "<input type='text' name='modif[$option->id][param]' value='$option->param' />";
					} else {
						$checked = " checked='checked'";
						echo "<label class='checkbox'><input type='checkbox' name='modif[$option->id][onoff]' $checked> $check->desc</label><br />";
					}
					$done = true;
				}
			}
			if ($check->withparam) { 
				echo "<label class='checkbox'><input type='checkbox' name='new[$count][onoff]'> $check->desc :&nbsp;</label>";
                                $function = new ReflectionMethod(get_class($check), 'check');
                                $params = $function->getParameters();
                                foreach($params as $p){
                                        if (($p->name == 'param')&&($p->isOptional())) $default = json_encode($p->getDefaultValue());
                                }
				echo "<input type='text' name='new[$count][param]' value='$default'/><br />";
                                $default = '';
				echo "<input type='hidden' name='new[$count][type]' value='$check_type' />";
				echo "<input type='hidden' name='new[$count][name]' value='$check_name' />";
			} elseif (!$done) {
				echo "<label class='checkbox'><input type='checkbox' name='new[$count][onoff]'> $check->desc</label><br />";
				echo "<input type='hidden' name='new[$count][type]' value='$check_type' />";
				echo "<input type='hidden' name='new[$count][name]' value='$check_name' />";
			}
			$count++;
		}
	}

	echo "<hr />";
	echo "<h4>Default checks : </h4>";
	$alloptions = $cron->getOptions(True, True);
	$checks = check::getAllOptions();
	$count=1;
	foreach($checks as $check_type => $check_type_data) {
		echo "<h5>$check_type</h5>";
		foreach ($check_type_data as $check_name => $check) {
			$done = false;
			foreach($alloptions as $option) {
				if ((!in_array($option->id, $options)) &&($option->check_name == $check_name) && ($option->check_type == $check_type)) {
					if ($option->matching) echo "<i class='icon-filter' title=\"".$option->matching."\"></i>";
					echo "<label><i class='icon-check'></i> $check->desc &nbsp;</label>";
					if ($check->withparam) echo ": <input type='text' value='$option->param' disabled />";
					echo "<br />";
				}
			}
		}
	}
	?>

    </div>
    <div class="modal-footer">
    <input type='hidden' name='submitted' value='notifications' />
    <a href="<?php echo $webpath.basename(__FILE__)."?deletecronid=$cron->id"; ?>" class="btn btn-danger pull-left"><i class="icon-white icon-remove"></i> Delete cron</a>
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    <button class="btn btn-primary"><i class="icon-white icon-ok"></i> Save changes</button>
    </form>
    </div>
    </div>

<?php

	if (!$cron->check()) echo "
		    <div class='alert alert-block'>
		    <button type='button' class='close' data-dismiss='alert'>&times;</button>
		    <h4>Warning!</h4>
		    $cron->error
		    
		    </div>
		";


	$oldtime = "";
	$diffs = array();
	echo "<table class='table table-bordered table-striped table-hover table-condensed'>";
	foreach($execs as $exec) {
		#if ($exec->check()) $badge="<span class='badge badge-success'>OK</span>";
		if ($exec->status == 0) $badge="<span class='badge badge'>Waiting</span>";
		elseif ($exec->status == 1) $badge="<span class='badge badge-success'>OK</span>";
		elseif ($exec->status == 2) $badge="<span class='badge badge-important'>!</span>";
		elseif ($exec->status == 3) $badge="<span class='badge badge-warning'>ACK</span>";

		$pneedack = false;
		foreach($exec->getProofs() as $proof) {
			#if ($proof->check()) $color='success';
			#else $color='error';
			$display='none';
			if ($proof->status == 0) { $color="info"; }
			elseif ($proof->status == 1) { $color="success"; }
			elseif ($proof->status == 2) { $color="error"; $display=''; $pneedack = true; $badge .= " <span class='badge badge-important'>!</span>";}
			elseif ($proof->status == 3) { $color="warning"; $badge .= " <span class='badge badge-warning'>ACK</span>";}
			$proofline = "<tr class='$color' id='proofs_$exec->id' style='display:$display;'>
				<td>
				  <a class='tooltip' data-placement='left' data-toggle='tooltip' href='#' data-original-title=\"".$proof->error."\" data-html='true'>$proof->type</a>$proof->type
				</td>
				<td><pre>$proof->log</pre></td>
			      </tr>";

		}

		$execline = "<tr>
			<th colspan='2'><a onclick='$(\"#proofs_$exec->id\").toggle(\"fast\");'>$badge ".$exec->datetime->format("c")."</a>";
		if (($exec->status == 2)||($pneedack)) $execline .= "<span class='pull-right'><a href='".$webpath.basename(__FILE__)."?cronid=$cron->id&ackuntil=$exec->id' class='btn btn-warning btn-mini'><i class='icon-white icon-tag'></i> Ack until here</a></span>";
		$execline .= " </tr>";

		echo $execline;
		echo $proofline;

		if ($oldtime != "") $diffs[] = $oldtime->getTimestamp() - $exec->datetime->getTimestamp();
		$oldtime = $exec->datetime;
	}
	echo "</table>";

}

include('footer.php');
?>
