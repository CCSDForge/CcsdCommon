<?php

/**
 *
 * @author rtournoy
 *
 */
class Ccsd_Search_Solr_Indexer_RefJournal extends Ccsd_Search_Solr_Indexer
{

    const DEFAULT_JOURNAL_NAME = 'Untitled';
    public static $_coreName = 'ref_journal';
    public static $_maxDocsInBuffer = 5000;
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
        $select->from([Ccsd_Referentiels_Journal::$_table], ['JID'])->order('JID');
    }

    protected function addMetadataToDoc($docId, $ndx)
    {
        $journal = self::getDocidData($docId);

        if ($journal == null) {
            Ccsd_Log::message('Pas de données pour ce DOCID ' . $docId . ', le document ne sera pas indexé.', true, 'ERR', $this->getLogFilename());
            return null;
        }

        $row = $journal->getData();

        $ndx->docid =  (int) $row['JID'];

        $tabRefAlias = Ccsd_Referentiels_Alias::getAllAlias($docId, 'REF_JOURNAL');
        foreach ($tabRefAlias as $ligne) {
            $ndx->addField('aliasDocid_i', $ligne['OLDREFID']);
        }


        foreach ($row as $rowKey => $rowValue) {
            $row[$rowKey] = self::cleanChars($rowValue);
        }


        $title = $this->getTitle($row['JNAME']);

        $dataToIndex = [
            'title_s' => $title,
            'titleAbbr_s' => $row['SHORTNAME'],
            'issn_s' => $row['ISSN'],
            'eissn_s' => $row['EISSN'],
            'publisher_s' => $row['PUBLISHER'],
            'doiRoot_s' => $row['ROOTDOI'],
            'url_s' => $row['URL'],
            'sherpaColor_s' => $row['SHERPA_COLOR'],
            'sherpaPrePrint_s' => $row['SHERPA_PREPRINT'],
            'sherpaPostPrint_s' => $row['SHERPA_POSTPRINT'],
            'sherpaPreRest_s' => $row['SHERPA_PRE_REST'],
            'sherpaPostRest_s' => $row['SHERPA_POST_REST'],
            'sherpaCondition_s' => $row['SHERPA_COND'],
            'valid_s' => $row['VALID']
        ];

        $dataToIndex['label_html'] = self::getLabel_html($title, $row);
        $dataToIndex['label_s'] = self::getLabel_s($title, $row);

        foreach ($dataToIndex as $fieldName => $fieldValue) {

            if (($fieldValue != '') && ($fieldValue != null) && ($fieldValue != '[]')) {
                $ndx->addField($fieldName, $fieldValue);
            }
        }
        unset($dataToIndex, $fieldName, $fieldValue, $label);

        if (($row['DATEMODIF'] != '') && ($row['DATEMODIF'] != '0000-00-00 00:00:00')) {
            // 2006-00-00 =====> 2006-01-01
            $date = str_replace('00-00', '01-01', $row['DATEMODIF']);
            $ndx->updateDate_s = $date;

            $date = str_replace(' ', 'T', $row['DATEMODIF']);
            $ndx->updateDate_tdate = $date . 'Z';
        }

        if (($row['SHERPA_DATE'] != '') && ($row['SHERPA_DATE'] != '0000-00-00')) {
            // 2006-00-00 =====> 2006-01-01
            $date = str_replace('00-00', '01-01', $row['SHERPA_DATE']);
            $ndx->sherpaDate_s = $date;
            $ndx->sherpaDate_tdate = $date . 'T00:00:00Z';
        }
        return $ndx;
    }

    protected function getDocidData($docId)
    {
        return Ccsd_Referentiels_Journal::findById($docId);
    }

    /**
     * Nettoie une chaine avant de l'indexer
     *
     * @param string $inputString
     * @return string
     */
    private static function cleanChars($inputString)
    {
        $outputString = html_entity_decode($inputString);
        $outputString = strip_tags($outputString);
        $outputString = Ccsd_Tools_String::stripCtrlChars($outputString);
        $outputString = trim($outputString, '"');
        $outputString = trim($outputString, '.');
        $outputString = trim($outputString, '-');
        $outputString = trim($outputString, '*');
        $outputString = trim($outputString, "'");
        return trim($outputString);

    }

    /**
     * @param array $row
     * @return mixed|string
     */
    private function getTitle($journalName)
    {

        if ($journalName == '') {
            $journalName = self::DEFAULT_JOURNAL_NAME;
        }
        return $journalName;
    }

    /**
     * @param string $title
     * @param array $row
     * @return string
     */
    private static function getLabel_html(string $title, array $row): string
    {
        $label = $title;


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
            return '<span class="' . strtolower($row['VALID']) . '">' . $label . '</span>';
        }

        return $label;
    }

    /**
     * @param string $title
     * @param array $row
     * @return string
     */
    private static function getLabel_s(string $title, array $row): string
    {
        $label = $title;


        if ($row['SHORTNAME'] != '') {
            $label .= ' [' . $row['SHORTNAME'] . ']';
        }

        if ($row['ISSN'] != '') {
            $label .= ' [ISSN:' . $row['ISSN'] . ']';
        }

        if ($row['EISSN'] != '') {
            $label .= ' [EISSN:' . $row['EISSN'] . ']';
        }
        return $label;
    }

}

//end class
