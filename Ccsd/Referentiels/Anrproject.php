<?php

/**
 * Class Ccsd_Referentiels_Anrproject
 * @property string ACRONYME
 * 
 */
class Ccsd_Referentiels_Anrproject extends Ccsd_Referentiels_Abstract
{

    static public $core = 'ref_projanr';

    protected $_table = 'REF_PROJANR';

    protected $_primary = 'ANRID';
    
    const METANAME = 'anrProject';

    const INI = 'Ccsd/Referentiels/Form/anrproject.ini';

    protected $_smallFormElements = array('ANRID', 'TITRE', 'ACRONYME', 'REFERENCE');

    protected $_mandatoryFormElements = array('TITRE', 'ACRONYME', 'REFERENCE', 'ANNEE');

    protected $_smallMandatoryFormElements = array('ACRONYME');

    static protected $_champsSolr = array("docid"
            , "label_s"
            , "label_html"
            , "text"
            , "title_s"
            , "acronym_s"
            , "callAcronym_s"
            , "callTitle_s"
            , "reference_s"
            , "title_t"
            , "acronym_t"
            , "callAcronym_t"
            , "callTitle_t"
            , "reference_t"
    		, 'valid_s'
    );

    static public $_champsEs = array(
        'ANRID',
        'TITRE',
        'ACRONYME',
        'REFERENCE',
        'INTITULE',
        'ACROAPPEL',
        'ANNEE',
        'VALID'
    );
    
    static public $_optionsTri = array(
    	'titre' 	=> 'Trier les projets ANR par titre',
    	'valid' 	=> 'Trier les projets ANR par validité',
    	'autre' 	=> 'Trier les projets ANR par accroappel'
    );
    
    static public $_optionsFilter = array(
    	'all'		=> 'Tous les projets ANR',
    	'valid'		=> 'Projets ANR valides',
    	'incoming' 	=> 'Projets ANR en attente de validation',
    	'old' 		=> 'Projets ANR fermés'
    );

    static protected $_solRsort = array (
    	'valid' 	=> '&sort=valid_s+desc,title_s+asc',
    	'titre' 	=> '&sort=title_s+asc,valid_s+desc',
    	'autre' 	=> '&sort=acronym_s+asc,title_s+asc,valid_s+desc'
    );

    // Champs acceptés par le formulaire MODIFY
    protected $_acceptedFormValues = ['ANRID', 'TITRE', 'ACRONYME', 'REFERENCE', 'INTITULE', 'ACROAPPEL', 'ANNEE', 'VALID'];

    // Champs modifiés par le formulaire MODIFY
    protected $_changeableFormValues = ['TITRE', 'ACRONYME', 'REFERENCE', 'INTITULE', 'ACROAPPEL', 'ANNEE', 'VALID'];

    /**
     * @see Ccsd_Referentiels_Abstract::_createBaseQuery()
     */
    protected function _createBaseQuery($critere, $orderby, $filter, $nbResultPerPage, $valid)
    {
    	/* @var $select Zend_Db_Select */
    	$select = Zend_Db_Table_Abstract::getDefaultAdapter()->select()->from($this->_table);

    	if ($critere != "*") {
    		$select->orWhere("ANRID = '?'",           $critere);
    		$select->orWhere("TITRE LIKE '%?%'",      $critere);
    		$select->orWhere("ACRONYME LIKE '%?%'",   $critere);
    		$select->orWhere("REFERENCE = '?'",       $critere);
    		$select->orWhere("INTITULE LIKE '%?%'",   $critere);
    		$select->orWhere("ACCROAPPEL LIKE '%?%'", $critere);
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
	 * Render the object
	 */  
    public function __toString() {
        /** @var Zend_Controller_Action_Helper_ViewRenderer $viewRenderer */
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
    
        //Ajout des variables pour la vue
        $viewRenderer->view->identifier = uniqid("ref");
        $viewRenderer->view->anrproject = $this;
        
        if ($arg = func_get_args ()) {
        	if ($arg = array_shift($arg)) {      		
        		if (is_array ($arg)) {
	        		if (array_key_exists('showOptions', $arg)) {
	        			$viewRenderer->view->options = true;
	        		}
	        		if (array_key_exists('showItem', $arg)) {
	        			$viewRenderer->view->item = (new Ccsd_Form_Element_Referentiel('anrproject'))->setType('anrproject');
	        		}
        		}
        		
        	}
        }
        
        //Ajout du répertoire des script de vues (library)
        $viewRenderer->view->addScriptPath(__DIR__ . "/views/");
    
        //Récupération du script traité
        return $viewRenderer->view->render("anrproject.phtml");
    }

    /**
     * Valide les données d'un référentiel
     * @param mixed $value : données du référentiel à valider
     * @return boolean
     */
     function isValid ($value)
     {
         if (SPACE_NAME == 'AUREHAL') {
            //Cas d'auréhal, on utilise la méthode isValid de Ccsd_Referentiel_Abstract
            return parent::isValid($value);
         }
    	//Autrement on vient de hal et $value correspond à un tableau de projets...
         if (!is_array ($value)) {
             return false;
         }

         $valid = true;
         foreach($value as $v) {
             $valid = $valid && parent::isValid($v);
         }
    	 return $valid;
    }

    /**
     * Calcul du MD5 de l'objet - ! cohérence avec les triggers TRIG_INS_ANR_MD5 et TRIG_UPT_ANR_MD5
     * @return string
     */
    public function getMd5() {
        return md5(strtolower('titre'.$this->TITRE.'acronyme'.$this->ACRONYME.'reference'.$this->REFERENCE));
    }

    public function getXML($header = true) {
    
        $xml = new Ccsd_DOMDocument('1.0', 'utf-8');
        $xml->formatOutput = true;
        $xml->substituteEntities = true;
        $xml->preserveWhiteSpace = false;

        $org = $xml->createElement('org');
        $org->setAttribute('type', 'anrProject');
        $org->setAttribute('xml:id', 'projanr-'.$this->ANRID);
        $org->setAttribute('status', strtoupper($this->VALID));
        if ( $this->REFERENCE ) {
            $idno = $xml->createElement('idno', $this->REFERENCE);
            $idno->setAttribute('type', 'anr');
            $org->appendChild($idno);
        }
        if ( $this->INTITULE ) {
            $idno = $xml->createElement('idno', $this->INTITULE);
            $idno->setAttribute('type', 'program');
            $org->appendChild($idno);
        }
        if ( $this->ACRONYME ) {
            $org->appendChild($xml->createElement('orgName', $this->ACRONYME));
        }
        if ( $this->TITRE ) {
            $org->appendChild($xml->createElement('desc', $this->TITRE));
        }
        if ( $this->ANNEE ) {
            $date = $xml->createElement('date', $this->ANNEE);
            $date->setAttribute('type', 'start');
            $org->appendChild($date);
        }
        $xml->appendChild($org);
        return ($header) ? $xml->saveXML() : $xml->saveXML($xml->documentElement) . PHP_EOL;
    }

    public function getUri()
    {
        return AUREHAL_URL . "/anrproject/{$this->ANRID}";
    }

}