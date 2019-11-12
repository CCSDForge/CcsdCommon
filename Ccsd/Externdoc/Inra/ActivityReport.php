<?php


class Ccsd_Externdoc_Inra_ActivityReport extends Ccsd_Externdoc_Inra
{

    /**
     * @var string
     */
    protected $_type = "";



    protected $_specific_wantedTags = [

    ];


    /**
     * @param string $id
     * @param DOMDocument $xmlDom
     * @return Ccsd_Externdoc_Inra_ActivityReport
     */
    static public function createFromXML($id, $xmlDom)
    {
        $doc = new Ccsd_Externdoc_Inra_ActivityReport($id);

        $domxpath = self::dom2xpath($xmlDom);

        $doc->setDomPath($domxpath);
        return $doc;
    }


    public function getMetadatas()
    {

        if (!empty($this->_metas)) {
            return $this->_metas;
        }

        $this->_metas = parent::getMetadatas();




        foreach ($this->_specific_wantedTags as $metakey) {

            $meta = "";

            switch ($metakey) {

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
Ccsd_Externdoc_Inra::registerType("/produit/record[@xsi:type='ns2:activityReport']", "Ccsd_Externdoc_Inra_ActivityReport");