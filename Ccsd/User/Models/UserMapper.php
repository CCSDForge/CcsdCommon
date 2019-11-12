<?php

/**
 * Mapper pour le modèle de la table utilisateurs CCSD
 * @author rtournoy
 *
 */
class Ccsd_User_Models_UserMapper {

    /** @var Zend_Db_Table_Abstract */
    protected $_dbTable;
    /**
     * @return Zend_Db_Table_Abstract
     */
    public function setDbTable($dbTable) {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable ();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $dbTable;
    }

    /**
     * @return Zend_Db_Table_Abstract
     */
    public function getDbTable() {
        if (null === $this->_dbTable) {
            $this->setDbTable('Ccsd_User_Models_DbTable_User');
        }

        return $this->_dbTable;
    }

    /**
     * Enregistre un nouveau compte utilisateur ou sauvegarde les modifications
     * d'un existant
     *
     *
     * @param Ccsd_User_Models_User $user
     *        	object Utilisateur
     * @return int
     */
    public function save(Ccsd_User_Models_User $user, $forceInsert = false) {

        // création compte
        if ((null === $user->getUid()) || ( $forceInsert === true)) {

            $data = array(
                'USERNAME' => $user->getUsername(),
                'PASSWORD' => $user->getPassword(),
                'EMAIL' => $user->getEmail(),
                'CIV' => $user->getCiv(),
                'LASTNAME' => $user->getLastname(),
                'FIRSTNAME' => $user->getFirstname(),
                'MIDDLENAME' => $user->getMiddlename(),
                'TIME_REGISTERED' => $user->getTime_registered(),
                'TIME_MODIFIED' => $user->getTime_modified(),
                'VALID' => $user->getValid()
            );

            // Le UID est déjà connu, pas d'autoincrément
            if ($forceInsert === true) {
                $data ['UID'] = $user->getUid();
            }

            try {
                $lastInsertId = $this->getDbTable()->insert($data);
            } catch (Zend_Db_Adapter_Exception $e) {
                return false;
            }
            if ($forceInsert === true) {
                // $user->setUid($lastInsertId);
            } else {
                $user->setUid($lastInsertId);
            }

            $user->setFtp_home();
            $this->saveFtpHome($user);

            // Le UID est déjà connu, pas d'autoincrément
            if ($forceInsert === true) {
                return $user->getUid();
            }

            return (int) $lastInsertId; // UID de l'utilisateur ajouté
        } else {
            // modification compte
            $user->setTime_modified();
            $data = array(
                'UID' => $user->getUid(),
                'PASSWORD' => $user->getPassword(),
                'EMAIL' => $user->getEmail(),
                'CIV' => $user->getCiv(),
                'LASTNAME' => $user->getLastname(),
                'FIRSTNAME' => $user->getFirstname(),
                'MIDDLENAME' => $user->getMiddlename(),
                'TIME_MODIFIED' => $user->getTime_modified(),
                'VALID' => $user->getValid()
            );

            // 'USERNAME' => $user->getUsername(),
            // on ne met à jour le password que si l'utilisateur à rempli le
            // champ
            if (null == $user->getPassword()) {
                unset($data ['PASSWORD']);
            }

            if (null == $user->getValid()) {
                unset($data ['VALID']);
            }

            $result = $this->getDbTable()->update($data, array(
                'UID = ?' => $user->getUid()
            ));

            if (1 != $result) {
                return false;
            } else {
                return $user->getUid();
            }
        }
    }

    /**
     *
     * @param Ccsd_User_Models_User $user
     * @return boolean
     */
    private function saveFtpHome(Ccsd_User_Models_User $user) {
        $data = array(
            'FTP_HOME' => $user->getFtp_home()
        );

        try {
            $result = $this->getDbTable()->update($data, array(
                'UID = ?' => $user->getUid()
            ));

            if (1 != $result) {
                return false;
            } else {
                return true;
            }
        } catch (Zend_Db_Adapter_Exception $e) {
            return false;
        }
    }

    /**
     *
     * @param Ccsd_User_Models_User $user
     * @return boolean
     */
    private function saveFtpQuota(Ccsd_User_Models_User $user) {
        $ufq = new Ccsd_User_Models_UserFtpQuota(array(
            'username' => $user->getUsername()
        ));
        $ufq->save();
    }

    /**
     * Sauvegarde le mot de passe de l'utilisateur
     *
     * @param Ccsd_User_Models_User $user
     */
    public function savePassword(Ccsd_User_Models_User $user) {
        $data = array(
            'UID' => $user->getUid(),
            'PASSWORD' => $user->getPassword(),
            'TIME_MODIFIED' => $user->getTime_modified()
        );

        $affectedRows = $this->getDbTable()->update($data, array(
            'UID = ?' => $user->getUid()
        ));
        return $affectedRows;
    }

    /**
     * Recherche un utilisateur par son UID
     *
     * @param integer $uid
     * @param Ccsd_User_Models_User $user
     * @return Zend_Db_Table_Row_Abstract
     *     */
    public function find($uid, Ccsd_User_Models_User $user = null) {


        $select = $this->getDbTable()->select()->where('UID = ?', $uid);

        $select->from($this->getDbTable(), ['UID',
            'USERNAME',
            'EMAIL',
            'CIV',
            'FIRSTNAME',
            'MIDDLENAME',
            'LASTNAME',
            'TIME_REGISTERED',
            'TIME_MODIFIED',
            'VALID']);

        $row = $this->getDbTable()->fetchRow($select);


        if (!isset($row) || 0 == count($row->toArray())) {
            return null;
        }
        if ($user != null) {
            $user->setUid($row->UID)->setUsername($row->USERNAME)->setEmail($row->EMAIL)->setCiv($row->CIV)->setLastname($row->LASTNAME)->setFirstname($row->FIRSTNAME)->setMiddlename($row->MIDDLENAME);
            $user->setTime_registered($row->TIME_REGISTERED)->setTime_modified($row->TIME_MODIFIED)->setValid($row->VALID);
        }
        return $row;
    }

    /**
     * Cherche des logins à partir d'une adresse mail
     *
     * @param string $email
     * @return NULL Zend_Db_Table_Rowset_Abstract
     */
    public function findLoginByEmail($email) {
        $select = $this->getDbTable()->select()->where('EMAIL = ?', $email)->order(array(
            'VALID DESC',
            'USERNAME ASC'
        ));

        $select->from($this->getDbTable(), array(
            'USERNAME',
            'TIME_REGISTERED',
            'VALID'
        ));

        $rows = $this->getDbTable()->fetchAll($select);

        if (! $rows) {
            return null;
        }

        return $rows;
    }

    /**
     * Trouve un utilisateur avec un compte actif, par son login, sinon renvoi
     * null
     *
     * @param string $username
     * @return null|Zend_Db_Table_Rowset_Abstract object:
     */
    public function findByUsername($username) {
        $select = $this->getDbTable()->select()->from($this->getDbTable())->where('USERNAME = ?', $username)->where('VALID= ?', 1);
        $rows = $this->getDbTable()->fetchAll($select);
        return $rows;
    }

    /**
     * Trouve un utilisateur UID
     * @param int $uid
     * @return Zend_Db_Table_Row_Abstract
     */
    public function findByUID($uid) {
        $select = $this->getDbTable()->select()->where('UID = ?', (int) $uid);

        $select->from($this->getDbTable(), ['UID',
            'USERNAME',
            'EMAIL',
            'CIV',
            'FIRSTNAME',
            'MIDDLENAME',
            'LASTNAME',
            'TIME_REGISTERED',
            'TIME_MODIFIED']);

        $row = $this->getDbTable()->fetchRow($select);

        if (count($row)) {
            return $row;
        }
        return null;
    }

    /**
     * Trouve un utilisateur avec un compte actif, par son login ou UID, sinon renvoi
     * null
     *
     * @param string|int $info
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function findByUsernameOrUID($info) {
        $select = $this->getDbTable()->select()->where('USERNAME = ? OR UID = ?', $info)->where('VALID= ?', 1);

        $select->from($this->getDbTable(), ['UID',
            'USERNAME',
            'EMAIL',
            'CIV',
            'FIRSTNAME',
            'MIDDLENAME',
            'LASTNAME',
            'TIME_REGISTERED',
            'TIME_MODIFIED']);

        $rows = $this->getDbTable()->fetchAll($select);

        if (count($rows) == 0) {
            return null;
        }
        return $rows;
    }

    /**
     * @param string $username
     * @param string $password
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function findByUsernamePassword($username, $password) {
        $user = new Ccsd_User_Models_User(array(
            'USERNAME' => $username,
            'PASSWORD' => $password
        ));
        $password = $user->getPassword();
        $username = $user->getUsername();

        $select = $this->getDbTable()->select()->where('USERNAME = ?', $username)->where('PASSWORD = ?', $password)->where('VALID= ?', 1);

        $select->from($this->getDbTable(), array(
            'UID',
            'USERNAME',
            'EMAIL',
            'CIV',
            'LASTNAME',
            'FIRSTNAME',
            'MIDDLENAME',
            'TIME_REGISTERED',
            'TIME_MODIFIED'
        ));
        $rows = $this->getDbTable()->fetchAll($select);

        if (0 == count($rows)) {
            return null;
        }

        return $rows->current();
    }

    /**
     * Indique si un compte est valide (a été activé
     * @param $uid
     * @return bool
     */
    public function accountValidity($uid)
    {
        $uid = intval(filter_var($uid, FILTER_SANITIZE_NUMBER_INT));
        $select = $this->getDbTable()->select()->from($this->getDbTable(), ['VALID'])->where('UID = ?', $uid);
        $row =  $this->getDbTable()->fetchRow($select);
        return boolval($row['VALID']);
    }

    /**
     * Active un compte selon son UID
     *
     * @param integer $uid
     * @return boolean true si succès | false si échec
     */
    public function activateAccountByUid($uid) {
        $uid = intval(filter_var($uid, FILTER_SANITIZE_NUMBER_INT));

        $result = $this->getDbTable()->update(array(
            'VALID' => 1
                ), array(
            'UID = ?' => $uid
        ));

        if ($result != 1) {
            throw new Zend_Db_Adapter_Exception("Erreur lors de l'activation du compte. Echec de la requête.");
        }

        // cherche le username de l'utilisateur activé
        $userMapper = new Ccsd_User_Models_UserMapper ();
        $user = new Ccsd_User_Models_User ();

        $userResult = $userMapper->find($uid, $user);
        $user->setUsername($userResult->USERNAME);

        // ajoute quota FTP à l'utilisateur activé
        $ufq = new Ccsd_User_Models_UserFtpQuota(array(
            'username' => $user->getUsername()
        ));

        $result = $ufq->save();

        if ($result == false) {
            throw new Zend_Db_Adapter_Exception("Erreur lors de l'activation des quotas FTP du compte.");
        }
    }

    /**
     * Désactive un compte selon son UID
     *
     * @param integer $uid
     * @return boolean true si succès | false si échec
     */
    public function terminateAccountByUid($uid) {
        $uid = intval(filter_var($uid, FILTER_SANITIZE_NUMBER_INT));

        $result = $this->getDbTable()->update(array(
            'VALID' => 0
                ), array(
            'UID = ?' => $uid
        ));

        if ($result != 1) {
            throw new Zend_Db_Adapter_Exception("Erreur lors de désactivation du compte. Echec de la requête.");
        }

        return true;
    }

    /**
     * Liste des fichiers sur le compte FTP de l'utilisateur
     *
     * @param int $uid
     *        	le UID de l'utilisateur
     * @throws InvalidArgumentException
     * @throws Exception
     * @return array liste de fichiers ou null si pas de fichiers/pas de
     *         répertoire FTP
     */
    static function getUserHomeFtpFiles($uid = null) {
        if ($uid == null) {
            throw new InvalidArgumentException('Le UID utilisateur manque.');
        }

        $uid = intval(filter_var($uid, FILTER_SANITIZE_NUMBER_INT));

        if ($uid <= 0) {
            throw new InvalidArgumentException('Le UID utilisateur doit être supérieur à 0.');
        }

        if (!defined('Ccsd_User_Models_User::CCSD_FTP_PATH')) {
            throw new Exception('La constante Ccsd_User_Models_User::CCSD_FTP_PATH doit être définie.');
        }

        $pathName = Ccsd_User_Models_User::CCSD_FTP_PATH . $uid;

        if (!file_exists($pathName)) {
            return null;
        }

        if (!is_readable($pathName)) {
            throw new Exception($pathName . ' ne peux pas être lu.');
        }

        $dir = new DirectoryIterator($pathName);

        $fileListArray = array();

        foreach ($dir as $fileinfo) {
            if ($fileinfo->isFile()) {
                $fileListArray [] = $fileinfo->getFilename();
            }
        }

        if (empty($fileListArray)) {
            return null;
        }

        return $fileListArray;
    }

    /**
     * Enregistre le changement d'identité d'un utilisateur
     *
     * @param int $fromUid
     * @param int $toUid
     * @param string $application
     * @param string $action
     *        	[GRANTED|DENIED]
     */
    static public function suLog($fromUid, $toUid, $application = 'unknown', $action) {
        $db = new Ccsd_User_Models_DbTable_SuLog ();
        try {
            $data = array(
                'FROM_UID' => (int) $fromUid,
                'TO_UID' => (int) $toUid,
                'APPLICATION' => $application,
                'ACTION' => $action
            );

            $db->insert($data);
        } catch (Exception $e) {
            return false;
        }
    }

}

///end Class
