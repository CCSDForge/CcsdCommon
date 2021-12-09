<?php

/**
 * Class Ccsd_Referentiels_Author - Formes auteur du referentiel Hal
 *
 * @property string $IDHAL
 * @property string $LASTNAME
 * @property string $FIRSTNAME
 * @property string $MIDDLENAME
 * @property string $EMAIL
 * @property string $URL
 * @property int $STRUCTID
 * @property $ORGANISM
 * @property $AUTHORID
 */
class Ccsd_Referentiels_Author extends Ccsd_Referentiels_Abstract
{
    const INI = 'Ccsd/Referentiels/Form/author.ini';
    static public $core = 'ref_author';
    static public $_optionsTri = array(
        'lastname' => 'Trier les auteurs par nom',
        'structid' => 'Trier les auteurs par organisme payeur',
        'current_bool' => 'Trier les auteurs par forme valide'
    );
    static public $_optionsFilter = array(
        'all' => 'Toutes les formes auteurs',
        'valid' => 'Formes auteurs valides',
        'incoming' => 'Formes auteurs en attente de validation',
        'old' => 'Formes auteurs fermés'
    );
    static protected $_champsSolr = array("docid"
    , "label_s"
    , "label_html"
    , "idHal_i"
    , "lastName_s"
    , "firstName_s"
    , "fullName_s"
    , "email_s"
    , "url_s"
    , "valid_s"
    , "structureId_i"
    , "structure_s"
    , "lastName_t"
    , "firstName_t"
    , "fullName_t"
    );
    static protected $_solRsort = array(
        'current_bool' => '&sort=valid_s+desc,lastName_s+asc,firstName_s+asc',
        'lastname' => '&sort=lastName_s+asc,firstName_s+asc,valid_s+desc',
        'structid' => '&sort=structureId_i+asc,lastName_s+asc,firstName_s+asc,valid_s+desc'
    );
    protected $_table = 'REF_AUTHOR';
    protected $_primary = 'AUTHORID';
    protected $_smallFormElements = array('AUTHORID', 'LASTNAME', 'FIRSTNAME', 'EMAIL');
    protected $_mandatoryFormElements = array('LASTNAME', 'FIRSTNAME');
    protected $_smallMandatoryFormElements = array('LASTNAME', 'FIRSTNAME');

    // Champs acceptés par le formulaire MODIFY
    protected $_acceptedFormValues = ['IDHAL', 'LASTNAME', 'FIRSTNAME', 'MIDDLENAME', 'EMAIL', 'URL', 'STRUCTID', 'ORGANISM'];

    // Champs modifiés par le formulaire MODIFY
    protected $_changeableFormValues = ["FIRSTNAME", "LASTNAME", "MIDDLENAME", "EMAIL", "URL", "STRUCTID", "ORGANISM"];

    static public function getCountOfRelatedDocid($id = 0)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        if (is_array($id) || is_numeric($id)) {
            $select = $db->select()
                ->from('DOC_AUTHOR', array(new Zend_Db_Expr('COUNT(*)')));
            if (is_array($id)) {
                $select->where('AUTHORID IN (?)', $id);
            } else {
                $select->where('AUTHORID = ?', (int)$id);
            }
            return ($db->fetchOne($select));
        } else {
            return 0;
        }
    }

    static public function getRelatedDocid($id = 0)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        if (is_array($id) || is_numeric($id)) {
            $select = $db->select()
                ->distinct()
                ->from('DOC_AUTHOR', array('DOCID'));
            if (is_array($id)) {
                $select->where('AUTHORID IN (?)', $id);
            } else {
                $select->where('AUTHORID = ?', (int)$id);
            }
            return ($db->fetchCol($select));
        } else {
            return array();
        }
    }

    /**
     * remplacements des structid des auteurs
     * @param int authorId
     * @param int structId
     * @return bool
     */
    public static function updateAuthorStructId($oldStructId = 0, $newStructId = 0)
    {
        try {
            if ($oldStructId == 0 || $newStructId == 0) {
                return false;
            }
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            $where['STRUCTID = ?'] = (int)$oldStructId;
            return $db->update('REF_AUTHOR', array('STRUCTID' => $newStructId), $where);
        } catch (Exception $e) {
            return false;
        }
    }

    public function setPaginatorAdapter($type = self::PAGINATOR_ADAPTER_SOLR, $options = array(), $filter = false)
    {
        $class = self::PAGINATOR_ADAPTER_SOLR == $type ? 'Ccsd_Paginator_Adapter_Curl' : 'Zend_Paginator_Adapter_DbSelect';
        $method = self::PAGINATOR_ADAPTER_SOLR == $type ? '_createSolrQuery' : '_createBaseQuery';

        $query = $this->$method(
            $options['critere'],
            $options['tri'],
            $options['filter'],
            $options['nbResultPerPage'],
            true,
            array_key_exists('IDHAL', $options) ? $options['IDHAL'] : false,
            $filter ? (array_key_exists('row', $options) ? $options['row'] : array()) : array()
        );

        $this->_adapter = new $class($query, static::$core);
        return $this;
    }

    /**
     * Render the object (but should return a string...)
     * @return string
     */
    public function __toString()
    {
        /*@var Zend_Controller_Action_Helper_ViewRenderer $viewRenderer*/
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');

        //Ajout des variables pour la vue
        $viewRenderer->view->o = $this;

        if ($arg = func_get_args()) {
            if ($arg = array_shift($arg)) {
                if (array_key_exists('showOptions', $arg)) {
                    $viewRenderer->view->options = true;
                }
                if (array_key_exists('showDetails', $arg)) {
                    $viewRenderer->view->details = true;
                }
            }
        }

        //Ajout du répertoire des script de vues (library)
        $viewRenderer->view->addScriptPath(__DIR__ . "/views/");

        //Récupération du script traité
        return $viewRenderer->view->render("author.phtml");
    }

    /*
    * Renvoie le nombre de dépôts liés à un élement du référentiel
     * @param int/array
     * @return int
    */

    public function getMd5()
    {
        if ($this->IDHAL == '') {
            $this->_data['IDHAL'] = 0;
        }
        if ($this->STRUCTID == '') {
            $this->_data['STRUCTID'] = 0;
        }
        return md5(strtolower($this->IDHAL . 'idhal' . $this->LASTNAME . 'lastname' . $this->FIRSTNAME . 'firstname' . $this->MIDDLENAME . 'middlename' . $this->EMAIL . 'email' . $this->URL . 'url' . $this->STRUCTID . 'structid'));
    }

    /*
     * Renvoie les docid des dépôts liés à un élement du référentiel
     * @param int|array
     * @return array
    */

    public function getXML($header = true, $structureID = null, $quanlity = null)
    {
        $xml = new Ccsd_DOMDocument('1.0', 'utf-8');
        $xml->formatOutput = true;
        $xml->substituteEntities = true;
        $xml->preserveWhiteSpace = false;
        $root = $xml->createElement('author');
        if (null != $quanlity) {
            $root->setAttribute('role', $quanlity);
        }
        $persName = $xml->createElement('persName');
        $first = $xml->createElement('forename', $this->FIRSTNAME);
        $first->setAttribute('type', 'first');
        $persName->appendChild($first);
        if ($this->MIDDLENAME) {
            $middle = $xml->createElement('forename', $this->MIDDLENAME);
            $middle->setAttribute('type', 'middle');
            $persName->appendChild($middle);
        }
        $persName->appendChild($xml->createElement('surname', $this->LASTNAME));
        $root->appendChild($persName);
        if ($this->EMAIL) {
            $email = $xml->createElement('email', md5( strtolower((string)$this->EMAIL )));
            $email->setAttribute('type', 'md5');
            $root->appendChild($email);

            $email = $xml->createElement('email', Ccsd_Tools::getEmailDomain((string)$this->EMAIL));
            $email->setAttribute('type', 'domain');
            $root->appendChild($email);
        }
        if ($this->URL) {
            $url = $xml->createElement('ptr');
            $url->setAttribute('type', 'url');
            $url->setAttribute('target', $this->URL);
            $root->appendChild($url);
        }
        $aInformations='';
        if ($this->IDHAL) {

            $cv = new Hal_Cv($this->IDHAL);
            $cv->load(false);
            $serversExt=$cv->getServerExt();
            $serversExtUrl=$cv->getServerUrl();
            $idExtList= $cv->getIdExt();

            // construit tableau $aInformations utilisé plus bas ['IdRef'] = 'http://www.idref.fr/060702494'
            if (is_array($idExtList)) {
                foreach ($idExtList as $idExtSiteId => $idExt) {
                    $servUrl = $serversExtUrl[$idExtSiteId];
                    $servName = $serversExt[$idExtSiteId];
                    $aInformations[$servName] = $servUrl . $idExt;
                }
            } else {
                $aInformations = null;
            }

            // Generate string idhal
            $idhal = $xml->createElement('idno', $cv->getUri());
            $idhal->setAttribute('type', 'idhal');
            $idhal->setAttribute('notation', 'string');
            $root->appendChild($idhal);
            // Generate numeric idhal
            $idhal = $xml->createElement('idno', $cv->getIdHal());
            $idhal->setAttribute('type', 'idhal');
            $idhal->setAttribute('notation', 'numeric');
            $root->appendChild($idhal);
        }
        $authorid = $xml->createElement('idno', $this->AUTHORID);
        $authorid->setAttribute('type', 'halauthorid');
        $root->appendChild($authorid);
        if (is_array($aInformations)) {
            foreach ($aInformations as $site => $id) {
                $ident = $xml->createElement('idno', $id);
                $ident->setAttribute('type', $site);
                $root->appendChild($ident);
            }
        }

        if ($this->STRUCTID) {
            $org = $xml->createElement('orgName');
            $org->setAttribute('ref', '#struct-' . $this->STRUCTID);
            $root->appendChild($org);
        }
        if (null != $structureID) {
            if (is_array($structureID) && count($structureID)) {
                foreach ($structureID as $id) {
                    $affi = $xml->createElement('affiliation');
                    $affi->setAttribute('ref', '#struct-' . $id);
                    $root->appendChild($affi);
                }
            }
        }

        $xml->appendChild($root);
        return ($header) ? $xml->saveXML() : $xml->saveXML($xml->documentElement) . PHP_EOL;
    }

    /*
     * Calcul du MD5 de l'objet - ! cohérence avec les triggers TRIG_INS_AUTHOR_MD5 et TRIG_UPT_AUTHOR_MD5
     * @return string
     */

    /**
     * Enregistrement d'une forme auteur
     * @param bool
     * @param bool : permet de savoir si on force la fusion en cas de doublon dans les formes auteur
     */
    public function save($forceUpdate = false, $acceptDedoublonnage = false)
    {
        if (empty($this->_data['ORGANISM']) && empty($this->_data['STRUCTID'])) { //Si aucune valeur renseignée on initialise la STRUCTID à 0
            $this->_data['STRUCTID'] = 0;
        }
        unset($this->_data['ORGANISM']);

        return parent::save($forceUpdate, $acceptDedoublonnage);
    }

    /**
     * @see Ccsd_Referentiels_Abstract::_createBaseQuery()
     */
    protected function _createBaseQuery($critere, $orderby, $filter, $nbResultPerPage, $valid)
    {
        /* @var $select Zend_Db_Select */
        $select = Zend_Db_Table_Abstract::getDefaultAdapter()->select()->from($this->_table);

        if ($critere != "*") {
            $select->orWhere("AUTHORID = '?'", $critere);
            $select->orWhere("FIRSTNAME LIKE '%?%'", $critere);
            $select->orWhere("LASTNAME LIKE '%?%'", $critere);
            $select->orWhere("IDHAL = '?'", $critere);
            $select->orWhere("EMAIL LIKE '%?%'", $critere);
        }

        if ('current_bool' == $orderby) {
            $select->order(array('VALID DESC', 'LASTNAME ASC'));
        } else if ('lastname' == $orderby) {
            $select->order(array('LASTNAME ASC', 'VALID DESC'));
        } else if ('structid' == $orderby) {
            $select->order(array('STRUCTID ASC', 'LASTNAME ASC', 'VALID DESC'));
        }

        return $select;
    }

    /**
     * @see Ccsd_Referentiels_Abstract::_createSolrQuery()
     */
    protected function _createSolrQuery($critere, $orderby, $filter, $nbResultPerPage, $valid)
    {
        $query = "q=" . urlencode($critere);

        if (array_key_exists($orderby, static::$_solRsort)) {
            $query .= static::$_solRsort[$orderby];
        }

        switch ($filter) {
            case "valid" :
                $query .= "&fq=valid_s:VALID";
                break;
            case "incoming" :
                $query .= "&fq=valid_s:INCOMING";
                break;
            case "old" :
                $query .= "&fq=valid_s:OLD";
                break;
            default :
                break;
        }

        $query .= $this->custom_filter_solR(array_slice(func_get_args(), (new ReflectionMethod(get_class($this), "_createSolrQuery"))->getNumberOfParameters()));

        return $query;
    }

    protected function custom_filter_solR($options)
    {
        $options = array_filter($options);

        if (!is_array($options) || empty ($options)) return parent::custom_filter_solR($options);

        $q = "";

        $shift = array_shift($options);

        switch (is_array($shift)) {
            case false:

                $q .= "&fq=idHal_i:" . $shift . "&fq=" . urlencode("valid_s:OLD OR valid_s:VALID");


                $shift = array_shift($options);
            default :
                if (!empty ($shift)) {
                    $q .= "&fq=" . urlencode("NOT docid:" . implode(" OR NOT docid:", $shift));
                }
                break;
        }

        return $q;
    }


}
