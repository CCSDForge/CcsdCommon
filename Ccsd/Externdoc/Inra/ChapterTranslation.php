<?php


class Ccsd_Externdoc_Inra_ChapterTranslation extends Ccsd_Externdoc_Inra
{

    /**
     * @var string
     */
    protected $_type = "OTHER";



    const META_BOOKLINK = 'bookLink';
    const META_SPECIALTITLE = 'inra_titreSpecial_local';

    protected $_specific_wantedTags = [
        self::META_TITLE,
        self::META_BOOKAUTHOR,
        self::META_BOOK_DIRECTOR,
        self::META_LINK,
        self::META_SPECIALTITLE,
        self::META_NBPAGES_INRA,
        self::META_BOOKTYPE
    ];


    /**
     * @param string $id
     * @param DOMDocument $xmlDom
     * @return Ccsd_Externdoc_Inra_ChapterTranslation
     */
    static public function createFromXML($id, $xmlDom)
    {
        $doc = new Ccsd_Externdoc_Inra_ChapterTranslation($id);

        $domxpath = self::dom2xpath($xmlDom);

        $doc->setDomPath($domxpath);
        return $doc;
    }


    /**
     * @return string
     */
    public function getBookType()
    {
        return 'Traduction';
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

                case self::META_BOOKAUTHOR :
                    $meta = $this->getBookAuthor();
                    break;
                case self::META_BOOK_DIRECTOR :
                    $meta = $this->getBookDirector();
                    break;
                case self::META_LINK :
                    $meta = $this->getBookLink();
                    break;
                case self::META_SPECIALTITLE :
                    $meta = $this->getTitreSpecial();
                    break;
                case self::META_NBPAGES_INRA :
                    $meta = $this->getRecordPagination();
                    break;
                case self::META_BOOKTYPE :
                    $meta = $this->getBookType();
                    break;
                default:
                    break;
            }

            if (!empty($meta)) {
                $this->_metas[self::META][$metakey] = $meta;
            }
        }
        if (!empty($this->getNbPage())){
            if (isset($this->_metas[self::META_PAGE]))  $this->_metas[self::META_PAGE].=' (Nbre de page de l\'ouvrage :'.$this->getNbPage().' ) ';
            else $this->_metas[self::META_PAGE] = '(Nbre de page de l\'ouvrage :'.$this->getNbPage().' )';
        }


        return $this->_metas;


    }
}


Ccsd_Externdoc_Inra::registerType("/produit/record[@xsi:type='ns2:chapterTranslation']", "Ccsd_Externdoc_Inra_ChapterTranslation");