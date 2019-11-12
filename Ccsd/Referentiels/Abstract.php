<?php


abstract class Ccsd_Referentiels_Abstract {

    /**
     * Chemin vers la définition du formulaire
     * @var string
     */
    const INI = '';

    /**
     * Connected is true if we have a reference to a live
     * Zend_Db_Table_Abstract object.
     * This is false after the Rowset has been deserialized.
     *
     * @var boolean
     */
    protected $_connected = true;

    /**
     * CORE SOLR du référentiel
     * @var $core string
     */
    static public $core = '';

    /**
     * Nom de la méta dans la table DOC_METADATA
     */
    const METANAME = '';

    /**
     * Table du référentiel
     * @var string
     */
    static public $_table = '';

    /**
     * Nom de la Clé primaire de la table associee a la classe fille
     * @var string
     */
    protected $_primary = '';

    /**
     * Données d'un élément du référentiel
     * @var array
     */
    protected $_data = [];

    /**
     * Données d'un élément du référentiel
     * @var array
     */
    protected $_prev_md5 = '';

    /**
     * Données d'un élément du référentiel
     * @var array
     */
    protected $_prev_data = [];

    /**
     * Formulaire pour une entrée du référentiel
     * @var Ccsd_Form
     */
    protected $_form = null;

    /**
     *  Champs acceptés par le formulaire MODIFY - à définir dans les classes filles
     * @var array
     */
    protected $_acceptedFormValues = [];

    /**
     *  Champs modifiables par le formulaire MODIFY - à définir dans les classes filles
     * @var array
     */
    protected $_changeableFormValues = null;

    /**
     * Elements présents pour le formulaire court (à partir de HAL)
     * @var array
     */
    protected $_smallFormElements = [];

    /**
     * Elements requis pour le formulaire
     * @var array
     */
    protected $_mandatoryFormElements = [];

    /**
     * Elements requis pour le formulaire court (à partir de HAL)
     * @var array
     */
    protected $_smallMandatoryFormElements = [];

    /**
     * Element de formulaire de type date
     */
    protected $_dateFormElements = [];

    /* @var $_adapter Zend_Paginator_Adapter_Interface */
    protected $_adapter = null;
    static public $_optionsTri = [];
    static protected $_solRsort = [];

    const NB_PAGES_IN_RANGE = 10;
    const PAGINATOR_ADAPTER_BASE = 'BASE';
    const PAGINATOR_ADAPTER_SOLR = 'SOLR';

    // Status possibles d'une donnée du référentiel

    // Forme courante
    const STATE_VALID = 'VALID';
    // Ancienne forme valide
    const STATE_OLD = 'OLD';
    // Forme non valide
    const STATE_INCOMING = 'INCOMING';

    public function __construct($id = 0, $data = array(), $recursive = true) {
        if ($id != 0) {
            $this->load($id);
            $this->_prev_data = $this->_data;
            $this->_prev_md5 = $this->getMd5();
        } else {
            $this->set($data);
        }
    }

    /**
     * Chargement à partir de la base
     * @param      $id int identifiant de l'entrée du référentiel
     * @param bool $recursive
     * @return $this|null
     */
    public function load($id, $recursive = true) {
        // recherche de l'id du nouvel alias éventuel
        //$objet = new $this->_class();
        $newid = Ccsd_Referentiels_Alias::getAliasNewId($id, static::$core);
        if ($newid != $id && $newid != 0) {
            $id = $newid;
        }

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        if (static::$_table == Ccsd_Referentiels_Author::$_table){
            $sql = $db->select()->from(array('ra' => static::$_table))
                ->joinLeft(array(
                    'rs' => 'REF_STRUCTURE'
                ), 'ra.STRUCTID = rs.STRUCTID', array(
                    'ORGANISM' => 'rs.STRUCTNAME'
                ))->where($this->_primary . ' = ?', (int)$id);
        } else {
            $sql = $db->select()->from(static::$_table)
                ->where($this->_primary . ' = ?', (int)$id);
        }

        $row = $db->fetchRow($sql);

        if ($row) {
            $this->_data = $row;
            return $this;
        }

        return null;
    }

    /**
     * Initialisation à partir d'un tableau associatif
     * @param array $data
     */
    public function set($data, $recursive = true) {
        if (!empty($data) && is_array($data)) {
            $this->_prev_md5 = $this->getMd5();
            $this->_prev_data = $this->_data;
            $this->_data = $data;
            return $this;
        }
        return null;
    }

    public function setData($meta, $value) {
        $this->_data[$meta] = $value;
    }

    /**
     * Valide les données d'un référentiel
     * @param mixed $value : données du référentiel à valider
     * @return boolean
     */
    public function isValid($value) {
        if (is_array($value)) {
            return (new $this)->getForm()->isValid($value);
        } else if (is_object($value)) {
            $arrayValues = $value->toArray();
            if ($value instanceof Ccsd_Referentiels_Journal || $value instanceof Ccsd_Referentiels_Anrproject || $value instanceof Ccsd_Referentiels_Europeanproject) {
                if (isset($arrayValues[$value->_primary])) {
                    $id = (int) $arrayValues[$value->_primary];
                } else {
                    $id = 0;
                }
                $form = $value->getForm(false, true, $id);
            } else {
                $form = $value->getForm();
            }
            return $form->isValid($arrayValues);
        } else {
            return (new $this)->load($value);
        }
    }

    /**
     * Transformation en tableau
     * @return array
     */
    public function toArray() {
        return $this->_data;
    }

    /**
     * On filtre les valeurs reçues par le formulaire
     *
     * @param $newValues : valeurs récupérées dans le formulaire
     * @param $changeableValues : valeurs changeables par le formulaire (toutes par défaut)
     *
     * @return array : valeurs filtrées enregistrable dans l'objet
     */
    public function getFilteredData($newValues, $changeableValues = null)
    {
        // Valeurs de l'objet que l'on a le droit de changer
        $acceptedValues = $this->getAcceptedValues();

        // S'il n'a pas été défini, on conserve le comportement actuel : tout est modifié
        if(empty($acceptedValues))
            return $newValues;

        $newData = $this->getData();

        foreach ($acceptedValues as $k) {
            // Lorsqu'il n'y pas de filtre, on garde tout (pour ne pas modifier le comportement par défaut actuel)
            // Pour les valeurs changeables par le formulaire, on les met à null, s'il n'y a pas de valeurs
            if (!$changeableValues || in_array($k, $changeableValues)) {
                if (isset($newValues[$k]))
                    $newData[$k] = $newValues[$k];
                else
                    $newData[$k] = null;
            }
        }

        return $newData;
    }

    /**
     * Récupération du formulaire
     * @param bool $extended formulaire complet
     * @param bool $populate
     * @return Ccsd_Form
     */
    public function getForm($extended = false, $populate = true, $id = 0) { // $id used in subclasses
        $this->initForm();

        $this->_form->addDecorator('FormRequired', array('class' => 'col-md-7 col-md-offset-3 ccsd_form_required'));

        foreach ($this->_form->getElements() as $element) {
            /** @var Zend_Form_Element $element */
            if ($extended) {
                if (in_array($element->getName(), $this->_mandatoryFormElements)) {
                    $this->_form->getElement($element->getName())->setRequired(true);
                }
            } else {
                if (!in_array($element->getName(), $this->_smallFormElements)) {
                    $this->_form->removeElement($element->getName());
                }
                if (in_array($element->getName(), $this->_smallMandatoryFormElements)) {
                    $this->_form->getElement($element->getName())->setRequired(true);
                }
            }
        }

        $this->postInitForm();

        if ($populate) {
            $this->populate();
        }

        return $this->_form;
    }

    /**
     * Initialisation du formulaire
     */
    public function initForm() {
        $config = new Zend_Config_Ini(static::INI);
        $this->_form = new Ccsd_Form();
        $this->_form->setConfig($config);
    }

    public function populate() {
        $this->_form->populate($this->_data);
    }

    public function postInitForm() {

    }

    /**
     * Nom de la clé primaire de la table du référentiel
     * @return string
     */
    public function getPK() {
        return $this->_primary;
    }

    /**
     * Nom de la table du référentiel
     * @return string
     */
    public function getTable() {
        return $this->_table;
    }

    /**
     * Retourne l'adaptateur de pagination
     * @return Zend_Paginator_Adapter_Interface
     */
    public function getPaginatorAdapter() {
        return $this->_adapter;
    }

    /**
     * Enregistre l'adaptateur de pagination
     * @param string $type
     * @param string $orderby
     * @param boolean $filter
     * @return Ccsd_Referentiels_Abstract
     */
    public function setPaginatorAdapter($type = self::PAGINATOR_ADAPTER_SOLR, $options = array(), $filter = false) {
        $class = self::PAGINATOR_ADAPTER_SOLR == $type ? 'Ccsd_Paginator_Adapter_Curl' : 'Zend_Paginator_Adapter_DbSelect';
        $method = self::PAGINATOR_ADAPTER_SOLR == $type ? '_createSolrQuery' : '_createBaseQuery';
        $query = $this->$method($options['critere'], $options['tri'], $options['filter'], $options['nbResultPerPage'], $filter ? array("VALID" => 'VALID', 'valid_s' => 'VALID') : array(), $options);
        $this->_adapter = new $class($query, static::$core);
        return $this;
    }

    /**
     * Crée la requête pour la pagination "Base"
     * @param string $critere
     * @param string $orderby
     * @param int $nbResultPerPage
     * @param array $filter
     */
    abstract protected function _createBaseQuery($critere, $orderby, $filter, $nbResultPerPage, $valid);

    /**
     * Crée la requête pour la pagination "SolR"
     * @param string $critere
     * @param string $orderby
     * @param array $filter
     * @param int $nbResultPerPage
     * @paran bool $valid
     * @todo: qd passage a php > 5.6 : utiliser le ...$otherArgs pour declarer les arguments variables supplementaire
     */
    protected function _createSolrQuery($critere, $orderby, $filter, $nbResultPerPage, $valid) {
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

        if ($valid) {
            if (is_array($valid) && isset($valid['OLD'])) {
                $query .= "&fq=NOT(valid_s:INCOMING)";
            } else {
                $query .= "&fq=valid_s:VALID";
            }
        }

        $query .= $this->custom_filter_solR(array_slice(func_get_args(), (new ReflectionMethod(get_class($this), "_createSolrQuery"))->getNumberOfParameters()));

        return $query;
    }

    /**
     * Enregistrement en base
     * @param $forceUpdate :
     * @param bool : permet de forcer la fusion de forme auteur lorsqu'une forme est modifiée depuis le profil de l'utilisateur et devient identique à une existante
     * @return int
     * @throws Zend_Db_Adapter_Exception
     * @throws Exception
     */
    public function save($forceUpdate = false, $acceptDedoublonnage = false) {
        // Beware: The MD5 will be erased after save!  Not sure it is good...
        $bind = array();
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        if (SPACE_NAME != 'AUREHAL' && !$forceUpdate) {
            //Pas de modifications d'entrée du référentiel à partir de HAL
            $this->{$this->_primary} = 0;
        }

        // Formatage de la date
        foreach ($this->_data as $cle => $valeur) {
            if (in_array($cle, $this->_dateFormElements)) {
                $valeur = Ccsd_Tools::str2date($valeur, true);

                // Rustine : La date ne doit jamais être vide. ça devrait être au niveau de str2date !
                if ($valeur == '') {
                    $valeur = null;
                }
            }
            $bind[$cle] = $valeur;
        }

        unset($bind['MD5']);

        if ($this->{$this->_primary} == 0) {
            //Enregistrement d'une nouvelle entrée dans le référentiel
            unset($bind[$this->_primary]);
            try {
                //On essaie d'insérer en base
                $db->insert(static::$_table, $bind);

                $this->{$this->_primary} = $db->lastInsertId(static::$_table);
                //Enregistrement Ok, on indexe la nouvelle entrée
                Ccsd_Search_Solr_Indexer::addToIndexQueue(array($this->{$this->_primary}), SPACE_NAME, Ccsd_Search_Solr_Indexer::O_UPDATE, static::$core, 10);
                //Log de l'insertion
                Ccsd_Referentiels_Logs::log($this->{$this->_primary}, static::$core, Hal_Auth::getUid(), "CREATED", null);
            } catch (Zend_Db_Statement_Exception $e) {
                if ($e->getCode() == 23000) { // Duplicate entry
                    $this->{$this->_primary} = $this->searchDoublon();

                } else {
                    Ccsd_Tools::panicMsg(__DIR__, __LINE__, 'Enregistrement dans le référentiel ' . static::$_table . ' de la donnée ' . serialize($bind) . ' a échoué !');
                }
            }
            return $this->{$this->_primary};
        } else {
            //Modification d'un entrée du référentiel
            try {
                //Si nécessaire : modification, réindexation de l'entrée, répercution sur les documents
                if (array_diff_assoc($this->_prev_data, $this->_data)) {
                    $db->update(static::$_table, $bind, $this->_primary . ' = ' . $this->{$this->_primary});
                    Ccsd_Referentiels_Logs::log($this->{$this->_primary}, static::$core, Hal_Auth::getUid(), "MODIFIED", Zend_Json::encode(array($this->_prev_data)));
                    Ccsd_Search_Solr_Indexer::addToIndexQueue(array($this->{$this->_primary}), SPACE_NAME, Ccsd_Search_Solr_Indexer::O_UPDATE, static::$core, 10);
                    Ccsd_Referentiels_Update::add(static::$core, $this->{$this->_primary});
                }
                return $this->{$this->_primary};
            } catch (Zend_Db_Statement_Exception $e) {
                //Duplicate entry on récupère l'entrée déjà existante on peut rajouter le test $e->getCode() == 23000
                //Cas de la tentative de création de doublons
                if ($e->getCode() == 23000) {
                    $oldId = $this->{$this->_primary};
                    $this->{$this->_primary} = $this->searchDoublon();

                    if ($acceptDedoublonnage && $this->{$this->_primary} !== false) {
                        //On a trouvé l'entrée en double
                        //Log du remplacement
                        Ccsd_Referentiels_Logs::log($oldId, static::$core, Hal_Auth::getUid(), "REPLACED_BY", Zend_Json::encode (array ($this->{$this->_primary})));
                        Ccsd_Referentiels_Logs::log($this->{$this->_primary}, static::$core, Hal_Auth::getUid(), "REPLACE", Zend_Json::encode (array ($oldId)));

                        //On supprime le doublon
                        $db->delete(static::$_table, $this->_primary . ' = ' . $oldId);

                        // On ajoute l'id supprimé dans la table des alias
                        try {
                            Ccsd_Referentiels_Alias::add($this->{$this->_primary}, static::$core, $oldId);
                        } catch (Zend_Db_Adapter_Exception $e) {
                            echo "erreur de requête : ".$e->getMessage();
                        }

                        //On indique à solr que l'entrée a été supprimée
                        Ccsd_Search_Solr_Indexer::addToIndexQueue(array($oldId), SPACE_NAME, 'DELETE', static::$core, 10);

                        //On indique que les documents associés à l'ancienne entrée doivent être associés à la nouvelle
                        Ccsd_Referentiels_Update::add(static::$core, $this->{$this->_primary}, $oldId);
                    }

                    return $this->{$this->_primary};
                } else {
                    throw new Exception($e->getMessage(), $e->getCode(), $e->getPrevious());
                }
            }
        }
    }

    /**
     * @param $options
     * @return string
     */
    protected function custom_filter_solR($options) {
        return "";
    }

    /**
     * @param array $data
     * @return int
     * @throws Exception
     * @throws Zend_Db_Adapter_Exception
     */
    public function restore(array $data = array()) {
        if (array_key_exists($this->_primary, $data)) {
            unset($data[$this->_primary]);
        }

        if (array_key_exists('MD5', $data)) {
            unset($data['MD5']);
        }

        $objet = new static(0, $data);

        $objet->save();

        Ccsd_Search_Solr_Indexer::addToIndexQueue(array($objet->{$this->_primary}), SPACE_NAME, 'UPDATE', static::$core, 10);
        //On indique que la donnée a été modifiée, il faut réindexer les documents associés
        Ccsd_Referentiels_Update::add(static::$core, $objet->{$this->_primary});

        return $objet->{$this->_primary};
    }

    /**
     * Recherche un doublon en base via le champ md5
     * @return bool|int
     * @throws Zend_Mail_Exception
     */
    protected function searchDoublon() {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = $db->select()->from(static::$_table, $this->_primary);
        $sql->where('MD5' . ' = ?', new Zend_Db_Expr('UNHEX("' . $this->getMd5() . '")'));
        $sql->where($this->_primary . ' != ?', $this->{$this->_primary});
        $primaryId = $db->fetchOne($sql);

        if ($primaryId !== false) { //on a trouvé une entrée
            return $primaryId;
        } else { //c'est bizarre
            if(defined('APPLICATION_ENV') && APPLICATION_ENV == 'production') {
                $mail = new Zend_Mail("UTF-8");
                $text = "SEARCH Doublon error : pas d'entrée trouvée pour :";
                $text .= '<br /><br />Identifiant: ' . $this->_primary;
                $text .= '<br /><br />MD5: ' . $this->getMd5();
                // On ne sait pas pourquoi, Il n'arrive pas à envoyer de dump dans le corps du mail (ça fait une boucle infinie)
                $mail->setBodyHtml($text);
                $mail->setFrom('ccsd-tech@ccsd.cnrs.fr');
                $mail->addTo('ccsd-tech@ccsd.cnrs.fr');
                $mail->setSubject('[' . SPACE_NAME . '] Search Doublon Error');
                $mail->send();
            }
        }
        return false;
    }

    /**
     * @return int
     */
    public function delete() {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $condition = $this->_primary . ' = ' . $this->{$this->_primary};

        $data = $this->toArray();
        unset($data['MD5']);

        //Log de suppression
        Ccsd_Referentiels_Logs::log($this->{$this->_primary}, static::$core, Hal_Auth::getUid(), "DELETED", Zend_Json::encode(array($data)));

        $nb = $db->delete(static::$_table, $condition);
        if ($nb == 1) {
            Ccsd_Search_Solr_Indexer::addToIndexQueue(array($this->{$this->_primary}), SPACE_NAME, 'DELETE', static::$core, 10);
        }
        return $nb;
    }

    /**
     * Recherche dans le référentiel
     * @param $q
     * @param int $nbResultats
     * @return array
     */
    public static function search($q, $nbResultats = 100) {
        try {
            $queryString = "fl=docid,label_html&df=text_autocomplete&q=" . urlencode($q) . "&omitHeader=true&rows=" . $nbResultats . "&wt=phps&sort=" . urlencode('valid_s desc,score desc,label_s asc');
            $queryString = rtrim($queryString, ",");
            $d = Ccsd_Tools::solrCurl($queryString, static::$core);
            $d = unserialize($d);

            if (array_key_exists('response', $d)) {
                return $d['response']['docs'];
            } else {
                return [];
            }
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Renvoie le nombre de dépôts liés à un élement du référentiel
     * @param int|array
     * @return int
     */

    static public function getCountOfRelatedDocid($id = 0) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        if (is_array($id) || is_numeric($id)) {
            $select = $db->select()
                    ->from('DOC_METADATA', array(new Zend_Db_Expr('COUNT(*)')))
                    ->where('METANAME = ?', static::METANAME);
            if (is_array($id)) {
                $select->where('METAVALUE IN (?)', $id);
            } else {
                $select->where('METAVALUE = ?', (int) $id);
            }
            return ($db->fetchOne($select));
        } else {
            return 0;
        }
    }

    /**
     * Renvoie les docid des dépôts liés à un élement du référentiel
     * @param int|array
     * @return array
     */

    static public function getRelatedDocid($id = 0) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        if (is_array($id) || is_numeric($id)) {
            $select = $db->select()
                    ->distinct()
                    ->from('DOC_METADATA', array('DOCID'))
                    ->where('METANAME = ?', static::METANAME);
            if (is_array($id)) {
                $select->where('METAVALUE IN (?)', $id);
            } else {
                $select->where('METAVALUE = ?', (int) $id);
            }
            return ($db->fetchCol($select));
        } else {
            return array();
        }
    }

    abstract public function getMd5();

    /**
     * Récupération des infos sur l'élément du référentiel
     * @param string $property
     * @return mixed
     */
    public function __get($property) {
        if (array_key_exists($property, $this->_data)) {
            return Ccsd_Tools::ifsetor($this->_data[$property]);
        }

        return false;
    }

    /**
     * Pour les proprietes stockee dans le champs _data,
     * Il faut le __set correspondant.
     * L'utilisation de _acceptedFormValues est abusive, cette variable ne contient pas l'ensemble des proprietes
     * @param $property
     * @param $value
     */
    /*
     * NE PAS AJOUTER LES PROPRIETES MANQUANTES A _acceptedFormValues
     * Changer de variable et la definir la ou c'est necessaire (Dans les differentes Meta)
     */
    public function __set($property, $value) {
        if (in_array($property, $this ->_acceptedFormValues)) {
            $this->_data[$property] = $value;
        } else {
            $this -> $property = $value;
        }
    }

    /**
     * @param $property
     * @return false
     */
    public function __unset($property) {
        if (in_array($property, $this ->_acceptedFormValues)) {
            unset($this->_data[$property]);
        }  
        return false;
    }
    /**
     * Récupération de l'ensemble des données de l'objet
     * @return array
     */
    
    public function getData() 
    {
        return $this->_data;
    }

    /**
     * == filter _data... Je ne sais pas quelle est la meilleure façon de faire.
     * @param array
     * @return array
     */
    public function getDataSubset(array $subset) {
        $res = [];
        foreach ($subset as $prop) {
            if (!is_null($this->__get($prop)) && !empty($this->__get($prop))) {
                $res[$prop] = $this->__get($prop);
            }
        }
        return $res;
    }

    /**
     * @return array
     */
    public function getAcceptedValues() 
    {
        return $this->_acceptedFormValues;
    }

    /**
     * @return array
     */
    public function getChangeableValues()
    {
        return $this->_changeableFormValues;
    }

    /**
     * recherche d'un élément du référentiel en base
     * @param int Id
     * @return int nb enregistrements trouvés
     */
    public function exist($id) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = $db->select()->from(static::$_table, 'COUNT(*) AS NB')
                ->where($this->_primary . ' = ?', $id);
        return $db->fetchOne($sql);
    }


    /**
     * Retourne l'ID d'une entrée de référentiel
     * @return string
     */
    public function getId()
    {
        return $this->{$this->_primary};
    }

    /**
     * @param int $from
     * @return array
     */
    public function getIds($from = 0)
    {
        $sql = Zend_Db_Table_Abstract::getDefaultAdapter()->select()->from(static::$_table, $this->_primary)->order($this->_primary . ' ASC');
        if ($from) {
            $sql->where($this->_primary . ' > ?', (int)$from );
        }
        return Zend_Db_Table_Abstract::getDefaultAdapter()->fetchCol($sql);
    }

    /**
     * Retourne tous les docids d'un référentiel par la base de données
     * @param int $count quantité à retourner
     * @param int $offset offset
     * @return array tableau de docid
     */
    public function getDocidsByDb($count = 10, $offset = 0) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = $db->select()->from(static::$_table, ['docid' => $this->_primary])
                ->where($this->_primary . ' > ?', (int) $offset)
                ->order($this->_primary . ' ASC')
                ->limit($count);
        return $db->fetchAll($sql);
    }

    /**
     * Retourne tous les docids d'un référentiel par solr
     * @param int $count quantité à retourner
     * @param string $cursorMark curseur de solr
     * @return array tableau de docid
     * @throws Exception
     */
    public function getDocidsBySolrCursorMark($count = 10, $cursorMark = '*') {
        $queryParams = ['wt' => 'phps', 'q' => '*:*', 'fl' => 'docid', 'sort' => 'docid asc', 'rows' => $count, 'cursorMark' => $cursorMark, 'omitHeader' => 'true'];
        $solrQuery = http_build_query($queryParams);
        $solrResult = Ccsd_Tools::solrCurl($solrQuery, static::$core, 'select', 30);
        return unserialize($solrResult);
    }

    /**
     * Cherche un tableau de docid dans la bdd
     * @param array $docids tableau de docid
     * @return int[]  tableau de docid trouvés
     */
    public function checkIfDocidsExistInDb(array $docids) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = $db->select()->from(static::$_table, ['docid' => $this->_primary])
                ->where($this->_primary . ' IN (' . implode(',', $docids) . ')')
                ->order($this->_primary . ' ASC');
        return $db->fetchAll($sql);
    }

    /**
     * Cherche un tableau de docid dans solr
     * @param array $docids tableau de docid
     * @param int $count nombre de résultats max à retourner
     * @return int[] tableau de docid
     * @throws Exception
     */
    public function checkIfDocidsExistInSolr(array $docids, $count = 1024) {
        $query = 'docid:(' . implode(' OR ', $docids) . ')';
        $queryParams = ['wt' => 'phps', 'q' => $query, 'fl' => 'docid', 'rows' => $count, 'cache' => 'false', 'omitHeader' => 'true'];
        $solrQuery = http_build_query($queryParams);
        $solrResult = Ccsd_Tools::solrCurl($solrQuery, static::$core, 'select', 30);
        return unserialize($solrResult);
    }

    /**
     * Compte le nombre d'entrées d'un référentiel
     * @return int
     */
    public function countDbEntries() {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = $db->select()->from(static::$_table, 'COUNT(*) AS NB');
        return (int) $db->fetchOne($sql);
    }

    /**
     * Compte le nombre d'entrées d'un référentiel par solr
     * @return int
     * @throws Exception
     */
    public function countSolrEntries() {
        $queryParams = ['wt' => 'phps', 'q' => '*:*', 'rows' => 0, 'cache' => 'false', 'omitHeader' => 'true'];
        $solrQuery = http_build_query($queryParams);
        $solrResult = Ccsd_Tools::solrCurl($solrQuery, static::$core, 'select', 30);
        return (int) unserialize($solrResult)['response']['numFound'];
    }

    /**
     * Retourne le core solr du referentiel
     * @return string
     */
    public function getCore() {
        return static::$core;
    }

    /**
     * Ajoute un attribut à une balise XML
     * @param string $ref
     * @param string $tag
     * @param string $key
     * @param mixed $value
     * @return string // xml
     */
    public function addAttributeXML($ref, $tag, $key, $value){
        $xml = new Ccsd_DOMDocument('1.0', 'utf-8');
        $xml->loadXML($ref);
        /** @var DOMElement $root */
        $root = $xml->getElementsByTagName($tag)[0];
        $root->setAttribute($key, $value);
        return $xml->saveXML($xml->documentElement) . PHP_EOL;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->_data);
    }
}
