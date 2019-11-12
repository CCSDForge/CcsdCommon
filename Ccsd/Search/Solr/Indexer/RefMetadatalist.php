<?php

/**
 *
 * @author rtournoy
 *
 */
class Ccsd_Search_Solr_Indexer_RefMetadatalist extends Ccsd_Search_Solr_Indexer {

    public static $_coreName = 'ref_metadatalist';
    public static $_maxDocsInBuffer = 1000;
    public static $dbConfName = 'hal';

    /**
     *
     * @param array $options
     */
    public function __construct(array $options) {
        $options['core'] = self::$_coreName;

        parent::initHalEnv();

        parent::__construct($options);
    }


    /**
     * Set the select request to get the list of Id to index
     */
    protected function selectIds($select)
    {
        $select->from(array('REF_METADATA'), array('METAID'))->order('METAID');
    }

    /**
     *
     * @see Ccsd_Search_Solr_Indexer::getDocidData()
     */
    protected function getDocidData($docId) {
        $db = $this->getDb();
        $select = $db->select();

        $select->from(array(
            'REF_METADATA'
        ));
        $select->where('METAID = ?', $docId);

        $stmt = $select->query();
        $res = $stmt->fetchAll();
        if (count($res) == 0) {
            return null;
        }
        return $res[0];
    }

    /**
     * @param int $docId
     * @param Solarium\QueryType\Update\Query\Document\Document $ndx
     * @return mixed
     * @see Ccsd_Search_Solr_Indexer::addMetadataToDoc()
     */
    protected function addMetadataToDoc($docId, $ndx) {
        $row = self::getDocidData($docId);

        if ($row == null) {
            Ccsd_Log::message('Pas de données pour ce DOCID ' . $docId . ', le document ne sera pas indexé.', true, 'ERR', $this->getLogFilename());
            return null;
        }


        $ndx->docid = $docId;

        // $lng = $this->getLanguageTranslations();

        $t = Zend_Registry::get('Zend_Translate');
        $languagesArray = Hal_Settings::getLanguages();

        $dataToIndex = array(
            'label_s' => $row['METANAME'] . ' : ' . $t->translate($row['METANAME'] . '_' . $row['METAVALUE'], 'fr'),
            'metaName_s' => $row['METANAME'],
            'metaValue_s' => $row['METAVALUE']
        );

        foreach ($languagesArray as $lang) {

            if ($t->isTranslated($row['METANAME'] . '_' . $row['METAVALUE'], $lang)) {
                $dataToIndex['metaLabel_s'][] = $t->translate($row['METANAME'] . '_' . $row['METAVALUE'], $lang);
                $dataToIndex[$lang . '_' . 'metaLabel_s'] = $t->translate($row['METANAME'] . '_' . $row['METAVALUE'], $lang);
            }
        }



        $this->setDoc($ndx);
        $this->addArrayOfMetaToDoc($dataToIndex);
        $this->getDoc();

        return $ndx;
    }

}

//end class
