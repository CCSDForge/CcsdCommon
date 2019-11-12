<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Max TOMPOUCE
 * Date: 13/06/14
 * Time: 11:40
 * To change this template use File | Settings | File Templates.
 */

require "config.php";
require "DbDiff.php";

require_once "Zend/Loader/Autoloader.php";
$autoloader = Zend_Loader_Autoloader::getInstance();

//Récupération des choix de l'utilisateur
try {
    $consoleOtps = new Zend_Console_Getopt(
        array(
            'help|h' => 'aide',
            'debug' => 'Mode debug',
            'test' => 'Test sans synchro',
            'force|f' => 'Passe sans confirmation les modif',
            'db1=s' => 'Premiere base, modèle',
            'db2=s' => 'Seconde base, reçoit la synchro'
        ));
    $parseResult = $consoleOtps->parse();
} catch (Zend_Console_Getopt_Exception $e) {
    exit($e->getMessage() . PHP_EOL . PHP_EOL . $consoleOtps->getUsageMessage());
}

if ($consoleOtps->help != false) {
    exit($consoleOtps->getUsageMessage());
}

$debug=false;
$test=false;
$force=false;

if ($consoleOtps->debug != false) {
    $debug=true;
}

if ($consoleOtps->test != false) {
    $test=true;
}


if ($consoleOtps->force != false){// || $consoleOtps->f != false) {
    $force=true;
}


$db1=$consoleOtps->db1;
$db2=$consoleOtps->db2;

if ($db1 == null || $db2 == null) {
    print("No table given, please see the help function\n");
    exit($consoleOtps->getUsageMessage());
}

$config1 = Array(
    'name' => $db1 . ' (' . HOST . ')',
    'config' => Array(
        'host' => HOST,
        'port' => PORT,
        'user' => USER,
        'password' => PASSWORD,
        'name' => $db1
    )
);
$config2 = Array(
    'name' => $db2 . ' (' . HOST . ')',
    'config' => Array(
        'host' => HOST,
        'port' => PORT,
        'user' => USER,
        'password' => PASSWORD,
        'name' => $db2
    )
);




if (strpos($db1,',')!=0) { // host,database
    $split=explode(',', $db1);
    $config1['name']= $split[1] . ' (' . trim($split[0],'@') . ')'; //split[0] contient l'host, et 1 contient le nom de base
    $config1['config']['name'] = $split[1];
    if (strpos($split[0],':')!=0) { // user:pwd@host,database
        $split2=explode(':', $split[0]);
        $config1['config']['user']=$split2[0];
        $split3=explode('@', $split2[1]);
        $config1['config']['password']=$split3[0];
        $config1['config']['host']=$split3[1];
    }
}

if (strpos($db2,',')!=0) { // host,database
    $split=explode(',', $db2);
    $config2['name']= $split[1] . ' (' . trim($split[0],'@') . ')'; //split[0] contient l'host, et 1 contient le nom de base
    $config2['config']['name'] = $split[1];
    if (strpos($split[0],':')!=0) { // user:pwd@host,database
        $split2=explode(':', $split[0]);
        $config2['config']['user']=$split2[0];
        $split3=explode('@', $split2[1]);
        $config2['config']['password']=$split3[0];
        $config2['config']['host']=$split3[1];
    }
}

//connection to the database
try {
    $config = $config1["config"];
    $database1 = new PDO("mysql:host=" . $config['host'] . ( isset($config['port'])?";port=".$config['port']:"" ) . ";dbname=" . $config['name'] , $config['user'], $config['password']);
    $config = $config2["config"];
    $database2 = new PDO("mysql:host=" . $config['host'] . ( isset($config['port'])?";port=".$config['port']:"" ) . ";dbname=" . $config['name'] , $config['user'], $config['password']);


} catch (PDOException $e) {
    print($e->getMessage());
    exit;
}


$dbdiff = new DbDiff();

$export1 = $dbdiff->export($config1['name'], $database1);

$export2 = $dbdiff->export($config2['name'], $database2);

$results = $dbdiff->compare($export1, $export2);

if ($debug) {
    if (count($results['results']) > 0) {

        print("\nFound differences in " . count($results['results']) . " table" . $dbdiff->s(count($results['results'])) . ":\n\n");

        foreach ($results['results'] as $table_name => $differences) {

            print("In table : $table_name \n\n");
            foreach ($differences as $difference) {
                print("   " . $difference . "\n");
            }
            print("\n");
        }

    } else {
        print_r("No differences found.");
    }
}



if (isset($results['sql'])) {
    if (!$test) {
        if (!$force) {
            foreach ($results['sql'] as $sql) {
                if (checkExec($sql)) {
                    $res = $database2->exec($sql);
                    if (!$res && $debug) {
                        print_r($database2->errorInfo());
                    }
                }
            }
        }
    } else {
        if (isset($results['sql'])) {
            print("\nChanges :\n");
            foreach ($results['sql'] as $sql) {
                print($sql . ";\n");
            }
        }
    }
} else {
        print("\n\n****\nNo changes to do\n****\n");
}


/*
 * @return boolean
 */
function checkExec($sql) {

    $bool = true;
    while ($bool) {
        if (PHP_OS == "WINNT") {
            echo "Do you want to execute : $sql ? (y/n/exit)\n";
            $input = stream_get_line(STDIN, 1024, PHP_EOL);
        } else {
            $input = readline("Do you want to execute : $sql ? (y/n/exit)\n");
        }

        if ($input == "exit") {
            exit;
        } else if ($input == "n") {
            $ret = false;
            $bool = false;
        } else if ($input == "y") {
            $ret=true;
            $bool=false;
        }
    }
    return $ret;
}