<?php

/**
 * Fusion de profil utilisateurs
 */
class Ccsd_User_Merge extends Zend_Db_Table_Abstract {

    /**
     * Liste des tables de la base de données
     * @var array
     */
    private $_tables;

    /**
     * Tables de la BDD à ne pas modifier
     * La méthode qui fusionne les profils ne modifiera pas ces tables
     * @var array
     */
    private $_tablesBlacklist = array();

    /**
     * Liste des colonnes d'une table
     * @var array
     */
    private $_columns;

    /**
     * Table des profils utilisateurs dans l'application
     * @var string
     */
    private $_applicationUsersTable;

    /**
     * Db adapter
     * @var object
     */
    protected $_db;

    /**
     * UID de l'utilisateur source
     * @var int
     */
    private $_uidFrom;

    /**
     *
     * @var int
     */
    private $_uidTo;

    /**
     *
     * @var string
     */
    private $_userMergeLogTable;

    /**
     * Constructor
     */
    public function __construct() {
        $this->_db = Zend_Db_Table_Abstract::getDefaultAdapter();
    }

    /**
     * List DB tables
     */
    public function getDbTables() {
        $stmt = $this->_db->query('SHOW TABLES FROM ' . $this->_db->getConfig()['dbname']);
        $stmt->setFetchMode(Zend_Db::FETCH_NUM);
        return $stmt->fetchAll();
    }

    /**
     * Retourne une liste de tables contenant les colonnes du tableau $columnName
     * @param string $columnName
     * @return array
     */
    public function getTablesWithColumnName($columnName) {

        $res = null;
        $tables = $this->getDbTables();
        $blackListTables = $this->getTablesBlacklist();

        foreach ($tables as $k => $v) {
            $tableName = $v[0];

            // Tables à ne pas toucher
            if (in_array($tableName, $blackListTables)) {
                continue;
            }

            $sql = "SHOW COLUMNS FROM `$tableName` WHERE `Field` = '$columnName'";

            try {
                $stmt = $this->_db->query($sql);
                $rows = $stmt->fetchAll();
            } catch (Zend_Db_Exception $exc) {
                // meh
                continue;
            }


            if (isset($rows[0])) {
                $columnName = $rows[0]['Field']; //nomDuChamp

                $res[] = $tableName;
            }
        }


        return $res;
    }

    /**
     * Retourne le nombre de lignes contenant une valeur $colValue dans une table $tableName pour la colonne $columnName
     * @param string $tableName
     * @param string $columnName
     * @param string[|int $colValue
     * @return int  nombre de lignes contenant la valeur
     */
    public function getLineCount($tableName, $columnName, $colValue) {
        try {
            $queryNumberOccurr = $this->_db->select()->from($tableName, array("numberOccurr" => "COUNT(*)"))->where($columnName . ' = ?', $colValue);

            $res = $this->_db->fetchRow($queryNumberOccurr);
            $lineCount = (int) $res["numberOccurr"];
        } catch (Exception $exc) {
            $lineCount = null;
        }

        return $lineCount;
    }

    /**
     * Retourne le nombre d'occurence de la valeur $colValue dans la colonne $columnName
     * @param string $columnName
     * @param mixed $colValue
     * @return array
     */
    public function getValueOccurr($columnName, $colValue) {
        $res = [];
        foreach ($this->getTablesWithColumnName($columnName) as $tableName) {
            $numberOfLines = $this->getLineCount($tableName, $columnName, $colValue);
            if ($numberOfLines != 0) {
                $res[$tableName] = $numberOfLines;
            }
        }
        return $res;
    }

    /**
     * Remplace le profil de l'utilisateur cible par celui de l'utilisateur source
     * Pour le cas ou l'utilisateur cible n'a pas de profil dans l'application
     * @return int nombre de lignes modifiées
     */
    public function moveUserProfile() {

        try {
            $nb = $this->_db->update($this->getApplicationUsersTable(), array('UID' => $this->getUidTo()), 'UID = ' . $this->getUidFrom());
        } catch (Exception $exc) {
            $nb = 0;
        }

        return (int) $nb;
    }

    /**
     * Fusionne les profils utilisateurs
     * @param array $tables Tables à modifier
     * @return array
     */
    public function mergeUsers($tables) {
        $result = [];

        $bindData = array('UID' => $this->getUidTo());

        foreach ($tables as $table) {

            if (in_array($table, $this->getTablesBlacklist())) {
                continue;
            }

            try {
                $result[$table]['ok'] = $this->_db->update($table, $bindData, 'UID = ' . $this->getUidFrom());
            } catch (Exception $e) {
                $result[$table]['error'] = 'Erreur : pas de modification';
            }
        }

        return $result;
    }

    /**
     * Loggue l'action de fusion de profil
     * @param int $uidOfMergeOperator UID de l'utilisateur qui fusionne les profils
     * @return boolean
     */
    public function logUserMerge($uidOfMergeOperator) {

        try {
            $res = $this->_db->insert($this->getUserMergeLogTable(), array('UID_OPERATOR' => (int) $uidOfMergeOperator, 'UID_FROM' => $this->getUidFrom(), 'UID_TO' => $this->getUidTo()));
        } catch (Zend_Db_Adapter_Exception $exc) {
            $res = false;
            error_log($exc->getMessage(), 0);
        }

        return $res;
    }

    /**
     * Supprime un profil utilisateur
     * @return int nombre de profils utilisateurs supprimés
     */
    public function removeUserProfile() {

        try {
            $nb = $this->_db->delete($this->getApplicationUsersTable(), 'UID = ' . $this->getUidFrom());
        } catch (Exception $exc) {
            $nb = 0;
        }

        return (int) $nb;
    }

    public function setUserMergeLogTable($_userMergeLogTable) {
        $this->_userMergeLogTable = $_userMergeLogTable;
        return $this;
    }


    public function getUserMergeLogTable() {
        return $this->_userMergeLogTable;
    }

    public function getTables() {
        return $this->_tables;
    }

    public function setTables($tables) {
        $this->_tables = $tables;
        return $this;
    }

    public function getColumns() {
        return $this->_columns;
    }

    public function setColumns($columns) {
        $this->_columns = $columns;
        return $this;
    }

    public function getTablesBlacklist() {
        return $this->_tablesBlacklist;
    }

    public function setTablesBlacklist($tablesBlacklist) {
        $this->_tablesBlacklist = $tablesBlacklist;
        return $this;
    }

    public function getApplicationUsersTable() {
        return $this->_applicationUsersTable;
    }

    public function setApplicationUsersTable($applicationUsersTable) {
        $this->_applicationUsersTable = $applicationUsersTable;
        return $this;
    }

    public function getUidFrom() {
        return $this->_uidFrom;
    }

    public function getUidTo() {
        return $this->_uidTo;
    }

    public function setUidFrom($uidFrom) {
        $this->_uidFrom = (int) $uidFrom;
        return $this;
    }

    public function setUidTo($uidTo) {
        $this->_uidTo = (int) $uidTo;
        return $this;
    }

}
