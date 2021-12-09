<?php

set_time_limit(0);
ini_set('memory_limit','-1');
set_include_path(implode(PATH_SEPARATOR, array_merge(array('/sites/phplib_test', '/sites/library_test'), array(get_include_path()))));
$debug = true;

header('Content-Type: plain/text; charset=UTF-8');

require 'Zend/Debug.php';
require 'Ccsd/Tools.php';
require 'Ccsd/Tools/String.php';
require 'Zend/Db.php';
require 'Zend/Db/Table/Abstract.php';

$db = new PDO('mysql:host=ccsddb04.in2p3.fr;port=3306;dbname=HALV3', 'root', 'e+d<HAL', array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"));
$stmtANRI = $db->prepare("INSERT INTO `REF_PROJANR` (TITRE,ACRONYME,REFERENCE,INTITULE,ACROAPPEL,ANNEE,VALID) VALUES (:TITRE,:ACRONYME,:REFERENCE,:INTITULE,:ACROAPPEL,:ANNEE,:VALID)");
$stmtANRU = $db->prepare("UPDATE `REF_PROJANR` set TITRE=:TITRE,ACRONYME=:ACRONYME,REFERENCE=:REFERENCE,INTITULE=:INTITULE,ACROAPPEL=:ACROAPPEL,ANNEE=:ANNEE,VALID=:VALID where ANRID=:ANRID");

/*$anrs = $db->query("SELECT * FROM `REF_PROJANR` ORDER BY CAST(VALID AS CHAR) DESC, ANRID ASC ") ;
foreach( $anrs->fetchAll() as $anr ) {
    $tabCar = ["\t", "\n", "\r", utf8_encode("\x0B"), utf8_encode("\xA0")];
    $bind = [];
    $bind[':TITRE'] = trim(Ccsd_Tools_String::stripCtrlChars(str_replace($tabCar, ' ', $anr['TITRE'])));
    $bind[':ACRONYME'] = trim(Ccsd_Tools_String::stripCtrlChars(str_replace($tabCar, ' ', $anr['ACRONYME'])));
    $bind[':REFERENCE'] = trim(Ccsd_Tools_String::stripCtrlChars(str_replace($tabCar, ' ', $anr['REFERENCE'])));
    $bind[':INTITULE'] = trim(Ccsd_Tools_String::stripCtrlChars(str_replace($tabCar, ' ', $anr['INTITULE'])));
    $bind[':ACROAPPEL'] = trim(Ccsd_Tools_String::stripCtrlChars(str_replace($tabCar, ' ', $anr['ACROAPPEL'])));
    $bind[':ANNEE'] = $anr['ANNEE'];
    $bind[':VALID'] = $anr['VALID'];
    $bind[':ANRID'] = $anr['ANRID'];
    $res = $stmtANRU->execute( $bind );
    if ($debug) {
        if ( $res ) {
            println('# ANR project updated REF_PROJANR');
        } else {
            if ( $stmtANRI->errorInfo()[0] == 23000 ) {
                println('# ANR already on REF_PROJANR ');
            } else {
                println($bind[':REFERENCE']);print_r($stmtANRI->errorInfo());
            }
        }
    }
}*/

// Récupération des ProjANR, csv -> Référence;Titre;Acronyme;Intitulé du programme;Acronyme du programme;Année
$csv = [];
$line = 0;
if (($handle = fopen("/sites/anr.csv", "r")) !== FALSE) {
    $size = [0,0,0,0,0,0,0,0,0,0,0];
    while (($data = fgetcsv($handle, 4096, ";")) !== FALSE) {
        $num = count($data);
        $csv[$line] = [];
        for ($c=0; $c < $num; $c++) {
            if (strlen($data[$c])>500 ) {println($data[0]);}
            if ( strlen($data[$c]) > $size[$c] ) {
                $size[$c] = strlen($data[$c]);
            }
            $csv[$line][$c] = trim(Ccsd_Tools_String::stripCtrlChars($data[$c]));
        }
        $line++;
    }
    fclose($handle);
}

if ( count($csv) ) {
    println(count($csv).' projets ANR à intégrer');
    foreach( $csv as $anr ) {
        $bind = [];
        $md5 = md5(strtolower('titre'.$anr[1].'acronyme'.$anr[2].'reference'.$anr[0]));
        $exist = $db->query("SELECT ANRID from `REF_PROJANR` where MD5 = UNHEX('" . $md5 ."')")->fetch();
        $bind[':TITRE'] = $anr[1];
        $bind[':ACRONYME'] = $anr[2];
        $bind[':REFERENCE'] = $anr[0];
        $bind[':INTITULE'] = $anr[3];
        $bind[':ACROAPPEL'] = $anr[4];
        $bind[':ANNEE'] = $anr[5];
        $bind[':VALID'] = 'VALID';
        if ( $exist ) {
            println('projet exist');
            $bind[':ANRID'] = $exist['ANRID'];
            $res = $stmtANRU->execute( $bind );
        } else {
            println('nouveau projet');
            $res = $stmtANRI->execute( $bind );
        }
        if ($debug) {
            if ( $res ) {
                println('# ANR inserted or updated into REF_PROJANR');
            } else {
                if ( $stmtANRI->errorInfo()[0] == 23000 ) {
                    println('# ANR already on REF_PROJANR ');
                } else {
                    println($anr[0]);print_r($stmtANRI->errorInfo());
                }
            }
        }
    }

}

println();

function println($s = '')
{
	print $s . "\n";
}
