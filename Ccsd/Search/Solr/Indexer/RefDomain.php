<?php

/**
 *
 * @author rtournoy
 *
 */
class Ccsd_Search_Solr_Indexer_RefDomain extends Ccsd_Search_Solr_Indexer
{

    public static $_coreName = 'ref_domain';
    public static $_maxDocsInBuffer = 100;
    public static $_maxSelectFromIndexQueue = 100;
    public static $dbConfName = 'hal';

    /**
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $options['core'] = self::$_coreName;
        parent::initHalEnv();
        parent::__construct($options);
    }

    /**
     * Set the select request to get the list of Id to index
     */
    protected function selectIds($select)
    {
        // on indexe que les domaines de hal : SID = 1
        $select->from(array('REF_DOMAIN'), array('ID'))
            ->where('`ID` IN (SELECT ID FROM PORTAIL_DOMAIN WHERE SID = 1)')
            ->order('CODE');
    }

    /**
     * @param int $docId
     * @param \Solarium\QueryType\Update\Query\Document\Document $ndx
     * @see Ccsd_Search_Solr_Indexer::addMetadataToDoc()
     */
    protected function addMetadataToDoc($docId, $ndx)
    {
        $row = self::getDocidData($docId);


        if ($row == null) {
            Ccsd_Log::message('Pas de données pour ce DOCID ' . $docId . ', le document ne sera pas indexé.', true, 'ERR', $this->getLogFilename());
            return null;
        }


        $ndx->docid = $docId;

        $translator = Zend_Registry::get('Zend_Translate');

        $dataToIndex = array(
            'code_s' => $row['CODE'],
            'parent_i' => intval($row['PARENT']),
            'level_i' => intval($row['LEVEL'])
        );

        if ($row['HAVENEXT'] == 0) {
            $ndx->addField('haveNext_bool', false);
        } else {
            $ndx->addField('haveNext_bool', true);
        }

        foreach ($dataToIndex as $fieldName => $fieldValue) {

            if (($fieldValue != '') && ($fieldValue != null)) {
                $ndx->addField($fieldName, $fieldValue);
            }
        }


        $domaines = Ccsd_Tools_String::getHalDomainPaths($row['CODE']);

        foreach ($domaines as $d) {
            $translDomains['en'][] = $translator->translate('domain_' . $d, 'en');
            $translDomains['fr'][] = $translator->translate('domain_' . $d, 'fr');
        }

        $ndx->addField('en_domain_s', implode('/', $translDomains['en']));
        $ndx->addField('fr_domain_s', implode('/', $translDomains['fr']));
        $ndx->addField('label_s', $row['CODE'] . ' = ' . implode('/', $translDomains['fr']));

        return $ndx;
    }

    /**
     * @see Ccsd_Search_Solr_Indexer::getDocidData()
     * @param int $docId
     * @return array|null
     */
    protected function getDocidData($docId)
    {
        $db = $this->getDb();
        $select = $db->select();

        $select->from(array('REF_DOMAIN'));
        $select->where('ID = ?', $docId);

        $stmt = $select->query();
        $res = $stmt->fetchAll();
        if (count($res) == 0) {
            return null;
        }

        return $res[0];
    }


}

//end class
