CronMon : monitoring for crons
==============================

Tired of receiving thousand of cron mails every day without really knowing if everything's fine or not? But you don't want to migration to a complete job scheduling software, because you love the simplicity, efficiency and flexibility of cron? CronMon is for you!
This software aims to : 
 * change almost nothing at your cron setup, keep adding crons the way you like, on any server you want
 * reduce unnecessary cron mails coming in your mailbox all the time
 * be warned if something's wrong
 * be warned if a mail is missing or not coming when it should
 * keep history of all those mails if you need it one day
 * save time

The only minor changes that might be needed for cronmon to work, if not already done :
 * configure all the cron mails to be sent to the same email
 * stop redirecting everything to /dev/null

Features
--------
 * registers crons with received e-mails, you don't have to add them manually in the first place
 * General and per-cron checks
 * write your own check plugins
 * write your own notification plugins
 * some useful checks are provided by default : 
  * detect if a word is in the mail body or not
  * detect empty or non-empty mail body
  * checks if schedule is respected, with a leeway
  * and some more

Requirements
------------
 * PHP5 at least (tested with PHP5.3 and PHP5.4)
 * MySQL 5 (tested with 5.1 and 5.5)
 * a web server with php support (tested with apache 2.2)
 * php5-imap
 * cron!

Setup
-----
 * clone this repository or download the files
 * source doc/cronmon.sql in a database, add permissions...
 * edit and adapt config.php
 * setup your web server to serve pages inside www
 * add these 2 crons scheduled as you wish. The first one fetches the mails, the second one checks everything and sends notifications : 
	*/10 * * * * php /srv/cronmon/collector.php
	5 * * * * php /srv/cronmon/checker.php
 * browse your new interface and start configuring cron checks.



