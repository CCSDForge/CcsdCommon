<?php

/**
 * Gestion des alias issus des fusions dans AuréHAL
 *
 * PHP version 5
 * 
 * @category CategoryName
 * @package  PackageName
 * @author   Original Author <author@example.com>
 * @license    
 * @link       
 */

/**
 * Classe permettant d'historiser les fusions dans les référentiels
 * REF_ALIAS
 *         REFID+REFNOM+OLDREFID clé primaire
 *         REFID cle primaire de l'enregistrement destination de la fusion
 *         REFNOM nom de la table qui correspond au core solR en majuscule
 *         OLDREFID clé primaire de l'enregistrement fusionné et supprimé
 *         DATEMDIF date de la fusion
 *
 * @category CategoryName
 * @package  PackageName
 * @author   Original Author <author@example.com>
 * @license    
 * @link
 */

class Ccsd_Referentiels_Alias
{

    /* propriétés obligatoires */
    protected $_name = 'ref_alias';
    /*
     * triplet de données représentant l'id primaire de la classe des alias
     */
    protected $_primary = ['REFID', 'REFNOM', 'OLDREFID'];
    
    static public $core = 'ref_alias';
        
    protected static $_table = 'REF_ALIAS';

    protected static $cles = ['REFID', 'REFNOM', 'OLDREFID', 'OLDREFMD5'];
    private static $ref_cles = ['REF_AUTHOR'=>'AUTHORID', 'REF_STRUCTURE'=>'STRUCTID', 'REF_PROJANR'=>'ANRID', 'REF_PROJEUROP'=>'PROJEUROPID', 'REF_JOURNAL'=>'JID', 'REF_HCERES'=>'HCERESID'];
    
     /**
     * Recherche de la liste des fusions d'un element du referentiel
     * limitée à un seul niveau de fusion
     * 
     * @param int    $id     identifiant de l'element referentiel
     * @param string $refnom nom du referentiel
     * 
     * @return array tableau des anciens alias
     */
    public static function getalias($id, $refnom)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        
        $sql = $db->select()->from(self::$_table)
                ->where(self::$cles[0] . " = ?", (int) $id)
                ->where(self::$cles[1] . " = ?", strtoupper($refnom))
            ->order('DATEMODIF DESC');

        return $db->fetchAll($sql);
    }   

     /**
     * Recherche récursive de la liste des fusions d'un element du referentiel
     * 
     * @param int    $id     identifiant de l'element referentiel
     * @param string $refnom nom du referentiel
     * 
     * @return array tableau de tous les anciens alias
     */
    
    public static function getAllAlias($id, $refnom)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $allAlias=[];
        if ($id != 0) {
            $sql = $db->select()->from(self::$_table)
                ->where(self::$cles[0] . " = ?", (int) $id)
                ->where(self::$cles[1] . " = ?", strtoupper($refnom))
                ->order('DATEMODIF DESC');

            $allAlias = $db->fetchAll($sql);
            foreach ($allAlias as $ligne) {
                $aliases = self::getAllAlias($ligne['OLDREFID'], $refnom);
                if (count($aliases) >0) {
                    $allAlias = array_merge($allAlias, $aliases);
                }
            }
        }
        return $allAlias;
    }

    /**
     * Recherche du nouvel objet d'un élément du référentiel fusionné
     * 
     * @param int    $id     ancien identifiant de l'element referentiel
     * @param string $refnom nom du referentiel
     * 
     * @return int  identifiant
     */
 
    public static function getAliasNewId($id, $refnom)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = $db->select()
            ->from(['alias'=>self::$_table], 'alias.REFID')
            ->from(['ref'=>strtoupper($refnom)], '')
            ->where('alias.REFID = ref.'.self::$ref_cles[strtoupper($refnom)])
            ->where(self::$cles[2] . " = ?", (int)$id)
            ->where(self::$cles[1] . " = ?", strtoupper($refnom))
            ->order(['DESC'=> 'alias.DATEMODIF' ] );

        /* Old ->order(['DESC'=>'ref.VALID','DESC'=> 'alias.DATEMODIF']); */
        return $db->fetchOne($sql);
    }   
    
    /**
     * Ajout d'un alias sur un element du referentiel
     * 
     * @param int    $refid    identifiant du nouvel element du referentiel
     * @param string $refnom   nom du référentiel concerné
     * @param int    $oldrefid identifiant de l'ancien élément du référeniel
     * @param string $time     date de la fusion (facultatif)
     * @param int    $md5      md5 de l'ancien élément du référeniel (facultatif)
     * 
     * @return int résultat de l'insertion dans la base
     * @throws Zend_Db_Adapter_Exception
     */
    public static function add($refid, $refnom, $oldrefid, $time = null, $md5 = null)
    {
        $bind = array(
            self::$cles[0]	=>	$refid,
            self::$cles[1]	=>	strtoupper($refnom),
            self::$cles[2]	=>	$oldrefid,
        );   
        
        if ($time) {
            $bind['DATEMODIF'] = $time; 
        }
        
        if ($md5) {
            $bind['OLDREFMD5'] = $md5;
        }
        // insertion de l'alias
        $ret = Zend_Db_Table_Abstract::getDefaultAdapter()->insert(self::$_table, $bind);
        // Si l'ancienne reference etait deja pointee par de plus ancienne, il faut
        // faire pointer les plus ancienne vers le nouveau egalement.
        $updateData = [ 'REFID' => $refid ];
        Zend_Db_Table_Abstract::getDefaultAdapter()->update(self::$_table, $updateData, "REFID = $oldrefid");

        return( $ret);
    }
    
    /**
     * Recherche récursive du nouvel identifiant d'un element du referentiel remplacé
     * 
     * @param int    $id     identifiant de l'ancien element referentiel
     * @param string $refnom nom du referentiel
     * @param int   $md5     md5 de l'ancien element referentiel
     * 
     * @return boolean élément trouvé ou pas
     *
     *
     * FONCTION NON UTILISEE ? */
    public static function getNewValidRef($id = null, $refnom, $md5 = null)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
               
        $sql = $db->select()->from(self::$_table)
            ->where(self::$cles[1] . " = ?", strtoupper($refnom));
            
        if ($id != 0) {
            $sql->where(self::$cles[2] . " = ?", (int) $id); 
        }
        else if ($md5 != 0) {
            $sql->where(self::$cles[3] . " = ?", new Zend_Db_Expr('UNHEX("' . $md5 . '")'));
        }
        else  {
            return false;
        }

        $newAlias = $db->fetchOne($sql);
        while ($alias= self::getAliasNewId($newAlias['REFID'], $refnom))
        {
            $newAlias = $alias;
        }
          
        return $newAlias;
    }
    
    /**
     * Recherche d'un element du referentiel remplacé par son md5
     * 
     * @param int   $md5     md5 de l'ancien element referentiel à chercher
     * 
     * @return int          nouvel identifiant de l'element referentiel
     */
    
    public static function existMd5($md5, $refnom = "")
    {
        if (!isset($md5) || ($md5 == 0)) {
            return false;
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
               
        $sql = $db->select()->from(self::$_table, self::$cles[0])
            ->where(self::$cles[3] . " = ?", new Zend_Db_Expr('UNHEX("' . $md5 . '")'));
        if ($refnom != "") {
            $sql->where(self::$cles[1] . " = ?", strtoupper($refnom)); 
        }
        $newAlias = $db->fetchOne($sql);
        return $newAlias;
    }

}