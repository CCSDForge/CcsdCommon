<?php

/**
 *
 * @author rtournoy
 *
 */
class Ccsd_Search_Solr_Indexer_RefProjanr extends Ccsd_Search_Solr_Indexer {

    public static $_coreName = 'ref_projanr';
    public static $_maxDocsInBuffer = 3000;
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

    protected function getDocidData($docId) {
        $db = $this->getDb();
        $select = new Zend_Db_Select($db);

        $select = $db->select();
        $select->from(array(
            'REF_PROJANR'
        ));
        $select->where('ANRID = ?', $docId);

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
            'REF_PROJANR'
                ), array(
            'ANRID'
        ))->order('ANRID');
    }

    /**
     * @param int $docId
     * @param Solarium\QueryType\Update\Query\Document\Document $ndx
     * @return mixed
     */
    protected function addMetadataToDoc($docId, $ndx) {


        $row = self::getDocidData($docId);

        if ($row == null) {
            Ccsd_Log::message('Pas de données pour ce DOCID ' . $docId . ', le document ne sera pas indexé.', true, 'ERR', $this->getLogFilename());
            return null;
        }



        $row = array_map('Ccsd_Tools_String::stripCtrlChars', $row);

        $ndx->docid = $row['ANRID'];

        $tabRefAlias = Ccsd_Referentiels_Alias::getAllAlias($docId, 'REF_PROJANR');
        foreach ($tabRefAlias as $ligne) {
            $ndx->addField('aliasDocid_i', $ligne['OLDREFID']);
        }

        $bufferedDocidList[] = $docId;

        $dataToIndex = array(
            'title_s' => $row['TITRE'],
            'acronym_s' => $row['ACRONYME'],
            'reference_s' => $row['REFERENCE'],
            'callTitle_s' => $row['INTITULE'],
            'callAcronym_s' => $row['ACROAPPEL'],
            'valid_s' => $row['VALID']
        );

        foreach ($dataToIndex as $fieldName => $fieldValue) {

            if ($fieldValue != '') {
                $ndx->addField($fieldName, $fieldValue);
            }
        }
        unset($dataToIndex, $fieldName, $fieldValue);


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
        if ($label != '') {
            $ndx->label_s = trim($label);
        }

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
        if ($label != '') {
            $ndx->label_html = '<span class="' . strtolower($row['VALID']) . '">' . trim($label) . '</span>';
        }


        if (($row['ACROAPPEL'] != '') and ( $row['INTITULE'])) {
            $ndx->callTitle_fs = $row['ACROAPPEL'] . self::SOLR_FACET_SEPARATOR . $row['INTITULE'];
        }


        if ($row['ANNEE'] != '') {
            // 2006-00-00 =====> 2006-01-01
            $ndx->yearDate_s = $row['ANNEE'];
            $ndx->yearDate_tdate = $row['ANNEE'] . '-01-01T00:00:00Z';
        }

        return $ndx;
    }

}

//end class
