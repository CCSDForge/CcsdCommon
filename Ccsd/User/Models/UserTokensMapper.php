<?php

/**
 * Mapper pour l'objet UserTokens
 * @author rtournoy
 *
 */
class Ccsd_User_Models_UserTokensMapper
{

    protected $_dbTable;

    public function setDbTable ($dbTable)
    {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (! $dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }

    public function getDbTable ()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable('Ccsd_User_Models_DbTable_UserTokens');
        }
        return $this->_dbTable;
    }

    /**
     * Enregistre un token
     *
     * @param Ccsd_User_Models_UserTokens $userTokens
     * @return integer Dernier ID de token enregistré
     */
    public function save (Ccsd_User_Models_UserTokens $userTokens)
    {
        $data = array(
                'UID' => $userTokens->getUid(),
                'EMAIL' => $userTokens->getEmail(),
                'TOKEN' => $userTokens->getToken(),
                'TIME_MODIFIED' => $userTokens->getTime_modified(),
                'USAGE' => $userTokens->getUsage()
        );

        $dbTable = $this->getDbTable();

        $lastInsertId = $dbTable->insert($data);

        return $lastInsertId;
    }

    /**
     * Vérifie si un token existe
     * Si oui retourne les infos sur la ligne du token
     *
     * @param string $token
     * @param Ccsd_User_Models_UserTokens $userTokens
     * @return null|Ccsd_User_Models_UserTokens
     */
    public function findByToken ($token, Ccsd_User_Models_UserTokens $userTokens)
    {
        $result = $this->getDbTable()->find($token);
        if (0 == count($result)) {
            return null;
        }

        $row = $result->current();

        $userTokens->setUid($row->UID)
            ->setEmail($row->EMAIL)
            ->setToken($row->TOKEN)
            ->setTime_modified($row->TIME_MODIFIED)
            ->setUsage($row->USAGE);

        return $userTokens;
    }

    /**
     * Supprime un token
     *
     * @param string $token
     * @param Ccsd_User_Models_UserTokens $userTokens
     */
    public function delete ($token, Ccsd_User_Models_UserTokens $userTokens)
    {
        $this->getDbTable()->delete(
                array(
                        'TOKEN = ?' => $token
                ));
    }
}

