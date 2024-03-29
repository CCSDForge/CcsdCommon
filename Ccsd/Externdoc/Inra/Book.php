<?php


class Ccsd_Externdoc_Inra_Book extends Ccsd_Externdoc_Inra
{


    /**
     * @var string
     */
    protected $_type = 'OUV';

    const META_ARRAY_BOOKTYPE =
        [   'Cahiers de recherche',
            'Dictionnaire/Encyclopédie',
            'Synthèse',
            'Traduction'
        ];


    //const META_BOOKTYPE = 'bookType';


    protected $_specific_wantedTags = [
        self::META_BOOK_DIRECTOR,
        self::META_PUBLISHED,
        self::META_BOOKTYPE,
        self::META_LINK,
        self::META_PUBLISHER_NAME
    ];



    /**
     * @param string $id
     * @param DOMDocument $xmlDom
     * @return Ccsd_Externdoc_Inra_Book
     */
    static public function createFromXML($id, $xmlDom)
    {
        $doc = new Ccsd_Externdoc_Inra_Book($id);

        $domxpath = self::dom2xpath($xmlDom);

        $doc->setDomPath($domxpath);
        return $doc;
    }

    /**
     * @return string|void
     */
    public function getHalTypology()
    {

        return $this->_type;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getOtherType()
    {
        return $this->getBookType();
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getBookType(){
        $bookType = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_BOOKTYPE);
        $bookType = empty($bookType) ? '' : $bookType;
        if (!empty($bookType) && !in_array($bookType,self::META_ARRAY_BOOKTYPE,true)){
            $bookType='';
        }
        return $bookType;
    }

    /**
     * @return string
     */
    public function getNbPage(): string
    {
        $nbPage = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_PAGES);
        $nbPage = empty($nbPage) ? '' : $nbPage;
        return $nbPage;

    }


    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getBookLink(){

        $bookLink = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_BOOKINFOS.self::REL_XPATH_BOOKINFOS_BOOKLINK);
        $bookLink = empty($bookLink) ? '' : $bookLink;
        return $bookLink;

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

                case self::META_BOOKTYPE :
                    $meta = $this->getBookType();
                    break;
                case self::META_BOOK_DIRECTOR :
                    $meta = $this->getDirector();
                    break;
                case self::META_PUBLISHED:
                    $meta = $this->getPublished();
                    break;
                case self::META_PUBLISHER_NAME:
                    $meta = $this->getAllPublisherInfo();
                    break;
                case self::META_DOCUMENTLOCATION:
                    $meta = $this->getDocumentLocation();
                    break;
                case self::META_PUBLICATION_LOCATION:
                    $meta = $this->getPubPlace();
                    break;
                case self::META_LINK:
                    $meta= $this->getRecordLink();
                    break;
                default:
                    break;
            }

            if (!empty($meta)) {
                $this->_metas[self::META][$metakey] = $meta;
            }
        }

        // Gestion spécifique de la méta page
        if (!empty($this->getNbPage())){
            $this->_metas[self::META][self::META_PAGE]= $this->getNbPage();
        }
        else if (isset($this->_metas[self::META][self::META_PAGE])) {
            unset($this->_metas[self::META][self::META_PAGE]);
        }

        return $this->_metas;

    }


}

Ccsd_Externdoc_Inra::registerType("/produit/record[@xsi:type='ns2:book']", "Ccsd_Externdoc_Inra_Book");