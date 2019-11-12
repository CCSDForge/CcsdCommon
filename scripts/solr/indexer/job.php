<?php
/**
 * Script pour l'indexation des données des applications dans solr
 */
ini_set("display_errors", '1');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_STRICT);
$timestart = microtime(true);

if (!defined('SOLRINIT')) {
    echo "This script must not be called directly.  Use the application scripts SolrJobs.php\n";
    exit (1);
}
// The application script set the correct include_path and define SOLRINIT

$options = [];

if (!($opts->docid || $opts->sqlwhere || $opts->delete || $opts->cron || $opts->file)) {
    fwrite(STDERR, "I need a valid input : a docid, a file, an SQL command, the delete option or cron option\n");
    fwrite(STDERR, $opts->getUsageMessage());
    exit(1);
}

$options ['env'] = APPLICATION_ENV;

if (posix_getuid() == 0) {
    fwrite(STDERR, "Do NOT run this as root, this script must use hal user (generaly nobody)");
    exit(1);
}

if ($opts->buffer) {
    $options ['maxDocsInBuffer'] = (int)$opts->buffer;
}




$core = $opts->c;
$matches=[];
$corefullename = $core;
if (preg_match('/^halspm-(.*)/', $core, $matches)) {
    $core4class = $matches[1];
} else {
    $core4class = $core;
}

switch ($core4class) {

    case 'ref_author' :
        $indexer = new Ccsd_Search_Solr_Indexer_RefAuthor($options);
        break;

    case 'ref_domain' :
        $indexer = new Ccsd_Search_Solr_Indexer_RefDomain($options);
        break;

    case 'ref_journal' :
        $indexer = new Ccsd_Search_Solr_Indexer_RefJournal($options);
        break;

    case 'ref_projanr' :
        $indexer = new Ccsd_Search_Solr_Indexer_RefProjanr($options);
        break;

    case 'ref_projeurop' :
        $indexer = new Ccsd_Search_Solr_Indexer_RefProjeurop($options);
        break;

    case 'ref_structure' :
        $indexer = new Ccsd_Search_Solr_Indexer_RefStructure($options);
        break;

    case 'ref_site' :
        $indexer = new Ccsd_Search_Solr_Indexer_RefSite($options);
        break;

    case 'episciences' :
        $indexer = new Ccsd_Search_Solr_Indexer_Episciences($options);
        break;

    case 'ref_metadatalist' :
        $indexer = new Ccsd_Search_Solr_Indexer_RefMetadatalist($options);
        break;

    case 'hal' :
        $indexer = new Ccsd_Search_Solr_Indexer_Halv3($options);
        if ($opts->delcache == 'no') {
            Ccsd_Log::message($core . " Indexation à partir des données en cache du document", $debug, '', $indexer->getLogFilename());
            $indexer->setDeleteCache(false);
        } else {
            Ccsd_Log::message($core . " Indexation avec suppression des caches du document", $debug, '', $indexer->getLogFilename());
            $indexer->setDeleteCache(true);
        }

        if ($opts->indexpdf == 'no') {
            Ccsd_Log::message($core . " Pas d'indexation des PDF", $debug, '', $indexer->getLogFilename());
            $indexer->setIndexPDF(false);
        } else {
            Ccsd_Log::message($core . " Indexation des PDF", $debug, '', $indexer->getLogFilename());
            $indexer->setIndexPDF(true);
        }

        break;


    default :
        if (($core != null) && ($core != '')) {
            Ccsd_Log::message($core . "n'est pas un core valide", 'ERR', true, '', $indexer->getLogFilename());
            Ccsd_Log::message("Le core est obligatoire", true, 'ERR', true, '', $indexer->getLogFilename());
        }

        echo $opts->getUsageMessage();
        exit();
        break;
}

if ($debug) {
    $indexer->setDebugMode(true);
} else {
    $indexer->setDebugMode(false);
}




Ccsd_Log::message($core . ' |  Indexation dans Apache Solr  | Solarium library version: ' . Solarium\Client::VERSION, $debug, '', $indexer->getLogFilename());


// indexation via CRON
if ((strtolower($opts->cron) == 'update') || (strtolower($opts->cron) == 'delete')) {
    Ccsd_Log::message($core . " Données récupérées dans la table d'indexation", $debug, '', $indexer->getLogFilename());
    $indexer->setOrigin(strtoupper($opts->cron));
    $arrayOfDocId = $indexer->getListOfDocidFromIndexQueue();
    $indexer->processArrayOfDocid($arrayOfDocId);
    exit();
}


/*
 * Suppression de l'index par Requête
 */
if ($opts->delete) {
    $indexer->setOrigin('DELETE');
    $indexer->deleteDocument($opts->delete);
    exit();
}


// indexation par DOCID
if (($opts->docid) && ($opts->docid != '%')) {
    $arrayOfDocId [] = $opts->docid;
    $indexer->setOrigin('UPDATE');
    $indexer->processArrayOfDocid($arrayOfDocId);

    // indexation par requête SQL
} elseif ($opts->sqlwhere || $opts->docid == '%') {
    $whereCondition = $opts->sqlwhere;
    $arrayOfDocId = $indexer->getListOfDocIdToIndexFromDb($whereCondition);
    $indexer->setOrigin('UPDATE');
    $indexer->processArrayOfDocid($arrayOfDocId);

    // indexation par fichier
} elseif ($opts->file) {
    $arrayOfDocId = $indexer->getListOfDocIdToIndexFromFile($opts->file);
    Ccsd_Log::message($core . " Nombre de documents à indexer: " . count($arrayOfDocId), $debug, '', $indexer->getLogFilename());
    $indexer->setOrigin('UPDATE');
    $indexer->processArrayOfDocid($arrayOfDocId);
}

$timeend = microtime(true);
$time = $timeend - $timestart;

Ccsd_Log::message('Début du script: ' . date("H:i:s", $timestart) . '/ fin du script: ' . date("H:i:s", $timeend), $debug, '', $indexer->getLogFilename());
Ccsd_Log::message('Script executé en ' . number_format($time, 3) . ' sec.', $debug, '', $indexer->getLogFilename());
exit(0);



