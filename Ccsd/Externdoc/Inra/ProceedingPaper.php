<?php


class Ccsd_Externdoc_Inra_ProceedingPaper extends Ccsd_Externdoc_Inra
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
        self::META_PEERREVIEWED,
        self::META_NBPAGES_INRA,
        self::META_PROCEEDINGSTYPE,
        self::META_PROCEEDINGSTITLE,
        self::META_CONFINVITE
    ];



    /**
     * @param string $id
     * @param DOMDocument $xmlDom
     * @return Ccsd_Externdoc_Inra_ProceedingPaper
     */
    static public function createFromXML($id, $xmlDom)
    {
        $doc = new Ccsd_Externdoc_Inra_ProceedingPaper($id);

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
                case self::META_NBPAGES_INRA:
                    $meta = $this->getRecordPagination();
                    break;
                case self::META_PAGE :
                    $meta = $this->getPage();
                    break;
                case self::META_PROCEEDINGSTYPE:
                    $meta = $this->getProceedingType();
                    break;
                case self::META_CONFINVITE :
                    $meta = $this->getConferenceInvite();
                    break;
                case self::META_PAPERNUMBER :
                    $meta = $this->getPaperNumber();
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


Ccsd_Externdoc_Inra::registerType("/produit/record[@xsi:type='ns2:proceedingPaper']", "Ccsd_Externdoc_Inra_ProceedingPaper");