<?php

/**
 * Page générale d'un site
 * @author Yannick Barborini
 *
 */
abstract class Ccsd_Website_Navigation_Page
{
    protected $_table   =   'WEBSITE_NAVIGATION';
    /**
     * Identifiant de la page
     * @var int
     */
    protected $_pageId  =   0;
    /**
     * Identifiant de la page parente (0 par défaut)
     * @var int
     */
    protected $_pageParentId    =   0;
    /**
     * Tableau des langues dispo de la page
     * @var array
     */
    protected $_languages  =   array();
    
    /**
     * Tableau des labels de la page
     * @var array
     */
    protected $_labels  =   array();
    
    /**
     * Page présente plusieurs fois pour un site
     * @var boolean
     */
    protected $_multiple = false;
    /**
     * @var int $_sid
     */
    protected $_sid = 0;
    /**
     * controller
     * @var string
     */
    protected $_controller = '';
    /**
     * @var Ccsd_Website_Navigation
     */
    protected $nav = null;
    /**
     * Action
     * @var string
     */
    protected $_action = '';
    /**
     * array
     */
    protected $_globalFields = [
        'languages' => 'setLanguages',
        'pageid'    => 'setPageId',
        'labels'    => 'setLabels',
        'parentid'  => 'setPageParentId',
        'sid'       => 'setSid',
        ];
    /**
     * To define in subclass to add fields to global one
     * array
     */
    protected $_localFields = [];
    /**
     * Formulaire de paramétrage de la page
     * @var Zend_Form
     */
    protected $_form = null;
    
    /**
     * Initialisation de la page
     * @param array $options
     */
    public function __construct($nav = null, $options = array())
    {
        $this -> nav = $nav;
        $this->setOptions($options);
        if (PHP_SAPI != 'cli') {
                $this->_form = new Ccsd_Form();
        }
    }
    /**'
     * @param array $options
     */
    public function setOptions($options = []) {
        $fields = array_merge($this->_globalFields, $this->_localFields);
        foreach ($options as $option => $value) {
            $option = strtolower($option);
            if (key_exists($option, $fields)) {
                $function = $fields[$option];
                $this -> $function($value);
            }
        }
    }
    /**
     * Définition des options de la page
     * @param array $options
     * @deprecated
     */
    public function setOptionsOld($options = array())
    {
        foreach ($options as $option => $value) {
            $option = strtolower($option);
            switch($option) {
                case 'languages':   $this->_languages = $value;
                                    break;
                case 'pageid'   :   $this->_pageId = (int) $value;
                                    break;
                case 'labels'   :   $this->setLabels($value);
                                    break; 
                case 'parentid' :   $this->setPageParentId($value);
                                    break;                                            
            }
        } 
    }
    /**
     * @param $value
     */
    public function setLanguages($value) {
        $this->_languages = $value;
    }
    /**
     * Récupération des labels de la page
     * @return array
     */
    public function getLabels()
    {
        $res = array();
        foreach($this->getLanguages() as $lang) {
            $res[$lang] = $this->getLabel($lang);
        }
        return $res;
    }
    /**
     * Récupération de la liste des langues de la page
     * @return array
     */
    public function getLanguages()
    {
        return $this->_languages;
    }
    /**
     * Retourne le label dans la langue demandée
     * @param string|Zend_Locale $lang
     * @return string
     */
    public function getLabel($lang)
    {
        if ($lang instanceof Zend_Locale) {
            $lang = $lang->toString();
        }
        return isset($this->_labels[$lang]) ? $this->_labels[$lang] : '';
    }
    /**
     * Retourne la clé de traduction du label de la page
     */
    public function getLabelKey()
    {
        return 'menu-label-' . $this->getPageId();
    }
    /**
     * Initialisation du label de la page
     * @param string $label
     * @param string $lang
     */
    public function setLabel($label, $lang)
    {
        $this->_labels[$lang]   =   $label;
    }
    /**
     * @param int $sid
     */
    public function setSid($sid) {
        $this -> _sid = $sid;
    }
    /**
     * @return int
     */
    public function getSid() {
        return $this -> _sid;
    }
    /**
     * Initialisation des labels
     * @param array $labels
     */
    public function setLabels ($labels)
    {
        if (is_string($labels)) {
                foreach ($this->getLanguages() as $lang) {
                        $this->setLabel($labels, $lang);
                }
        } else {
                //Réinitialisation
                $this->_labels = array();
                foreach ($labels as $lang => $label) {
                    if ($label != '') {
                        $this->setLabel($label, $lang);
                    }
                }
        }
    }
        
    /**
     * Retourne la classe de la page
     * @return string
     */
    public function getPageClass()
    {
        return get_class($this);
    }
    
    /**
     * Retourne le nom de la classe de la page
     * @return string
     */
    public function getPageClassLabel()
    {
        return $this->getPageClass();
    }
    /**
     * récupération de l'id de la page
     * @return int
     */
    public function getPageId()
    {
        return $this->_pageId;
    }
    /**
     * Initialisation de l'id de la page
     * @param int $pageId
     */
    public function setPageId($pageId)
    {
        $this->_pageId = (int) $pageId;
    }
    /**
     * récupération de l'id de la page parente
     * @return int
     */
    public function getPageParentId()
    {
        return $this->_pageParentId;
    }
    /**
     * Initialisation de l'id de la page parente
     * @param int $pageParentId
     */
    public function setPageParentId($pageParentId)
    {
        $this->_pageParentId = (int) $pageParentId;
    }
    /**
     * Retourne les données supplémentaires de la page
     * @return string
     */
    public function getSuppParams()
    {
        return '';
    }

    /**
     * Indique si une page est multiple pour un site
     */
    public function isMultiple()
    {
        return $this->_multiple;
    }
    
    public function save() {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $bind = array(
                        'SID'           => $this->getSid(),
                        'PAGEID'        => $this->getPageId(),
                        'TYPE_PAGE'     => $this->getPageClass(),
                        'CONTROLLER'    => $this->getController(),
                        'ACTION'        => $this->getAction(),
                        'LABEL'         => $this->getLabelKey(),
                        'PARENT_PAGEID' => $this->getPageParentId(),
                        'PARAMS'        => $this->getSuppParams()
                );
                $db->insert($this->_table, $bind);
    }

    public function load() {}
    /**
     * Retourne le controller associé à la page
     */
    public function getController()
    {
        return $this->_controller;
    }
    /**
     * Retourne l'action associée à la page
     * @return string
     */
    public function getAction()
    {
        return $this->_action;
    }
    /**
     * Retourne la ressource associée à la page
     * @return string
     */
    public function getResource()
    {
        return ($this->getController() !='' ? ($this->getController() . '-') : '') . $this->getAction();
    }
    /**
     * Transforme l'objet page en tableau associatif
     * @return array
     */
    public function toArray()
    {
        $array = array();
        $array['label'] = $this->getLabelKey();
        $array['controller'] = $this->getController();
        $array['action'] = $this->getAction();
        $array['resource'] = $this->getResource();
        return $array;
    }
    public function initForm()
    {
        unset($this->_form);
        $this->_form = new Ccsd_Form();
        $this->_form->setAttrib('class', 'form');
    }
    /**
     * Récupération du formulaire pour éditer une page
     * @param int $pageidx
     * @return Ccsd_Form
     */    
    public function getForm($pageidx)
    {
        $this->initForm();
        if (! $this->_form->getElement('pageid')) {
            try {
                $this->_form->addElement('hidden', 'pageid', array('value' => $pageidx, 'belongsTo' => 'pages_' . $pageidx));
            } catch (Zend_Form_Exception $e) {
            }
        }
        if (! $this->_form->getElement('type')) {
            try {
                $this->_form->addElement('hidden', 'type', array('label' => 'Type de la page', 'value' => $this->getPageClass(), 'belongsTo' => 'pages_' . $pageidx));
            } catch (Zend_Form_Exception $e) {
            }
        }
        if (! $this->_form->getElement('labels')) {
                        $populate = array();
            foreach($this->getLanguages() as $lang) {
                $populate[$lang] = $lang;
            }
            try {
                $this->_form->addElement('multiTextSimpleLang', 'labels', array(
                    'label' => 'Titre de la page',
                    'required' => true,
                    'value' => $this->getLabels(),
                    'populate' => $populate,
                    'class' => 'inputlangmulti',
                    //'length' => 0,
                    'belongsTo' => 'pages_' . $pageidx,
                    'validators' => array(new Ccsd_Form_Validate_RequiredLang(array('langs' => $this->getLanguages())))
                ));
            } catch (Zend_Form_Exception $e) {
            } catch (Zend_Validate_Exception $e) {
            }
        }
                return $this->_form;
    }

    protected function getNavigation() {
        return $this -> nav;
    }
} 