<?php
include('header.php');

	$cron = new Cronjob(0);
	if ((!empty($_POST))&&($_POST['submitted'] == 'checks')) {
		$options = option::all(False, False, True);
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
			if (in_array('matching', array_keys($newoption))) $option->matching = $newoption['matching'];
			if (in_array('param', array_keys($newoption))) $option->param = $newoption['param'];
			$option->check_type = $newoption['type'];
			$option->check_name = $newoption['name'];
			$option->add();
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

<form method='POST' class='form-inline' name='defaultchecks' action='<?=$_SERVER['PHP_SELF']?>'>
<div class='row-fluid'>
<div class='span6'>

<h3>Default checks</h3>
<p>These checks will be applied on every single cron.</p>
<h4>Notify on : </h4>
<?php
	$options = option::all();
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
			echo "<hr />";
		}
	}
?>

</div>
<div class='span6'>

<h3>Regexp matching checks</h3>
<p>These checks will be applied on crons whose title is matched by defined regexp:</p>
<h4>Notify on : </h4>
<?php
	$options2 = option::all(False, False, True);
	foreach($checks as $check_type => $check_type_data) {
		echo "<h5>$check_type</h5>";
		foreach ($check_type_data as $check_name => $check) {
			foreach($options2 as $id => $option) {
				if (($option->check_name == $check_name) && ($option->check_type == $check_type)) {
					if (isset($option->matching) && !empty($option->matching)) {
						echo "For crons with subject matching : <input type='text' name='modif[$option->id][matching]' value='$option->matching' /><br />";
						if ($check->withparam) { 
							echo "<label class='checkbox'><input type='checkbox' name='modif[$option->id][onoff]' checked='checked'> $check->desc :&nbsp;</label>";
							echo "<input type='text' name='modif[$option->id][param]' value='$option->param' /><br /><br />";
							$count++;
						} else {
							$checked = " checked='checked'";
							echo "<label class='checkbox'><input type='checkbox' name='modif[$option->id][onoff]' $checked> $check->desc</label><br /><br />";
						}
					}
				}
			}
			if ($check->withparam) { 
				echo "For crons with subject matching : <input type='text' name='new[$count][matching]'   /><br />";
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
				echo "For crons with subject matching : <input type='text' name='new[$count][matching]'   /><br />";
				echo "<label class='checkbox'><input type='checkbox' name='new[$count][onoff]'> $check->desc</label><br />";
				echo "<input type='hidden' name='new[$count][type]' value='$check_type' />";
				echo "<input type='hidden' name='new[$count][name]' value='$check_name' />";
			}
			$count++;
			echo "<hr />";
		}
	}
?>

</div>
</div>
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
