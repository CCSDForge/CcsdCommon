<?php


class Ccsd_Externdoc_Inra_Map extends Ccsd_Externdoc_Inra
{

    /**
     * @var string
     */
    protected $_type = 'MAP';



    protected $_specific_wantedTags = [
        self::META_SCALE,
        self::META_DESCRIPTION
    ];




    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getComment(){

        $note = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_NOTE);
        if (is_array($note)) {
            $note = implode($note);
        }
        $note = empty($note) ? '' : $note;
        $dimension  = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_SIZE);
        if (is_array($dimension)){
            $dimension = implode($dimension);
        }
        $dimension = empty($dimension) ? '' : $dimension ;
        $note=trim($note.' '.$dimension);
        return $note;
    }

    /**
     * @param string $id
     * @param DOMDocument $xmlDom
     * @return Ccsd_Externdoc_Inra_Map
     */
    static public function createFromXML($id, $xmlDom)
    {
        $doc = new Ccsd_Externdoc_Inra_Map($id);

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
                case self::META_SCALE :
                    $meta = $this->getScale();
                    break;
                case self::META_DESCRIPTION :
                    $meta = $this->getDescription();
                    break;
                case self::META_COMMENT :
                    $meta = $this->getComment();
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

Ccsd_Externdoc_Inra::registerType("/produit/record[@xsi:type='ns2:map']", "Ccsd_Externdoc_Inra_Map");