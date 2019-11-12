<?php

/**
 * Indexation de HAL
 * Class Ccsd_Search_Solr_Indexer_Halv3
 *
 */
class Ccsd_Search_Solr_Indexer_Halv3 extends Ccsd_Search_Solr_Indexer
{

    const LOCAL_META_SUFFIX = '_local';
    const PDF2TEXT_EXTENSION = 'pdf2txt';

    /** @const prefixes pour nom de champ solr types de structures */
    const PREFIX_STRUCTURES = 'struct';
    const PREFIX_INST_STRUCT = 'instStruct';
    const PREFIX_LAB_STRUCT = 'labStruct';
    const PREFIX_DEPT_STRUCT = 'deptStruct';
    const PREFIX_RTEAM_STRUCT = 'rteamStruct';

    const PREFIX_RGRP_INST = 'rgrpInstStruct';
    const PREFIX_RGRP_LAB = 'rgrpLabStruct';

    /**
     *
     * @var string langue par défaut si inconnue dans le document
     */
    const SOLR_DOCUMENT_DEFAULT_LANGUAGE = 'und';
    public static $_coreName = 'hal';
    public static $dbConfName = 'hal';
    public static $_maxDocsInBuffer = 1;
    public static $_maxSelectFromIndexQueue = 100;
    public static $logFileName;

    /**
     * True == index PDF
     * False == don't index PDF
     * @var boolean
     */
    public $indexPDF;


    /**
     * @var Hal_Document
     */

    private $_halDocument;

    /**
     *
     * @var boolean
     */
    private $_deleteCache;

    /**
     *
     * @param array $options
     */
    public function __construct($options)
    {
        static::initHalEnv();
        $options ['core'] = static::$_coreName;


        if (!isset($options ['maxDocsInBuffer'])) {
            $options ['maxDocsInBuffer'] = static::$_maxDocsInBuffer;
        }

        parent::__construct($options);

        date_default_timezone_set('Europe/Paris');
    }

    /**
     * @param boolean $_deleteCache
     * @return Ccsd_Search_Solr_Indexer_Halv3
     */
    public function setDeleteCache($_deleteCache)
    {
        $this->_deleteCache = $_deleteCache;
        return $this;
    }

    /**
     * Set the select request to get the list of Id to index
     */
    protected function selectIds($select)
    {
        $select->from(['DOCUMENT'], ['DOCID'])->order('DOCID')->where(
            '`DOCSTATUS` = ' . Hal_Document::STATUS_VISIBLE . ' || `DOCSTATUS` = ' . Hal_Document::STATUS_REPLACED);
    }

    /**
     * @param int $docId
     * @param \Solarium\QueryType\Update\Query\Document\Document $ndx
     * @return mixed|null|object|\Solarium\QueryType\Update\Query\Document\Document
     */
    protected function addMetadataToDoc($docId, $ndx)
    {
        $dataToIndex = [];
        /** @var $docObj Hal_Document */
        $docObj = static::getDocidData($docId);
        $translator = Zend_Registry::get('Zend_Translate');

        if ($docObj->getDocid() == 0) {
            $this->setErrorMessage($docId . ' pas de données, le document ne sera pas indexé.');
            Ccsd_Log::message($this->getErrorMessage(), true, 'ERR', $this->getLogFilename());
            return null;
        }

        if (!$docObj->isOnline()) {
            $this->setErrorMessage($docId . ' avec le statut : ' . $docObj->getStatus() . ' ne sera pas indexé.');
            Ccsd_Log::message($this->getErrorMessage(), true, 'ERR', $this->getLogFilename());
            return null;
        }

        // $helper = new Solarium\Core\Query\Helper ();

        $ndx->docid = $docObj->getDocid();

        /* ------ Citations & label_s */

        $citationFull = $docObj->getCitation('full');
        $ndx->addField('label_s', strip_tags($citationFull));/* REQUIRED */
        $ndx->addField('citationRef_s', $docObj->getCitation('ref'));
        $ndx->addField('citationFull_s', $citationFull);


        $ndx->label_xml = trim(Ccsd_Tools_String::stripCtrlChars($docObj->get('tei')));
        $ndx->label_bibtex = ltrim(Ccsd_Tools_String::stripCtrlChars($docObj->get('bib'), '', false, true));
        $ndx->label_endnote = ltrim(Ccsd_Tools_String::stripCtrlChars($docObj->get('enw'), '', false, true)) . PHP_EOL;
        $ndx->label_coins = trim(Ccsd_Tools_String::stripCtrlChars($docObj->getCoins()));


        if ($docObj->getFormat() != Hal_Document::FORMAT_FILE) {
            /** @var Hal_LinkExt $linkedExt */
            $linkedExt = $docObj->getMetaObj('LINKEXT');
            if ($linkedExt != null) {
                $ndx->linkExtUrl_s = $linkedExt->getUrl();
                $ndx->linkExtId_s = $linkedExt->getIdSite();
            }
        }

        // fichier accessible en OA
        $ndx->addField('openAccess_bool', $docObj->isOpenAccess());


        Ccsd_Log::message('Hal ID ' . $docObj->getId(), $this->isDebugMode(), 'INFO', $this->getLogFilename());

        if ($docObj->getHalMeta() == '') {
            $this->setErrorMessage($docObj->getId() . ' pas de metas pour / DOCID ' . $docId . ', le document ne sera pas indexé.');
            Ccsd_Log::message($this->getErrorMessage(), true, 'ERR', $this->getLogFilename());
            return null;
        }

        $this->setDoc($ndx);


        // TODO use
        //$docObj->getHalMeta()->getMetadatas();
        //$d = new Hal_Document_Meta_Simple();
        //$d->getValue();

        if ($docObj->getInstance() == 'hceres') {
            $this->addHceresMetas($docObj);
        }


        $meta = $this->indexListOrThesaurusMetadata($docObj->getHalMeta()->toArray());
        $meta = $this->indexLocalMetaData($meta);
        $this->indexDomains($meta['domain']);

        /** TODO: ON devrait recuperer les object Meta et utiliser un fonction index pour cette meta */
        $this->indexTitles($meta['title'], $docObj->getMainTitle());
        isset($meta['subTitle']) ? $this->indexSubTitles($meta['subTitle']) : false;
        isset($meta['keyword']) ? $this->indexKeywords($meta ['keyword']) : false;
        isset($meta['abstract']) ? $this->indexAbstract($meta ['abstract'], $meta ['language']) : false;
        $this->indexConference($meta);
        isset($meta['journal']) ? $this->indexJournal($meta ['journal']) : false;
        isset($meta['europeanProject']) ? $this->indexEuropeanProject($meta ['europeanProject']) : false;
        isset($meta['anrProject']) ? $this->indexAnrProject($meta ['anrProject']) : false;


        $this->indexAuthors($docObj->getAuthors(), $docObj->getStructures());
        $this->indexStructures($docObj->getStructures());
        $this->indexContributor($docObj->getContributor());

        $ndx = $this->getDoc();
        /**
         * Related Docs
         */
        if (($docObj->getRelated() != '') && (is_array($docObj->getRelated()))) {
            foreach ($docObj->getRelated() as $description) {
                $ndx->addField('related_s', $description['URI']);
            }
        }
        /**
         * Country
         */
        if (isset($meta ['country'])) {
            $countryUp = strtoupper($meta ['country']);
            $country [] = Zend_Locale::getTranslation($countryUp, 'country', 'fr');
            $country [] = Zend_Locale::getTranslation($countryUp, 'country', 'en');
            $country [] = Zend_Locale::getTranslation($countryUp, 'country', 'es');
            $country [] = Zend_Locale::getTranslation($countryUp, 'country', 'eu');
            $country [] = $meta ['country'];
            $dataToIndex ['country_s'] = $meta ['country'];
            $dataToIndex ['country_t'] = array_unique($country);
        }
        /**
         * Traitement Langue
         */
        if (isset($meta ['language'])) {
            $dataToIndex ['language_s'] = $meta ['language'];
            $metaLang = 'lang_' . $meta ['language'];

            $lang_fr = $translator->translate($metaLang, 'fr');
            $lang_en = $translator->translate($metaLang, 'en');
            $lang_es = $translator->translate($metaLang, 'es');
            $lang_eu = $translator->translate($metaLang, 'eu');

            if ($lang_fr == $metaLang) {
                $lang_fr = '';
            }
            if ($lang_en == $metaLang) {
                $lang_en = '';
            }
            if ($lang_es == $metaLang) {
                $lang_es = '';
            }
            if ($lang_eu == $metaLang) {
                $lang_eu = '';
            }
            $dataToIndex ['language_t'] = array_unique([
                $lang_fr,
                $lang_en,
                $lang_es,
                $lang_eu,
                $meta ['language']
            ]);
        }
        /**
         * Traitement Langue//
         */
        $this->setDoc($ndx);
        $this->addArrayOfMetaToDoc($dataToIndex);
        unset($dataToIndex);
        $ndx = $this->getDoc();

        if (isset($meta ['page'])) {
            $meta ['page'] = trim($meta ['page'], '-?');
            if ($meta ['page'] == '.') {
                $meta ['page'] = '';
            }
        }

        $dataToIndex = [
            'halId_s' => $docObj->getId(),
            'halIdSameAs_s' => $docObj->getSameasIds(),
            'uri_s' => $docObj->getUri(true),
            'version_i' => $docObj->getVersion(),
            'status_i' => $docObj->getStatus(),
            'instance_s' => $docObj->getInstance(),
            'sid_i' => $docObj->getSid(),
            'submitType_s' => $docObj->getFormat(),
            'inputType_s' => $docObj->getInputType(),
            'docType_s' => $docObj->getTypDoc(),
            'otherType_s' => $this->existsValue($meta, 'otherType', ''),
            'thumbId_i' => $docObj->getThumbid(),
            'selfArchiving_bool' => $docObj->getSelfArchiving(),
            'authorityInstitution_s' => $this->existsValue($meta, 'authorityInstitution', ''),
            'bookTitle_s' => $this->existsValue($meta, 'bookTitle', ''),
            'classification_s' => $this->existsValue($meta, 'classification', ''),
            'collaboration_s' => $this->existsValue($meta, 'collaboration', ''),
            'comment_s' => $this->existsValue($meta, 'comment', ''),
            'committee_s' => $this->existsValue($meta, 'committee', ''),
            'city_s' => $this->existsValue($meta, 'city', ''),
            'credit_s' => $this->existsValue($meta, 'credit', ''),
            'description_s' => $this->existsValue($meta, 'description', ''),
            'director_s' => $this->existsValue($meta, 'director', ''),
            // 'duration_s' => $meta ['duration'],
            // 'frequency_s' => $meta ['frequency'],
            'funding_s' => $this->existsValue($meta, 'funding', ''),
            'isbn_s' => $this->existsValue($meta, 'isbn', ''),
            'issue_s' => $this->existsValue($meta, 'issue', ''),
            'lectureName_s' => $this->existsValue($meta, 'lectureName', ''),
            'length_i' => (int)$this->existsValue($meta, 'length', 0),
            'localReference_s' => $this->existsValue($meta, 'localReference', ''),
            'mesh_s' => $this->existsValue($meta, 'mesh', ''),
            'number_s' => $this->existsValue($meta, 'number', ''),
            'nntId_s' => $this->existsValue($meta, 'nnt', ''),
            'page_s' => $this->existsValue($meta, 'page', ''),
            'patentId_s' => $this->existsValue($meta, 'patentId', ''),
            'publicationLocation_s' => $this->existsValue($meta, 'publicationLocation', ''),
            'publisher_s' => $this->existsValue($meta, 'publisher', ''),
            'publisherLink_s' => $this->existsValue($meta, 'publisherLink', ''),
            'seeAlso_s' => $this->existsValue($meta, 'seeAlso', ''),
            'seriesEditor_s' => $this->existsValue($meta, 'seriesEditor', ''),
            'serie_s' => $this->existsValue($meta, 'serie', ''),
            'scientificEditor_s' => $this->existsValue($meta, 'scientificEditor', ''),
            'source_s' => $this->existsValue($meta, 'source', ''),
            'thesisSchool_s' => $this->existsValue($meta, 'thesisSchool', ''),
            'volume_s' => $this->existsValue($meta, 'volume', ''),

            'softProgrammingLanguage_s' => $this->existsValue($meta, 'programmingLanguage', ''),
            'softCodeRepository_s' => $this->existsValue($meta, 'codeRepository', ''),
            'softPlatform_s' => $this->existsValue($meta, 'platform', ''),
            'softVersion_s' => $this->existsValue($meta, 'version', ''),
            'softDevelopmentStatus_s' => $this->existsValue($meta, 'developmentStatus', ''),
            'softRuntimePlatform_s' => $this->existsValue($meta, 'runtimePlatform', ''),
        ];

        /*if ($docObj->getInstance() == 'hceres') {
            $this->addHceresMetas($docObj);
        }
*/
        // boolean : true ou false est censé être rempli
        if (isset($meta ['inPress'])) {
            if ($meta ['inPress'] == "0") {
                $dataToIndex['inPress_bool'] = 'false';
            } else {
                $dataToIndex['inPress_bool'] = 'true';
            }
        } else {
            $dataToIndex['inPress_bool'] = 'false';
        }

        /**
         * Coordonnées
         */
        if (isset($meta ['latitude']) && ($meta ['latitude'] != '') && isset($meta ['longitude']) && ($meta ['longitude'] != '')) {
            $dataToIndex ['location'] = $meta ['latitude'] . ',' . $meta ['longitude'];
            $dataToIndex ['coordinates_s'] = $dataToIndex ['location'];
        }
        /**
         * Identifiants: Attention, Bd incoherente, on verifie que les identifiant sont bien des choses que l'on veut
         */
        if (isset($meta ['identifier']) && is_array($meta ['identifier'])) {
            foreach (Hal_Document::$_serverCopy as $id) {
                if (array_key_exists($id, $meta ['identifier'])) {
                    $dataToIndex [$id . 'Id_s'] = $meta ['identifier'] [$id];
                }
            }
        }
        $ndx = $this->addArrayOfMetaToDoc($dataToIndex, null, $ndx);
        unset($dataToIndex);

        /**
         * dates
         */
        $this->setDoc($ndx);
        if (($docObj->getTypDoc() == 'THESE') || ($docObj->getTypDoc() == 'HDR') || ($docObj->getTypDoc() == 'ETABTHESE')) {
            $this->indexDates($docObj->getHalMeta()->getMeta('date'), 'defenseDate', true);
        }

        $this->indexDates($docObj->getHalMeta()->getMeta('writingDate'), 'writingDate', true);
        $this->indexDates($docObj->getHalMeta()->getMeta('edate'), 'ePublicationDate', true);

        $this->indexDates($docObj->getLastModifiedDate(), 'modifiedDate');
        $this->indexDates($docObj->getSubmittedDate(), 'submittedDate');
        $this->indexDates($docObj->getReleasedDate(), 'releasedDate');
        $this->indexDates($docObj->getProducedDate(), 'producedDate', true);
        $this->indexDates($docObj->getPublicationDate(), 'publicationDate', true);


        $ndx = $this->getDoc();
        /**
         * Propriétaires
         */
        foreach ($docObj->getOwner() as $ownerId) {
            $ndx->addField('owners_i', $ownerId);
        }
        /**
         * Collections
         */
        $arrayOfCollSid = [];
        foreach ($docObj->getCollections() as $s) {
            $arrayOfCollSid[] = $s->getSid();
        }
        $this->indexCollections($docObj->getCollections(), null, $arrayOfCollSid);
        $this->indexFiles();
        $ndx = $this->getDoc();
        // fin de la creation de l'objet d'indexation du document
        if ($this->isDebugMode()) {
            Zend_Debug::dump($ndx->getFields());
        }

        return $ndx;
    }

    /**
     * Get the Hal Document object from Id
     * Erase cache data if necessary before loading document
     * And set the document in the indexer
     *
     * @see Ccsd_Search_Solr_Indexer::getDocidData()
     */
    protected function getDocidData($docId)
    {
        if ($this->getDeleteCache()) {
            if (Hal_Document::deleteCaches($docId, ['phps', 'tei', 'dc', 'bib', 'enw', 'json', 'dcterms'])) {
                Ccsd_Log::message($docId . ' Caches du doc supprimés', $this->isDebugMode(), 'INFO', $this->getLogFilename());
            } else {
                Ccsd_Log::message($docId . ' Echec de la suppression des caches', true, 'ERR', $this->getLogFilename());
            }
        }
        $halDocument = new Hal_Document($docId, '', 0, false);
        /*
        Si cache exist, alors on le prends, sinon on va dans la base.
        Si le cache a ete efface, on ira dans la base.
        */
        $halDocument->load('DOCID', false);
        $this->setHalDocument($halDocument);
        return $halDocument;
    }

    /**
     * @return bool
     */
    private function getDeleteCache()
    {
        return $this->_deleteCache;
    }

    /**
     * Ajoute les champs spécifiques HCERES
     * @param Hal_Document $docObj
     */
    protected function addHceresMetas(Hal_Document $docObj)
    {
        // ajoute les champs de l'entite evaluee
        $entity = $docObj->getHalMeta()->getMeta('hceres_entite_local');
        //$entity = $docObj->getMetaObj('hceres_entite_local');
        if ($entity instanceof Ccsd_Referentiels_Hceres) {
            $dataToIndex = $entity->getIndexationData();
            $this->addArrayOfMetaToDoc($dataToIndex, 'hceres_entity');
            $docObj->delMeta('hceres_entite_local');

            // recherche la (ou les) entite(s) fille(s)
            $oChild = $entity->loadChild();
            // ajoute les champs de(s) entité(s) fille(s)
            if ($oChild instanceof Ccsd_Referentiels_Hceres) {
                $dataToIndex = $oChild->getIndexationData();
                $this->addArrayOfMetaToDoc($dataToIndex, 'hceres_etab');
            }
            // recherche la (ou les) entite(s) meres(s)
            // ajoute les champs de(s) entité(s) mere(s)
        }

        // ajoute les champs de l'etablissement support
        $aEtabsupports = $docObj->getHalMeta()->getMeta('hceres_etabsupport_local');

        foreach ($aEtabsupports as $etabsupport) {
            if ($etabsupport instanceof Ccsd_Referentiels_Hceres) {
                $dataToIndex = $etabsupport->getIndexationData();
                $this->addArrayOfMetaToDoc($dataToIndex, 'hceres_etab');
            }
        }
        $docObj->delMeta('hceres_etabsupport_local');
    }

    /**
     * Indexe les metas de type liste et (thesaurus sauf les domaines)
     * @param $meta
     * @return mixed
     */
    private function indexListOrThesaurusMetadata($meta)
    {
        if (!is_array($meta)) {
            return $meta;
        }

        $dataToIndex = [];

        $languagesArray = Hal_Settings::getLanguages();
        $t = Zend_Registry::get('Zend_Translate');

        foreach ($meta as $metaName => $metaValue) {

            if (($metaName == 'domain') || ($metaName == 'domain_inter')) {
                continue;
            }

            if ((!$this->isTypeOfMetadataList($metaName)) && (!$this->isTypeOfThesaurusMetadata($metaName))) {
                continue;
            }

            $metasTranslated = [];

            //translate value in each language
            foreach ($languagesArray as $lang) {
                //si metalist complex, la variable $metaValue est un array
                if (!is_array($metaValue)) {
                    $metasTranslated[$lang] = $t->translate($metaName . '_' . $metaValue, $lang);
                    if ($metasTranslated[$lang] == $metaName . '_' . $metaValue) {
                        unset($metasTranslated[$lang]); // rm strings not translated
                    }
                } else {
                    foreach ($metaValue as $uniqValue) {
                        $metaTranslat = $t->translate($metaName . '_' . $uniqValue, $lang);
                        if ($metaTranslat != $metaName . '_' . $uniqValue) {
                            $metasTranslated[$lang][] = $metaTranslat;
                        }
                    }
                }
            }
            // + value
            $metasTranslated['val'] = $metaValue;
            if (!is_array($metaValue)) {
                $metasTranslated = array_unique($metasTranslated);
            }

            $dataToIndex [$metaName . '_s'] = $metaValue;
            $dataToIndex [$metaName . '_t'] = array_values($metasTranslated);

            unset($meta [$metaName]); // remove indexed meta from array of meta

        }

        $this->addArrayOfMetaToDoc($dataToIndex);
        return $meta;
    }

    /**
     * @param $metaName
     * @return bool
     */
    private function isTypeOfMetadataList($metaName)
    {

        try {
            $metaList = Zend_Registry::get('Hal_Referentiels_Metadata_metaList');
        } catch (Zend_Exception $e) {
            $metaList = Hal_Referentiels_Metadata::metaList(null, false);
            Zend_Registry::set('Hal_Referentiels_Metadata_metaList', $metaList);
        }

        return in_array($metaName, $metaList);

    }

    /**
     * @param $metaName
     * @return bool
     */
    private function isTypeOfThesaurusMetadata($metaName)
    {
        try {
            $metaThesaurus = Zend_Registry::get('Hal_Settings_getThesaurusMetas');
        } catch (Zend_Exception $e) {
            $metaThesaurus = Hal_Settings::getThesaurusMetas();
            Zend_Registry::set('Hal_Settings_getThesaurusMetas', $metaThesaurus);
        }
        return in_array($metaName, $metaThesaurus);
    }

    /**
     * Indexe les metas de type local pour un portail _local
     * @param array $meta
     * @return mixed
     */
    private function indexLocalMetaData($meta)
    {
        foreach ($meta as $metaName => $metaValue) {
            // meta locale mais pas de type liste, type liste indexé autrement
            if (($this->isTypeOfLocalMetadata($metaName)) && (!$this->isTypeOfMetadataList($metaName))) {
                $this->addArrayOfMetaToDoc([$metaName . '_s' => $metaValue, $metaName . '_t' => $metaValue]);
                unset($meta[$metaName]);
            }
        }
        return $meta;
    }

    /**
     * Determine si une metadonnée est de type local pour un portail '_local'
     * @param $metaName
     * @return bool
     */
    private function isTypeOfLocalMetadata($metaName)
    {
        if (static::endsWith($metaName, static::LOCAL_META_SUFFIX)) {
            return true;
        }
        return false;
    }

    /**
     * Determine si une chaine se termine pour une sous-chaine
     *
     * @param string $phrase
     * @param string|array $terms
     * @return bool
     */
    public static function endsWith($phrase, $terms)
    {
        foreach ((array)$terms as $term) {
            if ((string)$term === substr($phrase, -strlen($term))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Indexation des domaines
     * @param array $meta
     * @return boolean
     */
    private function indexDomains($meta)
    {

        if ((!is_array($meta)) || ($meta == '')) {
            return false;
        }

        $ndx = $this->getDoc();
        $domainLabelArray = [];


        $translator = Zend_Registry::get('Zend_Translate');
        foreach ($meta as $domNumber => $domain) {

            if ($domain == '') {
                continue;
            }

            $ndx->addField('domainAllCode_s', $domain);

            $domaines = Ccsd_Tools_String::getHalDomainPaths($domain);
            $pathString = '';
            foreach ($domaines as $pathIndex => $d) {

                $ndx->addField('level' . $pathIndex . '_domain_s', $d);

                $pathString = '.' . $d;
                $ndx->addField('domain_s', $pathIndex . $pathString);

                $d_fr = $translator->translate('domain_' . $d, 'fr');
                $d_en = $translator->translate('domain_' . $d, 'en');
                $d_es = $translator->translate('domain_' . $d, 'es');
                $d_eu = $translator->translate('domain_' . $d, 'eu');

                $domainLabelArray = array_merge($domainLabelArray, [$d_fr, $d_en, $d_es, $d_eu]);

                $domainLabelFrArr [] = $d_fr;
                $domainLabelEnArr [] = $d_en;
                $domainLabelEsArr [] = $d_es;
                $domainLabelEuArr [] = $d_eu;
            }

            $domainLabelFr = implode('/', $domainLabelFrArr);
            $domainLabelEn = implode('/', $domainLabelEnArr);
            $domainLabelEs = implode('/', $domainLabelEsArr);
            $domainLabelEu = implode('/', $domainLabelEuArr);

            $ndx->addField('fr_domainAllCodeLabel_fs', $domain . parent::SOLR_FACET_SEPARATOR . $domainLabelFr);
            $ndx->addField('en_domainAllCodeLabel_fs', $domain . parent::SOLR_FACET_SEPARATOR . $domainLabelEn);
            $ndx->addField('es_domainAllCodeLabel_fs', $domain . parent::SOLR_FACET_SEPARATOR . $domainLabelEs);
            $ndx->addField('eu_domainAllCodeLabel_fs', $domain . parent::SOLR_FACET_SEPARATOR . $domainLabelEu);

            // Domaine primaire
            if ($domNumber == 0) {
                $ndx->primaryDomain_s = $domain;
            }

        }

        foreach (array_unique($domainLabelArray) as $domain) {
            $ndx->addField('domain_t', $domain);
        }

        $this->setDoc($ndx);

        return true;
    }

    /**
     * Indexation titres du document
     * @param string[] $metaTitle
     * @param string $rawMainTitle
     */
    private function indexTitles($metaTitle, $rawMainTitle)
    {
        $ndx = $this->getDoc();

        $langTitleSortAdded = false;

        $mainTitle = $this->cleanString($rawMainTitle);

        $ndx->title_sort = Ccsd_Tools_String::truncate($mainTitle, 100, '', false);

        $titleArr [] = $mainTitle;

        foreach ($metaTitle as $language => $rawTitle) {
            // TODO enlever quand pb des titres dans la même langue corrigé
            if (is_array($rawTitle)) {
                foreach ($rawTitle as $t) {
                    $r = $this->indexTitleByLanguage($ndx, $t, $language, $langTitleSortAdded, $titleArr);
                    list($ndx, $language, $langTitleSortAdded, $titleArr) = $r;
                }
            } else {
                $r = $this->indexTitleByLanguage($ndx, $rawTitle, $language, $langTitleSortAdded, $titleArr);
                list($ndx, $language, $langTitleSortAdded, $titleArr) = $r;
            }
        }

        // Tous les titres, dans toutes les langues dans le champ title_s
        $titleArr = array_unique($titleArr);

        foreach ($titleArr as $title) {
            $ndx->addField('title_s', $title);
        }

        $this->setDoc($ndx);
    }

    /**
     * Nettoie les chaines de caractères
     */
    private function cleanString($inputString)
    {
        if (is_array($inputString)) {
            foreach ($inputString as $k => $v) {
                $t [$k] = $this->cleanString($v);
            }
            return $t;
        }

        $inputString = trim($inputString);
        $inputString = trim($inputString, '"');
        $inputString = trim($inputString, "'");
        $inputString = trim($inputString, '-/\\');
        $inputString = Ccsd_Tools::space_clean($inputString);
        /* @see http://stackoverflow.com/questions/4166896/trim-unicode-whitespace-in-php-5-2 */
        $inputString = preg_replace('/^[\pZ\pC]+|[\pZ\pC]+$/u', '', $inputString);
        $inputString = Ccsd_Tools_String::stripCtrlChars($inputString, '');
        return trim($inputString);
    }

    /**
     * index les titres en fonction de la langue
     * @param \Solarium\QueryType\Update\Query\Document\Document $ndx
     * @param string $rawTitle
     * @param string $language
     * @param boolean $langTitleSortAdded
     * @param array $titleArr
     * @return array
     */
    private function indexTitleByLanguage($ndx, $rawTitle, $language, $langTitleSortAdded, $titleArr)
    {

        $title = $this->cleanString($rawTitle);
        $titleArr [] = $title;
        // <=3 pour eviter les pbs
        if (($language != 'und') && ($title != "") && (strlen($language) <= 3)) {
            $ndx->addField($language . '_title_s', $title);
            $ndx->addField($language . '_title_t', $title);

            if ($langTitleSortAdded !== true) {
                $title = Ccsd_Tools_String::truncate($title, 100, '', false);
                $ndx->addField($language . '_title_sort', $title);
                $langTitleSortAdded = true;
            }

        }
        return [$ndx, $title, $langTitleSortAdded, $titleArr];
    }

    /**
     * indexation des sous-titres
     * @param string[] $subTitle
     */
    private function indexSubTitles($subTitle)
    {
        if ($subTitle) {
            $ndx = $this->getDoc();
            foreach ($subTitle as $language => $subtitle) {
                $subtitle = $this->cleanString($subtitle);
                if (($language != 'und') && ($subtitle != "")) {
                    $ndx->addField($language . '_subTitle_s', $subtitle);
                    $ndx->addField($language . '_subTitle_t', $subtitle);
                }
            }
            $this->setDoc($ndx);
        }
    }

    /**
     * Indexation des mots clés
     * @param array $allKeywords
     * @return boolean
     */
    private function indexKeywords($allKeywords)
    {

        if ($allKeywords == '') {
            return false;
        }
        $ndx = $this->getDoc();
        foreach ($allKeywords as $language => $keywordArray) {
            if (is_array($keywordArray)) {
                foreach ($keywordArray as $keyword) {

                    $keyword = $this->cleanKeywords($keyword);
                    $keyword = trim($keyword);
                    $keyword = Ccsd_Tools_String::utf8_ucfirst($keyword);

                    // cf schema pour keyword_s et keyword_t
                    $ndx->addField($language . '_keyword_s', $keyword);
                    $ndx->addField($language . '_keyword_t', $keyword);
                }
            }
        }
        $this->setDoc($ndx);
    }

    /**
     * Nettoie les mots clefs
     *
     * @param string $inputString
     * @return string
     */
    private function cleanKeywords($inputString)
    {
        $inputString = $this->cleanString($inputString);
        $utf8NeedleArray = [
            '"',
            '“',
            '”',
            '„',
            '«',
            '»',
            '‘',
            '¿',
            '§',
            '—',
            '_',
            '|',
            '(',
            '[',
            '{',
            '}',
            ']',
            ')',
            '#',
            '<',
            '>',
            ',',
            ';',
            ':',
            '*',
            '.'
        ];

        return str_replace($utf8NeedleArray, '', $inputString);
    }

    /**
     * Indexe les abstracts
     *
     * @param array $abstracts
     * @param string $docLanguage
     * @return boolean
     */
    private function indexAbstract($abstracts, $docLanguage)
    {
        if (!is_array($abstracts)) {
            return false;
        }


        if ((count($abstracts) > 1) && (array_key_exists($docLanguage, $abstracts))) {

            $abstractInDocLanguage = $this->cleanString($abstracts [$docLanguage]);
            $abstractInDocLanguage = Ccsd_Tools_String::truncate($abstractInDocLanguage, 30000, '[...]');

            $dataToIndex ['abstract_s'] [] = $abstractInDocLanguage;
            if ($docLanguage != 'und') {
                $dataToIndex [$docLanguage . '_abstract_s'] [] = $abstractInDocLanguage;
                $dataToIndex [$docLanguage . '_abstract_t'] [] = $abstractInDocLanguage;
            }

            unset($abstracts [$docLanguage]);
        }

        foreach ($abstracts as $language => $abstract) {
            $abstract = $this->cleanString($abstract);
            $abstract = Ccsd_Tools_String::truncate($abstract, 30000, '[...]');
            $dataToIndex ['abstract_s'] [] = $abstract;
            if ($language != 'und') {
                $dataToIndex [$language . '_abstract_s'] [] = $abstract;
                $dataToIndex [$language . '_abstract_t'] [] = $abstract;
            }
        }

        $this->addArrayOfMetaToDoc($dataToIndex);
    }

    /**
     * Indexe les conferences
     *
     * @param array $meta
     * @return boolean
     */
    private function indexConference($meta)
    {
        /** TODO: BM: Mais si pas de title, rien ne devrait etre fait non ?
         * Comme les bases sont tres disparate, on prends pas le risque, on indexe ce qui est present!
         */
        $dataToIndex = [
            'title_s' => $this->existsValue($meta, 'conferenceTitle', ''),
            'organizer_s' => $this->existsValue($meta, 'conferenceOrganizer', '')
        ];

        if (isset($meta ['conferenceStartDate'])) {
            $dataToIndex ['startDate_s'] = $this->existsValue($meta, 'conferenceStartDate', '');
            $dataToIndex ['startDate_tdate'] = Ccsd_Tools_String::stringToIso8601($meta ['conferenceStartDate']);
        }

        if (isset($dataToIndex ['startDate_s'])) {
            $conferenceStartDateArr = $this->exploseDate($dataToIndex ['startDate_s']);

            $year = (int)$conferenceStartDateArr [0];
            if ($year != 0) {
                $dataToIndex ['startDateY_i'] = (int)$conferenceStartDateArr [0];
                $dataToIndex ['startDateM_i'] = (int)$conferenceStartDateArr [1];
                $dataToIndex ['startDateD_i'] = (int)$conferenceStartDateArr [2];
            }
        }

        if (isset($meta ['conferenceEndDate'])) {
            $dataToIndex ['endDate_s'] = $this->existsValue($meta, 'conferenceEndDate', '');
            $dataToIndex ['endDate_tdate'] = Ccsd_Tools_String::stringToIso8601($meta ['conferenceEndDate']);
        }

        if (isset($dataToIndex ['endDate_s'])) {
            $conferenceEndDateArr = $this->exploseDate($dataToIndex ['endDate_s']);
            $year = (int)$conferenceEndDateArr [0];
            if ($year != 0) {
                $dataToIndex ['endDateY_i'] = (int)$conferenceEndDateArr [0];
                $dataToIndex ['endDateM_i'] = (int)$conferenceEndDateArr [1];
                $dataToIndex ['endDateD_i'] = (int)$conferenceEndDateArr [2];
            }
        }
        $this->addArrayOfMetaToDoc($dataToIndex, 'conference');
    }

    /**
     * @param $array
     * @param $key
     * @param $default
     * @return mixed
     */
    private function existsValue($array, $key, $default)
    {
        return isset($array[$key]) ? $array[$key] : $default;
    }

    /**
     * @param string $date
     * @return string[]
     */
    private function exploseDate($date)
    {
        /** Date au format YYYY[-MM[-[DD[ HH:MM ...]]]] */
        $dateWhithoutSecond = substr($date, 0, 10);
        $dateWithAtLeast2Minus = $dateWhithoutSecond . '--';
        return explode('-', $dateWithAtLeast2Minus);
    }

    /**
     * Indexe les metadonnées des revues
     * @param Ccsd_Referentiels_Journal $journal
     * @return bool
     */
    private function indexJournal($journal)
    {
        if (!$journal) {
            return false;
        }


        $journal = $journal->getData();

        $dataToIndex = [
            'id_i' => $journal ["JID"],
            'title_s' => $journal ["JNAME"],
            'idTitle_fs' => $journal ["JID"] . parent::SOLR_FACET_SEPARATOR . $journal ["JNAME"],
            'titleId_fs' => $journal ["JNAME"] . parent::SOLR_FACET_SEPARATOR . $journal ["JID"],
            'titleAbbr_s' => $journal ["SHORTNAME"],
            'publisher_s' => $journal ["PUBLISHER"],
            'issn_s' => $journal ["ISSN"],
            'eissn_s' => $journal ["EISSN"],
            'doiRoot_s' => $journal ["ROOTDOI"],
            'url_s' => $journal ["URL"],
            'date_s' => $journal ["DATEMODIF"],
            'valid_s' => $journal ["VALID"],
            'sherpaColor_s' => $journal ["SHERPA_COLOR"],
            'sherpaPrePrint_s' => $journal ["SHERPA_PREPRINT"],
            'sherpaPostPrint_s' => $journal ["SHERPA_POSTPRINT"],
            'sherpaPreRest_s' => $journal ["SHERPA_PRE_REST"],
            'sherpaPostRest_s' => $journal ["SHERPA_POST_REST"],
            'sherpaCondition_s' => $journal ["SHERPA_COND"],
            'sherpaDate_s' => $journal ["SHERPA_DATE"]
        ];

        $this->addArrayOfMetaToDoc($dataToIndex, 'journal');
        return true;

    }

    /**
     *
     * @param array $meta
     * @return boolean
     */
    private function indexEuropeanProject($meta)
    {
        if ($meta == '') {
            return false;
        }

        foreach ($meta as $europeanProject) {
            if (!$europeanProject) {
                continue;
            }
            $europeanProject = $europeanProject->getData();

            $dataToIndex = [
                'id_i' => $europeanProject ["PROJEUROPID"],
                'title_s' => $europeanProject ["TITRE"],
                'acronym_s' => $europeanProject ["ACRONYME"],
                'reference_s' => $europeanProject ["NUMERO"],
                'callId_s' => $europeanProject ["CALLID"],
                'financing_s' => $europeanProject ["FUNDEDBY"],
                'startDate_s' => $europeanProject ["SDATE"],
                'endDate_s' => $europeanProject ["EDATE"],
                'valid_s' => $europeanProject ["VALID"],
                'idTitle_fs' => $europeanProject ["PROJEUROPID"] . parent::SOLR_FACET_SEPARATOR . $europeanProject ["TITRE"],
                'titleId_fs' => $europeanProject ["TITRE"] . parent::SOLR_FACET_SEPARATOR . $europeanProject ["PROJEUROPID"]
            ];

            $this->addArrayOfMetaToDoc($dataToIndex, 'europeanProject');
        }
    }

    /**
     * Indexe les projets ANR
     *
     * @param array $meta
     * @return boolean
     */
    private function indexAnrProject($meta)
    {
        if ($meta == '') {
            return false;
        }

        foreach ($meta as $projetAnr) {
            $dataToIndex = [];

            if (!$projetAnr) {
                continue;
            }

            $projetAnr = $projetAnr->getData();

            $dataToIndex = [
                'id_i' => $projetAnr ["ANRID"],
                'title_s' => $projetAnr ["TITRE"],
                'acronym_s' => $projetAnr ["ACRONYME"],
                'reference_s' => $projetAnr ["REFERENCE"],
                'callTitle_s' => $projetAnr ["INTITULE"],
                'callAcronym_s' => $projetAnr ["ACROAPPEL"],
                'yearDate_s' => $projetAnr ["ANNEE"],
                'yearDate_tdate' => Ccsd_Tools_String::stringToIso8601($projetAnr ["ANNEE"]),
                'valid_s' => $projetAnr ["VALID"],
                'idTitle_fs' => $projetAnr ["ANRID"] . parent::SOLR_FACET_SEPARATOR . $projetAnr ["TITRE"],
                'titleId_fs' => $projetAnr ["TITRE"] . parent::SOLR_FACET_SEPARATOR . $projetAnr ["ANRID"]
            ];

            $this->addArrayOfMetaToDoc($dataToIndex, 'anrProject');
        }
    }

    /**
     * Indexation des auteurs
     * @param array $authorsList
     * @param array $structuresList
     * @return bool
     */
    private function indexAuthors($authorsList, $structuresList)
    {

        if ($authorsList == '') {
            return false;
        }

        foreach ($authorsList as $n => $author) {

            /* @var $author Hal_Document_Author */

            //$author = array_map('Ccsd_Tools_String::stripCtrlChars', $author);

            if ($author->getOrganismId() == 0) {
                $author->setOrganismid(''); // sinon 0 sera indexé
            }

            // 1ere lettre nom famille
            $lastnameFirstLetter = $this->getLastnameFirstLetter($author);
            // 1ere lettre nom famille forme valide éventuelle
            $lastnameFirstLetter_valid = $this->getLastnameFirstLetter($author, true);

            // Nom Prénom
            $lastNameFirstName = $this->getLastnameFirstname($author);
            // Nom Prénom de la forme valide éventuelle
            $lastNameFirstName_valid = $this->getLastnameFirstname($author, true);
            // Prénom Nom de la forme valide éventuelle
            $firstNameLastName_valid = $this->getFirstnameLastname($author, true);

            $authorid = $author->getAuthorid();
            $fullname = $author->getFullname();

            $dataToIndex = [
                'id_i' => $author->getAuthorid(),
                'lastName_s' => $author->getLastname(),
                'firstName_s' => $author->getFirstname(),
                'middleName_s' => $author->getOthername(),
                'fullName_s' => $fullname,
                'lastNameFirstName_s' => $lastNameFirstName,
                'idLastNameFirstName_fs' => $authorid . parent::SOLR_FACET_SEPARATOR . $lastNameFirstName,
                'alphaLastNameFirstNameId_fs' => $lastnameFirstLetter . parent::SOLR_ALPHA_SEPARATOR . $lastNameFirstName . parent::SOLR_FACET_SEPARATOR . $authorid,
                'idFullName_fs' => $authorid . parent::SOLR_FACET_SEPARATOR . $fullname,
                'fullNameId_fs' => $fullname . parent::SOLR_FACET_SEPARATOR . $authorid,
                'email_s' => Hal_Document_Author::getEmailHashed($author->getEmail(), 'md5'),
                'emailDomain_s' => Hal_Document_Author::getDomainFromEmail($author->getEmail()),
                'organism_s' => $author->getOrganism(),
                'organismId_i' => $author->getOrganismId(),
                'quality_s' => $author->getQuality()
            ];

            if ($n == 0) {
                // Champ auteur pour le Tri
                $dataToIndex ['_sort'] = trim($author->getLastname()); // tri par premier auteur
            }

            if ($author->getIdHal() != '0') {
                $dataToIndex ['idHal_i'] = $author->getIdHal();
                $dataToIndex ['idHal_s'] = $author->getIdhalstring();
            }

            $idhalstring = $author->getIdhalstring();
            $dataToIndex ['idHalFullName_fs'] = $idhalstring . parent::SOLR_FACET_SEPARATOR . $firstNameLastName_valid;
            $dataToIndex ['fullNameIdHal_fs'] = $firstNameLastName_valid . parent::SOLR_FACET_SEPARATOR . $idhalstring;
            $dataToIndex ['alphaLastNameFirstNameIdHal_fs'] = $lastnameFirstLetter_valid . parent::SOLR_ALPHA_SEPARATOR . $lastNameFirstName_valid . parent::SOLR_FACET_SEPARATOR . $idhalstring;

            if (count($author->getIdsAuthor()) > 0) {

                try {
                    $servUrl = Zend_Registry::get('extServers');
                } catch (Zend_Exception $e) {
                    $c = new Hal_Cv ();
                    $c->getServerExt();
                    $servUrl = $c->getServerUrl();
                    Zend_Registry::set('extServers', $servUrl);
                }

                foreach ($author->getIdsAuthor() as $serviceName => $idExt) {

                    $serviceName = strtolower($serviceName);
                    $extIdOnly = str_replace($servUrl, '', $idExt);

                    $serviceName = strtolower($serviceName);
                    $dataToIndex [$serviceName . 'IdExt_s'] = $idExt;
                    $dataToIndex [$serviceName . 'IdExt_id'] = $extIdOnly;
                }
            }

            $this->addArrayOfMetaToDoc($dataToIndex, 'auth');
            $this->addArrayOfMetaToDoc(['structId_i' => $author->getStructid()], 'auth');

            if (is_array($structuresList)) {
                foreach ($author->getStructid() as $structId) {
                    /* @var $structure Hal_Document_Structure */
                    foreach ($structuresList as $structure) {
                        if ($structure->getStructid() == $structId) {
                            $this->indexAuthorInStructures($author, $structure);
                        }
                    }
                }
            }


        }
        return true;
    }

    /** Retourne la première lettre du nom de famille
     * selon la forme valide si elle existe associée à un idhal
     * @param Hal_Document_Author $author
     * @param bool $useAuthorValidForm
     * @return string
     */
    private function getLastnameFirstLetter($author, $useAuthorValidForm = false)
    {

        if (($useAuthorValidForm) && ($author->getLastname_valid() != '')) {
            return Ccsd_Tools_String::getAlphaLetter($author->getLastname_valid());
        }
        return Ccsd_Tools_String::getAlphaLetter($author->getLastname());

    }

    /**
     * @param Hal_Document_Author $author
     * @param bool $useAuthorValidForm
     * @return string
     */
    private function getLastnameFirstname($author, $useAuthorValidForm = false)
    {

        if (($useAuthorValidForm) && ($author->getLastname_valid() != '') && ($author->getFirstname_valid() != '')) {
            return Ccsd_Tools::formatAuthor($author->getLastname_valid(), $author->getFirstname_valid());
        }
        return Ccsd_Tools::formatAuthor($author->getLastname(), $author->getFirstname());
    }

    /**
     * @param Hal_Document_Author $author
     * @param bool $useAuthorValidForm
     * @return string
     */
    private function getFirstnameLastname($author, $useAuthorValidForm = false)
    {
        if (($useAuthorValidForm) && ($author->getLastname_valid() != '') && ($author->getFirstname_valid() != '')) {
            return Ccsd_Tools::formatAuthor($author->getFirstname_valid(), $author->getLastname_valid());
        }
        return Ccsd_Tools::formatAuthor($author->getFirstname(), $author->getLastname());
    }

    /**
     * Indexe l'association auteur / structures et structures parentes
     *
     * @param Hal_Document_Author $author
     * @param Hal_Document_Structure[]|Hal_Document_Structure $structure
     * @param int $child
     */
    private function indexAuthorInStructures($author, $structure, $child = null)
    {
        if ($structure == null) {
            return;
        }

        //$author = array_map('Ccsd_Tools_String::stripCtrlChars', $author);


        // 1ere lettre nom famille
        $lastnameFirstLetter = $this->getLastnameFirstLetter($author);
        // 1ere lettre nom famille forme valide éventuelle
        $lastnameFirstLetter_valid = $this->getLastnameFirstLetter($author, true);

        // Nom Prénom
        $lastNameFirstName = $this->getLastnameFirstname($author);
        // Nom Prénom de la forme valide éventuelle
        $lastNameFirstName_valid = $this->getLastnameFirstname($author, true);


        $authorid = $author->getAuthorid();
        $fullname = $author->getFullname();
        $idhalstring = $author->getIdhalstring();


        if ($child != null) {
            $ndx = $this->getDoc();

            /* @var $parentStructure Hal_Document_Structure */

            foreach ($structure as $parentStructure) {

                $parentStructure = $parentStructure ['struct'];

                $parentStructId = $parentStructure->getStructid();
                $parentStructName = $parentStructure->getStructname();

                $autIdHasStructure_s = $authorid . parent::SOLR_FACET_SEPARATOR . $fullname . parent::SOLR_JOIN_SEPARATOR . $parentStructId . parent::SOLR_FACET_SEPARATOR . $parentStructName;
                $structHasAuthId_s = $parentStructId . parent::SOLR_FACET_SEPARATOR . $parentStructName . parent::SOLR_JOIN_SEPARATOR . $authorid . parent::SOLR_FACET_SEPARATOR . $fullname;
                $structHasAuthIdHal_s = $parentStructId . parent::SOLR_FACET_SEPARATOR . $parentStructName . parent::SOLR_JOIN_SEPARATOR . $idhalstring . parent::SOLR_FACET_SEPARATOR . $lastNameFirstName;

                $structHasAlphaAuthId_s = $lastnameFirstLetter . parent::SOLR_ALPHA_SEPARATOR . $parentStructId . parent::SOLR_FACET_SEPARATOR . $parentStructName . parent::SOLR_JOIN_SEPARATOR . $authorid . parent::SOLR_FACET_SEPARATOR . $lastNameFirstName;
                $structHasAlphaAuthIdHal_s = $lastnameFirstLetter_valid . parent::SOLR_ALPHA_SEPARATOR . $parentStructId . parent::SOLR_FACET_SEPARATOR . $parentStructName . parent::SOLR_JOIN_SEPARATOR . $idhalstring . parent::SOLR_FACET_SEPARATOR . $lastNameFirstName_valid;

                $ndx->addField('authIdHasStructure_fs', Ccsd_Tools_String::stripCtrlChars($autIdHasStructure_s));
                $ndx->addField('structHasAuthId_fs', Ccsd_Tools_String::stripCtrlChars($structHasAuthId_s));

                $ndx->addField('structHasAuthIdHal_fs', Ccsd_Tools_String::stripCtrlChars($structHasAuthIdHal_s));

                $ndx->addField('structHasAlphaAuthId_fs', Ccsd_Tools_String::stripCtrlChars($structHasAlphaAuthId_s));
                $ndx->addField('structHasAlphaAuthIdHal_fs', Ccsd_Tools_String::stripCtrlChars($structHasAlphaAuthIdHal_s));


                if (count($parentStructure->getParents()) != 0) {
                    $this->indexAuthorInStructures($author, $parentStructure->getParents(), $child = $parentStructId);
                }
            }
            $this->setDoc($ndx);
        } else {

            // Cas du laboratoire principal

            $structId = $structure->getStructid();
            $structName = $structure->getStructname();

            $autIdHasStructure_s = $authorid . parent::SOLR_FACET_SEPARATOR . $fullname . parent::SOLR_JOIN_SEPARATOR . $structId . parent::SOLR_FACET_SEPARATOR . $structName;
            $structHasAuthId_s = $structId . parent::SOLR_FACET_SEPARATOR . $structName . parent::SOLR_JOIN_SEPARATOR . $authorid . parent::SOLR_FACET_SEPARATOR . $fullname;
            $structHasAuthIdHal_s = $structId . parent::SOLR_FACET_SEPARATOR . $structName . parent::SOLR_JOIN_SEPARATOR . $idhalstring . parent::SOLR_FACET_SEPARATOR . $lastNameFirstName_valid;

            $structHasAlphaAuthId_s = $lastnameFirstLetter . parent::SOLR_ALPHA_SEPARATOR . $structId . parent::SOLR_FACET_SEPARATOR . $structName . parent::SOLR_JOIN_SEPARATOR . $authorid . parent::SOLR_FACET_SEPARATOR . $lastNameFirstName;
            $structHasAlphaAuthIdHal_s = $lastnameFirstLetter_valid . parent::SOLR_ALPHA_SEPARATOR . $structId . parent::SOLR_FACET_SEPARATOR . $structName . parent::SOLR_JOIN_SEPARATOR . $idhalstring . parent::SOLR_FACET_SEPARATOR . $lastNameFirstName_valid;

            $ndx = $this->getDoc();
            $ndx->addField('authIdHasPrimaryStructure_fs', Ccsd_Tools_String::stripCtrlChars($autIdHasStructure_s));
            $ndx->addField('structPrimaryHasAuthId_fs', Ccsd_Tools_String::stripCtrlChars($structHasAuthId_s));
            $ndx->addField('structPrimaryHasAuthIdHal_fs', Ccsd_Tools_String::stripCtrlChars($structHasAuthIdHal_s));

            $ndx->addField('structPrimaryHasAlphaAuthId_fs', Ccsd_Tools_String::stripCtrlChars($structHasAlphaAuthId_s));
            $ndx->addField('structPrimaryHasAlphaAuthIdHal_fs', Ccsd_Tools_String::stripCtrlChars($structHasAlphaAuthIdHal_s));

            $ndx->addField('authIdHasStructure_fs', Ccsd_Tools_String::stripCtrlChars($autIdHasStructure_s));
            $ndx->addField('structHasAuthId_fs', Ccsd_Tools_String::stripCtrlChars($structHasAuthId_s));
            $ndx->addField('structHasAuthIdHal_fs', Ccsd_Tools_String::stripCtrlChars($structHasAuthIdHal_s));

            $ndx->addField('structHasAlphaAuthId_fs', Ccsd_Tools_String::stripCtrlChars($structHasAlphaAuthId_s));
            $ndx->addField('structHasAlphaAuthIdHal_fs', Ccsd_Tools_String::stripCtrlChars($structHasAlphaAuthIdHal_s));
            $this->setDoc($ndx);

            if (count($structure->getParents()) != 0) {
                $this->indexAuthorInStructures($author, $structure->getParents(), $child = $structId);
            }
        }
    }

    /**
     * Indexe les structures et leurs parents
     * @param array $structureArr
     * @return bool
     */
    private function indexStructures($structureArr)
    {
        if ($structureArr == '') {
            return false;
        }

        /* @var $structure Hal_Document_Structure */

        foreach ($structureArr as $structure) {

            $dataToIndex = $this->getStructureDataForIndex($structure);
            $this->addArrayOfMetaToDoc($dataToIndex, self::PREFIX_STRUCTURES);

            /**
             * Traitement des parents
             */
            if ($structure->hasParent() != 0) {

                /* @var $parent Hal_Document_Structure */
                /* @var $parentArrLevel0 Hal_Document_Structure */
                /* @var $parentArrLevel1 Hal_Document_Structure */
                /* @var $parentArrLevel2 Hal_Document_Structure */

                foreach ($structure->getParents() as $parent) {

                    $childCode = $parent ["code"];
                    if ($childCode != '') {
                        // code de labo pour enfant
                        $this->addArrayOfMetaToDoc(['code_s' => $childCode], self::PREFIX_STRUCTURES);
                    }

                    // indexe les parents
                    $parentArrLevel0 = '';
                    $parentArrLevel0 = $parent ['struct'];

                    $dataToIndexParent0 = $this->getStructureDataForIndex($parentArrLevel0);
                    $this->addArrayOfMetaToDoc($dataToIndexParent0, self::PREFIX_STRUCTURES);


                    $this->indexStructuresParentChild($parentArrLevel0, $structure, $childCode);

                    if ($parentArrLevel0->hasParent() == 0) {
                        continue;
                    }

                    foreach ($parentArrLevel0->getParents() as $parent1) {

                        $childCode0 = $parent1 ["code"];
                        if ($childCode0 != '') {
                            // code de labo pour enfant
                            $this->addArrayOfMetaToDoc(['code_s' => $childCode0], self::PREFIX_STRUCTURES);
                        }

                        // indexe les parents
                        $parentArrLevel1 = '';
                        $parentArrLevel1 = $parent1 ['struct'];

                        $dataToIndexParent1 = $this->getStructureDataForIndex($parentArrLevel1);
                        $this->addArrayOfMetaToDoc($dataToIndexParent1, self::PREFIX_STRUCTURES);
                        $this->indexStructuresParentChild($parentArrLevel1, $parentArrLevel0, $childCode0);

                        if ($parentArrLevel1->hasParent() == 0) {
                            continue;
                        }

                        foreach ($parentArrLevel1->getParents() as $parent2) {

                            $childCode1 = $parent2 ["code"];
                            if ($childCode1 != '') {
                                // code de labo pour enfant
                                $this->addArrayOfMetaToDoc(['code_s' => $childCode1], self::PREFIX_STRUCTURES);
                            }

                            // indexe les parents
                            $parentArrLevel2 = $parent2 ['struct'];
                            $dataToIndexParent2 = $this->getStructureDataForIndex($parentArrLevel2);
                            $this->addArrayOfMetaToDoc($dataToIndexParent2, self::PREFIX_STRUCTURES);
                            $this->indexStructuresParentChild($parentArrLevel2, $parentArrLevel1, $childCode1);
                            unset($parentArrLevel2);
                        }
                    }
                }
            }
        }
        return true;
    }

    /**
     * Formatte les données d'une structure pour l'indexation
     * @param Hal_Document_Structure $structure
     * @return array
     */
    private function getStructureDataForIndex($structure)
    {

        $structName = $structure->getStructname();
        $structName = str_replace('/', '|', $structName);
        $structFirstLetter = Ccsd_Tools_String::getAlphaLetter($structName);
        $structId = $structure->getStructid();

        $dataToIndex = [
            'id_i' => $structId,
            'idName_fs' => $structId . parent::SOLR_FACET_SEPARATOR . $structName,
            'nameId_fs' => $structFirstLetter . parent::SOLR_ALPHA_SEPARATOR . $structName . parent::SOLR_FACET_SEPARATOR . $structId,
            'name_fs' => $structFirstLetter . parent::SOLR_ALPHA_SEPARATOR . $structName,
            'acronym_s' => $structure->getSigle(),
            'name_s' => $structName,
            'address_s' => $structure->getAddress(),
            'country_s' => $structure->getPaysid(),
            'type_s' => $structure->getTypestruct(),
            'valid_s' => $structure->getValid()
        ];


        if (is_array($structure->getIdextLink())) {
            foreach ($structure->getIdextLink() as $domain => $idext) {
                $domain = ucfirst(strtolower($domain));
                $dataToIndex[$domain . 'IdExt_id'] = $idext['id'];
                $dataToIndex[$domain . 'IdExt_s'] = $idext['id'];
                $dataToIndex[$domain . 'IdExtUrl_s'] = $idext['url'];
            }
        }

        switch ($structure->getTypestruct()) {

            case Ccsd_Referentiels_Structure::TYPE_REGROUPINSTITUTION:
                $this->addArrayOfMetaToDoc($dataToIndex, self::PREFIX_RGRP_INST);
                break;
            case Ccsd_Referentiels_Structure::TYPE_REGROUPLABORATORY:
                $this->addArrayOfMetaToDoc($dataToIndex, self::PREFIX_RGRP_LAB);
                break;
            case Ccsd_Referentiels_Structure::TYPE_INSTITUTION :
                $this->addArrayOfMetaToDoc($dataToIndex, self::PREFIX_INST_STRUCT);
                break;
            case Ccsd_Referentiels_Structure::TYPE_LABORATORY :
                $this->addArrayOfMetaToDoc($dataToIndex, self::PREFIX_LAB_STRUCT);
                break;
            case Ccsd_Referentiels_Structure::TYPE_DEPARTMENT :
                $this->addArrayOfMetaToDoc($dataToIndex, self::PREFIX_DEPT_STRUCT);
                break;
            case Ccsd_Referentiels_Structure::TYPE_RESEARCHTEAM :
                $this->addArrayOfMetaToDoc($dataToIndex, self::PREFIX_RTEAM_STRUCT);
                break;
            default:
                Ccsd_Log::message('Type de structure inconnu : ' . $structure->getTypestruct(), true, 'ERR', $this->getLogFilename());
                break;
        }

        return $dataToIndex;
    }

    /**
     * Indexation structures Parent - enfant
     * @param Hal_Document_Structure $parent
     * @param Hal_Document_Structure $child
     * @param string $childCode
     * @return bool
     */
    private function indexStructuresParentChild($parent, $child, $childCode = '')
    {
        if (($parent == '') || ($child == null)) {
            return false;
        }

        if (($parent->getStructid() == '') || ($child->getStructid() == null)) {
            return false;
        }

        $childStructName = $child->getStructname();
        $childFirstLetter = Ccsd_Tools_String::getAlphaLetter($childStructName);

        $ndx = $this->getDoc();

        $childStructId = $child->getStructid();
        $parentStructId = $parent->getStructid();
        $childStructTypeStruct = $child->getTypestruct();
        $parentStructName = $parent->getStructname();
        $ndx->addField('structIsChildOf_fs', $childStructId . '_' . $childStructTypeStruct . parent::SOLR_JOIN_SEPARATOR . $parentStructId . parent::SOLR_FACET_SEPARATOR . $parentStructName);
        $this->setDoc($ndx);

        /**
         * jointure des enfants <=> parents
         */
        $structIsChildOf = $childStructId . '_' . $childStructName . parent::SOLR_JOIN_SEPARATOR . $parentStructId . parent::SOLR_FACET_SEPARATOR . $parentStructName;

        $structIsParentOfType = $parentStructId;
        $structIsParentOfType .= parent::SOLR_JOIN_SEPARATOR;
        $structIsParentOfType .= $childStructTypeStruct;
        $structIsParentOfType .= '_' . $childFirstLetter;
        $structIsParentOfType .= parent::SOLR_ALPHA_SEPARATOR . $childStructId;
        $structIsParentOfType .= parent::SOLR_FACET_SEPARATOR . $childStructName;

        $structIsParentOf = $parentStructId;
        $structIsParentOf .= parent::SOLR_JOIN_SEPARATOR . $childFirstLetter;
        $structIsParentOf .= parent::SOLR_ALPHA_SEPARATOR . $childStructId;
        $structIsParentOf .= parent::SOLR_FACET_SEPARATOR . $childStructName;


        if ($childCode != '') {
            //$structIsParentOf .= ' [' . $childCode . ']';
            //$structIsParentOfType .= ' [' . $childCode . ']';

            $this->addArrayOfMetaToDoc([
                'code_s' => $childCode
            ], self::PREFIX_STRUCTURES);
        }

        $ndx->addField('structIsParentOf_fs', $structIsParentOf);
        $ndx->addField('structIsParentOfType_fs', $structIsParentOfType);

        $childToIndex ['isChildOf_fs'] = $structIsChildOf;

        switch ($childStructTypeStruct) {

            case Ccsd_Referentiels_Structure::TYPE_REGROUPINSTITUTION:
                $this->addArrayOfMetaToDoc($childToIndex, self::PREFIX_RGRP_INST);
                break;
            case Ccsd_Referentiels_Structure::TYPE_REGROUPLABORATORY:
                $this->addArrayOfMetaToDoc($childToIndex, self::PREFIX_RGRP_LAB);
                break;
            case Ccsd_Referentiels_Structure::TYPE_INSTITUTION:
                $this->addArrayOfMetaToDoc($childToIndex, self::PREFIX_INST_STRUCT);
                break;
            case Ccsd_Referentiels_Structure::TYPE_LABORATORY :
                $this->addArrayOfMetaToDoc($childToIndex, self::PREFIX_LAB_STRUCT);
                break;
            case Ccsd_Referentiels_Structure::TYPE_DEPARTMENT :
                $this->addArrayOfMetaToDoc($childToIndex, self::PREFIX_DEPT_STRUCT);
                break;
            case Ccsd_Referentiels_Structure::TYPE_RESEARCHTEAM :
                $this->addArrayOfMetaToDoc($childToIndex, self::PREFIX_RTEAM_STRUCT);
                break;
            default:
                Ccsd_Log::message('Type de structure inconnu : ' . $childStructTypeStruct, true, 'ERR', $this->getLogFilename());
                break;
        }

        $this->addArrayOfMetaToDoc($childToIndex, self::PREFIX_STRUCTURES);

        $parentToIndex ['isParentOf_fs'] = $structIsParentOf;

        switch ($parent->getTypestruct()) {

            case Ccsd_Referentiels_Structure::TYPE_REGROUPINSTITUTION:
                $this->addArrayOfMetaToDoc($parentToIndex, self::PREFIX_RGRP_INST);
                break;
            case Ccsd_Referentiels_Structure::TYPE_REGROUPLABORATORY:
                $this->addArrayOfMetaToDoc($parentToIndex, self::PREFIX_RGRP_LAB);
                break;
            case Ccsd_Referentiels_Structure::TYPE_INSTITUTION:
                $this->addArrayOfMetaToDoc($parentToIndex, self::PREFIX_INST_STRUCT);
                break;
            case Ccsd_Referentiels_Structure::TYPE_LABORATORY :
                $this->addArrayOfMetaToDoc($parentToIndex, self::PREFIX_LAB_STRUCT);
                break;
            case Ccsd_Referentiels_Structure::TYPE_DEPARTMENT :
                $this->addArrayOfMetaToDoc($parentToIndex, self::PREFIX_DEPT_STRUCT);
                break;
            case Ccsd_Referentiels_Structure::TYPE_RESEARCHTEAM :
                $this->addArrayOfMetaToDoc($parentToIndex, self::PREFIX_RTEAM_STRUCT);
                break;
            default:
                Ccsd_Log::message('Type de structure inconnu : ' . $parent->getTypestruct(), true, 'ERR', $this->getLogFilename());
                break;
        }
        return true;
    }

    /**
     * Indexe le contributeur d'un document
     * @param array $contributor
     * @return boolean
     */
    private function indexContributor($contributor)
    {
        if ($contributor == '') {
            return false;
        }
        $dataToIndex = [
            'id_i' => $contributor ['uid'],
            'fullName_s' => $contributor ['fullname'],
            'idFullName_fs' => $contributor ['uid'] . parent::SOLR_FACET_SEPARATOR . $contributor ['fullname'],
            'fullNameId_fs' => $contributor ['fullname'] . parent::SOLR_FACET_SEPARATOR . $contributor ['uid']
        ];

        $this->addArrayOfMetaToDoc($dataToIndex, 'contributor');
    }

    /**
     * Indexation de dates
     * @param string $date
     * @param string $solrFieldName
     * @param boolean $toIso8601
     * @return bool
     */
    private function indexDates($date = null, $solrFieldName, $toIso8601 = false)
    {
        if ($date == null) {
            return false;
        }

        if ($toIso8601 == true) {
            $dataToIndex[$solrFieldName . '_tdate'] = Ccsd_Tools_String::stringToIso8601($date);
        } else {
            $dataToIndex[$solrFieldName . '_tdate'] = str_ireplace(' ', 'T', $date) . 'Z';
        }

        $dataToIndex [$solrFieldName . '_s'] = $date;
        $dateArr = $this->exploseDate($date);

        if ((int)$dateArr [0] != 0) {
            $dataToIndex [$solrFieldName . 'Y_i'] = (int)$dateArr [0];
            $dataToIndex [$solrFieldName . 'M_i'] = (int)$dateArr [1];
            $dataToIndex [$solrFieldName . 'D_i'] = (int)$dateArr [2];
        }

        $this->addArrayOfMetaToDoc($dataToIndex);
        return true;
    }

    /**
     * Indexe les collections d'un document
     *
     * @param array $arrayOfCollections
     * @param Hal_Site_Collection $child
     * @param array $arrayOfDocumentCollSid
     * @return array
     */
    private function indexCollections(array $arrayOfCollections, $child = null, $arrayOfDocumentCollSid = [])
    {

        /* @var $collection Hal_Site_Collection */

        foreach ($arrayOfCollections as $collection) {

            // on garde en mémoire les SID du documents avant d'indexer des éventuels liens de parenté
            // on n'indexe que si le parent est dans les tampons

            if (!$collection instanceof Hal_Site_Collection) {
                continue;
            }


            if (in_array($collection->getSid(), $arrayOfDocumentCollSid)) {


                $ndx = $this->getDoc();

                $ndx->addField('collId_i', $collection->getSid());
                $ndx->addField('collName_s', $collection->getFullname());
                $ndx->addField('collCode_s', $collection->getSiteName());
                $ndx->addField('collCategory_s', $collection->getCategory());

                $ndx->addField('collIdName_fs', $collection->getSid() . parent::SOLR_FACET_SEPARATOR . $collection->getFullname());
                $ndx->addField('collNameId_fs', $collection->getFullname() . parent::SOLR_FACET_SEPARATOR . $collection->getSid());


                $ndx->addField('collCodeName_fs', $collection->getSiteName() . parent::SOLR_FACET_SEPARATOR . $collection->getFullname());
                $ndx->addField('collCategoryCodeName_fs', $collection->getCategory() . parent::SOLR_JOIN_SEPARATOR . $collection->getSiteName() . parent::SOLR_FACET_SEPARATOR . $collection->getFullname());


                $ndx->addField('collNameCode_fs', $collection->getFullname() . parent::SOLR_FACET_SEPARATOR . $collection->getSiteName());

                if ($child != null) {
                    $ndx->addField('collIsParentOfColl_fs', $collection->getSiteName() . parent::SOLR_FACET_SEPARATOR . $collection->getFullname() . parent::SOLR_JOIN_SEPARATOR . $child->getSiteName() . parent::SOLR_FACET_SEPARATOR . $child->getFullname());
                    $ndx->addField('collIsParentOfCategoryColl_fs', $collection->getSiteName() . parent::SOLR_FACET_SEPARATOR . $child->getCategory() . parent::SOLR_JOIN_SEPARATOR . $child->getSiteName() . parent::SOLR_FACET_SEPARATOR . $child->getFullname());
                    $ndx->addField('collIsChildOfColl_fs', $child->getSiteName() . parent::SOLR_FACET_SEPARATOR . $child->getFullname() . parent::SOLR_JOIN_SEPARATOR . $collection->getSiteName() . parent::SOLR_FACET_SEPARATOR . $collection->getFullname());
                }


                $this->setDoc($ndx);

                if (count($collection->getParents()) != 0) {
                    $this->indexCollections($collection->getParents(), $collection, $arrayOfDocumentCollSid);
                }
            }
        }


        return $arrayOfDocumentCollSid;
    }

    /**
     * Indexe les fichiers et le texte Intégral des PDF
     * @return bool
     */
    private function indexFiles()
    {
        $docObj = $this->getHalDocument();

        if ($docObj->getFiles() == '') {
            return false;
        }
        $ndx = $this->getDoc();

        foreach ($docObj->getFiles() as $file) {
            if (!$file instanceof Hal_Document_File) {
                continue;
            }

            $fileTypes[] = $file->getType();

            if (($file->getTypeMIME() == 'application/pdf') && ($file->canRead())) {
                $pdfFileList [] = $file->getPath();
            }


            if ($file->getType() == 'file') {
                // fichier principal
                if ($file->getDefault() == '1') {
                    $ndx->fileMain_s = $docObj->getUri() . '/document';
                }
                //autres fichiers
                $ndx->addField('files_s', $docObj->getUri() . '/file/' . rawurlencode($file->getName()));
            } elseif ($file->getType() == 'annex') {
                //annexe principale
                if ($file->getDefaultannex() == '1') {
                    $ndx->fileMainAnnex_s = $docObj->getUri() . '/file/' . rawurlencode($file->getName());
                }
                //autres annexes
                $ndx->addField('fileAnnexes_s', $docObj->getUri() . '/file/' . rawurlencode($file->getName()));

                // annexes répétées par format
                switch ($file->getFormat()) {
                    case 'figure':
                        $ndx->addField('fileAnnexesFigure_s', $docObj->getUri() . '/file/' . rawurlencode($file->getName()));
                        break;
                    case 'audio':
                        $ndx->addField('fileAnnexesAudio_s', $docObj->getUri() . '/file/' . rawurlencode($file->getName()));
                        break;
                    case 'video':
                        $ndx->addField('fileAnnexesVideo_s', $docObj->getUri() . '/file/' . rawurlencode($file->getName()));
                        break;
                    default:
                        break;
                }
            }
        }

        if (is_array($fileTypes)) {
            $fileTypes = array_unique($fileTypes);
            foreach ($fileTypes as $type) {
                $ndx->addField('fileType_s', $type);
            }
        }

        if ($this->getIndexPDF()) {
            $ndx = $this->indexPDFContent($ndx, $pdfFileList);
        }


        $this->setDoc($ndx);
    }

    /**
     * @return Hal_Document
     */
    public function getHalDocument()
    {
        return $this->_halDocument;
    }

    /**
     * @param Hal_Document $halDocument
     */
    public function setHalDocument(Hal_Document $halDocument)
    {
        $this->_halDocument = $halDocument;
    }

    /**
     * @return bool
     */
    public function getIndexPDF()
    {
        return $this->indexPDF;
    }

    /**
     * @param bool $indexPDF
     */
    public function setIndexPDF($indexPDF)
    {
        $this->indexPDF = $indexPDF;
    }

    /**
     * @param Solarium\QueryType\Update\Query\Document\Document $ndx
     * @param $pdfFileList
     * @return mixed
     */
    private function indexPDFContent($ndx, $pdfFileList)
    {

        $fullText = '';

        if (!count($pdfFileList)) {
            return $ndx;
        }


        foreach ($pdfFileList as $filePath) {
            try {
                $fullText .= Ccsd_FileConvert_Pdf::convertPDFtoText($filePath, 'poppler', true, $this->getPDFCacheFileName());
            } catch (Ccsd_FileConvert_Exception $e) {
                Ccsd_Log::message($e->getMessage(), $this->isDebugMode(), 'INFO', $this->getLogFilename());
            }
        }

        $fullText = $this->cleanFulltextChars($fullText);


        if ($fullText != '') {
            $ndx->addField('fulltext_t', $fullText);
        }

        return $ndx;
    }

    /**
     * get fulltext cache cacheFileName
     * @return string fulltext cache cacheFileName
     */
    private function getPDFCacheFileName()
    {
        return $this->getHalDocument()->getRacineCache() . DIRECTORY_SEPARATOR . $this->getHalDocument()->getDocid() . '.' . static::PDF2TEXT_EXTENSION;
    }

    /**
     * Nettoyage de la chaine de caractères du PDF avant indexation
     *
     * @param string $inputString
     * @return string
     */
    private function cleanFulltextChars($inputString)
    {
        $inputString = Ccsd_Tools_String::stripCtrlChars($inputString, ' ');
        return trim($inputString);
    }

    /**
     * Indexation des domaines applicatifs de l'HCERES
     * @param array $metas
     * @return boolean
     */
    private function indexHceresMeta($metas)
    {
        if (!is_array($metas)) {
            $metas = [$metas];
        }

        foreach ($metas as $metaName => $metaValue) {
            $metasTranslated = [];

            //traduction meta
            $languagesArray = Hal_Settings::getLanguages();
            $translator = Zend_Registry::get('Zend_Translate');
            //translate value in each language
            foreach ($languagesArray as $lang) {
                $metasTranslated[$lang] = $translator->translate($metaName . '_' . $metaValue, $lang);
                if ($metasTranslated[$lang] == $metaName . '_' . $metaValue) {
                    unset($metasTranslated[$lang]); // rm strings not translated
                }
            }
            // + value
            $metasTranslated['val'] = $metaValue;
            $metasTranslated = array_unique($metasTranslated);

            $dataToIndex [$metaName . '_s'] = $metaValue . '-' . $metasTranslated;
            //$dataToIndex [$metaName . '_t'] = array_values($metasTranslated);
        }

        $this->addArrayOfMetaToDoc($dataToIndex);

        return true;
    }


}

//end class
