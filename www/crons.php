<?php
include('header.php');
ob_start();

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
		$nbrules = count($cron->getOptions(), COUNT_RECURSIVE) - count($cron->getOptions());
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

		$checks = check::getAllOptions();
		foreach($checks as $type => $typechecks) {
			foreach ($typechecks as $name => $check) {
				if ($check->withparam) {
					if ((in_array($type."_".$name, array_keys($_POST))) && ($_POST[$type."_".$name] == "on")&&in_array($type."_".$name."_param", array_keys($_POST))) 
						$cron->setOption($type, $name, $_POST[$type."_".$name."_param"]);
					elseif (in_array($type."_".$name."_param", array_keys($_POST))) $cron->delOption($type, $name, $_POST[$type."_".$name."_param"]);

					$continue=True;
					$count = 1;
					while ($continue) {
						if ((in_array($type."_".$name."_".$count, array_keys($_POST))) && ($_POST[$type."_".$name."_".$count] == "on") && in_array($type."_".$name."_".$count."_param", array_keys($_POST))) 
							$cron->setOption($type, $name, $_POST[$type."_".$name."_".$count."_param"]);
						elseif (in_array($type."_".$name."_".$count."_param", array_keys($_POST))) 
							$cron->delOption($type, $name, $_POST[$type."_".$name."_".$count."_param"]);
						else $continue=false;
						$count++;
						#if ($count>20) $continue=false;
					}

				} else {
					if ((in_array($type."_".$name, array_keys($_POST))) && ($_POST[$type."_".$name] == "on")) { 
						$cron->setOption($type, $name);
					} else { 
						$cron->delOption($type, $name);
					}
				}
			}
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
	$getname = function($check) {
		return $check->name;
	};

	$defaultoptions = $cron->getOptions(3);
	$cron->getOptions(2);
	$checks = check::getAllOptions();

	echo "<h4>Notify on : </h4>";
	foreach($checks as $type => $typechecks) {
		echo "<h5>$type</h5>";
		foreach ($typechecks as $name => $check) {
			if (!$check->withparam) {
				if ( (in_array($type, array_keys($defaultoptions))) && (in_array($name, array_map($getname, $defaultoptions[$type])))) continue;
				if ((in_array($type, array_keys($cron->options))) && (in_array($name, array_map($getname, $cron->options[$type])))) $checked = " checked='checked' ";
				else $checked = "";

				echo "<label class='checkbox'><input type='checkbox' name='".$type."_".$name."' $checked> $check->desc</label><br />";
			} else {
				$count=1;
				foreach($cron->options as $optiontypes) {
					foreach($optiontypes as $optname => $option) {
						if (($option->type == $type)&&($option->name == $name)) {
							echo "<label class='checkbox'><input type='checkbox' name='".$type."_".$name."_$count' checked='checked'> $check->desc :&nbsp;</label>";
							echo "<input type='text' name='".$type."_".$name."_".$count."_param' value='$option->param' disabled/>";
							echo "<input type='hidden' name='".$type."_".$name."_".$count."_param' value='$option->param' /><br />";
							$count++;
						}
					}
				}
				echo "<label class='checkbox'><input type='checkbox' name='".$type."_".$name."'> $check->desc :&nbsp;</label>";
				$function = new ReflectionMethod(get_class($check), 'check');
				$params = $function->getParameters();
				foreach($params as $p){
					if (($p->name == 'param')&&($p->isOptional())) $default = json_encode($p->getDefaultValue());
				}
				echo "<input type='text' name='".$type."_".$name."_param' value='$default'/><br />";
				$default = '';

			}
		}
	}

	echo "<hr />";
	echo "<h4>Default checks : </h4>";
	foreach($checks as $type => $typechecks) {
		if (in_array($type, array_keys($defaultoptions))) echo "<h5>$type</h5>";
		foreach ($typechecks as $name => $check) {
			if ((!$check->withparam)&&(in_array($type, array_keys($defaultoptions))) && (in_array($name, array_map($getname, $defaultoptions[$type])))) {
				echo "<label><i class='icon-check'></i> $check->desc</label>";
				echo "<br />";
			} elseif($check->withparam) {
				$count=1;
				foreach($defaultoptions as $optiontypes) {
					foreach($optiontypes as $optname => $option) {
						if (($option->type == $type)&&($option->name == $name)) {
							echo "<label><i class='icon-check'></i> $check->desc :&nbsp;</label>";
							echo "<input type='text' name='".$type."_".$name."_".$count."_param' value='$option->param' disabled/><br />";
							$count++;
						}
					}
				}
			}
		}
	}
	?>

    </div>
    <div class="modal-footer">
    <input type='hidden' name='submitted' value='notifications' />
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
