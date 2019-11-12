<?php
/**
 * Lien entre les modifications des entrées des référentiels et les documents
 *
 */

class Ccsd_Referentiels_Update
{
    const TABLE = 'REF_UPDATE_DOC';
    
    static public function add($referentiel, $currentId, $deletedId = 0)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        if (!is_array ($deletedId)) {
        	$deletedId = array ($deletedId);
        }
        
        $bind = array(
            'REF'         =>  $referentiel,
            'CURRENTID'   =>  $currentId,
            'DELETEDID'   =>  Zend_Json::encode ($deletedId),
        );
        try {
            return $db->insert(self::TABLE, $bind);
        } catch(Exception $e) {return false;}
    }
    
    public static function moveDocument ($docids, $from, $to, $referentiel)
    {
    	return self::add ($referentiel, $to, array($from => $docids));
    }

    static public function lockRows($limit = 0)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        try {
            $sqlUpdate = "UPDATE ".self::TABLE." SET STATUS = ? WHERE STATUS = ? ORDER BY DATEMODIF ASC LIMIT ".$limit;
            return $db->query($sqlUpdate, array('locked', 'todo'));
        } catch(Exception $e) {return $e->getMessage();}
    }

    static public function getLockedRows()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        try {
            $sql = $db->select()
                       ->from(self::TABLE, array('UPDATEID', 'REF', 'CURRENTID', 'DELETEDID'))
                       ->where("STATUS = 'locked'");
            return $db->fetchAll($sql);
        } catch(Exception $e) {return array();}
    }

    static public function done($id = 0)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        try {
            if ( $id == 0 ) {
                return false;
            }
            $sql = "DELETE from ".self::TABLE." WHERE UPDATEID = ".(int)$id." LIMIT 1";
            return $db->query($sql);
        } catch(Exception $e) {return false;}
    }

    static public function error($id = 0)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        try {
            if ( $id == 0 ) {
                return false;
            }
            $sql = "UPDATE ".self::TABLE." SET STATUS = 'error' WHERE UPDATEID = ".(int)$id." LIMIT 1";
            return $db->query($sql);
        } catch(Exception $e) {return false;}
    }

    static public function process($row)
    {
        if ( isset($row['UPDATEID']) && isset($row['REF']) && isset($row['CURRENTID']) && isset($row['DELETEDID']) && in_array($row['REF'], array('ref_journal','ref_projanr','ref_projeurop','ref_author','ref_structure')) ) {
            try {
                $docids = array();
                try {
                    $row['DELETEDID'] = Zend_Json::decode($row['DELETEDID']);
                    if (is_array($row['DELETEDID'])) {
                        if (is_array(current($row['DELETEDID']))) { // modification des documents
                            $docids = current($row['DELETEDID']);
                            $row['DELETEDID'] = key($row['DELETEDID']);
                        } else { // remplacement d'element
                            $row['DELETEDID'] = current($row['DELETEDID']);
                        }
                    }
                } catch (Exception $e) {
                    $row['DELETEDID'] = null;
                }
                if ( $row['REF'] == 'ref_structure' ) {
                    $structid = ( $row['DELETEDID'] == 0 ) ? $row['CURRENTID'] : $row['DELETEDID'];
                    $childsStrucid = Ccsd_Referentiels_Structure::getAllChildsId($structid);
                    if ( $row['DELETEDID'] != 0 ) { // update dans REF_STRUCT_PARENT
                        Ccsd_Referentiels_Structure::updateChilds($row['DELETEDID'], $row['CURRENTID']);
                    }
                    if (isset($childsStrucid)) {
                        // indexation des structures modifiées
                        Ccsd_Search_Solr_Indexer::addToIndexQueue($childsStrucid, 'AUREHAL', 'UPDATE', Ccsd_Referentiels_Structure::$core);
                        // indexation des documents liés
                        $docids = Ccsd_Referentiels_Structure::getRelatedDocid(array_merge(array($structid), $childsStrucid));
                    }
                    if ( $row['DELETEDID'] != 0 ) {
                        Hal_Document_Structure::replace($row['DELETEDID'], $row['CURRENTID']);
                        //modification des formes auteurs referencant l'ancienne structure
                        if (Ccsd_Referentiels_Author::updateAuthorStructId($row['DELETEDID'], $row['CURRENTID']) > 0) {
                            // indexation des auteurs modifiés
                            $auteurs = Ccsd_Referentiels_Structure::getRelatedAuthorid($row['DELETEDID']);
                            if ((isset($auteurs)) && (is_array($auteurs))) {
                                Ccsd_Search_Solr_Indexer::addToIndexQueue($auteurs, 'AUREHAL', 'UPDATE', Ccsd_Referentiels_Author::$core);
                            }
                        }
                        $comment = 'Structure '.$row['DELETEDID'].' replaced by '.$row['CURRENTID'].' in AureHAL';
                    } else {
                        $comment = 'Structure '.$row['CURRENTID'].' updated in AureHAL';
                    }
                } else {
                    switch ( $row['REF'] ) {
                        case 'ref_journal' :
                            $class = 'Ccsd_Referentiels_Journal';
                            $comment = 'Journal ';
                        break;
                        case 'ref_projanr' :
                            $class = 'Ccsd_Referentiels_Anrproject';
                            $comment = 'ANR project ';
                            break;
                        case 'ref_projeurop' :
                            $class = 'Ccsd_Referentiels_Europeanproject';
                            $comment = 'European project ';
                            break;
                        case 'ref_author' :
                            $class = 'Ccsd_Referentiels_Author';
                            $comment = 'Author ';
                            break;
                    }
                    if ( $row['DELETEDID'] == 0 ) { // update de l'objet $row['CURRENTID']
                        /* @var $class Ccsd_Referentiels_Abstract */
                        $docids = $class::getRelatedDocid($row['CURRENTID']);
                        $comment .= $row['CURRENTID'].' updated in AureHAL';
                    } else { // remplacement de l'objet $row['DELETEDID'] par $row['CURRENTID']
                        if ( count($docids) == 0 ) {
                        $docids = $class::getRelatedDocid($row['DELETEDID']);
                            $comment .= $row['DELETEDID'].' replaced by '.$row['CURRENTID'].' in AureHAL';
                        } else {
                            $comment .= $row['DELETEDID'].' replaced by '.$row['CURRENTID'].' in AureHAL for '.implode(',', $docids);
                        }
                        if ( $row['REF'] == 'ref_author' ) {
                            Hal_Document_Author::replace($row['DELETEDID'], $row['CURRENTID'], $docids);
                        } else {
                            Hal_Document::updateMeta($docids, $class::METANAME, $row['CURRENTID'], $row['DELETEDID']);
                        }
                    }
                }
                if ( count($docids) ) {
                    foreach ($docids as $docid){
                        Hal_Document::changeDateModif($docid);
                    }
                    Hal_Document::deleteCaches($docids); //suppression des caches
                    Ccsd_Search_Solr_Indexer::addToIndexQueue($docids, 'AUREHAL'); //ré-indexation
                }
                return count($docids);
            } catch(Exception $e) {return false;}
        } else {
            return false;
        }
    }
}