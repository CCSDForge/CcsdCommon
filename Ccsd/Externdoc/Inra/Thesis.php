<?php


class Ccsd_Externdoc_Inra_Thesis extends Ccsd_Externdoc_Inra
{


    /**
     * @var string
     */
    protected $_type = "THESE";



    protected $_specific_wantedTags = [
        self::META_LINK,
        self::META_DATEDEFENDED,
        self::META_SPECIALITY_INRA,
        self::META_GRANT_INRA,
        self::META_HOSTLABORATORY_INRA,
        self::META_AUTHORITYINSTITUTION,
        self::META_JURYCHAIR,
        self::META_JURYCOMPOSITION,
        self::META_PAGE,
        self::META_THESISSCHOOL,


    ];


    /**
     * @param string $id
     * @param DOMDocument $xmlDom
     * @return Ccsd_Externdoc_Inra_Thesis
     */
    static public function createFromXML($id, $xmlDom)
    {
        $doc = new Ccsd_Externdoc_Inra_Thesis($id);

        $domxpath = self::dom2xpath($xmlDom);

        $doc->setDomPath($domxpath);
        return $doc;
    }

    /**
     * @return array
     */
    public function getMetadatas()
    {

        if (!empty($this->_metas)) {
            return $this->_metas;
        }

        $this->_metas = parent::getMetadatas();




        foreach ($this->_specific_wantedTags as $metakey) {

            $meta = "";

            switch ($metakey) {

                case self::META_GRANT_INRA:
                    $meta = $this->getGrant();
                    break;
                case self::META_HOSTLABORATORY_INRA :
                    $meta = $this->getHostLaboratory();
                    break;
                case self::META_SPECIALITY_INRA :
                    $meta = $this->getSpeciality();
                    break;
                case self::META_LINK :
                    $meta = $this->getRecordLink();
                    break;
                case self::META_DATEDEFENDED :
                    $meta = $this->getDefenseDate();
                    break;
                case self::META_AUTHORITYINSTITUTION :
                    $meta = $this->getOrganizationDegreeName();
                    break;
                case self::META_JURYCHAIR :
                    $meta = $this->getThesisDirector();
                    break;
                case self::META_JURYCOMPOSITION :
                    $meta = $this->getJuryComposition();
                    break;
                case self::META_PAGE :
                    $meta = $this->getNbPage();
                    break;
                case self::META_THESISSCHOOL :
                    $meta = $this->getAuthorityInstitution();
                    break;
                default:
                    break;
            }

            if (!empty($meta)) {
                $this->_metas[self::META][$metakey] = $meta;
            }
        }


        return $this->_metas;


    }
}

Ccsd_Externdoc_Inra::registerType("/produit/record[@xsi:type='ns2:thesis']", "Ccsd_Externdoc_Inra_Thesis");