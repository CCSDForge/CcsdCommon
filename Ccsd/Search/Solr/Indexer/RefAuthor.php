<?php

/**
 *
 * @author rtournoy
 *
 */
class Ccsd_Search_Solr_Indexer_RefAuthor extends Ccsd_Search_Solr_Indexer
{

    public static $_coreName = 'ref_author';
    public static $_maxDocsInBuffer = 1000;
    public static $dbConfName = 'hal';
    const EMAIL_HASH_TYPE = 'md5';

    /**
     * Ccsd_Search_Solr_Indexer_RefAuthor constructor.
     * @param array $options
     */
    public function __construct(array $options)
    {
        self::initHalEnv();
        $options['core'] = self::$_coreName;
        parent::__construct($options);
    }

    /**
     * Set the select request to get the list of Id to index
     */
    protected function selectIds($select)
    {
        $select->from(['REF_AUTHOR'], ['AUTHORID'])->order('AUTHORID');
    }

    /**
     * Sert a l'affichage des resultat: chaine precalculee plutot que refaire dynamiquement des concats
     * @param array $row
     * @param string $CleanedLastname
     * @param string $CleanedFirstname
     * @return string
     */
    private function getHtmlLabel($row, $CleanedLastname, $CleanedFirstname) {

        $label_html = '<span class="' . strtolower($row['valid']) . '">';
        $label_html .= htmlspecialchars($CleanedLastname . ' ' . $CleanedFirstname);

        if ($row['email'] != '') {
            $emailDomain = Hal_Document_Author::getDomainFromEmail($row['email']);
            $label_html .= ' <span class="address">' . htmlspecialchars('@' . $emailDomain) . '</span>';
        }

        if ($row['idhalstring'] != '') {
            $label_html .= ' <span class="idhal">' . htmlspecialchars($row['idhalstring']) . '</span>';
        }

        if ($row['url'] != '') {
            $label_html .= ' <span class="url">' . htmlspecialchars($row['url']) . '</span>';
        }

        if ($row['organism'] != '') {
            $label_html .= ' <span class="structure">' . htmlspecialchars($row['organism']) . '</span>';
        }

        return $label_html . '</span>';
    }

    /**
     * @param int $docId
     * @param Solarium\QueryType\Update\Query\Document\Document $ndx
     */
    protected function addMetadataToDoc($docId, $ndx)
    {
        $row = self::getDocidData($docId);
        if ($row == null) {
            Ccsd_Log::message('Pas de données pour ce DOCID ' . $docId . ', le document ne sera pas indexé.', true, 'ERR', $this->getLogFilename());
            return null;
        }

        $tabRefAlias = Ccsd_Referentiels_Alias::getAllAlias($docId, 'REF_AUTHOR');
        foreach ($tabRefAlias as $ligne) {
            $ndx->addField('aliasDocid_i', $ligne['OLDREFID']);
        }

        $row = array_map('Ccsd_Tools_String::stripCtrlChars', $row);

        $ndx->docid = intval($row['authorid']);

        $fullname = $row['fullname'];
        $lastname   = self::cleanChars($row['lastname']);
        $firstname  = self::cleanChars($row['firstname']);
        $middlename = self::cleanChars($row['othername']);

        $label_html = $this -> getHtmlLabel($row, $lastname, $firstname);

        $structure = self::cleanChars($row['organism']);

        if ($row['organismid'] != '0') {
            $structure_fs = $row['organismid'] . self::SOLR_FACET_SEPARATOR . $structure;
            $ndx->addField('structure_fs', $structure_fs);
        }

        if (false === filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
            $row['email'] = null;
        }

        if (is_array($row['idsauthor'])) {
            $c = new Hal_Cv();
            $c->getServerExt();
            $servUrl = $c->getServerUrl();

            foreach ($row['idsauthor'] as $domain => $idAuth) {
                $domain = strtolower($domain);
                $extIdOnly = str_replace($servUrl, '', $idAuth);
                $ndx->addField($domain . '_id', $extIdOnly);
                $ndx->addField($domain . '_s', $idAuth);
            }
        }

        $dataToIndex = array(
            'idHal_i'       => $row['idhal'],
            'idHal_s'       => $row['idhalstring'],
            'lastName_s'    => $lastname,
            'firstName_s'   => $firstname,
            'middleName_s'  => $middlename,
            'fullName_s'    => $fullname,
            'email_s'       => Hal_Document_Author::getEmailHashed($row['email'], self::EMAIL_HASH_TYPE),
            'emailDomain_s' => Hal_Document_Author::getDomainFromEmail($row['email']),
            'url_s'         => $row['url'],
            'structure_s'   => $structure,
            'label_s'       => $fullname,
            'label_html'    => $label_html,
            'valid_s'       => $row['valid'],
            'hasCV_bool'    => $row['hascv']
        );

        if ($row['organismid'] != 0) {
            $dataToIndex['structureId_i'] = $row['organismid'];
        }

        $dataToIndex = array_map('trim', $dataToIndex);
        $dataToIndex = array_map('Ccsd_Tools_String::stripCtrlChars', $dataToIndex);

        foreach ($dataToIndex as $fieldName => $fieldValue) {
            if (($fieldValue != '') && ($fieldValue != null)) {
                $ndx->addField($fieldName, $fieldValue);
            }
        }

        return $ndx;
    }

    protected function getDocidData($docId)
    {
        $a = new Hal_Document_Author($docId);


        $id = $a->getAuthorid();
        // Si le id n'est pas le même on a récupéré les données d'un alias
        if (($id == 0) || ($id != $docId)) {
            return null;
        }

        return $a->toArray();
    }

    /**
     * Nettoie une chaine avant de l'indexer
     *
     * @param string $inputString
     * @return string
     */
    private function cleanChars($inputString)
    {
        $outputString = html_entity_decode($inputString);
        $outputString = Ccsd_Tools_String::stripCtrlChars($outputString);
        return trim($outputString);
    }

}

//end class
