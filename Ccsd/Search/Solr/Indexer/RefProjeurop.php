<?php

/**
 *
 * @author rtournoy
 *
 */
class Ccsd_Search_Solr_Indexer_RefProjeurop extends Ccsd_Search_Solr_Indexer
{

    public static $_coreName = 'ref_projeurop';
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
        $select->from([Ccsd_Referentiels_Europeanproject::$_table], ['PROJEUROPID'])->order('PROJEUROPID');
    }

    /**
     * @param int $docId
     * @param \Solarium\QueryType\Update\Query\Document\Document $ndx
     * @return mixed
     */
    protected function addMetadataToDoc($docId, $ndx)
    {
        $projeurop = self::getDocidData($docId);

        if ($projeurop == null) {
            Ccsd_Log::message('Pas de données pour ce DOCID ' . $docId . ', le document ne sera pas indexé.', true, 'ERR', $this->getLogFilename());
            return null;
        }

        $projectRow = $projeurop->getData();

        $projectRow = array_map('Ccsd_Tools_String::stripCtrlChars', $projectRow);
        $projectRow = array_map('trim', $projectRow);

        $ndx->docid = (int)$projectRow['PROJEUROPID'];

        $tabRefAlias = Ccsd_Referentiels_Alias::getAllAlias($docId, 'REF_PROJEUROP');
        foreach ($tabRefAlias as $ligne) {
            $ndx->addField('aliasDocid_i', $ligne['OLDREFID']);
        }

        $dataToIndex = [
            'title_s' => $projectRow['TITRE'],
            'label_s' => self::getLabel_s($projectRow),
            'label_html' => self::getLabel_html($projectRow),
            'acronym_s' => $projectRow['ACRONYME'],
            'reference_s' => $projectRow['NUMERO'],
            'financing_s' => $projectRow['FUNDEDBY'],
            'callId_s' => $projectRow['CALLID'],
            'valid_s' => $projectRow['VALID'],
            'openaireId_s' => self::getOpenaireId($projectRow),
            'startDate_s' => self::getProjectDate($projectRow['SDATE']),
            'endDate_s' => self::getProjectDate($projectRow['EDATE']),
            'startDate_tdate' => self::getProjectDate($projectRow['SDATE'], 'ISO8601'),
            'endDate_tdate' => self::getProjectDate($projectRow['EDATE'], 'ISO8601'),

        ];

        foreach ($dataToIndex as $fieldName => $fieldValue) {

            if ($fieldValue != '') {
                $ndx->addField($fieldName, $fieldValue);
            }
        }


        return $ndx;
    }

    /**
     * @param int $docId
     * @return Ccsd_Referentiels_Europeanproject|null
     */
    protected function getDocidData($docId)
    {
        return Ccsd_Referentiels_Europeanproject::findById($docId);

    }

    /**
     * @param array $row
     * @return mixed|string
     */
    private static function getLabel_s(array $row)
    {
        $label = '';

        if ($row['TITRE'] != '') {
            $label = $row['TITRE'];
        }

        if ($row['ACRONYME'] != '') {
            $label .= ' [' . $row['ACRONYME'] . ']';
        }

        if ($row['NUMERO'] != '') {
            $label .= ' [' . $row['NUMERO'] . ']';
        }

        if ($row['CALLID'] != '') {
            $label .= ' [' . $row['CALLID'] . ']';
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
            $label = $row['TITRE'];
        }

        if ($row['ACRONYME'] != '') {
            $label .= ' <span class="acronym">' . $row['ACRONYME'] . '</span>';
        }

        if ($row['NUMERO'] != '') {
            $label .= ' <span class="reference">' . $row['NUMERO'] . '</span>';
        }

        if ($row['CALLID'] != '') {
            $label .= ' <span class="callId">' . $row['CALLID'] . '</span>';
        }

        if ($label != '') {
            $label_html = '<span class="' . strtolower($row['VALID']) . '">' . $label . '</span>';
        }
        return $label_html;
    }

    /** Calcul de l'Id openaire du projet
     * @param array $projectRow
     * @return string
     * @see Hal/Document/xsl/dc.xsl template: <xsl:template match="tei:org[@type='europeanProject']">
     */
    private static function getOpenaireId($projectRow)
    {
        $openaireId = 'info:eu-repo/grantAgreement/';
        $program = $projectRow['FUNDEDBY'];
        $matches = [];
        $match = preg_match("/^([^:]+):([^:]+)/", $program, $matches);
        if ($match) {
            $openaireId .= $matches[1] . '/' . $matches[2] . '/';
        }

        return $openaireId . $projectRow['NUMERO'] . "/EU/" . $projectRow['TITRE'] . "/" . $projectRow['ACRONYME'];
    }

    /**
     * @param string $date
     * @param string $format
     * @return string
     */
    private static function getProjectDate(string $date, string $format = 'ISO8601'): string
    {
        if (($date != '') && ($date != '0000-00-00')) {
            if ($format == 'ISO8601') {
                $date = Ccsd_Tools_String::stringToIso8601($date);
            }
            return $date;
        } else {
            return '';
        }

    }
}//end class
