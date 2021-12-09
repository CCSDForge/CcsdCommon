<?php

/**
 * Class Ccsd_Referentiels_Europeanproject
 * @property string PROJEUROPID
 * @property string NUMERO
 * @property string ACRONYME
 * @property string TITRE
 * @property string FUNDEDBY
 * @property string SDATE
 * @property string EDATE
 * @property string CALLID
 * @property bool VALID
 */
class Ccsd_Referentiels_Europeanproject extends Ccsd_Referentiels_Abstract
{

    static public $core = 'ref_projeurop';

    protected $_table = 'REF_PROJEUROP';

    protected $_primary = 'PROJEUROPID';

    const METANAME = 'europeanProject';

    const INI = 'Ccsd/Referentiels/Form/europeanproject.ini';

    protected $_smallFormElements = array('PROJEUROPID', 'NUMERO', 'ACRONYME', 'TITRE');

    protected $_mandatoryFormElements = array('NUMERO', 'ACRONYME', 'TITRE');

    protected $_smallMandatoryFormElements = array('TITRE');

    protected $_dateFormElements = array('SDATE', 'EDATE');

    static protected $_champsSolr = array("docid"
            , "label_s"
            , "label_html"
            , "text"
            , "title_s"
            , "acronym_s"
            , "callAcronym_s"
            , "financing_s"
            , "callId_s"
            , "valid_s"
            , "title_t"
            , "acronym_t"
            , "callAcronym_t"
            , "financing_t"
            , "callId_t"
    );

    static public $_champsEs = array(
        'PROJEUROPID',
        'NUMERO',
        'ACRONYME',
        'TITRE',
        'FUNDEDBY',
        'SDATE',
        'EDATE',
        'CALLID',
        'VALID'
    );

    static public $_optionsTri = array(
    	'titre' 	=> 'Trier les projets européens par titre',
    	'valid' 	=> 'Trier les projets européens par validité',
    	'autre' 	=> 'Trier les projets européens par acronyme'
    );

    static public $_optionsFilter = array(
    	'all'		=> 'Tous les projets européens',
    	'valid'		=> 'Projets européens valides',
    	'incoming' 	=> 'Projets européens en attente de validation',
    	'old' 		=> 'Projets européens fermés'
    );

    static protected $_solRsort = array (
    	'valid' 	=> '&sort=valid_s+desc,title_s+asc',
    	'titre' 	=> '&sort=title_s+asc,valid_s+desc',
    	'autre' 	=> '&sort=acronym_s+asc,title_s+asc,valid_s+desc'
    );

    // Champs acceptés par le formulaire MODIFY
    protected $_acceptedFormValues = ['PROJEUROPID', 'NUMERO', 'ACRONYME', 'TITRE', 'FUNDEDBY', 'SDATE', 'EDATE', 'CALLID', 'VALID'];

    // Champs modifiés par le formulaire MODIFY
    protected $_changeableFormValues = ['NUMERO', 'ACRONYME', 'TITRE', 'FUNDEDBY', 'SDATE', 'EDATE', 'CALLID', 'VALID'];

    /**
     * @param string $critere
     * @param string $orderby
     * @param string $filter
     * @param int $nbResultPerPage
     * @param bool $valid
     * @return Zend_Db_Select
     * @see Ccsd_Referentiels_Abstract::_createBaseQuery()
     */
    protected function _createBaseQuery($critere, $orderby, $filter, $nbResultPerPage, $valid)
    {
    	/* @var $select Zend_Db_Select */
    	$select = Zend_Db_Table_Abstract::getDefaultAdapter()->select()->from($this->_table);
    	
    	if ($critere != "*") {
    		$select->orWhere("PROJEUROPID = '?'",   $critere);
    		$select->orWhere("TITRE LIKE '%?%'",    $critere);
    		$select->orWhere("ACRONYME LIKE '%?%'", $critere);
    		$select->orWhere("FUNDEDBY LIKE '%?%'", $critere);
    		$select->orWhere("CALLID LIKE '%?%'",   $critere);
    	}
    	 
    	if ('valid' == $orderby) {
    		$select->order(array('VALID DESC', 'TITRE ASC'));
    	} else if ('titre' == $orderby) {
    		$select->order(array('TITRE ASC' , 'VALID DESC'));
    	} else if ('autre' == $orderby) {
    		$select->order(array('ACRONYME ASC', 'JNAME ASC' , 'VALID DESC'));
    	}
    	
    	return $select;
    }

    /**
     * @return string
     */
    public function __toString() {
        /** @var Zend_Controller_Action_Helper_ViewRenderer $viewRenderer*/
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');

        //Ajout des variables pour la vue
        /** @var Hal_View $view */
        $view = $viewRenderer->view;
        $view->identifier = uniqid("ref");
        $view->europeanproject = $this;

        if ($arg = func_get_args ()) {
        	if ($arg = array_shift($arg)) {
        		if (is_array($arg)) {
	        		if (array_key_exists('showOptions', $arg)) {
	        			$view->options = true;
	        		}
	        		if (array_key_exists('showItem', $arg)) {
	        			$view->item = (new Ccsd_Form_Element_Referentiel('europeanproject'))->setType('europeanproject');
	        		}
        		}
        	}
        }
        
        //Ajout du répertoire des script de vues (library)
        $view->addScriptPath(__DIR__ . "/views/");
    
        //Récupération du script traité
        return $view->render("europeanproject.phtml");
    }
    
    
    /**
     * Valide les données d'un référentiel
     * @param mixed $value : données du référentiel à valider
     * @return boolean
     */
    public function isValid ($value) {
        if (SPACE_NAME == 'AUREHAL') {
            //Cas d'auréhal, on utilise la méthode isValid de Ccsd_Referentiel_Abstract
            return parent::isValid($value);
        }

        if (!is_array ($value)) {
    		return false;
    	}
    	 
    	$valid = true;
    	 
    	foreach ($value as $v) {
    		$valid = $valid && parent::isValid($v);
    	}
    	 
    	return $valid;
    }

    /**
     * Calcul du MD5 de l'objet - ! cohérence avec les triggers TRIG_INS_PEUROP_MD5 et TRIG_UPT_PEUROP_MD5
     * @return string
     */
    public function getMd5() {
        return md5(strtolower('numero'.$this->NUMERO.'acronyme'.$this->ACRONYME.'titre'.$this->TITRE));
    }

    /**
     * @param bool $header
     * @return string
     */
    public function getXML($header = true) {
    
        $xml = new Ccsd_DOMDocument('1.0', 'utf-8');
        $xml->formatOutput = true;
        $xml->substituteEntities = true;
        $xml->preserveWhiteSpace = false;

        $org = $xml->createElement('org');
        $org->setAttribute('type', 'europeanProject');
        $org->setAttribute('xml:id', 'projeurop-'.$this->PROJEUROPID);
        $org->setAttribute('status', strtoupper($this->VALID));
        if ( $this->NUMERO) {
            $idno = $xml->createElement('idno', $this->NUMERO);
            $idno->setAttribute('type', 'number');
            $org->appendChild($idno);
        }
        if ( $this->FUNDEDBY ) {
            $idno = $xml->createElement('idno', $this->FUNDEDBY);
            $idno->setAttribute('type', 'program');
            $org->appendChild($idno);
        }
        if ( $this->CALLID ) {
            $idno = $xml->createElement('idno', $this->CALLID);
            $idno->setAttribute('type', 'call');
            $org->appendChild($idno);
        }
        if ( $this->ACRONYME ) {
            $org->appendChild($xml->createElement('orgName', $this->ACRONYME));
        }
        if ( $this->TITRE ) {
            $org->appendChild($xml->createElement('desc', $this->TITRE));
        }
        if ( $this->SDATE ) {
            $d = $xml->createElement('date', Ccsd_Tools::str2date($this->SDATE));
            $d->setAttribute('type', 'start');
            $org->appendChild($d);
        }
        if ( $this->EDATE ) {
            $d = $xml->createElement('date', Ccsd_Tools::str2date($this->EDATE));
            $d->setAttribute('type', 'end');
            $org->appendChild($d);
        }
        $xml->appendChild($org);
        return ($header) ? $xml->saveXML() : $xml->saveXML($xml->documentElement) . PHP_EOL;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return AUREHAL_URL . "/europeanproject/{$this->PROJEUROPID}";
    }

}