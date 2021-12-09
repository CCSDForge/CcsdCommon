<?php

/**
 *
 * @author rtournoy
 *
 */
class Ccsd_Search_Solr_Indexer_RefJournal extends Ccsd_Search_Solr_Indexer {

    public static $_coreName = 'ref_journal';
    public static $_maxDocsInBuffer = 5000;
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
     * Nettoie une chaine avant de l'indexer
     *
     * @param string $inputString
     * @return string
     */
    private function cleanChars($inputString) {
        $outputString = html_entity_decode($inputString);
        $outputString = Ccsd_Tools_String::stripCtrlChars($outputString);
        $outputString = trim($outputString, '"');
        $outputString = trim($outputString, '.');
        $outputString = trim($outputString, '-');
        $outputString = trim($outputString, '*');
        $outputString = trim($outputString, "'");
        $outputString = trim($outputString);

        return $outputString;
    }

    protected function getDocidData($docId) {
        $db = $this->getDb();
        $select = new Zend_Db_Select($db);

        $select = $db->select();

        $select->from(array(
            'REF_JOURNAL'
        ));
        $select->where('JID = ?', $docId);

        $stmt = $select->query();

        $res = $stmt->fetchAll();

        if (count($res) == 0) {

            return null;
        }

        return $res[0];
    }

    /**
     * Set the select request to get the list of Id to index
     */
    protected function selectIds($select)
    {
        $select->from(array('REF_JOURNAL' ), array( 'JID' ))->order('JID');
    }

    protected function addMetadataToDoc($docId, $ndx) {
        $row = self::getDocidData($docId);


        if ($row == null) {
            Ccsd_Log::message('Pas de données pour ce DOCID ' . $docId . ', le document ne sera pas indexé.', true, 'ERR', $this->getLogFilename());
            return null;
        }


        $ndx->docid = intval($row['JID']);

        $tabRefAlias = Ccsd_Referentiels_Alias::getAllAlias($docId, 'REF_JOURNAL');
        foreach ($tabRefAlias as $ligne) {
            $ndx->addField('aliasDocid_i', $ligne['OLDREFID']);
        }

        $bufferedDocidList[] = $docId;

        $title = self::cleanChars($row['JNAME']);
        if ($title == '') {
            $title = 'Untitled';
        }

        $dataToIndex = array(
            'title_s' => $title,
            'titleAbbr_s' => self::cleanChars($row['SHORTNAME']),
            'issn_s' => $row['ISSN'],
            'eissn_s' => $row['EISSN'],
            'publisher_s' => self::cleanChars($row['PUBLISHER']),
            'doiRoot_s' => $row['ROOTDOI'],
            'url_s' => $row['URL'],
            'sherpaColor_s' => $row['SHERPA_COLOR'],
            'sherpaPrePrint_s' => $row['SHERPA_PREPRINT'],
            'sherpaPostPrint_s' => $row['SHERPA_POSTPRINT'],
            'sherpaPreRest_s' => $row['SHERPA_PRE_REST'],
            'sherpaPostRest_s' => $row['SHERPA_POST_REST'],
            'sherpaCondition_s' => $row['SHERPA_COND'],
            'valid_s' => $row['VALID']
        );


        /**
         * label_html
         */
        $label = '';


        if ($title != '') {
            $label .= $title;
        }

        if ($row['SHORTNAME'] != '') {
            $label .= ' <span class="titleAbbr">' . $row['SHORTNAME'] . '</span>';
        }

        if ($row['ISSN'] != '') {
            $label .= ' <span class="issn">' . $row['ISSN'] . '</span>';
        }

        if ($row['EISSN'] != '') {
            $label .= ' <span class="eissn">' . $row['EISSN'] . '</span>';
        }

        if ($row['PUBLISHER'] != '') {
            $label .= ' <span class="publisher">' . $row['PUBLISHER'] . '</span>';
        }

        if ($label != '') {
            $dataToIndex['label_html'] = '<span class="' . strtolower($row['VALID']) . '">' . $label . '</span>';
        }
        /**
         * //label_html
         */
        /**
         * label_s
         */
        $label = '';

        if ($title != '') {
            $label = $title;
        }

        if ($row['SHORTNAME'] != '') {
            $label .= ' [' . $row['SHORTNAME'] . ']';
        }

        if ($row['ISSN'] != '') {
            $label .= ' [ISSN:' . $row['ISSN'] . ']';
        }

        if ($row['EISSN'] != '') {
            $label .= ' [EISSN:' . $row['EISSN'] . ']';
        }


        $dataToIndex['label_s'] = $label;
        /**
         * //label_s
         */
        foreach ($dataToIndex as $fieldName => $fieldValue) {

            $fieldValue = Ccsd_Tools_String::stripCtrlChars($fieldValue);
            $fieldValue = trim($fieldValue);

            if (($fieldValue != '') and ( $fieldValue != null) and ( $fieldValue != '[]')) {
                $ndx->addField($fieldName, $fieldValue);
            }
        }
        unset($dataToIndex, $fieldName, $fieldValue, $label);


        if (($row['DATEMODIF'] != '') and ( $row['DATEMODIF'] != '0000-00-00')) {
            // 2006-00-00 =====> 2006-01-01
            $date = str_replace('00-00', '01-01', $row['DATEMODIF']);
            $ndx->updateDate_s = $date;

            $date = str_replace(' ', 'T', $row['DATEMODIF']);
            $ndx->updateDate_tdate = $date . 'Z';
        }

        if (($row['SHERPA_DATE'] != '') and ( $row['SHERPA_DATE'] != '0000-00-00')) {
            // 2006-00-00 =====> 2006-01-01
            $date = str_replace('00-00', '01-01', $row['SHERPA_DATE']);
            $ndx->sherpaDate_s = $date;
            $ndx->sherpaDate_tdate = $date . 'T00:00:00Z';
        }
        return $ndx;
    }

}

//end class
