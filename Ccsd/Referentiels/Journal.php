<?php

/**
 * Class Ccsd_Referentiels_Journal
 *
 * @property string $EISSN
 * @property string $ISSN
 * @property string $JNAME
 * @property string $JID
 * @property string $SHORTNAME
 * @property string $PUBLISHER
 * @property string ROOTDOI
 * @property string SHERPA_COLOR
 * @property string $URL
 * @property string $VALID
 *
 */
class Ccsd_Referentiels_Journal extends Ccsd_Referentiels_Abstract
{

    static public $core = 'ref_journal';

    static public $_table = 'REF_JOURNAL';

    protected $_primary = 'JID';

    const METANAME = 'journal';

    const INI = 'Ccsd/Referentiels/Form/journal.ini';

    protected $_smallFormElements = array('JID', 'JNAME', 'ISSN', 'PUBLISHER');

    protected $_mandatoryFormElements = array('JNAME', 'PUBLISHER');

    protected $_smallMandatoryFormElements = array('JNAME');
    
    static protected $_champsSolr = array("docid"
            , "label_s"
            , "label_html"
            , "text"
            , "title_s"
            , "titleAbbr_s"
            , "issn_s"
            , "eissn_s"
            , "doiRoot_s"
            , "publisher_s"
            , "valid_s"
            , "title_t"
            , "titleAbbr_t"
            , "publisher_t"
    );

    static public $_champsEs = array(
        'EISSN',
        'JID',
        'PUBLISHER',
        'ROOTDOI',
        'SHERPA_COLOR',
        'URL',
        'VALID'
    );
    
    static public $_optionsTri = array(
    	'titre' 	=> 'Trier les revues par titre',
    	'valid' 	=> 'Trier les revues par validité',
    	'publisher' => 'Trier les revues par éditeur'
    );
    
    static public $_optionsFilter = array(
    	'all'		=> 'Toutes les revues',
    	'valid'		=> 'Revues valides',
    	'incoming' 	=> 'Revues en attente de validation',
    	'old' 		=> 'Revues fermées'
    );

    static protected $_solRsort = array (
    	'valid' 	=> '&sort=valid_s+desc,title_s+asc',
    	'titre' 	=> '&sort=title_s+asc,valid_s+desc',
    	'publisher'	=> '&sort=publisher_s+asc,title_s+asc,valid_s+desc'
    );

    // Champs acceptés par le formulaire MODIFY
    protected $_acceptedFormValues = ['JID', 'JNAME', 'SHORTNAME', 'ISSN', 'EISSN', 'PUBLISHER', 'ROOTDOI', 'URL', 'VALID'];

    // Champs modifiés par le formulaire MODIFY
    protected $_changeableFormValues = ['JNAME', 'SHORTNAME', 'ISSN', 'EISSN', 'PUBLISHER', 'ROOTDOI', 'URL', 'VALID'];


    /**
     * @see Ccsd_Referentiels_Abstract::_createBaseQuery()
     */
    protected function _createBaseQuery($critere, $orderby, $filter, $nbResultPerPage, $valid) 
    {
     	/* @var $select Zend_Db_Select */
    	$select = Zend_Db_Table_Abstract::getDefaultAdapter()->select()->from(self::$_table);
    	 
    	if ($critere != "*") {
    		$select->orWhere("JID = '?'",            $critere);
    		$select->orWhere("JNAME LIKE '%?%'",     $critere);
    		$select->orWhere("SHORTNAME LIKE '%?%'", $critere);
    		$select->orWhere("ISSN = '?'",           $critere);
    		$select->orWhere("PUBLISHER LIKE '%?%'", $critere);
    	}
    	
    	$select->where("VALID LIKE '?'", $filter);
    	
    	if ('valid' == $orderby) {
    		$select->order(array('VALID DESC', 'JNAME ASC'));
    	} else if ('titre' == $orderby) {
    		$select->order(array('JNAME ASC' , 'VALID DESC'));
    	} else if ('publisher' == $orderby) {
    		$select->order(array('PUBLISHER ASC', 'JNAME ASC' , 'VALID DESC'));
    	}

    	return $select;
    }

    /*
     * Calcul du MD5 de l'objet - ! cohérence avec les triggers TRIG_INS_JOURNAL_MD5 et TRIG_UPT_JOURNAL_MD5
     * @return string
     */
    public function getMd5() {
        return md5(strtolower('jname'.$this->JNAME.'issn'.$this->ISSN.'eissn'.$this->EISSN.'publisher'.$this->PUBLISHER));
    }

    public function getForm($extended = false, $populate = true, $id = 0) {

        $this->_form = parent::getForm($extended, $populate);
 
        if ( defined('SPACE_NAME') && SPACE_NAME == 'AUREHAL' && $this->_form->getElement('ISSN')) {
        	$monValidateur = new Zend_Validate_Db_NoRecordExists(array(
                'table' => 'REF_JOURNAL',
                'field' => 'ISSN',
                'exclude' => array('field' => 'JID', 'value' => $id)
        	));
        	
        	$this->_form->getElement('ISSN')->addValidator($monValidateur);
        }
        
        if ( defined('SPACE_NAME') && SPACE_NAME == 'AUREHAL' && $this->_form->getElement('EISSN')) {
        	$monValidateur2 = new Zend_Validate_Db_NoRecordExists(array(
                'table' => 'REF_JOURNAL',
                'field' => 'EISSN',
                'exclude' => array('field' => 'JID', 'value' => $id)
	        ));
	        
	        $this->_form->getElement('EISSN')->addValidator($monValidateur2);
        }

        return $this->_form;
    }

    /**
     * @deprecated : This fonction seems not to be used!!!  Creator.php don't use it!
     * @param bool $header
     * @return string
     *
     */
    public function getXML($header = true) {
    
        $xml = new Ccsd_DOMDocument('1.0', 'utf-8');
        $xml->formatOutput = true;
        $xml->substituteEntities = true;
        $xml->preserveWhiteSpace = false;
    
        $root = $xml->createElement('journal');
        $root->setAttribute('xml:id', '' . $this->JID);
        $root->setAttribute('date', $this->DATEMODIF);
        $root->setAttribute('valid', $this->VALID);

        if ( $this->JNAME ) {
            $root->appendChild($xml->createElement('journalName', Ccsd_Tools_String::xmlSafe($this->JNAME)));
        }
        if ( $this->SHORTNAME ) {
            $root->appendChild($xml->createElement('journalNameAbrege', Ccsd_Tools_String::xmlSafe($this->SHORTNAME)));
        }
        if ( $this->PUBLISHER ) {
            $root->appendChild($xml->createElement('editeur', Ccsd_Tools_String::xmlSafe($this->PUBLISHER)));
        }
        if ( $this->ISSN ) {
            $root->appendChild($xml->createElement('journalISSN', Ccsd_Tools_String::xmlSafe($this->ISSN)));
        }
        if ( $this->EISSN ) {
            $root->appendChild($xml->createElement('journalEISSN', Ccsd_Tools_String::xmlSafe($this->EISSN)));
        }
        if ( $this->RACINEDOI ) {
            $root->appendChild($xml->createElement('journalRacineDOI', Ccsd_Tools_String::xmlSafe($this->RACINEDOI)));
        }
        if (( $this->URL ) and ( false != Zend_Uri::check($this->URL) )) {
            $ref = $xml->createElement('ref', Ccsd_Tools_String::xmlSafe($this->URL));
            $ref->setAttribute('type', 'url');
            $root->appendChild($ref);
        }
        if ( $this->SHERPA_COLOR ) {
            $sherpa = $xml->createElement('sherpa');
            $sherpa->setAttribute('date', $this->SHERPA_DATE);
            $sherpa->appendChild($xml->createElement('sherpaColor', Ccsd_Tools_String::xmlSafe($this->SHERPA_COLOR)));
            $sherpa->appendChild($xml->createElement('sherpaPreprint', Ccsd_Tools_String::xmlSafe($this->SHERPA_PREPRINT)));
            $sherpa->appendChild($xml->createElement('sherpaPostPrint', Ccsd_Tools_String::xmlSafe($this->SHERPA_POSTPRINT)));
            $sherpa->appendChild($xml->createElement('sherpaPreRest', Ccsd_Tools_String::xmlSafe($this->SHERPA_PRE_REST)));
            $sherpa->appendChild($xml->createElement('sherpaPostRest', Ccsd_Tools_String::xmlSafe($this->SHERPA_POST_REST)));
            $sherpa->appendChild($xml->createElement('sherpaCond', Ccsd_Tools_String::xmlSafe($this->SHERPA_DATE)));
            $root->appendChild($sherpa);
        }
        $xml->appendChild($root);
        return ($header) ? $xml->saveXML() : $xml->saveXML($xml->documentElement) . PHP_EOL;
    }

    /** @return string */
    public function __toString() {
    	/** @var Zend_Controller_Action_Helper_ViewRenderer $viewRenderer */
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');

        //Ajout des variables pour la vue
        $viewRenderer->view->identifier = uniqid("ref");
        $viewRenderer->view->journal    = $this;       
        $viewRenderer->view->options    = false;
         
        if ($arg = func_get_args ()) {
        	if ($arg = array_shift($arg)) {
        		if (is_array($arg) && array_key_exists('showOptions', $arg)) {
        			$viewRenderer->view->options = true;
        		}
        		if (is_array($arg) && array_key_exists('showItem', $arg)) {
        			$viewRenderer->view->item = (new Ccsd_Form_Element_Referentiel('journal'))->setType('journal');
        		}
        		if (is_array($arg) && array_key_exists('hideSHERPA', $arg)) {
        			$viewRenderer->view->hideSHERPA = true;
        		}
        	}
        }

        //Ajout du répertoire des script de vues (library)
        $viewRenderer->view->addScriptPath(__DIR__ . "/views/");

        //Récupération du script traité        
        return $viewRenderer->view->render("journal.phtml");
    }

    public function getUri()
    {
        return AUREHAL_URL . "/revue/{$this->JID}";
    }

    public function getJName()
    {
        return $this->JNAME;
    }

    /**
     * @param $id
     * @return Ccsd_Referentiels_Journal|null
     */
    public static function findById(int $id)
    {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $sql = $db->select()->from(static::$_table)->where('JID = ?', $id);

        $row = $db->fetchRow($sql);

        if ($row) {
            return new Ccsd_Referentiels_Journal(0, $row);
        }

        return null;
    }


}