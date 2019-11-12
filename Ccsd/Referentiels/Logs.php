<?php
/*
 * Classe permettant de logguer les opérations sur la base de données
 * REF_LOG
 *         ID clé primaire
 *         ID_TAB cle primaire de l'enregistrement dans la table d'origine de l'enregistrement
 *         TABLE_NAME nom de la table qui correspond au core solR en majuscule
 *         DATE_ACTION date courante
 *         UID identifiant de l'utilisateur
 *         ACTION (MODIFIED, CREATED, DELETED, REPLACED_BY, REPLACE)
 *         PREV_VALUES (valeur sérialisée de l'enregistrement précédent)
 */

Class Ccsd_Referentiels_Logs {

    /*
     * Table où sont enregistrés les logs
     */
    protected static $table = 'REF_LOG';
    /*
     * couple de données représentant l'id primaire de la classe des logs
     */
    protected static $cles = array('ID_TAB', 'TABLE_NAME', 'ACTION');
    
    /*
     * Recherche de l'historique des modifications d'un element du referentiel
     * @param int $id identifiant de l'element referentiel
     * @param string $coreSolr core du referentiel
     * returns tableau des modifications
     */
    
    public static function getlogs($id, $coreSolR = null, $action = null)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        
        $sql = $db->select()->from(self::$table)
                ->where(self::$cles[0] . " = ?", (int) $id)
                ->order('DATE_ACTION DESC');

        if ($coreSolR) {
        	$sql->where(self::$cles[1] . " = ?", strtoupper($coreSolR));
        }
        
        if ($action) {
        	$sql->where(self::$cles[2] . " = ?", $action);
        }
       
        return ($db->fetchAll($sql));
    }
    
    public static function find($id)
    {
    	$db = Zend_Db_Table_Abstract::getDefaultAdapter();
    	return $db->fetchRow($db->select()->from(self::$table)->where("ID = ?", $id, Zend_Db::INT_TYPE));
    }
    
    public static function findDeleted($id, $time = null)
    {
    	$db = Zend_Db_Table_Abstract::getDefaultAdapter();
    	
    	$sql = $db->select()->from(self::$table)->where("ID_TAB = ?", $id, Zend_Db::INT_TYPE)->where("ACTION LIKE 'DELETED'");
    	
    	if (isset ($time)) {
    		$sql->where('DATE_ACTION = ?', $time);
    	} else {
    		$sql->limit(1);
    	}
    	
    	return $db->fetchRow($sql);
    }
    
    /**
     * Log des actions effectuées sur un element du referentiel
     * @param int $id identifiant de l'element referentiel
     * @param string $coreSolr core du referentiel
     * @param int $uid identifiant du compte
     * @param int $action action à logger
     * @param string valeur valeur serialisee avant l'action
     */
    public static function log($id, $coreSolR, $uid, $action, $valeur, $time = null)
    {
        $bind = array(
        	self::$cles[0]	=>	$id,
        	self::$cles[1]	=>	 strtoupper($coreSolR),
        	'UID'	=>	$uid,
            'ACTION'	=>	$action,
        	'PREV_VALUES'	=>	$valeur,
        );   

        if ($time) {
        	$bind['DATE_ACTION'] = $time; 
        }
        
    	return( Zend_Db_Table_Abstract::getDefaultAdapter()->insert(self::$table, $bind));
    }

    public static function findByReplaceID ($id)
    {    	
    	ini_set ('max_execution_time', '1');
    	
    	$db = Zend_Db_Table_Abstract::getDefaultAdapter();
    	
    	$statement = new Zend_Db_Statement_Pdo($db, 
    										   $db->select()
							    				  ->from(array ('t1' => self::$table), array ())
							    				  ->join(array ('t2' => self::$table), "t1.ID_TAB = t2.PREV_VALUES", array("t2.ID_TAB"))
							    				  ->where("t1.ID_TAB = ? AND t1.ACTION LIKE 'REPLACED_BY'")
    											  ->where("t2.ACTION LIKE 'REPLACE'"));

    	$statement->execute(array($id));
    	
    	$return = null;
    	while (($id = $statement->fetchColumn(0)) != FALSE) {
    		$statement->execute(array($id));
    		$return = $id;
    	}
    	    	
    	return $return;
    }
}