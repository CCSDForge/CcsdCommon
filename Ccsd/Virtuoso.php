<?php
/**
 * Created by PhpStorm.
 * User: marmol
 * Date: 10/12/18
 * Time: 09:11
 */

namespace Ccsd;

use Ccsd\Virtuoso\Exception;

/**
 * Class Ccsd_Virtuoso
 *
 * curl -i -u "demo:demo" -H "Content-Type: application/sparql-query" http://example.com/DAV/xx/yy
 * curl -H "Content-Type: text/xml" -d @post.xml  https://data.archives-ouvertes.fr/fct/service
 *
 */
class Virtuoso
{
    const VIRTUOSODEFAULTPORT = 8890;
    /** @var string */
    private $_host = null;
    /** @var int */
    private $_port = self::VIRTUOSODEFAULTPORT;
    /** @var string  */
    private $_user = null;
    /** @var string  */
    private $_password = null;
    /** @var string */
    private $_proto = 'https';
    /** @var resource */
    private $httpClient = null;
    /**
     * return Zend_Config
     * @throws Virtuoso\Exception
     */
    private function readConfig() {
        if (!defined('VIRTUOSOINIFILE')) {
            throw new Virtuoso\Exception("Virtuoso need const VIRTUOSOINIFILE to be define.  Look in application.ini");
        }
        try {
            $config = new \Zend_Config_Ini(VIRTUOSOINIFILE);
        } catch (\Zend_Config_Exception $e) {
            \Ccsd_Tools::panicMsg(__FILE__, __LINE__, VIRTUOSOINIFILE . ' virtuoso config file has error:' . $e->getMessage());
            return new \Zend_Config([], false);
        }
        return $config;
    }
    /**
     * Ccsd_Virtuoso constructor.
     * @param string $user
     * @param string $password
     * @param string $host
     * @param int $port
     * @param string $proto
     * @throws Virtuoso\Exception | \Zend_Http_Client_Exception
     */
    public function __construct($user= null, $password= null, $host = null, $port = null, $proto = 'https')
    {
        $config = $this->readConfig();
        if($host === null) {
            $this -> setHost($config -> get('host', null));
        }
        if($port === null) {
            $this -> setPort($config -> get('port', self::VIRTUOSODEFAULTPORT));
        }
        if($user === null) {
            $this -> setUser($config -> get('user', ''));
        }
        if($password === null) {
            $this -> setPassword($config -> get('password', ''));
        }
        if (!is_int($this->getPort())  || ($this->getHost() === null)) {
            throw new Exception("Virtuoso need a Host to be configured");
        }

        if (($proto == 'http') || ($proto == 'https')) {
            $this->setProto($proto);
        } else {
            throw new Exception("Bad proto: $proto");
        }

        $curlOptions = array(CURLOPT_FOLLOWLOCATION => true);
        //if ($this->getProto() == 'https') {
        //   $curlOptions[ ] = ;
        //}

        $config = array(
            'adapter'   => 'Zend_Http_Client_Adapter_Curl',
            'curloptions' => $curlOptions,
        );

        $client = new \Zend_Http_Client(null, $config);
        $client->setAuth($user, $password, \Zend_Http_Client::AUTH_BASIC);
        $client->setMethod('POST');
        $client->setEncType(\Zend_Http_Client::ENC_URLENCODED);

        $this->httpClient = $client;
    }

    /**
     * @param string $baseUrl
     * @return string
     */
    private function getUrl($baseUrl='') {
        return $this->getProto() . '://' . $this->getHost() . $baseUrl;
    }

    /**
     * @return string
     */
    private function getUserAgent() {
        if (defined('VIRTUOSOUSERAGENT')) return VIRTUOSOUSERAGENT;
        if ($ua = getenv('VIRTUOSOUSERAGENT')) return $ua;
        return 'CcsdToolsCurl';
    }
    /**
     * @param Virtuoso\Query $query
     * @param string $baseURL
     * @param string $format
     * @return false|Virtuoso\Response
     * @throws \Zend_Http_Exception
     */
    public function send($query, $baseURL='/sparql', $format="application/json") {
        $params=array(
            "default-graph" =>  "",
            "should-sponge" =>  "soft",
            "query"         =>  $query -> __toString(),
            "debug"         =>  "on",
            "timeout"       =>  "",
            "format"        =>  $format,
            "save"          =>  "display",
            "fname"         =>  ""
        );

        $client = $this->httpClient;

        $client->setUri($this->getUrl($baseURL));
        $client->setParameterPost($params);
        $response = $client->request();
        if ($response -> getStatus() == 200) {
            $response = Virtuoso\Response::fromString($response);
            return $response;
        } else {
            return false;
        }
    }

    /**
     * @return string[]
     */
    public function listGraphNames() {
        $virtuosoQuery = new Virtuoso\SparqlQuery("select distinct ?g where  { graph ?g {  ?s ?p ?v  } } ");
        try {
            $ret = [];
            $response = $this->send($virtuosoQuery);
            foreach ($response -> getRows() as $row) {
                $ret[] = $row->g;
            }
            return $ret;
        } catch (\Exception $e) {

        }
        return [];
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->_host;
    }

    /**
     * @param string $host
     * @return Virtuoso
     */
    public function setHost(string $host): Virtuoso
    {
        $this->_host = $host;
        return $this;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->_port;
    }

    /**
     * @param int $port
     * @return Virtuoso
     */
    public function setPort(int $port): Virtuoso
    {
        $this->_port = $port;
        return $this;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->_user;
    }

    /**
     * @param string $user
     */
    public function setUser(string $user)
    {
        $this->_user = $user;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->_password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password)
    {
        $this->_password = $password;
    }

    /**
     * @return string
     */
    public function getProto(): string
    {
        return $this->_proto;
    }

    /**
     * @param string $proto
     */
    public function setProto(string $proto)
    {
        $this->_proto = $proto;
    }

}