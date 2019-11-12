<?php

class  Ccsd_Paper_Log
{
	const ACTION_SUBMITTED	=	'submitted'; // Papier déposé
	const ACTION_REPLACED	=	'replaced'; // Nouvelle version d'un papier déposé
	const ACTION_UPDATED	=	'updated'; // Papier modifié
	const ACTION_REVIEWED	=	'reviewed'; // Papier relu
	const ACTION_ACCEPTED	=	'accepted'; // Papier accepté
	const ACTION_REFUSED	=	'refused'; // Papier refusé
	const ACTION_DELETED	=	'deleted'; // Papier supprimé

	const ACTION_ANNOTATE	=	'annotate'; // Annotation d'un papier (spécifique HAL)
	const ACTION_ADDFILE	=	'addfile'; // Ajout du texte intégral (spécifique HAL)
	const ACTION_JREF		=	'jref'; // Ajout références publication (spécifique HAL)
	const ACTION_CROSS		=	'cross'; // Modifications disciplines (spécifique HAL)
	const ACTION_STAMPED	=	'stamped'; // Tamponnage d'un papier (spécifique HAL)
	const ACTION_UNSTAMPED	=	'unstamped'; // Détamponnage d'un papier (spécifique HAL)
	const ACTION_ONLINE		=	'online'; // Mise en ligne d'un papier après embargo (spécifique HAL)
    
    const TABLE = 'PAPER_LOGS';
    /* table logs à créer sur chaque plateforme :
    *  CREATE TABLE IF NOT EXISTS `PAPER_LOGS` (
 	*	 `LOGID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	*	 `DOCID` int(11) UNSIGNED NOT NULL,
	*	 `UID` int(11) UNSIGNED NOT NULL,
	*	 `ACTION` varchar(25) NOT NULL,
	*	 `COMMENT` text NOT NULL,
	*	 `WHEN` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	*	 PRIMARY KEY (`LOGID`)
	*	) ENGINE=MyISAM DEFAULT CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = 'Locale Paper Logs' AUTO_INCREMENT=1 ;*/
     
    
    /**
     * Log des actions effectuées sur un papier
     * @param int $docid identifiant du papier
     * @param int $uid identifiant du compte
     * @param int $action action à logger
     * @param string $comment commentaire optionnel
     */
    public static function add($docid, $uid, $action, $comment = '')
    {
        $bind = array(
        	'DOCID'	=>	$docid,
        	'UID'	=>	$uid,
        	'ACTION'	=>	$action,
        	'COMMENT'	=>	$comment,
        );
    	
    	Zend_Db_Table_Abstract::getDefaultAdapter()->insert(self::TABLE, $bind);
    }
    
    /**
     * Retourne les logs d'un papier
     * @param int $docid identifiant du papier
     */
    public static function get($docid)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = $db->select()
                    ->from(self::TABLE)
                    ->where('DOCID = ?', $docid)
        			->order('WHEN DESC');
        return $db->fetchAll($select); 
    }
    
    /**
     * Suppression des logs d'un papier
     * @param int $docid
     */
    public static function delete($docid)
    {
    	Zend_Db_Table_Abstract::getDefaultAdapter()->delete(self::TABLE, 'DOCID = '. (int) $docid);
    }
}