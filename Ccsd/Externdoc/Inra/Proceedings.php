<?php


class Ccsd_Externdoc_Inra_Proceedings extends Ccsd_Externdoc_Inra
{


    /**
     * @var string
     */
    protected $_type = "COMM";



    protected $_specific_wantedTags = [
        self::META_LINK,
        self::META_CONFTITLE,
        self::META_CONFDATESTART,
        self::META_CONFDATEEND,
        self::META_CITY,
        self::META_COUNTRY,
        self::META_PEERREVIEWED
    ];

    /**
     * @param string $id
     * @param DOMDocument $xmlDom
     * @return Ccsd_Externdoc_Inra_Proceedings
     */
    static public function createFromXML($id, $xmlDom)
    {
        $doc = new Ccsd_Externdoc_Inra_Proceedings($id);

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
                case self::META_LINK :
                    $meta = $this->getRecordLink();
                    break;
                case self::META_CONFTITLE:
                    $meta = $this->getEventName();
                    break;
                case self::META_CONFDATESTART:
                    $meta=$this->getEventMeetingStartDate();
                    break;
                case self::META_CONFDATEEND:
                    $meta = $this->getEventMeetingEndDate();
                    break;
                case self::META_CITY:
                    $meta = $this->getEventMeetingCity();
                    break;
                case self::META_COUNTRY:
                    $meta = $this->getEventMeetingCountry();
                    break;
                case self::META_PEERREVIEWED:
                    $meta = $this->getRecordPeerReviewed();
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



Ccsd_Externdoc_Inra::registerType("/produit/record[@xsi:type='ns2:proceedings']", "Ccsd_Externdoc_Inra_Proceedings");