<?php

/**
 *
 * @author rtournoy
 *
 */
class Ccsd_Search_Solr_Indexer_RefStructure extends Ccsd_Search_Solr_Indexer
{

    public static $_coreName = 'ref_structure';
    public static $_maxDocsInBuffer = 3000;
    public static $dbConfName = 'hal';

    /**
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $options ['core'] = self::$_coreName;
        parent::initHalEnv();
        parent::__construct($options);
    }

    /**
     * Set the select request to get the list of Id to index
     */
    protected function selectIds($select)
    {
        $select->from(['REF_STRUCTURE'], ['STRUCTID'])->order('STRUCTID');
    }

    /**
     * @param int $docId
     * @param Solarium\QueryType\Update\Query\Document\Document $ndx
     */
    protected function addMetadataToDoc($docId, $ndx)
    {
        $structure = self::getDocidData($docId);


        if ($structure == null) {
            Ccsd_Log::message('Pas de données pour ce DOCID ' . $docId . ', le document ne sera pas indexé.', true, 'ERR', $this->getLogFilename());
            return null;
        }


        $tabRefAlias = Ccsd_Referentiels_Alias::getAllAlias($docId, 'REF_STRUCTURE');
        foreach ($tabRefAlias as $ligne) {
            $ndx->addField('aliasDocid_i', $ligne['OLDREFID']);
        }

        $ndx->docid = $docId;


        $ndx = $this->indexIdExt($ndx, $structure);


        $structureArray = $structure->toArray();
        $structureArray ['tei'] = Ccsd_Referentiels_Structure::getFullXml($docId, false);

        $structuresParentes = $structureArray ['parents'];
        unset($structureArray ['parents']);

        $label_s = $structureArray ['STRUCTNAME'];

        $acronym_s = $structureArray ['SIGLE'];


        if ($acronym_s != '') {
            $label_s .= ' [' . $acronym_s . ']';
        }

        $structureDataToIndex = [
            'label_xml' => $structureArray ['tei'],
            'label_s' => $label_s,
            'name_s' => $structureArray ['STRUCTNAME'],
            'acronym_s' => $acronym_s,
            'address_s' => $structureArray ['ADDRESS'],
            'country_s' => $structureArray ['PAYSID'],
            'url_s' => $structureArray ['URL'],
            'type_s' => $structureArray ['TYPESTRUCT'],
            'valid_s' => $structureArray ['VALID']
        ];

        if (isset($structureArray ['LOCKED'])) {
            if ($structureArray ['LOCKED'] == 1) {
                $ndx->addField('locked_bool', true);
            } else {
                $ndx->addField('locked_bool', false);
            }
        }

        if ($structureArray ['DATEMODIF'] != '') {
            $structureDataToIndex ['updateDate_s'] = $structureArray ['DATEMODIF'];
            $structureDataToIndex ['updateDate_tdate'] = str_ireplace(' ', 'T', $structureArray ['DATEMODIF']) . 'Z';
        }

        $label = $structureArray ['STRUCTNAME'];
        if ($acronym_s != '') {
            $label .= ' <span class="acronym">' . $acronym_s . '</span>';
        }

        foreach ($structureDataToIndex as $fieldName => $fieldValue) {
            if (($fieldValue != '') && ($fieldValue != null)) {
                $fieldValue = trim($fieldValue);
                $fieldValue = Ccsd_Tools_String::stripCtrlChars($fieldValue);
                $ndx->addField($fieldName, $fieldValue);
            }
        }


        unset($structureDataToIndex, $fieldName, $fieldValue);

        if (count($structuresParentes) == 0) {

            $structureArray ['VALID'] = trim($structureArray ['VALID']);
            $structureArray ['VALID'] = Ccsd_Tools_String::stripCtrlChars($structureArray ['VALID']);
            $label = trim($label);
            $label = Ccsd_Tools_String::stripCtrlChars($label);

            $ndx->addField('label_html', '<span class="' . strtolower($structureArray ['VALID']) . '">' . trim($label) . '</span>');
            return $ndx;
        }

        $label .= '<dl>';

        foreach ($structuresParentes as $parent) {

            $parentArray = $parent ['struct'];
            unset($parentArray ['parents']);

            // Le code est groupé avec la structure plutôt que le
            // parent
            $parent ['code'] = trim($parent ['code']);

            if ($parent ['code'] != '') {
                $ndx->addField('code_s', $parent ['code']);
            }

            $parentDataToIndex = [
                'parentDocid_i' => $parentArray ['STRUCTID'],
                'parentAcronym_s' => $parentArray ['SIGLE'],
                'parentName_s' => $parentArray ['STRUCTNAME'],
                'parentAddress_s' => $parentArray ['ADDRESS'],
                'parentCountry_s' => $parentArray ['PAYSID'],
                'parentUrl_s' => $parentArray ['URL'],
                'parentType_s' => $parentArray ['TYPESTRUCT'],
                'parentValid_s' => $parentArray ['VALID']
            ];

            foreach ($parentDataToIndex as $fieldName => $fieldValue) {
                if (($fieldValue != '') && ($fieldValue != null)) {
                    $fieldValue = trim($fieldValue);
                    $fieldValue = Ccsd_Tools_String::stripCtrlChars($fieldValue);
                    $ndx->addField($fieldName, $fieldValue);
                }
            }
            if (is_array($parentArray ['URLEXT'])) {

                foreach ($parentArray ['URLEXT'] as $domain => $idext) {

                    $domain = strtolower($domain);

                    $domain = trim($domain);
                    $domain = Ccsd_Tools_String::stripCtrlChars($domain);


                    /**
                     * Ne fonctionne plus avec array_map depuis qu'il y a un sous-tableau
                     * Ne fonctionne pas avec array_walk_recursive
                     */
                    $idext ['id'] = trim($idext ['id']);
                    $idext ['id'] = Ccsd_Tools_String::stripCtrlChars($idext ['id']);

                    $idext ['url'] = trim($idext ['url']);
                    $idext ['url'] = Ccsd_Tools_String::stripCtrlChars($idext ['url']);

                    $domain = ucfirst($domain);
                    $ndx->addField('parent' . $domain . '_id', $idext ['id']);
                    $ndx->addField('parent' . $domain . '_s', $idext ['id']);
                    $ndx->addField('parent' . $domain . 'Url_s', $idext ['url']);
                }
            }


            $label .= '<dt>';
            if ($parentArray ['SIGLE'] != '') {
                $label .= '<span class="acronym">' . $parentArray ['SIGLE'] . '</span>';
            }
            if ($parentArray ['STRUCTNAME'] != '') {
                $label .= ' <span class="name">' . $parentArray ['STRUCTNAME'] . '</span>';
            }
            if ($parent ['code'] != '') {
                $label .= '<span class="code">' . $parent ['code'] . '</span>';
            }
            $label .= '</dt>';
        }

        $label .= '</dl>';

        $structureArray ['VALID'] = trim($structureArray ['VALID']);
        $structureArray ['VALID'] = Ccsd_Tools_String::stripCtrlChars($structureArray ['VALID']);
        $label = trim($label);
        $label = Ccsd_Tools_String::stripCtrlChars($label);

        $ndx->addField('label_html', '<span class="' . strtolower($structureArray ['VALID']) . '">' . trim($label) . '</span>');

        return $ndx;
    }

    /**
     * @param int $docId
     * @return Ccsd_Referentiels_Structure|null
     */
    protected function getDocidData($docId)
    {
        $s = new Ccsd_Referentiels_Structure($docId);

        $id = $s->getStructid();
        // Si le id n'est pas le même on a récupéré les données d'un alias
        if (($id == 0) || ($id != $docId)) {
            return null;
        }

        return $s;
    }

    /**
     * Index Id ext eg rnsr, idref etc.
     * @param $ndx
     * @param $structure
     * @return Ccsd_Referentiels_Idext
     */
    private function indexIdExt($ndx, $structure, $prefix = '')
    {

        foreach ($structure->getIdextLink() as $serverName => $serverData) {

            $serverData = array_map('trim', $serverData);

            $solrFieldName = trim(strtolower($serverName));

            if ($prefix != '') {
                $solrFieldName = $prefix . ucfirst($solrFieldName);
            }

            $ndx->addField($solrFieldName . '_s', $serverData['id']);
            if ($serverData['url']) {
                $ndx->addField($solrFieldName . 'Url_s', $serverData['url']);
            }
        }

        return $ndx;

    }

}

//end class
