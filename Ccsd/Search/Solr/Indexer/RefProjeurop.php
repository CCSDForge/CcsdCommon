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
    public function __construct (array $options)
    {
        $options['core'] = self::$_coreName;
        parent::initHalEnv();
        parent::__construct($options);
    }

    protected function getDocidData ($docId)
    {
        $db = $this->getDb();
        $select = $db->select();

        $select->from(array(
                'REF_PROJEUROP'
        ));
        $select->where('PROJEUROPID = ?', $docId);

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
        $select->from(array(
                'REF_PROJEUROP'
        ), array(
                'PROJEUROPID'
        ))->order('PROJEUROPID');
    }

    /**
     * @param int $docId
     * @param \Solarium\QueryType\Update\Query\Document\Document $ndx
     * @return mixed
     */
    protected function addMetadataToDoc ($docId, $ndx)
    {
        $row = self::getDocidData($docId);

        if ($row == null) {
            Ccsd_Log::message('Pas de données pour ce DOCID ' . $docId . ', le document ne sera pas indexé.', true, 'ERR', $this->getLogFilename());
            return null;
        }

		$row = array_map('Ccsd_Tools_String::stripCtrlChars', $row);

        $ndx->docid = $row['PROJEUROPID'];

        $tabRefAlias =  Ccsd_Referentiels_Alias::getAllAlias($docId, 'REF_PROJEUROP');
        foreach($tabRefAlias as $ligne) {
            $ndx->addField('aliasDocid_i', $ligne['OLDREFID']);
        }

        $dataToIndex = array(
                'title_s' => $row['TITRE'],
                'acronym_s' => $row['ACRONYME'],
                'reference_s' => $row['NUMERO'],
                'financing_s' => $row['FUNDEDBY'],
                'callId_s' => $row['CALLID'],
                'valid_s' => $row['VALID']
        );

        foreach ($dataToIndex as $fieldName => $fieldValue) {

            if ($fieldValue != '') {
                $ndx->addField($fieldName, trim($fieldValue));
            }
        }

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

        if ($label != '') {
            $ndx->label_s = trim($label);
        }

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
            $ndx->label_html = '<span class="'. strtolower($row['VALID']) . '">' . trim($label) . '</span>';
        }

        if ( ($row['SDATE'] != '') && ($row['SDATE'] != '0000-00-00') ) {
            // 2006-00-00 =====> 2006-01-01
            $ndx->startDate_s = $row['SDATE'];
            $ndx->startDate_tdate = Ccsd_Tools_String::stringToIso8601($row['SDATE']);
        }

        if ( ($row['EDATE'] != '') && ($row['EDATE'] != '0000-00-00') ) {
            // 2006-00-00 =====> 2006-01-01
            $ndx->endDate_s = $row['EDATE'];
            $ndx->endDate_tdate = Ccsd_Tools_String::stringToIso8601($row['EDATE']);
        }

        return $ndx;
    }
}//end class
