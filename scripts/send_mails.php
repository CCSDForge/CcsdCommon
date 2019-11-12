#!/opt/php5/bin/php
<?php

include __DIR__ . "/../Ccsd/Tools.php";
include __DIR__ . "/../Ccsd/Mail/Sender.php";

if ( is_array($argv) && Ccsd_Tools::preg_in_array("^--help$", $argv)) {
	echo "** Envoi de mail **\n";
	echo "> Paramètres : \n";
	echo " - [--app=NAME] : NAME - nom de l'application (hal, isidore, sciencesconf, episciences, ...)\n";
	exit;
}
$appname = ( Ccsd_Tools::preg_in_array("^--app=", $argv) ) ? substr(strstr($argv[Ccsd_Tools::preg_get_key("^--app=", $argv)], "="), 1) : '';
$start_timer = microtime(true);

$mail = new Ccsd_Mail_Sender();

if ($appname != '') {
	$mail->sendAllFor($appname);
} else {
	$mail->sendAll();
}

$end_timer = microtime(true);
$time = $end_timer - $start_timer;
echo "\nTemps d'execution : ".number_format($time, 3)."s\n";