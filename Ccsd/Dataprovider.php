<?php

/*
 *
 * @author S. Denoux
 */

abstract class Ccsd_Dataprovider
{
    // Identifiant de la référence dont on cherche les metadonnees
    public $_id;

    // Nom du service dont on cherche les metadonnees
    protected $_type;

    /**
     * Erreur s'il y en a une lors de la requête cURL
     * @var string
     */
    protected $_error = "";

    // C'est pas terrible qu'il y ait besoin d'une base (surtout seulement pour Arxiv)
    /** @var Zend_Db_Adapter_Abstract  */
    protected $_dbAdapter;

    /**
     * Ccsd_Dataprovider constructor.
     * @param null $dbAdapter
     */
    public function __construct($dbAdapter=NULL)
    {
        $this->_dbAdapter = $dbAdapter;
    }

    /**
     * Renvoit un Externdoc de type spécifique au dataprovider
     * @param $id
     * @return Ccsd_Externdoc
     */
    abstract public function getDocument($id);

    /**
     * Envoi de la requête Curl à @url pour recevoir la description xml des metadonnees
     * @param $url
     * @param string $postfield  // specify data to POST to server
     * @param int $timeout
     * @return DOMDocument|null
     */
    public function requestXml($url, $postfield = NULL, $timeout = 10)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_USERAGENT, "CCSD - HAL Proxy");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        if (isset($postfield)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postfield);
        }

        curl_setopt($curl, CURLOPT_FAILONERROR, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        $string = curl_exec($curl);

        if (curl_errno($curl) == CURLE_OK) {
            curl_close($curl);

            try {
                $dom = new DOMDocument();
                $dom->loadXML((string)$string);
                return $dom;
            } catch (Exception $e) {
                error_log("Requête de récupération des métadonnées a échouée");
                $this->_error = 'library_meta_badid';
                return null;
            }
        } else {
            $this->_error = curl_error($curl);
            curl_close($curl);
            return null;
        }
    }

    /**
     * Retourne l'e type'identifiant de la métadonnée
     * @return $type : string;
     */
    public function getID()
    {
        return $this->_id;
    }

    /**
     * Retourne le type de la métadonnée
     * @return $type : string;
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Retourne erreur qui a pu se produire à l'occasion de la requête cURL
     * @return string $error
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * @param $err
     */
    public function setError($err)
    {
        $this->_error = $err;
    }

    //</editor-fold>
}

