<?php

/**
 * Adapter base de données CAS
 * @author Yannick Barborini
 *
 */
class Ccsd_Db_Adapter_Cas extends Ccsd_Db_Adapter
{
	const USER_TABLE = "T_UTILISATEURS";

	/** @var Zend_Db_Adapter_Pdo_Mysql null : On garde l'adapter pour ne le construire qu'un seule fois! */
	static private $cas_adapter = null;
    /**
     * Retourne l'adapter base de données de la base CAS (utilisateurs)
     * @return Zend_Db_Adapter_Pdo_Mysql
     */
    public static function getAdapter($env = APPLICATION_ENV)
    {
        if (! self::$cas_adapter) {
            //  Pas deja defini, on le construit
            // Lecture de la configuration: On doit utiliser pwd.json
            //$config = new Zend_Config_Ini(__DIR__ . '/Config/cas.ini', $env);
            //self::$_params = $config->db->toArray();
            self::$_params = ['dbname' => CAS_NAME, 'port' => CAS_PORT, 'username' => CAS_USER, 'host' => CAS_HOST, 'password' => CAS_PWD ];
            self::$cas_adapter = parent::getAdapter();
        }
        return self::$cas_adapter;
    }
}
