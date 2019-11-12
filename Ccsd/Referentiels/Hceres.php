<?php

class Ccsd_Referentiels_Hceres extends Ccsd_Referentiels_Abstract
{
    //Définition des types d'entité
    const TYPE_ETABLISSEMENT 	= 'etablissement';
    const TYPE_COORDTERR 		= 'coordterr';
    const TYPE_ENTRECHERCHE 	= 'entrecherche';
    const TYPE_CHAMPRECHERCHE = 'champrecherche';
    const TYPE_LICENCE 		= 'licence';
    const TYPE_LICENCEPRO = 'licencepro';
    const TYPE_MASTER = 'master';
    const TYPE_ECOLEDOCT = 'ecoledoct';
    const TYPE_CHAMPFORMATION = 'champformation';
    const TYPE_INTERNATIONALE = 'internationale';

    /* @var $core string : Définition du core solR */
    static public $core = 'ref_hceres';

    /**
     * Table du référentiel
     * @var string
     */
    static public $_table = 'REF_HCERES';

    /**
     * Clé primaire de la table
     * @var string
     */
    protected $_primary = 'HCERESID';

    /* @var $_identifiant string : identifiant de l'entité */
    protected $_identifiant;

    /* @var $_nom string : nom de l'entité */
    protected $_nom;

    /* @var $_sigle string : sigle de l'entité */
    protected $_sigle;

    /* @var $_nomalt string : ?? de l'entité */
    protected $_nomalt;

    /* @var $_typeentite string : type de l'entité */
    protected $_typeentite;

    /* @var $_typeentite string : ?? */
    protected $_typeobjet;

    /* @var $_typehceres string : ?? */
    protected $_typehceres;

    /* @var $_url string : site web de l'entité */
    protected $_url;

    /* @var $_adresse string : adresse de l'entité */
    protected $_adresse;

    /* @var $_paysid string : identifiant du pays de l'entité */
    protected $_paysid;

    /* @var $_ville string : ville de l'entité */
    protected $_ville;

    /* @var $_typehceres string : région de l'entité */
    protected $_region;

    /* @var $_typehceres string : académie de l'entité */
    protected $_academie;

    /* @var $_typehceres string : entité valide ou non */
    protected $_valid;

    /* @var $_typehceres string : md5 de l'entité */
    protected $_md5;

    const METANAME = 'hceres';

    const INI = 'Ccsd/Referentiels/Form/hceres.ini';

    protected $_smallFormElements = array('HCERESID', 'IDENTIFIANT', 'NOM', 'TYPEHCERES');

    protected $_mandatoryFormElements = array('IDENTIFIANT', 'NOM');

    protected $_smallMandatoryFormElements = array('IDENTIFIANT');

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

    static public $_optionsTri = array(
        'titre' 	=> 'Trier les projets européens par titre',
        'valid' 	=> 'Trier les projets européens par validité',
        'autre' 	=> 'Trier les projets européens par nom'
    );

    static public $_optionsFilter = array(
        'all'		=> 'Toutes les entités HCERES',
        'valid'		=> 'Entités HCERES valides',
        'incoming' 	=> 'Entités HCERES en attente de validation',
        'old' 		=> 'Entités HCERES fermés'
    );

    static protected $_solRsort = array (
        'valid' 	=> '&sort=valid_s+desc,title_s+asc',
        'titre' 	=> '&sort=title_s+asc,valid_s+desc',
        'autre' 	=> '&sort=acronym_s+asc,title_s+asc,valid_s+desc'
    );

    // Champs acceptés par le formulaire MODIFY
    protected $_acceptedFormValues = ['HCERESID', 'IDENTIFIANT', 'NOM', 'TYPEHCERES', 'URL', 'ADRESSE', 'PAYSID', 'VILLE', 'REGION', 'ACADEMIE', 'VALID'];

    // Champs modifiés par le formulaire MODIFY
    protected $_changeableFormValues = ['IDENTIFIANT', 'NOM', 'TYPEHCERES', 'URL', 'ADRESSE', 'PAYSID', 'VILLE', 'REGION', 'ACADEMIE', 'VALID'];

    /**
     * Chargement des données d'une entité
     *
     * @param int  $id        : identifiant de l'entité
     * @param bool $recursive : [F] récursivité (non utilisé)
     *
     * @return mixed : Ccsd_Referentiels_Hceres | null
     */
    public function load($id, $recursive = true)
    {
        if ($id == null) return null;

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $sql = $db->select()->from(self::$_table)
                ->where($this->_primary . ' = ?', $id);
        $row = $db->fetchRow($sql);

        if ($row) {
            $this->_data = $row;
            return $this;
        }

        return null;
    }

    /**
     * @see Ccsd_Referentiels_Abstract::_createBaseQuery()
     */
    protected function _createBaseQuery($critere, $orderby, $filter, $nbResultPerPage, $valid)
    {
        /* @var $select Zend_Db_Select */
        $select = Zend_Db_Table_Abstract::getDefaultAdapter()->select()->from(self::$_table);

        if ($critere != "*") {
            $select->orWhere("HCERESID = '?'",   $critere);
            $select->orWhere("NOM LIKE '%?%'",    $critere);
            $select->orWhere("IDENTIFIANT LIKE '%?%'", $critere);
            $select->orWhere("URL LIKE '%?%'",   $critere);
        }

        if ('valid' == $orderby) {
            $select->order(array('VALID DESC', 'NOM ASC'));
        } else if ('titre' == $orderby) {
            $select->order(array('NOM ASC' , 'VALID DESC'));
        } else if ('autre' == $orderby) {
            $select->order(array('IDENTIFIANT ASC', 'NOM ASC' , 'VALID DESC'));
        }

        return $select;
    }

    public function __toString() {
        /** @var Zend_Controller_Action_Helper_ViewRenderer $viewRenderer*/
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');

        if (! $viewRenderer->view) {
            return $this->HCERESID;
        }

        //Ajout des variables pour la vue
        $viewRenderer->view->identifier = uniqid("ref");
        $viewRenderer->view->hceres = $this;

        if ($arg = func_get_args ()) {
            if ($arg = array_shift($arg)) {
                if (is_array($arg)) {
                    if (array_key_exists('showOptions', $arg)) {
                        $viewRenderer->view->options = true;
                    }
                    if (array_key_exists('showItem', $arg)) {
                        $viewRenderer->view->item = (new Ccsd_Form_Element_Referentiel('hceres'))->setType('hceres');
                    }
                }
            }
        }

        //Ajout du répertoire des script de vues (library)
        $viewRenderer->view->addScriptPath(__DIR__ . "/views/");

        //Récupération du script traité
        return $viewRenderer->view->render("hceres.phtml");
    }


    /**
     * Valide les données d'un référentiel
     * @param mixed $value : données du référentiel à valider
     * @return boolean
     */
    public function isValid ($value) {

        return true;
    }

    /*
     * Calcul du MD5 de l'objet - ! cohérence avec les triggers TRIG_INS_PEUROP_MD5 et TRIG_UPT_PEUROP_MD5
     * @return string
     */
    public function getMd5() {
        return md5(strtolower('identifiant'.$this->IDENTIFIANT.'nom'.$this->NOM.'typehceres'.$this->TYPEHCERES));
    }

    public function getXML($header = true) {

        $xml = new Ccsd_DOMDocument('1.0', 'utf-8');
        $xml->formatOutput = true;
        $xml->substituteEntities = true;
        $xml->preserveWhiteSpace = false;

        $org = $xml->createElement('org');
        $org->setAttribute('type', 'hceres');
        $org->setAttribute('xml:id', 'hceres-'.$this->HCERESID);
        $org->setAttribute('status', strtoupper($this->VALID));
        if ( $this->IDENTIFIANT) {
            $idno = $xml->createElement('idno', $this->IDENTIFIANT);
            $idno->setAttribute('type', 'identifiant');
            $org->appendChild($idno);
        }
        if ( $this->TYPEHCERES ) {
            $idno = $xml->createElement('idno', $this->TYPEHCERES);
            $idno->setAttribute('type', 'typehceres');
            $org->appendChild($idno);
        }
        if ( $this->URL ) {
            $idno = $xml->createElement('idno', $this->URL);
            $idno->setAttribute('type', 'url');
            $org->appendChild($idno);
        }
        if ( $this->NOM ) {
            $org->appendChild($xml->createElement('desc', $this->NOM));
        }
        $xml->appendChild($org);
        return ($header) ? $xml->saveXML() : $xml->saveXML($xml->documentElement) . PHP_EOL;
    }

    public function getUri()
    {
        return AUREHAL_URL . "/hceres/{$this->HCERESID}";
    }

    public function initForm()
    {
        parent::initForm();

        $types = array();
        foreach ($this->getTypes() as $type) {
            $types[$type] = $type;
        }

        $this->_form->getElement('TYPEHCERES')->addMultiOptions($types);

        $this->_form->getElement('PAYSID')->addMultiOptions(array_merge(array(''=>''),Ccsd_Locale::getCountry(null, true, true)));
    }

    /**
     * Retourne la liste des types d'entité HCERES
     * se base sur les constantes de classe TYPE
     */
    public function getTypes ()
    {
        $res = array();
        $reflect = new ReflectionClass(get_class($this));
        foreach ($reflect->getConstants() as $const => $value) {
            if (substr($const, 0, 5) === 'TYPE_') {
                $res[] = $value;
            }
        }
        return $res;
    }

    public static function search($q, $nbResultats = 100)
    {
        $sql = Zend_Db_Table_Abstract::getDefaultAdapter()->select()->from('REF_HCERES', [new Zend_Db_Expr('HCERESID AS docid'), new Zend_Db_Expr('CONCAT_WS(" ", NOM, "(", IDENTIFIANT, ")") AS label_html')]);
        $sql->where("HCERESID LIKE ?", $q . '%');
        $sql->orWhere("NOM LIKE ?", $q . '%');
        $sql->orWhere("IDENTIFIANT LIKE ?", $q . '%');
        return Zend_Db_Table_Abstract::getDefaultAdapter()->fetchAll($sql);
    }

    //Pas d'enregistrement possible dans ce référentiel
    public function save($forceUpdate = false, $acceptDedoublonnage = false)
    {
        return $this->{$this->_primary};
    }

    /**
     * Récupération de données d'indexation SolR
     *
     * @return array : tableau des données à indexer pour l'Hceres
     */
    public function getIndexationData()
    {
        $dataToIndex = [
            // hceres_entityName_s : nom de l'entité évaluée
            'name_s' => $this->NOM,
            // hceres_entityCodeUai_s : code UAI
            'codeUai_s' => $this->CODE_UAI,
            // hceres_entityCodeRnsr_s : code RNSR
            'codeRnsr_s' => $this->CODE_RNSR,
            // hceres_entityCodeAccredit_s : code accreditation
            'codeAccredit_s' => $this->CODE_ACCREDITATION,
            // hceres_entityCommonName_s : nom d'usage
            'commonName_s' => $this->NOM_USAGE,
            // hceres_entityAliasName_s : nom alias
            'aliasName_s' => $this->NOM_ALIAS,
            // hceres_entityCity_s : ville
            'city_s' => $this->VILLE,
            // hceres_entityAcademy_s : académie
            'academy_s' => $this->ACADEMIE,
            // hceres_entityRegion_s : région
            'region_s' => $this->REGION,
            // hceres_entityType_s : type de structure évaluée:x
            'type_s' => $this->TYPEHCERES,
        ];
        /**
         *  hceres_entityCountry_s : pays
         */
        if ($this->PAYSID) {
            $countryUp = strtoupper($this->PAYSID);
            $country[] = Zend_Locale::getTranslation($countryUp, 'country', 'fr');
            $country[] = Zend_Locale::getTranslation($countryUp, 'country', 'en');
            $country[] = $this->PAYSID;
            $dataToIndex['country_s'] = $this->PAYSID;
            $dataToIndex['country_t'] = array_unique($country);
        }

        if (in_array($this->TYPEHCERES, ['FF', 'FE'])) {
            // hceres_entityFormationType_s :
            $dataToIndex['formationType_s'] = $this->STYPEHCERES;
        } else if (in_array($this->TYPEHCERES, ['ER'])) {
            // hceres_entityFormationType_s :
            $dataToIndex['rechercheType_s'] = $this->STYPEHCERES;
        } else {
            // hceres_entityEtabType_s : type d'établissement/organisme
            $dataToIndex['etabType_s'] = $this->STYPEHCERES;
        }

        return $dataToIndex;
    }

    /**
     * Chargement des enfants d'une entité
     *
     * @param int  $id : identifiant de l'entité
     *
     * @return mixed : Ccsd_Referentiels_Hceres | null
     */
    public function loadChild()
    {
        if ($this->NEW_HCERESID == null) return null;

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $sql = $db->select()->from(self::$_table)
            ->where($this->_primary . ' = ?', $this->NEW_HCERESID);
        $row = $db->fetchRow($sql);
        if ($row) {
            $this->_dataChild = $row;
            return $this;
        }
        return null;
    }


}