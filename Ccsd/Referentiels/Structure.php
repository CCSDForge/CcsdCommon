<?php
/**
 * Structure
 *
 */
class Ccsd_Referentiels_Structure extends Ccsd_Referentiels_Abstract
{
    //Définition des types de structures
    const TYPE_RESEARCHTEAM 	= 'researchteam';
    const TYPE_DEPARTMENT 		= 'department';
    const TYPE_LABORATORY 		= 'laboratory';
    const TYPE_REGROUPLABORATORY = 'regrouplaboratory';
    const TYPE_INSTITUTION 		= 'institution';
    const TYPE_REGROUPINSTITUTION = 'regroupinstitution';

    static $typeOrder = [
        self::TYPE_RESEARCHTEAM       => 1,
        self::TYPE_DEPARTMENT 		  => 2,
        self::TYPE_LABORATORY 		  => 3,
        self::TYPE_REGROUPLABORATORY  => 4,
        self::TYPE_INSTITUTION 		  => 5,
        self::TYPE_REGROUPINSTITUTION => 6,
    ];

    //Définition des états de structures
    const STATE_VALID 			= 'VALID';
    const STATE_OLD	 			= 'OLD';
    const STATE_INCOMING 		= 'INCOMING';

    //Definition des etapes de modifications
    static public $steps = array('meta', 'modif_affi', 'ajout_affi');

    const INI = 'Ccsd/Referentiels/Form/structure.ini';

    /* @var $core string : Définition du core solR */
    static public $core 	= 'ref_structure';

    /**
     * Table du référentiel
     * @var string
     */
    protected $_table 			= 'REF_STRUCTURE';

    /**
     * Table du référentiel
     * @var string
     */
    protected $_table_ref 		= 'REF_STRUCT_PARENT';


    protected $_table_idext 		= 'REF_STRUCTURE_IDEXT';

    /**
     * Clé primaire de la table
     * @var string
     */
    protected $_primary 		= 'STRUCTID';

    /* @var array $_parents : Tableau de couple [ code ,  ] Structures parentes de la structure */
    protected $_parents 		= array();

    /* @var $_parentCount int : Nombre de structures parentes */
    protected $_parentCount		= 0;

    /* @var Ccsd_Referentiels_Structure[] $_children : Structures enfant de la structure */
    protected $_children 		= array();

    /* @var $_childCount int : Nombre de structures enfants */
    protected $_childCount		= 0;

    /* @var $_structid int : Identifiant de la structure */
    protected $_structid 		= 0;

    /* @var $_sigle string : Sigle de la structure */
    protected $_sigle 			= '';

    /* @var $_structname string : Nom de la structure */
    protected $_structname 		= '';

    /* @var $_address string : Adresse postale de la structure */
    protected $_address 		= '';

    /* @var $_paysid string : Code pays de la structure */
    protected $_paysid 			= '';

    /* @var $_url string : URL de la structure */
    protected $_url 			= '';

    /* @var $_sdate string : Date ouverture de la structure */
    protected $_sdate 			= '';

    /* @var $_edate string : Date fermeture de la structure */
    protected $_edate 			= '';

    /* @var $_typestruct string : Type de la structure */
    protected $_typestruct 		= '';

    /* @var $_valid string : État de la structure */
    protected $_valid 			= '';

    /* @var $_locked boolean : Verrouillage ou non de la structure */
    protected $_locked 			= 0;

    /* @var $_idExt array : Identfiants exterieurs de la structure*/
    protected $_idExt			= array();

    /* @var $_datemodif timestamp */
    protected $_datemodif;

    /* @var $_md5 string */
    protected $_md5;

    /* @var $_form Ccsd_Form */
    protected $_form;

    /* @var $_optionsTri array */
    static public $_optionsTri = array(
        'name' 	=> 'Trier les structures par nom',
        'type' 	=> 'Trier les structures par type',
        'valid' => 'Trier les structures par validité'
    );

    static public $_optionsFilter = array(
        'all'		=> 'Toutes les structures',
        'valid'		=> 'Structures valides',
        'incoming' 	=> 'Structures en attente de validation',
        'old' 		=> 'Structures fermées'
    );

    static protected $_solRsort = array (
        'valid' => '&sort=valid_s+desc,name_s+asc',
        'name' 	=> '&sort=name_s+asc,valid_s+desc',
        'type' 	=> '&sort=type_s+asc,name_s+asc,valid_s+desc'
    );

    private static $_champsSolr = array(
        "docid",
        "label_s",
        "label_html",
        "name_s",
        "acronym_s",
        "country_s",
        "type_s",
        "url_s",
        "valid_s",
        "name_t",
        "url_t"
    );

    public static $_champsEs = array(
        'STRUCTID',
        'SIGLE',
        'STRUCTNAME',
        'ADDRESS',
        'PAYSID',
        'URL',
        'SDATE',
        'EDATE',
        'TYPESTRUCT',
        'VALID',
        'parents'
    );

    protected $_smallFormElements 			= array('STRUCTID', 'STRUCTNAME', 'TYPESTRUCT');

    protected $_mandatoryFormElements 		= array('STRUCTNAME', 'TYPESTRUCT');

    protected $_smallMandatoryFormElements 	= array('STRUCTNAME', 'TYPESTRUCT');

    protected $_dateFormElements = array('SDATE', 'EDATE');

    /* @var $_adminStruct array : Référents de la structure */
    protected $_adminStruct = array();

    // Champs acceptés par le formulaire MODIFY
    protected $_acceptedFormValues = ['STRUCTID', 'STRUCTNAME', 'SIGLE', 'ADDRESS', 'PAYSID', 'TYPESTRUCT', 'URL', 'SDATE', 'EDATE', 'VALID', 'IDEXT'];

    // Champs modifiés par le formulaire MODIFY
    protected $_changeableFormValues = ['STRUCTNAME', 'SIGLE', 'ADDRESS', 'PAYSID', 'TYPESTRUCT', 'URL', 'SDATE', 'EDATE', 'VALID', 'IDEXT'];

    //Constructeur...
    public function __construct ($structid = 0, $data = array(), $recursive = true)
    {
        if (0 != $structid) {
            $this->setStructid($structid);
            $this->load($this->_structid, $recursive);
        } else {
            $this->set($data, $recursive);
        }
    }

    //Chargement des données d'une structure en fonction du référentiel
    public function load ($id, $recursive = true)
    {
        $this->_structid 	= 0;
        $this->_sigle 		= '';
        $this->_structname 	= '';
        $this->_address 	= '';
        $this->_paysid 		= '';
        $this->_url 		= '';
        $this->_typestruct 	= '';
        $this->_valid 		= '';
        $this->_locked 		= 0;
        $this->_datemodif 	= '';
        $this->_md5 		= '';
        $this->_parents 	= array ();

        // recherche de l'id du nouvel alias éventuel
        $newid = Ccsd_Referentiels_Alias::getAliasNewId($id, static::$core);
        if ($newid != $id && $newid != 0) {
            $id = $newid;
        }

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $sql = $db->select()
            ->from($this->_table, array ('STRUCTID', 'SIGLE', 'STRUCTNAME', 'ADDRESS', 'PAYSID', 'URL', 'SDATE', 'EDATE', 'TYPESTRUCT', new Zend_Db_Expr('HEX(MD5) AS MD5'), 'DATEMODIF', 'VALID', 'LOCKED'))
            ->where('STRUCTID = ?', $id);

        $row = $db->fetchRow($sql);

        if ($row) {

            $this->set($row);

            //Récupération des identifiants exterieurs
            $sql = $db->select()
                ->from($this->_table_idext, array ('SERVERID', 'ID'))
                ->where('STRUCTID = ?', $id);
            $this->_idExt = $db->fetchPairs($sql);

            if ($recursive) {
                $sql2 = $db->select()
                    ->from($this->_table_ref)
                    ->where('STRUCTID = ?', $id);

                foreach ($db->fetchAll($sql2) as $parent) {
                    $this->addParent(new Ccsd_Referentiels_Structure($parent['PARENTID']), $parent['CODE']);
                }
            }

            if (!$this->getAdminStruct()) {
                $this->setAdminStruct();
            }
        } else {
            $this->setStructid(0);
        }

        return $this;
    }

    public function set ($data, $recursive = true)
    {
        $methods = get_class_methods($this);
        if (is_array ($data) && !empty ($data)) {
            foreach ($data as $key => $value) {
                if ('parents' == $key) continue;
                $key = strtolower($key);
                $method = 'set' . ucfirst($key);
                if (in_array($method, $methods)) {
                    $this->$method($value);
                }
            }
        }
        if ($this->getStructid()) {
            $this->setAdminStruct();
        }

        if (isset($data['parents']) &&  is_array($data['parents']) && $recursive) {
            foreach($data['parents'] as  $dataParent) {
                //Cas ou la structure n'a pas était instanciée et est sous la forme d'un tableau de donnée
                if (is_array($dataParent['struct'])) {
                    if (SPACE_NAME != 'AUREHAL' && isset($dataParent['struct']['STRUCTID']) && $dataParent['struct']['STRUCTID'] != 0 && isset($dataParent['struct']['VALID']) && $dataParent['struct']['VALID'] == 'VALID' ) {
                        $structure = new static($dataParent['struct']['STRUCTID']);
                    } else {
                        $structure = new static();
                        $structure->set($dataParent['struct']);
                    }
                } else if (!$dataParent['struct'] instanceof static) {
                    $structure = new static ($dataParent['struct']);
                } else {
                    $structure = $dataParent['struct'];
                }

                $this->addParent($structure, Ccsd_Tools::ifsetor($dataParent['code']));
            }
        }

        return true;
    }

    /**
     *
     */
    public function children ()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $sql = $db->select()->from(array("ref" => $this->_table_ref), array('ref.STRUCTID', 'ref.CODE'))->where("ref.PARENTID = ?", $this->_structid, Zend_Db::INT_TYPE)
            ->joinLeft(array("s" => $this->_table), "ref.STRUCTID = s.STRUCTID");

        if (($row = $db->fetchAll($sql)) !== false) {
            foreach ($row as $r) {
                $child = new static(0, $r, false);
                $this->addChild($child, $r['CODE']);
            }
        }
    }

    /**
     * @see Ccsd_Referentiels_Abstract::_createBaseQuery()
     * @deprecated  Ne semble pas utilisee : voir _createSolrQuery
     * Heureusement car getSelectFromTable non definie...
     */
    protected function _createBaseQuery($critere, $orderby, $filter, $nbResultPerPage=20, $valid, $category = '*')
    {
        //je rajoute la condition
        if ($critere == "*")
        {
            $condition = 1;
        } else {
            $condition = "(STRUCTID = '" . $critere . "')";
            $condition .= " OR (STRUCTNAME LIKE '%" . $critere . "%')";
            $condition .= " OR (ADDRESS LIKE '%" . $critere . "%')";
        }

        switch ($orderby) {
            case "valid" :
                $order = array('VALID DESC', 'STRUCTNAME ASC');
                break;
            case "name" :
                $order = array('STRUCTNAME ASC' , 'VALID DESC');
                break;
            case "type" :
                $order = array('TYPESTRUCT ASC', 'LASTNAME ASC' , 'VALID DESC');
                break;
            default :
                $order = array();
        }
        $select = $this->getSelectFromTable()->where($condition)->order($order);

        return $select;
    }

    protected function custom_filter_solR ($options)
    {
        if (!is_array ($options) || empty ($options)) return parent::custom_filter_solR($options);
        return "&fq=type_s:" . array_shift($options);
    }

    /**
     * @param Ccsd_Referentiels_Structure $oneParent est de type Ccsd_Referentiels_Structure
     * @param string $code
     * @return int
     */
    public function addParent ($oneParent, $code)
    {
        $this->setParentCount($this->getParentCount() + 1);
        $this->_parents[$this->_parentCount] = array(
            'code' => $code,
            'struct' => $oneParent
        );
        return $this->_parentCount;
    }

    public function getParents ()
    {
        return $this->_parents;
    }

    public function getAllParents()
    {
        $res = array();
        foreach ($this->_parents as $parent) {
            $res[] = $parent;
            if ($parent['struct']->hasParent()) {
                $res = array_merge($res, $parent['struct']->getAllParents());
            }
        }
        return $res;
    }

    public function removeParents ()
    {
        $this->_parents = array();
        $this->_parentCount = 0;
        return $this;
    }

    public function hasParent()
    {
        return ($this->_parentCount > 0);
    }

    /*
     * $child est de type Ccsd_Referentiels_Structure
     */
    public function addChild ($child, $code)
    {
        $this->setChildCount($this->_childCount + 1);
        $this->_children[$this->_childCount] = array(
            'code' => $code,
            'struct' => $child
        );
        return $this->_childCount;
    }

    public function getChildren ()
    {
        if (empty ($this->_children)) {
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();

            $sql = $db->select()
                ->from($this->_table_ref)
                ->where('PARENTID = ?', $this->_structid);

            foreach ($db->fetchAll($sql) as $child) {
                $this->addChild(new Ccsd_Referentiels_Structure($child['STRUCTID'], '', true), $child['CODE']);
            }
        }
        return $this->_children;
    }

    public function getAllChildren()
    {
        $res = array();
        foreach ($this->getChildren() as $child) {
            $res[] = $child['struct'];
            if ($child['struct']->hasChildren()) {
                $res = array_merge($res, $child['struct']->getAllChildren());
            }
        }
        return $res;
    }

    public function hasChildren()
    {
        return $this->_childCount > 0;
    }

    /**
     * Duplique une structure et la ferme / Si newParent alors change le parent de la structure dupliquée
     * $newParent Ccsd_Referentiels_Structure
     * @return Ccsd_Referentiels_Structure[][]  Tableau de couple
     */
    public function duplicate($oldParent = null, $newParent = null)
    {
        $newstruct = new Ccsd_Referentiels_Structure($this->getStructid()); //Crée le duplicata de la structure
        $newstruct->setStructid(0); //Reset de son Id

        if ($newParent != null && $oldParent != null) {
            $newstruct->replaceParents($oldParent,$newParent); //Remplacement de l'ancien parent par le nouveau
        }

        $newstruct->save();
        $closedStructures = [ $this ];
        $duplicatedStructures = [ $newstruct ];
        $this->setValid(self::STATE_OLD); //Ferme l'ancienne structure
        $this->save();

        $couple = [ $this, $newstruct ];
        $oldId = $this -> getId();
        $returnListe = [ "x$$oldId" => $couple ];  // Tableau associatif idRemplace => couple (il faut des cles chaines pour arraymerge?

        foreach ($this->getChildren() as $child) { //On boucle pour tous les enfants de la structure
            /** @var Ccsd_Referentiels_Structure $childObj */
            $childObj = $child['struct'];
            if ($childObj->getValid() != self::STATE_VALID) {
                continue;
            } else {
                $ret = $childObj->duplicate($this, $newstruct);
                $returnListe = array_merge($returnListe, $ret);
            }
        }
        return $returnListe;
    }

    /**
     * Transfert en dupliquant les structures enfants d'une structure A à une structure B
     * Structure A + ses enfants sont fermés
     * @param Ccsd_Referentiels_Structure $newStruct
     * @return array
     */
    public function transfertStruct($newStruct)
    {
        $returnListe =[];
        foreach ($this->getChildren() as $child) {
            /** @var Ccsd_Referentiels_Structure $childObj */
            $childObj = $child['struct'];
            // On transfert/duplique seulement les structures VALID
            if ($childObj->getValid() != self::STATE_VALID){
                continue;
            } else {
                //Stock les enfants de l'ancienne structure pour le récap
                $closedStructures[] = $childObj;
                //Stock les enfants de la future structure pour le récap / Duplique l'enfant
                $res = $childObj->duplicate($this,$newStruct);
                $returnListe = array_merge($returnListe, $res);
            }
        }

        $this->setValid(self::STATE_OLD); //Ferme l'ancienne structure
        $this->save();

        $newStruct->save();

        return $returnListe;
    }

    public function isValid ($value = null)
    {
        if (is_array ($value)) {
            return (new $this)->getForm()->isValid($value);
        } else  if (isset($value)) {
            return (new $this)->load($value);
        } else return $this->_valid == self::STATE_VALID || $this->_valid == self::STATE_OLD;
    }

    public function  getXML ($header = true)
    {
        $xml = new Ccsd_DOMDocument('1.0', 'utf-8');

        $this->_appendXMLProperties($xml);

        $root = $xml->createElement('org');
        $root->setAttribute('type', $this->_typestruct);
        $root->setAttribute('xml:id', 'struct-' . $this->_structid);
        $root->setAttribute('status', strtoupper($this->_valid));

        $xml->appendChild($root);

        $this->_appendXMLStructnamePart	 ($xml, $root)
            ->_appendXMLSiglePart		 ($xml, $root)
            ->_appendXMLAdditionnalPart ($xml, $root)
            ->_appendXMLParentsPart	 ($xml, $root);

        return ($header) ? $xml->saveXML() : $xml->saveXML($xml->documentElement) . PHP_EOL;
    }

    protected function _appendXMLProperties (DOMDocument &$xml)
    {
        $xml->formatOutput = true;
        $xml->substituteEntities = true;
        $xml->preserveWhiteSpace = false;
        return $this;
    }

    protected function _appendXMLStructnamePart (DOMDocument &$xml, DOMElement &$root)
    {
        if ( is_array($this->_idExt) ) {
            foreach ( $this->getIdextLink() as $domain => $idext ) {
                $ident = $xml->createElement('idno', $idext['id']);
                $ident->setAttribute('type', $domain);
                $root->appendChild($ident);
            }
        }
        if ($this->_structname) {
            $root->appendChild($xml->createElement('orgName', $this->_structname));
        }
        return $this;
    }

    protected function _appendXMLSiglePart (DOMDocument &$xml, DOMElement &$root)
    {
        if ($this->_sigle) {
            $acronym = $xml->createElement('orgName', $this->_sigle);
            $acronym->setAttribute('type', 'acronym');
            $root->appendChild($acronym);
        }
        if ( $this->_sdate && $this->_sdate != '0000-00-00' ) {
            $d = $xml->createElement('date', Ccsd_Tools::str2date($this->_sdate));
            $d->setAttribute('type', 'start');
            $root->appendChild($d);
        }
        if ( $this->_edate  && $this->_edate != '0000-00-00' ) {
            $d = $xml->createElement('date', Ccsd_Tools::str2date($this->_edate));
            $d->setAttribute('type', 'end');
            $root->appendChild($d);
        }
        return $this;
    }

    protected function _appendXMLAdditionnalPart (DOMDocument &$xml, DOMElement &$root)
    {
        if ($this->_address || $this->_paysid || $this->_url) {
            $desc = $xml->createElement('desc');
            if ($this->_address || $this->_paysid) {
                $address = $xml->createElement('address');
                if ($this->_address) {
                    $address->appendChild($xml->createElement('addrLine', $this->_address));
                }
                if ($this->_paysid) {
                    $country = $xml->createElement('country');
                    $country->setAttribute('key', strtoupper($this->_paysid));
                    $address->appendChild($country);
                }
                $desc->appendChild($address);
            }
            if (($this->_url) and (false != Zend_Uri::check($this->_url))) {
                $ref = $xml->createElement('ref', $this->_url);
                $ref->setAttribute('type', 'url');
                $desc->appendChild($ref);
            }
            $root->appendChild($desc);
        }
        return $this;
    }

    protected function _appendXMLParentsPart (DOMDocument &$xml, DOMElement &$root)
    {
        if ($this->getParentCount()) {
            $lr = $xml->createElement('listRelation');
            $uniq = array();
            // Integration de toute l'arborescence des parents : @type="direct" pour les parents directs
            // et type="indirect" pour les parents de parents
            foreach ($this->getAllParents() as $parent) {
                if ( !in_array(Ccsd_Tools::ifsetor($parent['code'], '').$parent['struct']->getStructid(), $uniq) ) {
                    $r = $xml->createElement('relation');
                    if ($parent['code']) {
                        $r->setAttribute('name', $parent['code']);
                    }
                    $r->setAttribute('active', '#struct-' . $parent['struct']->getStructid());
                    if ( in_array($parent['struct']->getStructid(), $this->getParentsStructids(false)) ) {
                        $r->setAttribute('type', 'direct');
                    } else {
                        $r->setAttribute('type', 'indirect');
                    }
                    $lr->appendChild($r);
                    $uniq[] = Ccsd_Tools::ifsetor($parent['code'], '').$parent['struct']->getStructid();
                }
            }
            $root->appendChild($lr);
        }
    }

    static public function getFullXml ($structid = null, $header = true)
    {
        if (null != $structid) {
            $struct = new self($structid);
            $xml = $struct->getXML($header);
            if ($struct->getParentCount()) {
                foreach ($struct->getParents() as $parent) {
                    $xml .= self::getFullXml($parent['struct']->getStructid(), false);
                }
            }
            return $xml;
        }
    }

    /*
    * Renvoie le nombre de dépôts liés à un élement du référentiel
     * @param int|array
     * @return int
    */
    static public function getCountOfRelatedDocid($id=0) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        if ( is_array($id) || is_numeric($id) ) {
            $select = $db->select()
                ->from(array('DA' => 'DOC_AUTHOR'), array( new Zend_Db_Expr('COUNT(DISTINCT DOCID)') ))
                ->joinLeft(array('DAS' => 'DOC_AUTSTRUCT'),'DAS.DOCAUTHID = DA.DOCAUTHID');
            if ( is_array($id) ) {
                $select->where('DAS.STRUCTID IN (?)', $id);
            } else {
                $select->where('DAS.STRUCTID = ?', (int)$id);
            }
            return ($db->fetchOne($select));
        } else {
            return false;
        }
    }

    /*
     * Renvoie les docid des dépôts liés à un élement du référentiel
     * @param int|array
     * @return array
    */
    static public function getRelatedDocid($id=0) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        if ( is_array($id) || is_numeric($id) ) {
            $select = $db->select()
                ->distinct()
                ->from(array('DA' => 'DOC_AUTHOR'), array('DOCID'))
                ->joinLeft(array('DAS' => 'DOC_AUTSTRUCT'),'DAS.DOCAUTHID = DA.DOCAUTHID');
//            ->joinLeft(array('DAS' => 'DOC_AUTSTRUCT'),'DAS.DOCAUTHID = DA.DOCAUTHID', null);
            if ( is_array($id) ) {
                $select->where('DAS.STRUCTID IN (?)', $id);
            } else {
                $select->where('DAS.STRUCTID = ?', (int)$id);
            }
            return ($db->fetchCol($select));
        } else {
            return false;
        }
    }

    /*
     * Renvoie les structid des fils
     * @param int
     * @return array
    */
    static public function getAllChildsId($id=0) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        if ( is_numeric($id) ) {
            $sql = $db->select()
                ->from('REF_STRUCT_PARENT', 'STRUCTID')
                ->where('PARENTID = ?', (int)$id);
            $res = $db->fetchCol($sql);
            if ( count($res) ) {
                foreach($res as $childsStructid) {
                    $res = array_merge( $res, self::getAllChildsId($childsStructid));
                }
            }
            return $res;
        } else {
            return array();
        }
    }

    /*
     * remplacements des structid parents
     * @param int
     * @param int
     * @return bool
    */
    static public function updateChilds($from = 0, $to = 0) {
        try {
            if ( $from == 0 || $to == 0 ) {
                return false;
            }
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            $where['PARENTID = ?'] = (int)$from;
            $where['STRUCTID != ?'] = (int)$from;
            return $db->update('REF_STRUCT_PARENT', array('PARENTID' => $to), $where);
        } catch (Exception $e) {return false;}
    }

    /**
     * @var Ccsd_Referentiels_Structure $old
     * @param Ccsd_Referentiels_Structure $new
     * @return array
     */
    public function replaceParents($old, $new)
    {
        foreach ($this->_parents as $key => $structCodeArray) {
            /** @var Ccsd_Referentiels_Structure $struct */
            $struct = $structCodeArray['struct'];
            if ($old->getStructid() == $struct->getStructid()) {
                $code = $structCodeArray['code'];
                unset ($this->_parents[$key]);
                $this->_parents[$key]['code'] = $code; //Le code est copié de l'ancienne structure, est-ce bien ?
                $this->_parents[$key]['struct'] = $new;
                break;
            }
        }
        return $this->_parents;
    }


    public function toArray (array $filter = [])
    {
        $res = array(
            'STRUCTID'   => $this->_structid,
            'SIGLE'      => $this->_sigle,
            'STRUCTNAME' => $this->_structname,
            'ADDRESS'    => $this->_address,
            'PAYSID'     => $this->_paysid,
            'URL' 	     => $this->_url,
            'SDATE' 	 => $this->_sdate,
            'EDATE' 	 => $this->_edate,
            'TYPESTRUCT' => $this->_typestruct,
            'DATEMODIF'  => $this->_datemodif,
            'VALID'      => $this->_valid,
            'LOCKED'     => $this->_locked,
            'IDEXT'      => $this->_idExt,
            'parents'    => count($this->getParents())
        );

        $resFiltered = Array();
        if (!empty($filter)) {
            foreach ($filter as $field) {
                if (isset($res[$field]) && !empty($res[$field])) {
                    $resFiltered[$field] = $res[$field];
                } else if ($field == 'URLEXT') { // sytematic call to database: give only if asked
                    $resFiltered['URLEXT'] = $this->getIdextLink();
                }
            }
        } else {
            $resFiltered = $res;
            $resFiltered['URLEXT'] = $this->getIdextLink();
        }

        if (isset($resFiltered['parents'])) {
            $resFiltered['parents'] =  Array();
            foreach ($this->getParents() as $structure) {
                $structure['struct'] = $structure['struct']->toArray($filter);
                $resFiltered['parents'][] = $structure;
            }
        }

        return $resFiltered;
    }

    public function initForm()
    {
        parent::initForm();

        $types = array();
        foreach ($this->getTypes() as $type) {
            $types[$type] = $type;
        }

        if (SPACE_NAME == 'AUREHAL') {
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            $sql = $db->select()
                ->from('REF_SERVEREXT', array('SERVERID', 'NAME'))
                ->where('TYPE IN ("S", "AS")')
                ->order('ORDER ASC');
            $this->_form->addElement('multiTextSimpleLang', 'IDEXT', array(
                'label' => 'Identifiants externes',
                'populate' => $db->fetchPairs($sql)
            ));
        }

        $this->_form->getElement('TYPESTRUCT')->addMultiOptions($types);

        $this->_form->getElement('PAYSID')->addMultiOptions(array_merge(array(''=>''),Ccsd_Locale::getCountry(null, true, true)));

        if ($this->canLockStruct()){
            $this->_form->addElement('select', 'LOCKED', array(
                'label' => 'Verrouiller la structure',
                'multioptions' => array (
                    '0' => 'Non',
                    '1' => 'Oui')));
        }
    }

    public function populate ()
    {
        $this->initForm();
        foreach ($this->_form->getElements() as $element) {
            $method = "get" . ucfirst(strtolower($element->getName()));
            if (method_exists($this, $method)) {
                $this->_form->getElement($element->getName())->setValue($this->$method());
            }
        }

    }

    /*
     * A mettre dans un div avec l'echo de la structure et la poubelle
     * La modification d'un code n'affecte que la structure courante
     */
    public function getFormAffiliation($populate = true)
    {
        $data = array();
        $this->_formAffiliation = new Ccsd_Form();
        foreach ($this->getParents() as $structAffi )
        {
            $nomElement = 'code_' . $structAffi['struct']->getStructId();
            $data[$nomElement] = $structAffi['code'];
            $this->_formAffiliation->addElement('text', $nomElement, array("Label" => "Code"));
        }
        $this->_formAffiliation->setActions(true)->createSubmitButton('modify', array(
            "label" => Ccsd_Form::getDefaultTranslator()->translate("Modifier les affiliations"),
            "class" => "btn btn-primary",
            "style" => "margin-top: 15px;"
        ));
        if ($populate) {
            $this->_formAffiliation->populate($data);
        }
        return $this->_formAffiliation;
    }

    /**
     * Retourne la liste des types de structures disponibles
     * se base sur les constantes de classe TYPE
     */
    public function getTypes ()
    {
        $res = array();
        $reflect = new ReflectionClass(get_class($this));
        foreach ($reflect->getConstants() as $const => $value) {
            if (substr($const, 0, 5) === 'TYPE_') {
                if (SPACE_NAME == 'hal' && ($value == self::TYPE_REGROUPLABORATORY || $value == self::TYPE_REGROUPINSTITUTION)){
                    continue;
                }
                $res[] = $value;
            }
        }
        return $res;
    }
    /**
     * Enregistrement en base
     * @param
     * @param bool : ici n'est pas utilisé
     *          permet de forcer la fusion de forme auteur lorsqu'une forme est modifiée depuis le profil de l'utilisateur et devient identique à une existante
     */
    public function save ($forceUpdate = false, $acceptDedoublonnage = false)
    {
        $action_log = 'NOTHING';
        // Pas de modification d'un laboratoire du référentiel en dehors de l'application AUREHAL
        // on renvoie le structid existant
        if (SPACE_NAME != 'AUREHAL' && $this->getStructid() != 0) {
            return $this->getStructid();
        }
        // Cas d'une nouvelle structure pour les applications autre que AUREHAL
        // ou Sauvegarde les affiliations dans le cas d'AUREHAL
        if (!empty ($this->_parents)) {
            foreach($this->_parents as $parent) {
                // Je demande la sauvegarde de chaque parent (niveau immédiatement supérieur)
                // Et on parcourt l'arborescence de proche en proche
                if ($parent['struct'] instanceof static) {
                    if ($parent['struct']->getStructid() != 0) {
                        // On ne modifie pas une structure parente depuis une structure fille (si elle existe déjà)
                        continue;
                    } else {
                        $parent['struct']->save();
                    }
                }
            }
        }

        $defaultState = (SPACE_NAME == 'AUREHAL') ? self::STATE_VALID : self::STATE_INCOMING;
        $bind = array(
            'TYPESTRUCT'	=>	$this->_typestruct,
            'SIGLE'	        =>	$this->_sigle,
            'STRUCTNAME'	=>	$this->_structname,
            'ADDRESS'       =>	$this->_address,
            'PAYSID'	    =>	$this->_paysid,
            'URL'	        =>	$this->_url,
            'VALID'         => $this->_valid ? $this->_valid : $defaultState,
            'LOCKED'        => $this->_locked
        );

        $chaineMd5 = "";
        foreach ( $bind as $col=>$val ) {
            $chaineMd5 .= $col.$val;
        }

        if (!empty ($this->_parents)) {
            foreach ($this->_parents as $parent) {
                $chaineMd5 .= $parent['struct']->getMd5() . $parent['code'];
            }
        }

        $this->setMd5(md5(strtolower($chaineMd5)));
        $bind['MD5'] = new Zend_Db_Expr('UNHEX("' . $this->getMd5() . '")');
        $bind['SDATE'] = Ccsd_Tools::str2date($this->_sdate, true);
        $bind['EDATE'] = Ccsd_Tools::str2date($this->_edate, true);
        $adapter = Zend_Db_Table_Abstract::getDefaultAdapter();
        try {
            if (! $this->getStructid() ) {
                // recherche si structure fusionnée
                if ($newStructid = Ccsd_Referentiels_Alias::existMd5($this->getMd5(), static::$core)) {
                    $this->setStructid($newStructid);
                    $this->load($newStructid);
                    Ccsd_Referentiels_Logs::log($newStructid, static::$core, Hal_Auth::getUid(), 'NOTHING', null);
                    return $newStructid;
                }
            }

            if (! $this->getStructid() ) {
                // Nouvelle structure
                try {
                    $adapter->insert($this->_table, $bind);
                    $this->setStructid($adapter->lastInsertId($this->_table));
                    //Log de l'insertion
                    $action_log = 'CREATED';
                } catch (Exception $e) {
                    if ($e->getCode() == '23000') {
                        // cas du duplicate key : essai de creation d'un doublon lors de la création
                        throw new Ccsd_Referentiels_Exception_InsertStructureException();
                    } else {

                        throw new Exception($e->getMessage(), $e->getCode(), $e->getPrevious());
                    }
                }

            } else {

                // Mise à jour
                $tempo = new self();
                $tempo->load($this->getStructid());
                $donneesPrecedentes = $tempo->toArray();
                try {
                    // La structure pouvant etre aliasee, l'ID n'est pas forcement dans la table
                    // Tempo est la structure cible de l'alias
                    // Est-on en train de sauvegarder une structure qui a ete aliase / detruite entre temps ?
                    $exist = (bool) $adapter->fetchOne(
                        $adapter->select()
                            ->from($this->_table, "STRUCTID")
                            ->where($this->_primary . ' = ' . $this->getStructid())
                    );

                    if ($exist) {
                        $adapter->update($this->_table, $bind,  $this->_primary . ' = ' . $this->getStructid());
                        $action_log = 'MODIFIED';
                    } else {
                        // On recree la structure avec le mem ID precemment supprimee ????
                        $bind['STRUCTID'] = $this->getStructid();
                        $adapter->insert($this->_table, $bind);
                        $action_log = 'NOTHING';
                    }

                } catch (Exception $e) {
                    if ($e->getCode() == '23000') {
                        // cas du duplicate key : essai de creation d'un doublon lors de la modification d'une structure
                        throw new Ccsd_Referentiels_Exception_UpdateStructureException();
                    } else {

                        throw new Exception($e->getMessage(), $e->getCode(), $e->getPrevious());
                    }
                }
            }

            //Enregistrement des identfiants exterieurs
            Zend_Db_Table_Abstract::getDefaultAdapter()->delete($this->_table_idext, 'STRUCTID = ' . $this->getStructid());
            foreach($this->_idExt as $serverid => $id) {
                Zend_Db_Table_Abstract::getDefaultAdapter()->insert($this->_table_idext, array(
                    'STRUCTID'  =>  $this->getStructid(),
                    'SERVERID'  =>  $serverid,
                    'ID'  =>  $id
                ));
            }
        } catch (Ccsd_Referentiels_Exception_InsertStructureException $e) {

            // l'enregistrement existait dejà on ne fait rien, je renvoie l'id de la structure identifiée
            $structid = $this->searchDoublon();
            $this->setStructid($structid);

            return $structid;
        } catch (Ccsd_Referentiels_Exception_UpdateStructureException $e) {
            // La modification de l'enregistrement entraine la possible création d'un doublon
            // On supprime l'enregistrement courant qu'on lie à l'enregistrement existant dans la base
            $structid = $this->searchDoublon();

            if ($structid != false) {
                $this->fusion($structid);
            }
            return $structid;
        }

        // La sauvegarde des données de la structure courante s'est bien passé normalement
        // et on est sorti si il y a eu remplacement ou qu'on a trouve un enregistrement identique

        // On sauvegarde les affiliations présentes (on sait que si le type de la structure est institution
        // $bind['TYPESTRUCT'] == $self::TYPE_INSTITUTION alors $this->_parents est vide

        /* Je supprime les anciennes affiliations presentes en base */
        $this->deleteAffiliations();

        if (!empty ($this->_parents)) {
            foreach ($this->_parents as $parent) {
                if ($this->getStructid() == $parent['struct']->getStructid()) continue;
                try {
                    Zend_Db_Table_Abstract::getDefaultAdapter()->insert($this->_table_ref, array (
                        'STRUCTID' => $this->getStructid(),
                        'PARENTID' => $parent['struct']->getStructid(),
                        'CODE'	   => $parent['code']
                    ));

                } catch (Exception $e) {
                    throw new Exception($e->getMessage(), $e->getCode(), $e->getPrevious());
                }
            }
        }
        //Enregistrement Ok et affiliation mise à jour, on indexe la nouvelle entrée
        if ($action_log == "CREATED") {
            Ccsd_Referentiels_Logs::log($this->getStructid(), static::$core, Hal_Auth::getUid(), $action_log, null);
            Ccsd_Search_Solr_Indexer::addToIndexQueue(array($this->getStructid()), SPACE_NAME, 'UPDATE', static::$core, 10);
            // On informe les référents des structures parentes de l'ajout
            $this->mailAdminStruct(
                Hal_Mail::TPL_REF_STRUCT_AJOUT, array(
                    'STRUCTNAME' => $this->getStructname(),
                    'STRUCTID' => $this->getStructid(),
                    'STRUCTURL' => $this->getStructUrl($this->getStructid())
                )
            );

        } else {
            //Je vérifie qu'il y a bien eu modification... je loggue et je demande la reindexation
            if ( strcmp(Zend_Json::encode($donneesPrecedentes) , Zend_Json::encode($this->toArray())) ) {
                Ccsd_Referentiels_Logs::log($this->getStructid(), static::$core, Hal_Auth::getUid(), $action_log, Zend_Json::encode(array ($donneesPrecedentes)));
                // On informe les référents structure de la modification (sauf celui qui est en train de faire la modif)
                $this->mailAdminStruct(
                    Hal_Mail::TPL_REF_STRUCT_MODIF, array(
                    'STRUCTNAME' => $this->getStructname(),
                    'STRUCTID' => $this->getStructid(),
                    'STRUCTURL' => $this->getStructUrl($this->getStructid()),
                ),
                    [Hal_Auth::getUid()]
                );
                Ccsd_Search_Solr_Indexer::addToIndexQueue(array($this->getStructid()), SPACE_NAME, 'UPDATE', static::$core, 10);
                Ccsd_Referentiels_Update::add(static::$core, $this->getStructid());
            }
        }
        return $this->getStructid();
    }

    public function deleteAffiliations() {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $nb = 0 ;
        $condition = $this->_primary . ' = ' . $this->getStructid();

        //Suppression des affiliations
        $nb = $db->delete($this->_table_ref, $condition);
        return $nb;
    }

    public function restore (array $data = array())
    {
        function array_filter_recursive($input, $primary)
        {
            foreach ($input as &$value) {
                if (array_key_exists('parents', $value)) {
                    $value = array_filter_recursive($value['parents'], $primary);
                }
            }

            return array_diff_key(array($primary => "", 'MD5' => ""), $input);
        }

        if (array_key_exists('parents', $data)) {
            $data['parents'] = array_filter_recursive($data['parents'], $this->_primary);
        }

        if (array_key_exists ($this->_primary, $data)) {
            unset ($data[$this->_primary]);
        }

        if (array_key_exists ('MD5', $data)) {
            unset ($data['MD5']);
        }

        $objet = new static (0, $data);

        /*
        Ccsd_Search_Solr_Indexer::addToIndexQueue(array($objet->getStructid()), SPACE_NAME, 'UPDATE', static::$core, 10);
        //On indique que la donnée a été modifiée, il faut réindexer les documents associés
        Ccsd_Referentiels_Update::add(static::$core, $objet->getStructid());
      */

        return $objet->save();
    }

    public function delete()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $nb = 0 ;
        $condition = $this->_primary . ' = ' . $this->getStructid();

        //Suppression des affiliations
        $db->delete($this->_table_ref, $condition);

        $data = $this->toArray();
        unset ($data['MD5']);

        //Log de suppression
        Ccsd_Referentiels_Logs::log($this->getStructid(), static::$core, Hal_Auth::getUid(), "DELETED", Zend_Json::encode(array ($data)));

        $nb = $db->delete($this->_table, $condition);
        if ($nb == 1) {
            Ccsd_Search_Solr_Indexer::addToIndexQueue(array($this->getStructid()), SPACE_NAME, 'DELETE', static::$core, 10);
        }

        // On informe les référents structure
        $this->mailAdminStruct(
            Hal_Mail::TPL_REF_STRUCT_SUPPR, array(
                'STRUCTNAME' => $this->_structname,
                'STRUCTID' => $this->getStructid(),
            )
        );

        return $nb;
    }

    public function __toString()
    {
        if (SPACE_NAME == 'AUREHAL') {
            $arg = [];
            if ($arg = func_get_args ()) {
                $arg = array_shift($arg);
            }

            return $this->toHtml($arg);
        } else {
            $str = $this->getStructname();
            if ($this->getSigle() != '') {
                $str =  $this->getSigle() . ' - ' . $str;
            }
            if ($this->hasParent()) {
                foreach($this->getParents() as $parent) {
                    $str .=  ' - ' . $parent['struct'];
                }
            }
            return $str;
        }
    }

    public function toHtml()
    {
        /** @var Zend_Controller_Action_Helper_ViewRenderer $viewRenderer */
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');

        $viewRenderer->view->parents = false;
        $viewRenderer->view->options = false;

        if ($arg = func_get_args ()) {
            if ($arg = array_shift($arg)) {
                if (array_key_exists('showParents', $arg)) {
                    $viewRenderer->view->parents = true;
                }
                if (array_key_exists('showOptions', $arg)) {
                    $viewRenderer->view->options = true;
                }
            }
        }

        //Ajout des variables pour la vue
        $viewRenderer->view->structure = $this;
        //Ajout du répertoire des script de vues (library)
        $viewRenderer->view->addScriptPath(__DIR__ . "/views/");
        //Récupération du script traité
        return (string)$viewRenderer->view->render("structure.phtml");
    }

    public function getParentsStructids($recursive = true)
    {
        $res = array();
        foreach ($this->_parents as $structure) {
            $res[] = $structure['struct']->getStructid();
            if ( $recursive ) {
                $res = array_merge($res, $structure['struct']->getParentsStructids($recursive));
            }
        }
        return $res;
    }

    public function setPaginatorAdapter($type = self::PAGINATOR_ADAPTER_SOLR, $options = array(), $filter = false)
    {

        $class  = self::PAGINATOR_ADAPTER_SOLR == $type ? 'Ccsd_Paginator_Adapter_Curl' : 'Zend_Paginator_Adapter_DbSelect';
        $method = self::PAGINATOR_ADAPTER_SOLR == $type ? '_createSolrQuery' : '_createBaseQuery';

        $valid = $filter ? array("VALID" => self::STATE_VALID, 'valid_s' => self::STATE_VALID) : array();
        if ($method == '_createSolrQuery' && count($valid)) {
            $valid['OLD'] = self::STATE_OLD;  //permet de remplacer une structure par une structure fermée
        }

        $query = $this->$method($options['critere'], $options['tri'], $options['filter'], $options['nbResultPerPage'], $valid, $options['category']);
        $this->_adapter = new $class($query, static::$core);
        return $this;
    }

    //GETTERS / SETTERS

    public function getCore ()
    {
        return self::$core;
    }

    public function getParentCount ()
    {
        return $this->_parentCount;
    }

    public function setParentCount ($newCount)
    {
        $this->_parentCount = $newCount;
        return $this;
    }

    public function getChildCount ()
    {
        return $this->_childCount;
    }

    public function setChildCount ($newCount)
    {
        $this->_childCount = $newCount;
        return $this;
    }

    public function getStructid ()
    {
        return $this->_structid;
    }

    public function setStructid ($id)
    {
        $this->_structid = intval(filter_var($id, FILTER_SANITIZE_NUMBER_INT));

        if ($this->_structid < 0) {
            throw new InvalidArgumentException('STRUCTID doit être >= 0.');
        }

        return $this;
    }

    public function setSigle ($sigle)
    {
        $this->_sigle = $sigle;
        return $this;
    }

    public function getSigle ()
    {
        return $this->_sigle;
    }

    public function setStructname ($name)
    {
        $this->_structname = $name;
        return $this;
    }

    public function getStructname ()
    {
        return $this->_structname;
    }

    public function getAddress ()
    {
        return $this->_address;
    }

    public function setAddress ($_address)
    {
        $this->_address = $_address;
        return $this;
    }

    public function getPaysid ()
    {
        return $this->_paysid;
    }

    public function setPaysid ($_paysid)
    {
        $this->_paysid = $_paysid;
        return $this;
    }

    public function getUrl ()
    {
        return $this->_url;
    }

    public function setUrl ($_url)
    {
        $this->_url = filter_var($_url, FILTER_SANITIZE_URL);
        if (Zend_Uri::check($_url) == false) {
            $this->_url = '';
        } else {
            $this->_url = $_url;
        }

        return $this;
    }

    public function getSdate ()
    {
        return $this->_sdate;
    }

    public function setSdate ($_sdate)
    {
        $this->_sdate = Ccsd_Tools::str2date($_sdate);
        return $this;
    }

    public function getEdate ()
    {
        return $this->_edate;
    }

    public function setEdate ($_edate)
    {
        $this->_edate = Ccsd_Tools::str2date($_edate);
        return $this;
    }

    public function getTypestruct ()
    {
        return $this->_typestruct;
    }

    public function setTypestruct ($_typestruct)
    {
        $this->_typestruct = $_typestruct;
        return $this;
    }

    public function getValid ()
    {
        return $this->_valid;
    }

    public function setValid ($_valid)
    {
        $this->_valid = $_valid;
        return $this;
    }

    public function setLocked ($_locked)
    {
        $this->_locked = $_locked;
        return $this;
    }

    public function getLocked ()
    {
        return $this->_locked;
    }

    public function setIdext ($_idExt)
    {
        $this->_idExt = $_idExt;
        return $this;
    }

    public function getIdext ()
    {
        return $this->_idExt;
    }

    public function getIdextLink ()
    {
        $res = [];
        if ($this->_idExt) {
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            $sql = $db->select()
                ->from('REF_SERVEREXT', array('SERVERID', 'NAME', 'URL'))
                ->where('SERVERID IN (?)', array_keys($this->_idExt));
            $tmp = $db->fetchAssoc($sql);
            foreach($this->_idExt as $serverid => $id) {
                $res[$tmp[$serverid]['NAME']] = ['id'  =>  $id, 'url'  => $tmp[$serverid]['URL'] . $id ];
            }
        }
        return $res;
    }

    public function getDatemodif ()
    {
        return $this->_datemodif;
    }

    public function setDatemodif ($_datemodif)
    {
        $this->_datemodif = $_datemodif;
        return $this;
    }

    public function getMd5 ()
    {
        return $this->_md5;
    }

    public function setMd5 ($_md5)
    {
        $this->_md5 = $_md5;
        return $this;
    }

    public function display($prefixUrl, $code = '', $showStructId = false)
    {
        $return = '<a href="' . $prefixUrl . 'search/index/q/*/structId_i/' . $this->getStructId() . '" target="_blank">';
        if ($this->getSigle() != '') {
            $return .= $this->getSigle() . ' - ';
        }
        $return .= $this->getStructname();
        $return .= '</a>';
        if ($code != '') {
            $return .= ' : ' . $code;
        }
        $address = Zend_Locale::getTranslation(strtoupper($this->getPaysid()), 'country', Zend_Registry::get('Zend_Locale'));
        if ($this->getAddress()) {
            $address = $this->getAddress() . ' - ' . $address;
        }

        if ($address != '') {
            $return .= '<small> (' . $address . ')</small>';
        }
        $return .= ' <label class="label label-primary">StructId : ' . $this->getStructid() . '</label>';

        if ($this->hasParent()) {
            $return .= '<ul>';
            foreach ($this->getParents() as $parent) {
                $return .= '<li>' . $parent['struct']->display($prefixUrl, $parent['code'], $showStructId) . '</li>';
            }
            $return .= '</ul>';
        }
        return $return;
    }

    /**
     * Indique si une structure est bien formée :
     * researchteam, department, laboratory, regrouplaboratory, institution, regroupinstitution
     * @return bool
     */
    public function isWellFormed()
    {
        if ($this->getStructid() != 0) {
            // An allready saved structure is considered as valid
            // To avoid, user selecting an allready existing form to be said: not valid!
            return true;
        }

        $typestruct = $this->getTypestruct();
        if ($typestruct == self::TYPE_REGROUPINSTITUTION) { // Regroupement d'Institutions
            //La structure ne doit pas avoir de structure supérieure
            if ( $this->getParentCount() != 0 ) {
                return false;
            }
        } else { // Institutions, Regroupement de Laboratoires, Laboratoire, Département, Equipe
            if ($this->getParentCount() == 0 && $typestruct != self::TYPE_INSTITUTION) {
                //La structure doit forcement avoir une structure supérieure
                return false;
            }
            /* Les type de structure sont bien ordonnes */
            foreach($this->getParents() as $parent) {
                /** @var Ccsd_Referentiels_Structure $pstruct */
                $pstruct = $parent['struct'];
                if (!$this -> compareTypeStruct($this -> getTypestruct(), $pstruct->getTypestruct())) {
                    return false;
                }
            }
        }
        if ( $this->getStructname() == '') {
            return false;
        }
        //On vérifie que les structures parentes sont également bien formées
        foreach($this->getParents() as $parent) {
            /** @var Ccsd_Referentiels_Structure $pstruct */
            $pstruct = $parent['struct'];
            if (! $pstruct->isWellFormed()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Indique si le type de la structure courante est compatible avec les autres structures
     * qui dépendent de cette structure
     */
    public function isValidTypeStruct($structid = 0, $typeStructure = null)
    {
        return $this->getNbInvalidStruct($structid, $typeStructure) == 0;
    }

    /**
     * Retourne le nombre de structures qui dépendent de la structure courante
     * et qui ne sont pas bien formées
     * @param int $structid
     * @param null $typeStructure
     * @return bool|string
     */
    public function getNbInvalidStruct($structid = 0, $typeStructure = null)
    {
        if ($structid == 0) {
            $structid = $this->getStructid();
        }

        if ($typeStructure == null) {
            $typeStructure = $this->getTypestruct();
        }

        if ($structid == 0) {
            return 0;
        }

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = $db->select()
            ->from(array('r' => $this->_table_ref), 'COUNT(*)')
            ->where('PARENTID = ?', $structid);


        if ($typeStructure != self::TYPE_RESEARCHTEAM) {
            $sql->join(array('s' => $this->_table), 'r.STRUCTID=s.STRUCTID', null);

            //Pour un département les structures associées ne peuvent etre que des équipes de recherche
            $typeStructAccepted = [self::TYPE_RESEARCHTEAM];

            if ($typeStructure == self::TYPE_LABORATORY) {
                //Pour un laboratoire les structures associées ne peuvent etre que des équipes de recherche et département
                $typeStructAccepted = [self::TYPE_RESEARCHTEAM, self::TYPE_DEPARTMENT];
            } else if ($typeStructure == self::TYPE_REGROUPLABORATORY) {
                //Pour un regroupement de laboratoire les structures associées ne peuvent etre que des équipes de recherche et département et laboratoire
                $typeStructAccepted = [self::TYPE_RESEARCHTEAM, self::TYPE_DEPARTMENT, self::TYPE_LABORATORY];
            } else if ($typeStructure == self::TYPE_INSTITUTION) {
                //Pour une institution les structures associées ne peuvent etre que des équipes de recherche, département, laboratoire et des regroupements de laboratoires
                $typeStructAccepted = [self::TYPE_RESEARCHTEAM, self::TYPE_DEPARTMENT, self::TYPE_LABORATORY, self::TYPE_REGROUPLABORATORY];
            } else if ($typeStructure == self::TYPE_REGROUPINSTITUTION) {
                //Pour une institution les structures associées ne peuvent etre que des équipes de recherche, département, laboratoire, des regroupements de laboratoires et des institutions
                $typeStructAccepted = [self::TYPE_RESEARCHTEAM, self::TYPE_DEPARTMENT, self::TYPE_LABORATORY, self::TYPE_REGROUPLABORATORY, self::TYPE_INSTITUTION];
            }

            $sql->where('s.TYPESTRUCT NOT IN (?)', $typeStructAccepted);

        } // else Equipe de recherche, aucune structure ne doit être associée

        return $db->fetchOne($sql);
    }

    /**
     * Indique si le type de la structure 1 est inférieure au type de la structure 2
     * @param $typStruct1
     * @param $typStruct2
     * @return bool
     */
    static public function compareTypeStruct($typStruct1, $typStruct2)
    {
        return self::$typeOrder[$typStruct1] < self::$typeOrder[$typStruct2];
    }

    /**
     * Renvoie les authorid liés à une structure
     * @param int|array $structid
     * @return array|bool
     */
    static public function getRelatedAuthorid($structid=0) {
        if ( $structid == 0 ) {
            return false;
        }
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        if ( is_array($structid) || is_numeric($structid) ) {
            $select = $db->select()
                ->distinct()
                ->from(array('REF_AUTHOR'), array('AUTHORID'));
            if ( is_array($structid) ) {
                $select->where('STRUCTID IN (?)', $structid);
            } else {
                $select->where('STRUCTID = ?', (int)$structid);
            }
            return ($db->fetchCol($select));
        } else {
            return false;
        }
    }

    /**
     * Renvoie les référents structure de la structure
     * @return array
     */
    public function getAdminStruct()
    {
        return $this->_adminStruct;
    }

    /**
     * Renseigne le champ des référents structure d'une structure
     * @param int|array $structid
     * @return bool
     */
    public function setAdminStruct()
    {
        $this->_adminStruct = array();
        if ( !$this->getStructid()) {
            return false;
        }

        $lStructid = $this->getStructid();

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $select = $db->select()
            ->distinct()
            ->from(array('UR' => 'USER_RIGHT'), array( 'UID' ))
            ->where('VALUE  LIKE ?', (int)$lStructid);
        $res=$db->fetchCol($select);

        foreach ($res as $uid) {
            $ccsdUser = new Ccsd_User_Models_User();
            $ccsdUserMapper = new Ccsd_User_Models_UserMapper();
            $ccsdUserMapper->find($uid, $ccsdUser);
            $this->_adminStruct[] = $ccsdUser;
        }
        return true;
    }

    /**
     * retourne des structures parentes et filles de la structure courante
     * @return array
     */
    protected function getStructHierarchy() {
        $lStructid = array();

        // ajout de l'id de la structure
        $lStructid[] = (int)$this->getStructid();

        // ajout des id des structures filles
        $tabStruct = self::getAllChildsId((int)$this->getStructid());
        foreach ($tabStruct as $struct) {
            $lStructid[] = (int)$struct;
        }

        // ajout des id des structures parentes
        $tabStruct = $this->getAllParents();
        foreach ($tabStruct as $struct) {
            /** @var Ccsd_Referentiels_Structure $oStruct */
            $oStruct = $struct['struct'];
            $lStructid[] = $oStruct->getStructid();
        }
        return $lStructid;
    }

    /**
     * Envoi un mail aux référents structure d'une structure
     * @param string $tplName template du mail
     * @param array $tags valeurs pour individualiser le mail
     * @param array $filter UIDs à filtrer pour l'envoie du mail
     */
    protected function mailAdminStruct($tplName, $tags, $filter = [])
    {
        // recherche des structures parentes et filles de la structure courante
        $lStructid = $this->getStructHierarchy();

        $tags['APPLI'] = "AuréHAL";
        /** @var  Hal_User[] $users */
        $users =[];
        // construction de la liste des utilisateurs qui recevront un mail
        foreach ($lStructid as $structid) {
            $struct = new Ccsd_Referentiels_Structure();
            $struct->setStructid($structid);
            // recherche des reférents de la structure
            $struct->setAdminStruct();
            //pour chaque référent
            foreach ($struct->getAdminStruct() as $refStruct) {
                // recherche de l'utilisateur
                $uid = $refStruct->getUid();
                if ($uid && (is_numeric($uid))) {
                    if (array_key_exists($uid, $users) || (in_array($uid, $filter))) {
                        // On peut filtrer certains UID si l'option est positionnée
                        continue;
                    } else {
                        $user = new Hal_User();
                        $user->find((int)$refStruct->getUid());
                        $users[$uid] = $user;
                    }
                }
            }
        }
        $currentUserId = Hal_Auth::getUid();
        foreach ($users as $user) {
            // envoi du mail
            if ($currentUserId == $user -> getUid()) {
                // Pas d'envoie a celui qui fait la modification
                continue;
            }
            $mail = new Hal_Mail();
            $mail->prepare($user, $tplName, $tags);
            $mail->writeMail();
        }
    }

    /**
     * Renseigne le champ des référents structure d'une structure
     * @param int $currentId
     */
    public function fusion($currentId)
    {
        //Log du remplacement
        $oldId = $this->getStructid();
        Ccsd_Referentiels_Logs::log($oldId, static::$core, Hal_Auth::getUid(), "REPLACED_BY", Zend_Json::encode (array ($currentId)));
        Ccsd_Referentiels_Logs::log($currentId, static::$core, Hal_Auth::getUid(), "REPLACE", Zend_Json::encode (array ($oldId)));
        // Dans la suppression on met dans les logs et on reindexe
        $this->delete();

        //On indique que les documents associés à l'ancienne entrée doivent être associés à la nouvelle
        Ccsd_Referentiels_Update::add(static::$core, $currentId, $oldId);

        // On informe les référents structure de la fusion
        $newStruct = new Ccsd_Referentiels_Structure($currentId);
        $this->mailAdminStruct(
            Hal_Mail::TPL_REF_STRUCT_FUSION, array(
                'STRUCTNAME' => $this->_structname,
                'STRUCTID' => $oldId,
                'NEWSTRUCTID' => $currentId,
                'NEWSTRUCTNAME' => $newStruct->getStructname(),
                'NEWSTRUCTURL' => $newStruct->getStructUrl($currentId)
            )
        );
    }

    /**
     * Retourne l'URL de lecture de l'objet
     *
     * @param int $structid : id de la structure
     *
     * @return string
     */
    public function getStructUrl($structid=0)
    {
        if (defined(AUREHAL_URL)) {
            if (is_int($structid) && ($structid != 0)) {
                return AUREHAL_URL . "/structure/read/id/" . $structid;
            } else {
                return AUREHAL_URL . "/structure/index";
            }
        } else {
            return "";
        }
    }

    /**
     * Indique si l'utilisateur peut locker une structure
     * Condition prealable au lock:
     *    1) Un admin de structure est positionne
     *    2) La structure est valide ou bien fermee
     * Droit:
     *    1) Les admin de Hal
     *    2) Le referents des institutions parentes de la structures
     * @return bool
     */
    public function canLockStruct()
    {
        if (Hal_Auth::isHALAdministrator()) {
            return true;
        }
        if ($this->getAdminStruct() && $this->isValid()) {
            //Référent Struct + Structure Valid ou Fermé
            $structAuth    = Hal_Auth::getStructId(); // StructIds du référent structure
            $structInstitu = $this->getAllParents();  // Tous les parents de la structure
            $structInstitu[]['struct'] = $this;       // Ajout de la structure
            foreach ($structInstitu as $si) {
                /** @var Ccsd_Referentiels_Structure $struct */
                $struct = $si['struct'];
                if (in_array($struct->getStructid(), $structAuth)){
                    return true;
                }
            }

        }
        return false;
    }

    /**
     * @return string
     */
    public function getURI()
    {
        return AUREHAL_URL . "/structure/{$this->getStructid()}";
    }
}

