<?php

/**
 * Class Ccsd_Db_Adapter_SolrIndexQueue
 * Adapter base de données pour la file d'indexation de solr
 */
class Ccsd_Db_Adapter_SolrIndexQueue extends Ccsd_Db_Adapter
{

    /**
     * Retourne l'adapter base de données pour la file d'indexation de solr
     * @param string $env
     * @return Zend_Db_Adapter_Abstract
     */
    public static function getAdapter($env = APPLICATION_ENV)
    {
        $config = new Zend_Config_Ini(__DIR__ . '/Config/solrIndexQueue.ini', $env);
        self::$_params = $config->db->toArray();
        return parent::getAdapter();
    }
}