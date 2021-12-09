<?php

/**
 * Class Ccsd_Sword_Client_Exception
 */
class Ccsd_Sword_Client_Exception extends Exception {

}

/**
 * Class Ccsd_Sword_Client
 */
class Ccsd_Sword_Client
{
    private $version = '1.0';
    /**
     * @var string
     */
    private $url = null;
    /**
     * @var string
     */
    private $filename = null;
    /**
     * @var string
     */
    private $file = null;
    /**
     * @var string[]
     */
    private $headers = ['User-Agent: HAL SWORD Client (version 1.0) hal.archives-ouvertes.fr'];

    /**
     * Ccsd_Sword_Client constructor.
     * @param string $url
     */
    public function __construct($url = null)
    {
        if (null !== $url) {
            $this->url = $url;
        }
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     */
    public function setFile($filename)
    {
        $this->file = null;
        $this->unsetHeader('Content-Length');
        $this->unsetHeader('Content-MD5');
        if (is_file($filename)) {
            $this->filename = $filename;
            $this->file = file_get_contents($filename);
            $this->setHeaders('Content-MD5: ' . md5($this->file));
        } else if (is_string($filename) && $filename != '') {
            $this->filename = $filename;
            $this->file = $filename;
        }
    }

    /**
     * @param string $needle
     */
    public function unsetHeader($needle = '')
    {
        $headers = array();
        foreach ($this->headers as $header) {
            if (strpos($header, $needle) === false) {
                $headers[] = $header;
            }
        }
        $this->headers = $headers;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return string[]
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param string|string[] $header
     */
    public function setHeaders($header = '')
    {
        if (is_array($header)) {
            $this->headers = array_merge($this->headers, $header);
        } else if (is_string($header)) {
            array_push($this->headers, $header);
        }
    }

    /**
     * @param string $obo
     */
    public function setOBO($obo)
    {
        $this->setHeaders('X-On-Behalf-Of: ' . $obo);
    }

    /**
     * Request a servicedocument at the specified url, with the specified credentials, and on-behalf-of the specified user.
     *
     * @param string $user
     * @param string $password
     * @return Ccsd_Sword_Servicedocument Object
     * @throws Ccsd_Sword_Client_Exception
     */
    public function servicedocument($user = '', $password = '')
    {
        if (!is_null($this->url)) {
            $timeout = $this -> getGoodTimeOutValue(sizeof($this->file));
            $curl = curl_init($this->url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_AUTOREFERER, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            // Attention: Si timeout, le depot peut etre accepte sur Arxiv et nous le considerons comme echoue
            // 25 s est une valeur trop petite on mets 50 (BM: mercredi 01 mars 2017)
            curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            if (!empty ($user) && !empty ($password)) {
                curl_setopt($curl, CURLOPT_USERPWD, $user . ':' . $password);
            }
            curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
            $resp = curl_exec($curl);
            $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            // Parse the response
            $sword_resp = new Ccsd_Sword_Servicedocument($http_status, $resp);
            if ($http_status == 200) {
                try {
                    $xml = @new SimpleXMLElement ($resp);
                    $ns = $xml->getNamespaces(true);
                    $sword_resp->version = $xml->children($ns['sword'])->version;
                    $sword_resp->verbose = $xml->children($ns['sword'])->verbose;
                    $sword_resp->noop = $xml->children($ns ['sword'])->noOp;
                    $sword_resp->maxuploadsize = $xml->children($ns['sword'])->maxUploadSize;
                    $sword_resp->buildhierarchy($xml->children(), $ns);
                } catch (Exception $e) {
                    throw new Ccsd_Sword_Client_Exception("Error parsing service document (" . $e->getMessage() . ")");
                }
            }
            return $sword_resp;
        } else {
            return false;
        }
    }

    /**
     * @param int $size
     */
    static private function getGoodTimeOutValue(int $size)
    {
        $timeout = log($size / 100) * 3 + 40;
        return $timeout;
    }


	/**
	 * Perform a deposit to the specified url, with the sepcified credentials, on-behlf-of the specified user, and with the given file and formatnamespace
	 *
	 * @param string $user        	
	 * @param string $password        	
	 * @param string $method POST or PUT
	 * @return Ccsd_Sword_Entry|Ccsd_Sword_Errordocument|false
     * @throws Ccsd_Sword_Client_Exception
	 */
	public function deposit($user, $password, $method = 'POST') {
		if (! is_null ( $this->url ) && ! is_null ( $this->file )) {
			$curl = curl_init ( $this->url );
			if ($method == 'PUT') {
				curl_setopt ( $curl, CURLOPT_PUT, true );
			} else {
				curl_setopt ( $curl, CURLOPT_POST, true );
			}

			$timeout = $this -> getGoodTimeOutValue(sizeof($this->file));

			curl_setopt ( $curl, CURLINFO_HEADER_OUT, true );
			curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, true );
			curl_setopt ( $curl, CURLOPT_HEADER, false );
			curl_setopt ( $curl, CURLOPT_FOLLOWLOCATION, true );
			curl_setopt ( $curl, CURLOPT_AUTOREFERER, true );
			curl_setopt ( $curl, CURLOPT_CONNECTTIMEOUT, 10 );
			curl_setopt ( $curl, CURLOPT_TIMEOUT, $timeout );
			curl_setopt ( $curl, CURLOPT_MAXREDIRS, 10 );
			curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, 0 );
			if (! empty ( $user ) && ! empty ( $password )) {
				curl_setopt ( $curl, CURLOPT_USERPWD, $user . ':' . $password );
			}
			curl_setopt ( $curl, CURLOPT_HTTPHEADER, $this->headers );
			if ($method == 'PUT') {
				$putData = tmpfile ();
				fwrite ( $putData, $this->file );
				fseek ( $putData, 0 );
				curl_setopt ( $curl, CURLOPT_INFILE, $putData );
				curl_setopt ( $curl, CURLOPT_INFILESIZE, strlen ( $this->file ) );
			} else {
				curl_setopt ( $curl, CURLOPT_POSTFIELDS, $this->file );
			}
			// DEBUG
            $pid=getmypid();
            file_put_contents ( PATHTEMPDOCS . 'sword_sent_' . $pid . '_' . time () . '.txt', $method . " " . $this->url . "\n" . $this->file );
			$resp = curl_exec ( $curl );
			if ($resp === false) {
                throw new Ccsd_Sword_Client_Exception ( "Curl error: try later..."); // timeout...
            }
            $resp = trim($resp);
			$http_status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
			// DEBUG
			file_put_contents ( PATHTEMPDOCS . 'sword_response_' . $pid . '_' . time() . '.txt', $http_status . "\n" . curl_getinfo ( $curl, CURLINFO_HEADER_OUT ) . "\n" . $resp );
			curl_close( $curl );
			// Parse the response
			if ( in_array($http_status, [200, 201, 202]) ) {
				try {
                    $sword_resp = new Ccsd_Sword_Entry ( $http_status, $resp );
                    $xml = @new SimpleXMLElement ( $resp );
					$ns = $xml->getNamespaces ( true );
					$sword_resp->buildhierarchy ( $xml, $ns );
				} catch ( Exception $e ) {
					throw new Ccsd_Sword_Client_Exception ( "Error parsing service document (" . $e->getMessage () . ") " . $resp );
				}
                return $sword_resp;
			} else {
				try {
					$dresponse = new Ccsd_Sword_Errordocument ( $http_status, $resp );
					$xml = @new SimpleXMLElement ( $resp );
					$ns = $xml->getNamespaces ( true );
                    $dresponse->buildhierarchy ( $xml, $ns );
				} catch ( Exception $e ) {
					throw new Ccsd_Sword_Client_Exception( "Error parsing error document: Http status=$http_status (" . $e->getMessage () . ") " . $resp );
				}
                return $dresponse;
			}
		} else {
			return false;
		}
	}
}
