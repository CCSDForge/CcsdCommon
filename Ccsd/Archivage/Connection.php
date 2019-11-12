<?php

/**
 * Init archivage ssh connection
 * Class Ccsd_Archivage_Connection
 */
Class Ccsd_Archivage_Connection
{
    /**
     * @var string
     */
    private $_server;
    /**
     * @var int
     */
    private $_serverPort;
    /**
     * @var string
     */
    private $_login;
    /**
     * @var string
     */
    private $_password;

    /**
     * @var resource
     */
    private $_sshResource;
    /**
     * @var resource
     */
    private $_sftpResource;


    /**
     * Ccsd_Archivage_Connection constructor.
     * @param array|null $options
     */
    public function __construct(array $options = null)
    {

        if ($options == null) {
            $options = $this->getDefaultOptions();
        }


        $methods = get_class_methods($this);
        foreach ($options as $propertyName => $propertyValue) {
            $method = 'set' . ucfirst($propertyName);
            if (in_array($method, $methods)) {
                $this->$method($propertyValue);
            }
        }


        try {
            $this->initSshResource();
            $this->initSftpResource();
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return $this;
    }

    /**
     * get default connection options
     * @return array
     */
    private function getDefaultOptions()
    {
        return ['server' => ARCHIVAGE_HOST, 'serverPort' => ARCHIVAGE_PORT, 'login' => ARCHIVAGE_USER, 'password' => ARCHIVAGE_PWD];
    }


    /**
     * SSH init connection
     * @return resource
     * @throws Ccsd_Archivage_Connection_Exception
     */
    private function initSshResource()
    {

        if ($this->getSshResource() != null) {
            return $this->getSshResource();
        }

        $this->setSshResource(ssh2_connect($this->getServer(), $this->getServerPort()));

        if ($this->getSshResource() === false) {
            throw new Ccsd_Archivage_Connection_Exception(SSH_CONN_FAILED);
        }

        return $this->getSshResource();


    }

    /**
     * @return resource
     */
    public function getSshResource()
    {
        return $this->_sshResource;
    }

    /**
     * @param resource $sshResource
     */
    public function setSshResource($sshResource)
    {
        $this->_sshResource = $sshResource;
    }

    /**
     * @return string
     */
    public function getServer() :string
    {
        return $this->_server;
    }

    /**
     * @param array string
     */
    public function setServer(string $server)
    {
        $this->_server = $server;
    }

    /**
     * @return int
     */
    public function getServerPort()
    {
        return $this->_serverPort;
    }

    /**
     * @param int $serverPort
     */
    public function setServerPort($serverPort)
    {
        $this->_serverPort = $serverPort;
    }

    /**
     * Init SFTP resource
     * @return null|resource
     * @throws Ccsd_Archivage_Connection_Exception
     */
    function initSftpResource()
    {
        if ($this->getSftpResource() != null) {
            return $this->getSftpResource();
        }


        if (ssh2_auth_password($this->getSshResource(), $this->getLogin(), $this->getPassword()) === false) {
            throw new Ccsd_Archivage_Connection_Exception('SSH_AUTH_FAILED');
        }


        $this->setSftpResource(ssh2_sftp($this->getSshResource()));

        if ($this->getSftpResource() === false) {
            throw new Ccsd_Archivage_Connection_Exception('SFTP_INIT_FAILED');
        }

        return $this->getSftpResource();


    }

    /**
     * @return resource
     */
    public function getSftpResource()
    {
        return $this->_sftpResource;
    }

    /**
     * @param resource $sftpResource
     */
    public function setSftpResource($sftpResource)
    {
        $this->_sftpResource = $sftpResource;
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->_login;
    }

    /**
     * @param string $login
     */
    public function setLogin($login)
    {
        $this->_login = $login;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->_password;
    }

    /**
     * @param string $psswd
     */
    public function setPassword($psswd)
    {
        $this->_password = $psswd;
    }


}