<?php

/**
 *
 * @author rtournoy
 *
 */
class Ccsd_Search_Solr_Indexer_RefProjanr extends Ccsd_Search_Solr_Indexer
{

    public static $_coreName = 'ref_projanr';
    public static $_maxDocsInBuffer = 3000;
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
        $select->from([Ccsd_Referentiels_Anrproject::$_table], ['ANRID'])->order('ANRID');
    }

    /**
     * @param int $docId
     * @param Solarium\QueryType\Update\Query\Document\Document $ndx
     * @return mixed
     */
    protected function addMetadataToDoc($docId, $ndx)
    {
        $anr = self::getDocidData($docId);

        if ($anr == null) {
            Ccsd_Log::message('Pas de données pour ce DOCID ' . $docId . ', le document ne sera pas indexé.', true, 'ERR', $this->getLogFilename());
            return null;
        }

        $row = $anr->getData();

        $row = array_map('Ccsd_Tools_String::stripCtrlChars', $row);
        $row = array_map('trim', $row);

        $ndx->docid = (int) $row['ANRID'];

        $tabRefAlias = Ccsd_Referentiels_Alias::getAllAlias($docId, Ccsd_Referentiels_Anrproject::$_table);
        foreach ($tabRefAlias as $ligne) {
            $ndx->addField('aliasDocid_i', $ligne['OLDREFID']);
        }

        $dataToIndex = [
            'label_s' => self::getLabel_s($row),
            'label_html' => self::getLabel_html($row),
            'title_s' => $row['TITRE'],
            'acronym_s' => $row['ACRONYME'],
            'reference_s' => $row['REFERENCE'],
            'callTitle_s' => $row['INTITULE'],
            'callAcronym_s' => $row['ACROAPPEL'],
            'valid_s' => $row['VALID'],
            'callTitle_fs' => self::getCallTitle_fs($row),
            'yearDate_s' => $row['ANNEE'],
            'yearDate_tdate' => self::getYear_tdate($row['ANNEE'])

        ];

        foreach ($dataToIndex as $fieldName => $fieldValue) {
            if ($fieldValue != '') {
                $ndx->addField($fieldName, $fieldValue);
            }
        }


        return $ndx;
    }

    protected function getDocidData($docId)
    {
        return Ccsd_Referentiels_Anrproject::findById($docId);
    }

    /**
     * @param array $row
     * @return string
     */
    private static function getLabel_s(array $row): string
    {

        $label = '';
        if ($row['TITRE'] != '') {
            $label .= $row['TITRE'];
        }

        if ($row['ACRONYME'] != '') {
            $label .= ' [' . $row['ACRONYME'] . ']';
        }

        if ($row['REFERENCE'] != '') {
            $label .= ' [' . $row['REFERENCE'] . ']';
        }
        if ($row['INTITULE'] != '') {
            $label .= ' [' . $row['INTITULE'] . ']';
        }
        if ($row['ACROAPPEL'] != '') {
            $label .= ' [' . $row['ACROAPPEL'] . ']';
        }

        return $label;
    }

    /**
     * @param array $row
     * @return string
     */
    private static function getLabel_html(array $row): string
    {

        $label = '';
        if ($row['TITRE'] != '') {
            $label .= $row['TITRE'];
        }

        if ($row['ACRONYME'] != '') {
            $label .= ' <span class="acronym">' . $row['ACRONYME'] . '</span>';
        }

        if ($row['REFERENCE'] != '') {
            $label .= ' <span class="reference">' . $row['REFERENCE'] . '</span>';
        }
        if ($row['INTITULE'] != '') {
            $label .= ' <span class="callTitle">' . $row['INTITULE'] . '</span>';
        }
        if ($row['ACROAPPEL'] != '') {
            $label .= ' <span class="callAcronym">' . $row['ACROAPPEL'] . '</span>';
        }

        return '<span class="' . strtolower($row['VALID']) . '">' . trim($label) . '</span>';

    }

    /**
     * @param array $row
     * @return string
     */
    private static function getCallTitle_fs(array $row): string
    {
        $callTitleFs = '';
        if (($row['ACROAPPEL'] != '') and ($row['INTITULE'])) {
            $callTitleFs = $row['ACROAPPEL'] . self::SOLR_FACET_SEPARATOR . $row['INTITULE'];
        }
        return $callTitleFs;
    }

    /**
     * @param $year
     * @return string
     */
    private static function getYear_tdate($year = ''): string
    {
        if ($year != '') {
            $year .= '-01-01T00:00:00Z';
        }

        return $year;

    }

}

//end class
