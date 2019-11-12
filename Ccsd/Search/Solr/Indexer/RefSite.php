<?php

/**
 *
 * @author rtournoy
 *
 */
class Ccsd_Search_Solr_Indexer_RefSite extends Ccsd_Search_Solr_Indexer
{

    public static $_coreName = 'ref_site';
    public static $_maxDocsInBuffer = 2000;
    public static $_maxSelectFromIndexQueue = 2000;
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
        // on indexe que les sites
        $select->from(array('SITE'), array('SID'))
            ->order('SID');
    }

    /**
     * @param $url
     * @return string
     */
    protected function cleanUrl($url)
    {
        $url = str_replace(['http://','https://'], '', $url);
        $url = rtrim($url, '/');

        return strtolower($url);
    }

    /**
     * @param int $docId
     * @param \Solarium\QueryType\Update\Query\Document\Document $ndx
     * @see Ccsd_Search_Solr_Indexer::addMetadataToDoc()
     */
    protected function addMetadataToDoc($docId, $ndx)
    {
        $row = $this->getDocidData($docId);

        if ($row == null) {
            Ccsd_Log::message('Pas de données pour ce DOCID ' . $docId . ', le document ne sera pas indexé.', true, 'ERR', $this->getLogFilename());
            return null;
        }

        $ndx->docid = $docId;

        $dataToIndex = array(
            'type_s' => $row['TYPE'],
            'site_s' => $row['SITE'],
            'url_s' => $this->cleanUrl($row['URL']),
            'id_s' => $row['ID'],
            'name_s' => $row['NAME'],
            'creationDate_tdate' => date_format(new DateTime($row['DATE_CREATION']), "Y-m-d\Th:i:s\Z")
        );

        foreach ($dataToIndex as $fieldName => $fieldValue) {

            if (($fieldValue != '') && ($fieldValue != null)) {
                $ndx->addField($fieldName, $fieldValue);
            }
        }

        // Ajout des Urls ALIAS
        $aliases = $this->getAliases($docId);

        if (empty($aliases)) {
            return $ndx;
        }

        foreach ($aliases as $alias) {
            $ndx->addField('url_s', $this->cleanUrl($alias));
        }


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
        $select = $db->select()
                    ->from(array('SITE'))
                    ->where('SID = ?', $docId);

        $res = $db->fetchAll($select);
        if (count($res) == 0) {
            return null;
        }

        return $res[0];
    }

    /**
     * @param int $docId
     * @return array|null
     */
    protected function getAliases($docId)
    {
        $db = $this->getDb();
        $select = $db->select()
                    ->from(array('SITE_ALIAS'), 'URL')
                    ->where('SID = ?', $docId);

        $res = $db->fetchCol($select);

        if (count($res) == 0) {
            return null;
        }

        return $res;
    }
}

//end class

