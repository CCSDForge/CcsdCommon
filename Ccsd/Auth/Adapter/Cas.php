<?php

if (!file_exists(APPROOT.'/vendor/autoload.php'))
    require_once 'CAS.php';

/**
 * Adapter Zend_Auth pour l'authentification via CAS
 *
 * @see https://wiki.jasig.org/display/CASC/phpCAS JASIG phpCAS library
 * @see https://github.com/Jasig/phpCAS
 * @author ccsd
 *
 */
class Ccsd_Auth_Adapter_Cas implements Zend_Auth_Adapter_Interface , \Ccsd\Auth\Adapter\UserManager{

    /**
     * Nom par défaut de l'action pour le login
     *
     * @var string
     */
    const DEFAULT_LOGIN_ACTION = 'login';

    /**
     * Nom par défaut de l'action pour le logout
     *
     * @var string
     */
    const DEFAULT_LOGOUT_ACTION = 'logout';

    /**
     * Nom par défaut du controller d'authentification
     *
     * @var string
     */
    const DEFAULT_AUTH_CONTROLLER = 'user';

    /**
     * Nom de l'action pour le login
     *
     * @var string
     */
    protected $_loginAction = null;

    /**
     * Nom de l'action pour le logout
     *
     * @var string
     */
    protected $_logoutAction = null;

    /**
     * Nom du controller d'authentification
     *
     * @var string
     */
    protected $_authController = null;

    /**
     * Version du protocole CAS
     *
     * @var string
     */
    protected $_casVersion;

    /**
     * Nom d'hôte du serveur CAS
     *
     * @var string
     */
    protected $_casHostname;

    /**
     * Port serveur CAS
     *
     * @var int
     */
    protected $_casPort;

    /**
     * URL du serveur CAS
     *
     * @var string
     */
    protected $_casUrl;

    /**
     * Définit si PhpCAS doit démarrer les sessions : non si c'est déjà géré par
     * l'application
     *
     * @var string
     */
    protected $_casStartSessions;

    /**
     * Définit si on doit faire la validation SSL du serveur CAS
     *
     * @var bool
     */
    protected $_casSslValidation;

    /**
     * Chemin vers le certificat de l'autorité de certification
     * @var string
     */
    protected $_casCACert;

    /**
     * URL du service pour lequel on s'authentifie * et sur lequel on reviendra
     * *
     *
     * @var string
     */
    protected $_serviceURL;

    /**
     * Structure de l'identité d'un utilisateur
     *
     * @var Ccsd_User_Models_User
     */
    protected $_identity = null;

    /**
     * @param string $env
     */
    public function __construct($env = APPLICATION_ENV) {
        $this->setCasOptions($env);
    }

    /**
     * Initialisation de la structure de l'identité utilisateur
     *
     * @param $identity
     */
    public function  setIdentityStructure($identity) {
        $this->_identity = $identity;
    }

    /**
     * Authentification d'un utilisateur
     * @see Zend_Auth_Adapter_Interface::authenticate()
     */
    public function authenticate() {

        if (!isset($PHPCAS_CLIENT)) {
            phpCAS::client($this->getCasVersion(), $this->getCasHostname(), $this->getCasPort(), $this->getCasUrl(), $this->getCasStartSessions());
        }

        if (defined('APPLICATION_ENV')) {
            if (APPLICATION_ENV == 'development') {
                phpCAS::setDebug(realpath(sys_get_temp_dir()) . '/cas.log');
            } elseif ((APPLICATION_ENV == 'testing')) {
                phpCAS::setDebug('/sites/logs/phpCas_testing.log');
            }
        }

        if ($this->getCasSslValidation() == false) {
            // no SSL validation for the CAS server
            phpCAS::setNoCasServerValidation();
        } else {
            phpCAS::setCasServerCACert($this->getCasCACert());
        }
        // Url de retour/service après authentification
        if (null != $this->getServiceURL()) {
            phpCAS::setFixedServiceURL($this->getServiceURL());
        }

        // force CAS authentication
        try {
            $resultOfAuth = phpCAS::forceAuthentication();
        } catch (Exception $e) {
            $resultOfAuth = false;
        }

        if ($resultOfAuth == true) {

            $userMapper = new Ccsd_User_Models_UserMapper();
            if ($this->_identity instanceof Ccsd_User_Models_User) {
                $user = $this->_identity;
            } else {
                $user = new Ccsd_User_Models_User();
            }
            $userMapper->find(phpCAS::getAttribute('UID'), $user);

            // at this step, the user has been authenticated by the CAS server
            // and the user's login name can be read with phpCAS::getUser().

            return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $user, array());
        } else {
            return new Zend_Auth_Result(Zend_Auth_Result::FAILURE, new Ccsd_User_Models_User(), array("Échec de l'authentification depuis CAS"));
        }
    }

    /**
     * Déconnexion de l'utilisateur, avec URL de retour/destination facultative
     *
     * @param string $urlDeDestination
     *            URL de retour/destination
     */
    public function logout($urlDeDestination = null) {
        if (!isset($PHPCAS_CLIENT)) {
            phpCAS::client($this->getCasVersion(), $this->getCasHostname(), $this->getCasPort(), $this->getCasUrl(), $this->getCasStartSessions());
        }

        if ($this->getCasSslValidation() == false) {
            // no SSL validation for the CAS server
            phpCAS::setNoCasServerValidation();
        } else {
            phpCAS::setCasServerCACert($this->getCasCACert());
        }

        if (null == $urlDeDestination) {
            phpCAS::logout(); // logout et reste sur la page CAS
        } else {
            phpCAS::logoutWithRedirectService($urlDeDestination);
        }
    }

    /**
     * Définit les options par défaut du serveur CAS
     * @param string $env
     * @return Ccsd_Auth_Adapter_Cas
     */
    private function setCasOptions($env) {
        $casConfig = new Zend_Config_Ini(__DIR__.'/config/cas.ini', $env);


        $this->setCasVersion($casConfig->params->version)
                ->setCasHostname($casConfig->params->hostname)
                ->setCasPort($casConfig->params->port)
                ->setCasUrl($casConfig->params->url)
                ->setCasStartSessions($casConfig->params->startphpsessions)
                ->setCasSslValidation($casConfig->params->sslvalidation)
                ->setCasCACert($casConfig->params->sslcacert);
        return $this;
    }

    /**
     *
     * @return string $_casVersion
     */
    public function getCasVersion() {
        return $this->_casVersion;
    }

    /**
     *
     * @return string $_casHostname
     */
    public function getCasHostname() {
        return $this->_casHostname;
    }

    /**
     *
     * @return string $_casPort
     */
    public function getCasPort() {
        return $this->_casPort;
    }

    /**
     *
     * @return string $_casUrl
     */
    public function getCasUrl() {
        return $this->_casUrl;
    }

    /**
     *
     * @return string $_casStartSessions
     */
    public function getCasStartSessions() {
        return $this->_casStartSessions;
    }

    /**
     *
     * @return bool $_casSslValidation
     */
    public function getCasSslValidation() {
        return $this->_casSslValidation;
    }

    /**
     *
     * @param string $_casVersion
     * @return Ccsd_Auth_Adapter_Cas
     */
    public function setCasVersion($_casVersion) {
        $this->_casVersion = $_casVersion;
        return $this;
    }

    /**
     *
     * @param string $_casHostname
     * @return Ccsd_Auth_Adapter_Cas
     */
    public function setCasHostname($_casHostname) {
        $this->_casHostname = $_casHostname;
        return $this;
    }

    /**
     *
     * @param string $_casPort
     * @return Ccsd_Auth_Adapter_Cas
     */
    public function setCasPort($_casPort) {
        $this->_casPort = intval($_casPort);
        return $this;
    }

    /**
     *
     * @param string $_casUrl
     * @return Ccsd_Auth_Adapter_Cas
     */
    public function setCasUrl($_casUrl) {
        $this->_casUrl = $_casUrl;
        return $this;
    }

    /**
     *
     * @param bool $_casStartSessions
     * @return Ccsd_Auth_Adapter_Cas
     */
    public function setCasStartSessions($_casStartSessions = false) {
        $this->_casStartSessions = (bool) $_casStartSessions;
        return $this;
    }

    /**
     *
     * @param bool $_casSslValidation
     * @return Ccsd_Auth_Adapter_Cas
     */
    public function setCasSslValidation($_casSslValidation) {
        $this->_casSslValidation = $_casSslValidation;
        return $this;
    }

    /**
     *
     * @return string $_casCACert
     */
    public function getCasCACert() {
        return $this->_casCACert;
    }

    /**
     *
     * @param string $_casCACert
     * @return Ccsd_Auth_Adapter_Cas
     */
    public function setCasCACert($_casCACert) {
        $this->_casCACert = $_casCACert;
        return $this;
    }

    /**
     *
     * @return string $_serviceURL
     */
    public function getServiceURL() {
        return $this->_serviceURL;
    }

    /**
     *
     * @param string[] $params
     * @return Ccsd_Auth_Adapter_Cas
     */
    public function setServiceURL($params = []) {
        $_serviceURL = $this->buildLoginDestinationUrl($params);

        if (isset($_serviceURL)) {
            $this->_serviceURL = $_serviceURL;
        }

        return $this;
    }

    /**
     * Retourne le nom d'hôte que l'application CAS va utiliser
     * Pour redirection après login et logout
     *
     * @return string Nom de l'hôte
     */
    static function getCurrentHostname() {
        if ((isset($_SERVER['HTTPS'])) and ( $_SERVER['HTTPS'] != '')) {
            $scheme = 'https://';
        } else {
            $scheme = 'http://';
        }

        $hostname = $scheme . $_SERVER['SERVER_NAME'];

        if ((isset($_SERVER['SERVER_PORT'])) and ( $_SERVER['SERVER_PORT'] != '')) {
            switch ($_SERVER['SERVER_PORT']) {
                case '80':
                    break;
                case '443':
                    break;
                case '':
                    break;
                default:
                    $hostname .= ":" . $_SERVER['SERVER_PORT'];
                    break;
            }
        }
        // Prefix url de HAL, eg '/LKB/'
        if (defined('PREFIX_URL')) {
            $hostname .= PREFIX_URL;
        }

        return $hostname;
    }

    /**
     * @param array $params
     * @return bool|string
     */
    private function buildLoginDestinationUrl($params = []) {
        if (empty($params)) {
            return null;
        }

        $hostname = self::getCurrentHostname();

        // si defined('PREFIX_URL') de HAL, eg '/LKB/'
        $hostname = rtrim($hostname, '/');

        $uri = $hostname . '/user/login';
        $forwardController = null;
        if (array_key_exists('forward-controller', $params)) {
            $forwardController = $params['forward-controller'];
        }
        $forwardAction = null;
        if (array_key_exists('forward-action', $params)) {
            $forwardAction = $params['forward-action'];
        }

        // Si pas de controller ou si controller == user/logout
        if (($forwardController == null) || ( ($forwardController == 'user') && ( $forwardAction == 'logout'))) {
            // destination par défaut
            $uri .= '/forward-controller/user';
        } else {
            if (null != $params['forward-action']) {

                $uri .= '/forward-controller/' . urlencode($forwardController);
                $uri .= '/forward-action/' . urlencode($forwardAction);

                // Concaténation des paramètres supplémentaires à l'uri de retour
                foreach ($params as $name => $value) {
                    switch ($name) {
                        case 'forward-controller':
                        case 'forward-action':
                        case 'controller':
                        case 'action':
                        case 'module':
                        case 'ticket':
                            continue;
                        default:
                            $uri .= '/' . urlencode($name) . '/';

                            if (is_array($value)) {
                                $uri .= urlencode($value[0]);
                            } else {
                                $uri .= urlencode($value);
                            }
                    }
                }
            } else {
                $uri .= '/forward-controller/' . urlencode($forwardController);
            }
        }


        return $uri;
    }

    /**
     *
     * @return string $_loginAction
     */
    public function getLoginAction() {
        if ($this->_loginAction == null) {
            return self::DEFAULT_LOGIN_ACTION;
        }
        return $this->_loginAction;
    }

    /**
     *
     * @param string $_loginAction
     * @return Ccsd_Auth_Adapter_Cas
     */
    public function setLoginAction($_loginAction) {
        $this->_loginAction = $_loginAction;
        return $this;
    }

    /**
     *
     * @return string $_logoutAction
     */
    public function getLogoutAction() {
        if ($this->_logoutAction == null) {
            return self::DEFAULT_LOGOUT_ACTION;
        }
        return $this->_logoutAction;
    }

    /**
     *
     * @param string $_logoutAction
     * @return Ccsd_Auth_Adapter_Cas
     */
    public function setLogoutAction($_logoutAction) {
        $this->_logoutAction = $_logoutAction;
        return $this;
    }

    /**
     *
     * @return string $_authController
     */
    public function getAuthController() {
        if ($this->_authController == null) {
            return self::DEFAULT_AUTH_CONTROLLER;
        }
        return $this->_authController;
    }

    /**
     *
     * @param string $_authController
     * @return Ccsd_Auth_Adapter_Cas
     */
    public function setAuthController($_authController) {
        $this->_authController = $_authController;
        return $this;
    }

    /**
     * Get user create form for this Adapter
     * @return Ccsd_User_Form_Accountcreate
     */
    public function getUserCreateForm() {
        return new Ccsd_User_Form_Accountcreate ([ 'ini' => 'Ccsd/User/configs/accountcreate.ini', 'section' => 'CAS']);
    }

    /**
     * @param $user
     */
    public function completeUserInfoIfNeeded($user) {
    }
}
