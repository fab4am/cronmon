<?php
include('header.php');

	$cron = new Cronjob(0);
	if ((!empty($_POST))&&($_POST['submitted'] == 'checks')) {
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


	if ((!empty($_POST))&&($_POST['submitted'] == 'addnotification')) {
		$n = new notification();
		$n->name = $_POST['plugin'];
		$n->title = $_POST['title'];
		$n->message = $_POST['message'];
		$n->destinataire = $_POST['dest'];
		$n->add();
	}

	if (!empty($_GET['deletenotif'])) {
		$n = new notification($_GET['deletenotif']);
		$n->del();
	}

?>

<h2>Options</h2>

<h3>Default checks</h3>
<form method='POST' class='form-inline' name='defaultchecks' action='<?=$_SERVER['PHP_SELF']?>'>
<p>These checks will be applied to every single cron.</p>
<h4>Notify on : </h4>
<?php
	$getname = function($check) {
		return $check->name;
	};

	$cron->getOptions();
	$checks = check::getAllOptions();
	foreach($checks as $type => $typechecks) {
		echo "<h5>$type</h5>";
		foreach ($typechecks as $name => $check) {
			if (!$check->withparam) {
				if ((in_array($type, array_keys($cron->options))) && (in_array($name, array_map($getname, $cron->options[$type])))) $checked = " checked='checked' ";
				else $checked = "";
				echo "<label class='checkbox'><input type='checkbox' name='".$type."_".$name."' $checked> $check->desc</label><br />";
			} else {
				$count=1;
				foreach($cron->options as $optiontypes) {
					foreach($optiontypes as $optname => $option) {
						if ($option->name == $name) {
							echo "<label class='checkbox'><input type='checkbox' name='".$type."_".$name."_$count' checked='checked'> $check->desc :&nbsp;</label>";
							echo "<input type='text' name='".$type."_".$name."_".$count."_param' value='$option->param' disabled/>";
							echo "<input type='hidden' name='".$type."_".$name."_".$count."_param' value='$option->param' /><br />";
							$count++;
						}
					}
				}
				echo "<label class='checkbox'><input type='checkbox' name='".$type."_".$name."'> $check->desc :&nbsp;</label>";
				echo "<input type='text' name='".$type."_".$name."_param' /><br />";
			}
		}
	}
?>
    <br />
    <input type='hidden' name='submitted' value='checks' />
    <button class="btn btn-primary"><i class="icon-white icon-ok"></i> Save changes</button>
  </form>

<hr />

<h3>Notifications</h3>
<div class='row-fluid'>
    <div class='span12'>
	<h4>Current notifications : </h4>
	<?php
		$notifications = notification::getAllNotifications();
		echo "<table class='table table-striped table-hover table-condensed>";
		echo "<tr><th>Channel</th><th>Title</th><th>Message</th><th>To</th><th>Actions</th></tr>";
		foreach($notifications as $notification) {
			echo "<tr>";
			  echo "<td>".$notification->plugin->desc."</td>";
			  echo "<td>$notification->title</td>";
			  echo "<td>".nl2br($notification->message)."</td>";
			  echo "<td>$notification->destinataire</td>";
			  echo "<td><a href='".$_SERVER['PHP_SELF']."?deletenotif=$notification->id' title='Delete' class='btn btn-danger btn-mini'><i class='icon-white icon-remove'></i></a></td>";
			echo "</tr>";
		}
		echo "</table>";
	?>
    </div>
</div>
<div class='row-fluid'>
    <div class='span3'>
      <form method='POST' name='addnotification' action='<?=$_SERVER['PHP_SELF']?>'>
	<h4>Add notification : </h4>
	<?php
		$plugins = notification::getAllNotificationPlugins();

		echo "<select name='plugin'>";
		foreach ($plugins as $plugin) {
			echo "<option value='".get_class($plugin)."'>$plugin->desc</option>";
		}
		echo "</select><br />";
		echo "<input type='text' name='dest' placeholder='To' />";
		echo "<input type='text' name='title' placeholder='Notification title' />";
		echo "<textarea name='message' placeholder='message...'></textarea>";

	?>
    <br />
    <input type='hidden' name='submitted' value='addnotification' />
    <button class="btn btn-primary"><i class="icon-white icon-ok"></i> Add</button>
	</form>
    </div>
    <div class='span2'>
	<h5 class='muted'>Available variables : </h5>
		<ul>
<?php
		$error = new error();
		foreach(array_keys(get_object_vars($error)) as $var) {
			echo "<li class='muted'>[%$var%]</li>";
		}
?>
		</ul>
    </div>
</div>

<?php
include('footer.php');
?>
