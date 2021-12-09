<?php

class Ccsd_Auth extends Zend_Auth
{

    public static function isLogged ()
    {
        return self::getInstance()->hasIdentity();
    }

    /**
     *
     * @param object $user
     *            Ccsd_User ou Hal_User ou ...
     */
    public static function setIdentity ($user)
    {
        self::getInstance()->getStorage()->write($user);
    }

    public static function getUser ()
    {
    	return self::getInstance()->getIdentity();
    }

    public static function getUsername ()
    {
        return self::getInstance()->getIdentity()->getUsername();
    }

    public static function getUid ()
    {
        return self::isLogged() ? self::getInstance()->getIdentity()->getUid() : 0;
    }


    public static function getFullName ()
    {
        return self::getInstance()->getIdentity()->getFullName();
    }


    public static function getRoles ()
    {}



}