<?php

require_once(__DIR__.'/../basecheck.php');
require_once(__DIR__.'/../../lib/Crontab.class.php');

class schedule extends basecheck {

	var $desc = "Schedule missed";
	var $alert = "This cron wasn't executed when it should have.";
	var $withparam = true;
	function check($object, $param=array('range'=> 60, 'leeway'=> 5)) {
		$cron = $object;
		if ($cron->schedule == "") return true;

		if (!is_array($param)) $param = array();
		if (!in_array('range', $param)) $param['range'] = 60;
		if (!in_array('leeway', $param)) $param['leeway'] = 5;

		// checker sur les x dernières minutes = range
		$range = new DateInterval("PT".$param['range']."M");
		// marge acceptable = leeway
		$leeway = new DateInterval("PT".$param['leeway']."M");

		//
		$now = new Datetime();

		// maintenant - range = limit
		$limit = clone $now;
		$limit->sub(new DateInterval("PT".$param['range']."M"));

		// liste des executions
		$execs = $cron->getExecutions($limit);
		if (count($execs) == 0) return true;
		$rexecs = array_reverse($execs);
		foreach($rexecs as $exec) {

			#echo "limit : ".$limit->format('c')."<br />";

			#echo "exec : ".$exec->datetime->format('c')."<br />";
			// calcul de la prochaine execution = nextschedule
			$nextschedule = DateTime::createFromFormat('U', 
						Crontab::parse($cron->schedule, $limit->format('U')), 
						new DateTimeZone(date_default_timezone_get())
					);
			$nextschedule->setTimeZone(new DateTimeZone(date_default_timezone_get()));
			#echo "nextschedule : ".$nextschedule->format('c')."<br />";

			// prochaine execution dans le futur -> on stoppe
			if ($nextschedule > $now) return true;

			// nextschedule - leeway = earliest
			$earliest = clone $nextschedule;
			$earliest->sub($leeway);
			#echo "earliest : ".$earliest->format('c')."<br />";

			// nextschedule + leeway = latest
			$latest = clone $nextschedule;
			$latest->add($leeway);
			#echo "latest : ".$latest->format('c')."<br />";

			// date exec doit etre entre earliest et latest
			if ($exec->datetime < $earliest) {
				$this->alert .= " Execution around ".$nextschedule->format('c')." happend earlier at ".$exec->datetime->format('c')." !";
				return false;
			}
			if ($exec->datetime > $latest) {
				$this->alert .= " Execution around ".$nextschedule->format('c')." happend later at ".$exec->datetime->format('c')." !";
				return false;
			}

			$limit = clone $nextschedule;
			$limit->add(new DateInterval("PT1M"));

			#echo "-----<br />";
		}

		// calcul de la prochaine execution = nextschedule
		$nextschedule = DateTime::createFromFormat('U', 
					Crontab::parse($cron->schedule, $limit->format('U')), 
					new DateTimeZone(date_default_timezone_get())
				);
		$nextschedule->setTimeZone(new DateTimeZone(date_default_timezone_get()));

		// nextschedule + leeway = latest
		$latest = clone $nextschedule;
		$latest->add($leeway);

		// dernier delai pour prochaine execution PAS dans le futur -> probleme
		if ($latest < $now) {
			$this->alert .= " No execution since ".$exec->datetime->format('c').", the cron should have run around ".$nextschedule->format('c')." !";
			return false;
		}


		return true;
	}
}

?>
