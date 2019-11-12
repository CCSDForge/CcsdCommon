<?php
/**
 * Created by PhpStorm.
 * User: genicot
 * Date: 07/03/19
 * Time: 17:01
 */

class Ccsd_Externdoc_Irstea_Article extends Ccsd_Externdoc_Irstea
{

    /**
     * @var string
     */
    protected $_type = 'ART';



    protected $_specific_wantedTags = [
        self::META_ARTICLETYPE,
        self::META_COLLECTION_SHORTTITLE,
        self::META_VULGARISATION,
        self::META_NOSPECIAL,
        self::META_TITRESPECIAL,
        self::META_PEERREVIEWED,
        self::META_PAGE
    ];







    /**
     * @param string $id
     * @param DOMDocument $xmlDom
     * @return Ccsd_Externdoc_Irstea_Article
     */
    public static function createFromXML($id, $xmlDom) : Ccsd_Externdoc_Irstea_Article
    {
        $doc = new self($id);

        $domxpath = self::dom2xpath($xmlDom);

        $doc->setDomPath($domxpath);
        return $doc;
    }



    // TODO

    /**
     * @return string
     */
    public function getHalTypology()
    {
        return 'ART';
    }



    /**
     * @return array
     */
    public function getMetadatas()
    {
        if (!empty($this->_metas)) {
            return $this->_metas;
        }

        $this->_metas[self::META] = [];
        $this->_metas[self::AUTHORS] = [];

        foreach ($this->_wantedTags as $metakey) {

            $meta = '';

            switch ($metakey) {
                case self::META_TITLE :
                    $meta = $this->getTitle();
                    break;
                case self::META_ALTTITLE :
                    $meta = $this->getAltTitle();
                    break;
                case self::META_SUBTITLE :
                    $meta = $this->getSubtitle();
                    break;
                case self::META_IDENTIFIER :
                    $meta = [];
                    break;
                case self::META_CONFISBN :
                    $meta = $this->getIsbn();
                    break;
                case self::META_DATE :
                    $meta = $this->getDate();
                    break;
                case self::META_COMMENT :
                    $meta = $this->getComment();
                    break;
                case self::META_SERIE :
                    $meta = $this->getSerie();
                    break;
                case self::META_DIRECTOR :
                    $meta = $this->getDirector();
                    break;
                case self::META_VOLUME :
                    $meta = $this->getVolume();
                    break;
                case self::META_ISSUE :
                    $meta = $this->getIssue();
                    break;
                case self::META_PUBLISHER :
                    $meta = $this->getPublisher();
                    break;
                case self::META_PUBLICATION :
                    $meta = $this->getPubPlace();
                    break;
                case self::META_SERIESEDITOR :
                    $meta = $this->getSeriesEditor();
                    break;
                case self::META_ABSTRACT :
                    $meta = $this->getAbstract();
                    break;
                case self::META_KEYWORD :
                    $meta = $this->getKeywords();
                    break;
                case self::META_DOMAIN :
                    $meta = $this->getHalDomain();
                    break;
                case self::META_SENDING:
                    $meta = $this->getHalSending();
                    break;
                case self::META_EXPERIMENTALUNIT:
                    $meta = $this->getExperimentalUnit();
                    break;
                case self::META_TARGETAUDIENCE:
                    $meta = $this->getTargetAudience();
                    break;
    //            case self::META_ISSN:
    //                $meta = $this->getIssn();
    //                break;
                case self::META_SOURCE:
                    $meta = $this->getSource();
                    break;
                case self::META_EUROPEANPROJECT:
                    $meta = $this->getEuropeanProject();
                    break;
                case self::META_FUNDING :
                    $meta = $this->getFunding();
                    break;
                default:
                    break;
            }

            if (!empty($meta)) {
                $this->_metas[self::META][$metakey] = $meta;
            }
        }

        foreach ($this->_specific_wantedTags as $metakey) {

            $meta = '';

            switch ($metakey) {
                case self::META_ARTICLETYPE :
                    $meta = $this->getTypeArticle();
                    break;
                case self::META_COLLECTION_SHORTTITLE:
                    $meta = $this->getCollectionShortTitle();
                    break;
                case self::META_VULGARISATION:
                    $meta = $this->getVulgarisation();
                    break;
                case self::META_NOSPECIAL:
                    $meta = $this->getTypeSpecial();
                    break;
                case self::META_TITRESPECIAL:
                    $meta = $this->getTitreSpecial();
                    break;
                case self::META_PEERREVIEWED:
                    $meta = $this->getArticlePeerReviewed();
                    break;
                case self::META_PAGE :
                    $meta = $this->getArticlePagination();
                    break;
                case self::META_EXPERIMENTALUNIT :
                    $meta = $this->getExperimentalUnit();
                    break;
                default:
                    break;
            }

            if (!empty($meta)) {
                $this->_metas[self::META][$metakey] = $meta;
            }
        }




        // Récupération de la langue du premier titre
        $titleLang = isset($this->_metas[self::META_TITLE]) ? array_keys($this->_metas[self::META_TITLE])[0] : '';

        // Ajout de la langue
        $this->_metas[self::META_LANG] = $this->formateLang($this->getDocLang(), $titleLang);


        // Gestion des identifiants
        if (!empty($this->getDOI())) $this->_metas[self::META_IDENTIFIER][self::META_DOI] = $this->getDOI();
        if (!empty($this->getIdentifier())) $this->_metas[self::META_IDENTIFIER][self::META_PRODINRA] = $this->getIdentifier();
        if (!empty($this->getUtKey())) $this->_metas[self::META_IDENTIFIER][self::META_WOS] = $this->getUtKey();
        if (!empty($this->getIssn())) $this->_metas[self::META_IDENTIFIER][self::META_ISSN] = $this->getIssn();


        // Construction des auteurs avec auteurs externes
        if (!empty($this->getAuthors())) $this->_metas[self::AUTHORS] = $this->getAuthors();

        if (!empty($this->getExtAuthors())) $this->_metas[self::EXTAUTHORS] = $this->getExtAuthors();

        // Ajout du document location
        if (!empty($this->getDocumentLocation())) $this->_metas[self::META_DOCUMENTLOCATION] = $this->getDocumentLocation();



        return $this->_metas;
    }
}

Ccsd_Externdoc_Irstea::registerType('Article de revue technique à comité de lecture', 'Ccsd_Externdoc_Irstea_Article');
Ccsd_Externdoc_Irstea::registerType('Article de revue technique sans comité de lecture','Ccsd_Externdoc_Irstea_Article');
Ccsd_Externdoc_Irstea::registerType('Data Paper','Ccsd_Externdoc_Irstea_Article');