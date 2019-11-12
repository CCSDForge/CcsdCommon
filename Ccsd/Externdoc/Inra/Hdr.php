<?php


class Ccsd_Externdoc_Inra_Hdr extends Ccsd_Externdoc_Inra
{


    /**
     * @var string
     */
    protected $_type = "HDR";



    protected $_specific_wantedTags = [
        self::META_HOSTLABORATORY_INRA,
        self::META_DATEDEFENDED,
        self::META_JURYCHAIR,
        self::META_JURYCOMPOSITION,
        self::META_NBPAGES_INRA,
        self::META_AUTHORITYINSTITUTION,
        self::META_SPECIALITY_INRA,
    ];

    /**
     * @param string $id
     * @param DOMDocument $xmlDom
     * @return Ccsd_Externdoc_Inra_Hdr
     */
    static public function createFromXML($id, $xmlDom)
    {
        $doc = new Ccsd_Externdoc_Inra_Hdr($id);

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
                case self::META_HOSTLABORATORY_INRA :
                    $meta = $this->getHostLaboratory();
                    break;
                case self::META_DATEDEFENDED :
                    $meta = $this->getDefenseDate();
                    break;
                case self::META_JURYCHAIR :
                    $meta = $this->getJuryChair();
                    break;
                case self::META_JURYCOMPOSITION :
                    $meta = $this->getJury();
                    break;
                case self::META_NBPAGES_INRA :
                    $meta = $this->getNbPage();
                    break;
                case self::META_AUTHORITYINSTITUTION :
                    $meta = $this->getOrganizationDegreeName();
                    break;
                case self::META_SPECIALITY_INRA :
                    $meta = $this->getSpeciality();
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

Ccsd_Externdoc_Inra::registerType("/produit/record[@xsi:type='ns2:hdr']", "Ccsd_Externdoc_Inra_Hdr");