<?php

/**
 * Modèle pour la table des utilisateurs CCSD
 * @author rtournoy
 *
 */
class Ccsd_User_Models_DbTable_User extends Zend_Db_Table_Abstract {

    protected $_name = 'T_UTILISATEURS';
    protected $_primary = 'UID';

    public function __construct($env = APPLICATION_ENV) {

        $this->_setAdapter(Ccsd_Db_Adapter_Cas::getAdapter($env));
    }

    public function search($q, $limit = 100, $valid = false) {
        $q = trim($q);
        $sql = $this->select()->from(['U' => $this->_name], ['UID', 'USERNAME', 'EMAIL', 'CIV', 'LASTNAME', 'FIRSTNAME', 'MIDDLENAME', 'TIME_REGISTERED', 'TIME_MODIFIED', 'VALID']);
        if (is_numeric($q)) {
            $sql->where('U.UID = ?', $q);
            $sql->limit(1);
        } else {

            $finalQuery = '%' . $q . '%';

            if (strpos($q, ' ') !== false) {
                //Recherche nom prenom + prenom nom
                $sql->where("(CONCAT_WS(' ', FIRSTNAME, LASTNAME) LIKE ? OR CONCAT_WS(' ', LASTNAME, FIRSTNAME) LIKE ? )", $finalQuery);
            } else {
                $sql->where('(LASTNAME LIKE ? OR USERNAME LIKE ? OR FIRSTNAME LIKE ? OR EMAIL LIKE ? )', $finalQuery);
            }
            $sql->order(['LASTNAME ASC', 'FIRSTNAME ASC', 'EMAIL ASC'])->limit($limit);
        }
        if ($valid) {
            $sql->where('VALID = 1');
        }
        return $this->fetchAll($sql)->toArray();
    }

    /**
     * Retourne la liste des UID associés à une adresse mail
     * @param $email
     * @return array
     */
    public function getUidByEmail($email)
    {
        $db = $this->getAdapter();
        $sql = $db->select()->from($this->_name, 'UID')->where('EMAIL = ?', $email);
        return $db->fetchCol($sql);
    }

}
