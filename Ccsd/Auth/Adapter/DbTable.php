<?php

/**
 * Adapter Zend_Auth pour l'authentification via Mysql
 *
 * @author ccsd
 *
 */
class Ccsd_Auth_Adapter_DbTable extends  Zend_Auth_Adapter_DbTable implements \Ccsd\Auth\Adapter\UserManager
{
    /** pour compatibilite avec Auth_Adapter_Cas
    // Mais je vois pas encore bien l'utilite!
     */
    public function  setIdentityStructure($identity) {
        $this->_identity = $identity;
    }

    /**
     * @param string $foobar unused parameter
     */
    public function logout($foobar) {
        Zend_Auth::getInstance()->clearIdentity();
        Zend_Session::destroy();
    }

    /**
     * Get user create form for this Adapter
     * @return Ccsd_User_Form_Accountcreate
     */
    public function getUserCreateForm() {
        return new Ccsd_User_Form_Accountcreate (['ini' => 'Ccsd/User/configs/accountcreate.ini', null]);
    }

    /**
     * @param Hal_User $user
     * TODO Remplacer Hal_User par Ccsd...User
     */
    public function completeUserInfoIfNeeded($user) {
        $user -> setPassword(sha1(time() . uniqid(rand(), true)));
    }
}

