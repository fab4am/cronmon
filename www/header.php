<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

include("../boot.php");

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>CronMon</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <script src="<?=$webpath?>js/jquery.min.js"></script>
    <script src="<?=$webpath?>js/bootstrap.min.js"></script>

    <script>
	$( document ).ready(function() {
	    $('.tooltip').tooltip();
	    $('.lemodal').modal()
	});
    </script>

    <!-- Le styles -->
    <link href="css/bootstrap.css" rel="stylesheet">
    <style type="text/css">
      body {
        padding-top: 20px;
        padding-bottom: 40px;
      }

      /* Custom container */
      .container-narrow {
        margin: 0 auto;
        max-width: 700px;
      }
      .container-narrow > hr {
        margin: 30px 0;
      }

      /* Main marketing message and sign up button */
      .jumbotron {
        margin: 60px 0;
        text-align: center;
      }
      .jumbotron h1 {
        font-size: 72px;
        line-height: 1;
      }
      .jumbotron .btn {
        font-size: 21px;
        padding: 14px 24px;
      }

      /* Supporting marketing content */
      .marketing {
        margin: 60px 0;
      }
      .marketing p + h4 {
        margin-top: 28px;
      }
    </style>
	    <link href="<?=$webpath?>css/bootstrap-responsive.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
	<script src="<?=$webpath?>js/html5shiv.js"></script>
    <![endif]-->

    <!-- Fav and touch icons -->
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="<?=$webpath?>ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?=$webpath?>ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?=$webpath?>ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="<?=$webpath?>ico/apple-touch-icon-57-precomposed.png">
    <link rel="shortcut icon" href="<?=$webpath?>ico/favicon.png">
  </head>

  <body>

    <div class="container">

      <div class="masthead">
        <ul class="nav nav-pills pull-right">
	<li <?php if (strtok($_SERVER["REQUEST_URI"],'?') == $webpath) echo 'class="active"'; 			?>><a href="<?=$webpath?>">Home</a></li>
	<li <?php if (strtok($_SERVER["REQUEST_URI"],'?') == $webpath."crons.php") echo 'class="active"'; 	?>><a href="<?=$webpath?>crons.php">Crons</a></li>
	<li <?php if (strtok($_SERVER["REQUEST_URI"],'?') == $webpath."unmatched.php") echo 'class="active"'; 	?>><a href="<?=$webpath?>unmatched.php">Unmatched</a></li>
	<li <?php if (strtok($_SERVER["REQUEST_URI"],'?') == $webpath."options.php") echo 'class="active"'; 	?>><a href="<?=$webpath?>options.php">Options</a></li>
        </ul>
        <h3 class="muted">CronMon</h3>
      </div>

      <hr>
