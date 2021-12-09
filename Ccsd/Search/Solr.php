<?php

/**
 *
 * @author rtournoy
 *
 */
class Ccsd_Search_Solr {

    /**
     * A synchroniser avec le schema de Solr si changement
     *
     * @var string
     */
    const SOLR_ALPHA_SEPARATOR = '_AlphaSep_';

    /** @const string endpoint pour interrogation */
    const ENDPOINT_RESPONDER = 'responder';

    /** @const string endpoint pour indexation */
    const ENDPOINT_MASTER = 'master';

    /** @const string endpoint par défaut */
    const ENDPOINT_DEFAULT = 'defaultEndpoint';

    /**
     * A synchroniser avec le schema de Solr si changement
     *
     * @var string
     */
    const SOLR_FACET_SEPARATOR = '_FacetSep_';

    /**
     * A synchroniser avec le schema de Solr si changement
     *
     * @var string
     */
    const SOLR_JOIN_SEPARATOR = '_JoinSep_';

    /** @const int Nombre de facettes retournées par défaut */
    const SOLR_MAX_RETURNED_FACETS_RESULTS = 1000;

    /**
     * Tableau des enpoints utilisés par Solarium.
     * 'master' est la machine d'indexation
     *
     * @var array
     */
    private $_endpoints = [];

    /**
     * Core Solr
     * @var string
     */
    private $_core;

    /**
     * Handler de requête pour solr
     * @var string
     */
    private $_handler;


    /**
     * Ccsd_Search_Solr constructor.
     * @param array $options
     * @throws Ccsd_FileNotFoundException
     * @throws Zend_Config_Exception
     */
    public function __construct($options = null) {
        if ($options == null) {
            return null;
        }

        $this->setOptions($options);
        return $this;
    }

    /**
     * Retourne la clé utilisée dans le fichier ini pour trouver les paramètres du core
     * @param string $core
     * @return string
     */
    static function getIniCoreKeyName($core) {
        return str_replace(['dev-', 'test-', 'preprod-'], '', $core);
    }

    /**
     * @return mixed
     * @throws Ccsd_FileNotFoundException
     */
    private function getEndpointConfigFilename()
    {
        $env = getenv('SOLR_ENDPOINTSINI');
        if ($env) {
            $files[] = $env;
        }
        $files[] = APPROOT . '/' . CONFIG . '/' . 'endpoints.ini';
        $files[] = __DIR__ . '/Solr/configs/endpoints.ini';
        foreach ($files as $file) {
            if (file_exists($file)) {
                return $file;
            }
        }
        throw new Ccsd_FileNotFoundException('Config file for solr (endpoints.ini) not found in default localizations.');
    }

    /**
     * Définit les options à partir du fichier ini de paramètres
     * @param array $options
     * @return \Ccsd_Search_Solr
     * @throws Ccsd_FileNotFoundException
     * @throws Zend_Config_Exception
     */
    public function setOptions($options) {

        $configFile = $this -> getEndpointConfigFilename();
        $configParams = new Zend_Config_Ini($configFile, $options['env']);

        if (!isset($options[self::ENDPOINT_DEFAULT])) {
            $options[self::ENDPOINT_DEFAULT] = self::ENDPOINT_RESPONDER;
        }

        if ($options[self::ENDPOINT_DEFAULT] != self::ENDPOINT_MASTER && $options[self::ENDPOINT_DEFAULT] != self::ENDPOINT_RESPONDER) {
            $options[self::ENDPOINT_DEFAULT] = self::ENDPOINT_RESPONDER;
        }

        $coreKeyName = self::getIniCoreKeyName($options['core']);

        $coreName = $configParams->core->{$coreKeyName}->name;

        $endpoints = [
            'endpoint' => [
                $options[self::ENDPOINT_DEFAULT] => [
                    'host' => $configParams->endpoints->{$options[self::ENDPOINT_DEFAULT]}->host,
                    'port' => (int) $configParams->endpoints->{$options[self::ENDPOINT_DEFAULT]}->port,
                    'path' => $configParams->endpoints->{$options[self::ENDPOINT_DEFAULT]}->path,
                    'timeout' => (int) $configParams->endpoints->{$options[self::ENDPOINT_DEFAULT]}->timeout,
                    'username' => $configParams->endpoints->{$options[self::ENDPOINT_DEFAULT]}->username,
                    'password' => $configParams->endpoints->{$options[self::ENDPOINT_DEFAULT]}->password,
                    'core' => $coreName
                ]
            ]
        ];
        $endpoints['endpoint'][$options[self::ENDPOINT_DEFAULT]][self::ENDPOINT_DEFAULT] = true;


        $this->setCore($coreName);

        $this->setEndpoints($endpoints);

        if (isset($options['handler'])) {
            $this->setHandler($options['handler']);
        } else {
            $this->setHandler();
        }

        return $this;
    }

    /**
     * Retourne l'Url d'un endpoint
     *
     * @param string $endpointType
     * @return string url du endpoint eg string
     *         'http://ccsdsolrvip.in2p3.fr:8080/solr/hal/select/'
     */
    public function getEndPointUrl($endpointType = self::ENDPOINT_RESPONDER) {
        $endpoints = $this->getEndpoints();
        $endpoint = $endpoints['endpoint'][$endpointType];
        return 'http://' . $endpoint['host'] . ':' . $endpoint['port'] . $endpoint['path'] . '/' . $this->getCore() . '/' . $this->getHandler() . '/';
    }

    /**
     * Retourne les paramètres d'authentification pour un type de endpoint
     * @param string $endpointType
     * @return array
     */
    public function getEndPointAuth($endpointType = self::ENDPOINT_RESPONDER) {
        $endpoints = $this->getEndpoints();
        $auth['username'] = $endpoints['endpoint'][$endpointType]['username'];
        $auth['password'] = $endpoints['endpoint'][$endpointType]['password'];

        return $auth;
    }

    /**
     *
     * @return array $_endpoints
     */
    public function getEndpoints() {
        return $this->_endpoints;
    }

    public function toArray() {
        return $this->_endpoints;
    }

    /**
     *
     * @param array $_endpoints
     */
    public function setEndpoints($_endpoints) {
        $this->_endpoints = $_endpoints;
        return $this;
    }

    static function facetStringResultAsArray($string) {
        return explode(self::SOLR_FACET_SEPARATOR, $string);
    }

    /**
     *
     * @return string $_core
     */
    public function getCore() {
        return $this->_core;
    }

    /**
     *
     * @return string $_handler
     */
    public function getHandler() {
        return $this->_handler;
    }

    static public function getConstantesFacet() {
        return [
            self::SOLR_ALPHA_SEPARATOR,
            self::SOLR_FACET_SEPARATOR,
            self::SOLR_JOIN_SEPARATOR
        ];
    }

    /**
     * Get solr Core
     * @param string $_core
     */
    public function setCore($_core) {
        $this->_core = $_core;
        return $this;
    }

    /**
     * "select" est le handler par défaut pour la recherche dans solr
     *
     * @param string $_handler
     */
    public function setHandler($_handler = 'select') {
        $this->_handler = $_handler;
        return $this;
    }

}
