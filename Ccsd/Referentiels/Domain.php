<?php

/**
 * Referentiel Domaine
 * @author Ln
 *
 */
/*
 * Classe de meme type que le thesaurus
 *
 * Structure de Table par représentation intervallaire
 * ID, CODE, BORNE_INF, BORNE_SUP
 */

Class Ccsd_Referentiels_Domain extends Ccsd_Referentiels_Abstract {

    const TABLE_DOMAIN_ARXIV = 'REF_DOMAIN_ARXIV';

    public $locale = 'fr';
    static public $core = 'ref_domain';
    static public $_table = 'REF_DOMAIN';
    protected $_primary = 'ID';
    static protected $_metaname = 'domain';

    const INI = 'Ccsd/Referentiels/Form/domain.ini';

    protected $_smallFormElements = array('ID', 'CODE', 'PARENT');
    protected $_mandatoryFormElements = array('ID', 'CODE', 'PARENT');
    protected $_smallMandatoryFormElements = array('ID', 'CODE', 'PARENT');
    static protected $_champsSolr = array("docid"
        , "label_s"
        , "code_s"
        , "*_domain_s"
        , "level_i"
        , "havenext_bool"
    );
    static public $_optionsTri = array('code' => 'code'
        , 'libelle' => 'label_s'
    );
    protected $_relationDomainePortail = 'PORTAIL_DOMAIN';
    protected $_sid = 0;
    public $tableauFils = array();

    public function __construct($sid = 0, $config = array()) {
        $this->_sid = $sid;
        $this->tableauFils = $this->arbreNoeuds();
    }

    public function getMd5() {
        return md5($this->ID);
    }

    /**
     * @see Ccsd_Referentiels_Abstract::_createBaseQuery()
     * @deprecated  Ne semble pas utilisee : voir _createSolrQuery
     * Heureusement car getSelectFromTable non definie...
     */
    protected function _createBaseQuery($critere, $orderby, $filter, $nbResultPerPage, $valid) {
        if ($critere == "*") {
            $condition = 1;
        } else {
            $condition = "(ID = '" . $critere . "')";
            $condition .= " OR (CODE LIKE '%" . $critere . "%')";
        }

        if ($orderby == "code") {
            $order = array('CODE ASC');
        } else {
            $order = array();
        }
        return $this->getSelectFromTable()->where($condition)->order($order);
    }

    /**
     * @see Ccsd_Referentiels_Abstract::_createSolrQuery()
     * Crée la requête pour la pagination "SolR"
     * @param string $critere
     * @param string $orderby
     * @param array $filter
     * @param int $nbResultPerPage
     * @paran bool $valid
     */
    protected function _createSolrQuery($critere, $orderby, $filter, $nbResultPerPage, $valid) {
        /*
         * Rajouter les langues avec *_domain_s en_domain_s, fr_domain_s,...
         */
        switch ($orderby) {
            case "code" :
                $paramUrlSort = "&sort=code+asc";
                break;
            case "label_s" :
                $paramUrlSort = "&sort=label_s+asc";
                break;
            default :
                $paramUrlSort = "";
        }
        return "q=" . urlencode($critere) . $paramUrlSort;
    }

    /**
     * Permet de passer d'un arbre classique a un arbre intervallaire
     * BASE DE DEPART
     * ID CODE PARENT LEVEL HAVENEXT
     * BASE ARRIVEE
     * ID CODE NIVEAU BORNE INF BORNE SUP
     * */
    public function arborescenceBornes() {
        // Insertion de chaque Fils de chaque niveau
        $monTableauDeDomaine = array();

        //recherche des elements classe par parents, libelles
        $elements = $this->arborescence();

        // Initialisation
        $codeParentPrecedent = "root";
        $niveau = 1;
        $borne_inf = 1;
        $borne_sup = 0;
        $departBorneInfSuivant = array();

        // Pour chaque element du niveau courant
        foreach ($elements as $elementTableau) {
            $codeParent = ( $elementTableau['PARENT'] == 0 ) ? "root" : mb_substr($elementTableau['CODE'], 0, -mb_strlen(strrchr($elementTableau['CODE'], '.')));
            $niveauCourant = mb_substr_count($elementTableau['CODE'], ".") + 1;
            if ($codeParent != $codeParentPrecedent) {
                if (array_key_exists($elementTableau['PARENT'], $monTableauDeDomaine)) {
                    // je descends d'un niveau
                    $borne_inf ++;
                } else {
                    $borne_inf = ($niveauCourant > $niveau) ? $borne_sup + 1 : $departBorneInfSuivant[$niveauCourant];
                }
                $codeParentPrecedent = $codeParent;
                $departBorneInfSuivant[$niveau] = $borne_sup + 1;
            } else {
                // Je suis toujours sur le même niveau on prévoit le tri sur les libelles
                $borne_inf = ($borne_sup == 0 ) ? 2 : $borne_sup + 1;
            }
            $borne_sup = $borne_inf + $this->nbFils($elementTableau['CODE']) * 2 + 1;
            $niveau = $niveauCourant;
            $enreg = array(
                'ID' => $elementTableau['ID'],
                'CODE' => $elementTableau['CODE'],
                'CODE_PARENT' => $elementTableau['PARENT'],
                'LIBELLE' => Zend_Registry::get("Zend_Translate")->translate("domain_" . $elementTableau['CODE'], $this->locale),
                'NIVEAU' => mb_substr_count($elementTableau['CODE'], ".") + 1,
                'BORNE_INF' => $borne_inf,
                'BORNE_SUP' => $borne_sup
            );
            $monTableauDeDomaine[$elementTableau['ID']] = $enreg;
        }
        return $monTableauDeDomaine;
    }

    /**
     * Renvoie une chaine json contenant l'arborescence complete
     *
    */
    public function creeArborescenceJson($tableauDomaine) {
        //initialisation
        $tableauFinal=[];
        foreach ($tableauDomaine as $domaineCourant) {
            // suis-je dans la bonne arborescence ?
            $CodeCourant = $domaineCourant['CODE'];
            $cles = explode(".", $CodeCourant);
            $refTableau = &$tableauFinal;
            $cle = ''; $sep ='';
            foreach ($cles as $clePartielle) {
                $cle .= $sep . $clePartielle;
                $sep = '.';
                if (!isset($refTableau[$cle])) {
                    $refTableau[$cle] = array();
                }
                $refTableau = &$refTableau[$cle];
            }
        }
        return(Zend_Json::encode($tableauFinal));
    }

    //Renvoie un tableau plat d'elements complets à partir d'un tableau d'ids
    // pour un sid donne
    public function arborescence() {
        $idsTries = array();
        $racines = array_unique(array_values($this->tableauFils));
        foreach ($racines as $racine) {
            if (!array_key_exists($racine, $this->tableauFils)) {
                $this->rechercheSousArborescence($racine, $racine, $idsTries);
            }
        }
        //$this->rechercheSousArborescence(0, $idsTries);
        return($this->tableauDomaineTrie($idsTries));
    }

    /**
     * Renvoie un tableau plat d'elements complets à partir d'un tabelau d'ids
     * @Todo: on devrait rendre un table d'objets!!!
     *
     * @param int[]
     * @return array
     */
    public function tableauDomaineTrie($ids) {

        // Lecture de la base dans l'ordre de la base...
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $tableauDomaineTotal = $db->fetchAll($db->select()->from(self::$_table));
        // Table de row non cherchable, on cree un tableau indexe
        foreach ($tableauDomaineTotal as $domainrow) {
            $tableauDomaineTotalIndexe[$domainrow['ID']] = $domainrow;
        }
        // On re-ordonne comme demande
        foreach ($ids as $id) {
            $tableauDomaine[$id] = $tableauDomaineTotalIndexe[$id];
        }
        return $tableauDomaine;
    }

    //Renvoie un tableau plat contenant les id de l'arborescence trie
    public function rechercheSousArborescence($idInitial, $id, &$tableauFinal) {
        if ($id != $idInitial) {
            $tableauFinal[] = $id;
        }
        // je suis un noeud
        if (in_array($id, $this->tableauFils)) {
            //je recupere les fils trié
            $idsFils = $this->idsTrieDuParent($id);
            foreach ($idsFils as $idFils) {
                $this->rechercheSousArborescence($idInitial, $idFils, $tableauFinal);
            }
        }
    }

    /**
     * Je parcours tous les domaines je récupère tous les ID
     * qui ont des fils
     * Hum... Non, je recupere l'ensemble de domain avec ou sans parent
     * Il n'y a pas de condition sur le parent
     *
     * @todo Devrait rendre un tableau d'object! et on devrait nommer cette fonction de facon comprehensible...
     * @return int[]
     */

    private function arbreNoeuds() {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        /* Je recupere tous les noeuds */
        $select = $db->select();
        $select->from(array('d' => self::$_table), array('ID', 'PARENT'))
                ->order('PARENT');

        if ($this->_sid != 0) {
            $select->joinUsing($this->_relationDomainePortail, 'ID')   // magic method
                    ->where('SID = ?', $this->_sid);
        }
        $elements = $db->fetchAll($select);
        // On indexe le tableau pour l'acceder par ID
        foreach ($elements as $element) {
            $tab[$element['ID']] = $element['PARENT'];
        }
        return ($tab);
    }

    /**
     * Fonction qui renvoie les enfants dans un tableau trie
     * en fonction des libelles
     * @param int $idParent
     */

    private function idsTrieDuParent($idParent) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $nomTablePortailDomain = "PORTAIL_DOMAIN";

        $select = $db->select();
        $select->from(self::$_table)
                ->where('PARENT = ?', $idParent);

        if ($this->_sid != 0) {
            $select->joinUsing($this->_relationDomainePortail, 'ID')    // magic method
                    ->where('SID = ?', $this->_sid);
        }

        $elements = $db->fetchAll($select);

        $libelles = array();
        foreach ($elements as $cle => $valeur) {
            $libelles[$cle] = Zend_Registry::get("Zend_Translate")->translate("domain_" . $valeur['CODE'], $this->locale);
        }
        array_multisort($libelles, SORT_ASC, $elements);
        $tab = array();
        foreach ($elements as $element) {
            $tab[] = $element['ID'];
        }
        return $tab;
    }

    private function nbFils($code) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $select = $db->select();
        $select->from(self::$_table, new Zend_Db_Expr('COUNT(*)'));
        $select->where('PARENT = ?', $code);

        if ($this->_sid != 0) {
            $select->joinUsing($this->_relationDomainePortail, 'ID');
            $select->where('SID = ?', $this->_sid);
        }

        $resultat = $db->fetchOne($select);
        return $resultat;
    }

    /**
     * Retourne la liste des domaines arXiv correspondant aux domaines HAL
     * @param mixed $domainHal
     */
    public static function getDomainArxiv($domainHal) {
        if (!is_array($domainHal)) {
            $domainHal = array($domainHal);
        }

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = $db->select()->from(self::TABLE_DOMAIN_ARXIV, 'ARXIV')
                ->where('CODE IN (?)', $domainHal);
        return $db->fetchCol($sql);
    }

    /**
     * Indique s'il existe des domaines arXiv pour les domaines d'un document
     * @param mixed $domainHal
     */
    public static function domainArxivExist($domainHal) {
        if (!is_array($domainHal)) {
            $domainHal = array($domainHal);
        }

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = $db->select()->from(self::TABLE_DOMAIN_ARXIV, 'COUNT(*)')
                ->where('CODE IN (?)', $domainHal);
        return $db->fetchOne($sql) > 0;
    }

    /**
     *  @todo Fonction non utilisee ???
     */
    public static function getDomainJSON($sid = 1, $lang = 'fr') {
        /**
          @TODO cache
         */
        $obj = new self($sid);
        $obj->locale = $lang;
        return $obj->creeArborescenceJson($obj->arborescence());
    }

    /**
     * Retourne tous les docids des Domaines à indexer dans solr == uniquement ceux de HAL
     * @param int $count nombre de domaines à retourner par requête
     * @param int $offset offset
     * @return array tableau de docid
     */
    public function getDocidsByDb($count = 10, $offset = 0) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = $db->select()->from(static::$_table, ['docid' => $this->_primary])
                ->order($this->_primary . ' ASC')
                ->where('`ID` IN (SELECT ID FROM ' . $this->_relationDomainePortail . ' WHERE SID = 1)')
                ->limit($count, $offset);
        return $db->fetchAll($sql);
    }

    /**
     * Compte le nombre d'entrées du référentiel à indexer dans solr == uniquement ceux de HAL
     * @return int nombre d'entrées du référentiel
     */
    public function countDbEntries() {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = $db->select()->from(static::$_table, 'COUNT(*) AS NB')
                ->where('`ID` IN (SELECT ID FROM ' . $this->_relationDomainePortail . ' WHERE SID = 1)');
        return (int) $db->fetchOne($sql);
    }

    public function getTranslation($code, $locale = null)
    {
        return Zend_Registry::get("Zend_Translate")->translate("domain_" . $code, $locale);
    }

    public function getCodeId($code)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = $db->select()->from(static::$_table, $this->_primary)
            ->where("CODE = ?", $code);
        return $db->fetchOne($sql);
    }

    public function getCode($id)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = $db->select()->from(static::$_table, 'CODE')
            ->where($this->_primary . " = ?", $id);
        return $db->fetchOne($sql);
    }

    public function isValidCode($code, $id = null)
    {
        if ($id == null) {
            $id = $this->getCodeId($code);
        }
        return $id !== false;
    }

    public function exist($code)
    {
        return $this->isValidCode($code);
    }

    public function getBroader ($code)
    {
        $id = $this->getCodeId($code);
        if (! $this->isValidCode($code, $id)) {
            return false;
        }

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = $db->select()->from(self::$_table, 'PARENT')
            ->where($this->_primary . ' = ?', $id);
        $parentid = $db->fetchOne($sql);
        if ($parentid === false || $parentid === 0) {
            return false;
        }
        return $this->getCode($parentid);
    }

    public function getNarrower ($id)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = $db->select()->from(self::$_table, 'CODE')
            ->where('PARENT = ?', $id);

        return $db->fetchCol($sql);
    }

    public function getUri($code)
    {
        return AUREHAL_URL . "/subject/{$code}";
    }

    public function getIds($from = NULL)
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $sql = $db->select()->from(self::$_table, 'CODE');
        return $db->fetchCol($sql);
    }
    public function toHtml($option = [])
    {
        /** TODO: Why there is no need of this fonction for that class
        *  Other referential need it!!!
         * @see Aurehal_Controller_Referentiel:
         */
    }
}
