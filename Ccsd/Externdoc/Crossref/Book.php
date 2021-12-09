<?php

/**
 * Created by PhpStorm.
 * User: sdenoux
 * Date: 06/07/18
 * Time: 16:19
 */

require_once "Ccsd/Externdoc/Crossref.php";

// @see https://wiki.epfl.ch/infoscience-historique/documents/crossref.pdf
class Ccsd_Externdoc_Crossref_Book extends Ccsd_Externdoc_Crossref
{
    /**
     * @var string
     */
    protected $_type = "OUV";

    // Relister les XPATH avec les constantes qui vont bien
    // BOOK_TYPE = edited_book, monograph, reference, other
    const XPATH_BOOK_TYPE = "/doi_records/doi_record/crossref/book/@book_type";

    // Contributors - Path Relatif
    const REL_XPATH_PERS = "/person_name";
    const REL_XPATH_LASTNAMES = "/person_name/surname";
    const REL_XPATH_FIRSTNAMES = "/person_name/given_name";
    const REL_XPATH_ORCID = "/person_name/ORCID";
    const REL_XPATH_SUFFIX = "/person_name/suffix";
    const REL_XPATH_AFFILIATIONS = "/person_name/affiliation";
    const REL_XPATH_ORGANIZATIONS = "/person_name/organization";
    const REL_XPATH_ORG_SEQUENCE = "/person_name/organization/@sequence";
    const REL_XPATH_ORG_ROLE = "/person_name/organization/@contributor_role";

    // Date de publication - Path relatif
    const REL_XPATH_PUBLICATION_DAY = "/day";
    const REL_XPATH_PUBLICATION_MONTH = "/month";
    const REL_XPATH_PUBLICATION_YEAR = "/year";

    // Path relatif - Numéros de page
    const REL_XPATH_FIRSTPAGE = "/first_page";
    const REL_XPATH_LASTPAGE = "/last_page";

    // Doi - Path relatif
    const REL_XPATH_DOI = "/doi";
    const REL_XPATH_TIMESTAMP = "/timestamp";
    const REL_XPATH_RESOURCE = "/resource";

    // Title - Path relatif
    const REL_XPATH_TITLE = "/titles/title";
    const REL_XPATH_SUBTITLE = "/subtitle";
    const REL_XPATH_ORG_TITLE = "/original_language_title";
    const REL_XPATH_ORG_LANG = "/original_language_title/@langugage";

    // Metas sur le book en question
    const XPATH_LANG = "/doi_records/doi_record/crossref/book/book_metadata/@language";
    const XPATH_VOLUME = "/doi_records/doi_record/crossref/book/book_metadata/volume";
    const XPATH_TITLES = "/doi_records/doi_record/crossref/book/book_metadata";
    const XPATH_PAGES = "/doi_records/doi_record/crossref/book/book_metadata/pages";
    const XPATH_FIRSTPAGE = "/doi_records/doi_record/crossref/book/book_metadata/pages/first_page";
    const XPATH_LASTPAGE = "/doi_records/doi_record/crossref/book/book_metadata/pages/last_page";
    const XPATH_OTHERPAGE = "/doi_records/doi_record/crossref/book/book_metadata/pages/other_pages";
    const XPATH_EDITION_NUMBER = "/doi_records/doi_record/crossref/book/book_metadata/edition_number";
    const XPATH_PUBLICATION_DATE = "/doi_records/doi_record/crossref/book/book_metadata/publication_date";
    const XPATH_ISBN = "/doi_records/doi_record/crossref/book/book_metadata/isbn[@media_type=\"print\"]";
    const XPATH_ISSN = "/doi_records/doi_record/crossref/book/book_metadata/isbn[@media_type=\"electronic\"]";
    const XPATH_PUBLISHER = "/doi_records/doi_record/crossref/book/book_metadata/publisher";
    const XPATH_PUBLISHER_NAME = "/doi_records/doi_record/crossref/book/book_metadata/publisher/publisher_name";
    const XPATH_PUBLISHER_PLACE = "/doi_records/doi_record/crossref/book/book_metadata/publisher/publisher_place";
    const XPATH_PUBLISHER_ITEM = "/doi_records/doi_record/crossref/book/book_metadata/publisher/publisher_place";
    const XPATH_PUBLISHER_ITEM_NUMBER = "/doi_records/doi_record/crossref/book/book_metadata/publisher_item/item_number";
    const XPATH_PUBLISHER_ITEM_ID = "/doi_records/doi_record/crossref/book/book_metadata/publisher_item/identifier";
    const XPATH_PUBLISHER_ITEM_ID_TYPE = "/doi_records/doi_record/crossref/book/book_metadata/publisher_item/identifier/@id_type";
    const XPATH_DOI_DATA = "/doi_records/doi_record/crossref/book/book_metadata/doi_data";

    // series_metadata correspond à des informations lorsque le livre se trouve dans une série de livres
    const XPATH_CONTRIBUTORS = "/doi_records/doi_record/crossref/book/book_metadata/contributors";
    const XPATH_SERIE_TITLES = "/doi_records/doi_record/crossref/book/book_metadata/series_metadata";
    const XPATH_SERIE_DOI = "/doi_records/doi_record/crossref/book/book_metadata/series_metadata/doi_data/doi";
    const XPATH_SERIE_RESOURCE = "/doi_records/doi_record/crossref/book/book_metadata/series_metadata/doi_data/resource";
    const XPATH_SERIE_ISBN = "/doi_records/doi_record/crossref/book/series_metadata/isbn";
    const XPATH_SERIE_CONTRIBUTORS = "/doi_records/doi_record/crossref/book/book_metadata/series_metadata/contributors/person_name";
    const XPATH_SERIE_LASTNAMES = "/doi_records/doi_record/crossref/book/book_metadata/series_metadata/contributors/person_name/surname";
    const XPATH_SERIE_FIRSTNAMES = "/doi_records/doi_record/crossref/book/book_metadata/series_metadata/contributors/person_name/given_name";
    const XPATH_SERIE_AFFILIATIONS = "/doi_records/doi_record/crossref/book/book_metadata/series_metadata/contributors/person_name/affiliation";

    // Les collections sont des éléments liés à la ressource principal par un attribut - Pas vraiment utilisé de ce que je comprends
    const XPATH_COL_ITEMS = "/doi_records/doi_record/crossref/book/book_metadata/doi_data/collection/item";
    const XPATH_COL_PROPERTY = "/doi_records/doi_record/crossref/book/book_metadata/doi_data/collection/item/property";
    const XPATH_COL_ITEM_DOI = "/doi_records/doi_record/crossref/book/book_metadata/doi_data/collection/item/doi_data/doi";
    const XPATH_COL_ITEM_RESOURCE = "/doi_records/doi_record/crossref/book/book_metadata/doi_data/collection/item/doi_data/resources";

    // Content_Item décrit le contenu du book
    // Todo ATTENTION IL Y A DU NESTING : UN content_item peut se trouver dans un autre (chapitre dans une section par exemple)
    const XPATH_CONTENT_ITEM = "/doi_records/doi_record/crossref/book/content_item";
    const XPATH_CONTENT_TYPE = "/doi_records/doi_record/crossref/book/content_item/@component_type";
    const XPATH_CONTENT_CONTRIBUTORS = "/doi_records/doi_record/crossref/book/content_item/contributors";
    const XPATH_CONTENT_TITLES = "/doi_records/doi_record/crossref/book/content_item/titles";
    const XPATH_COMP_NUMBER = "/doi_records/doi_record/crossref/book/content_item/component_number";
    const XPATH_CONTENT_DATE = "/doi_records/doi_record/crossref/book/content_item/publication_date";
    const XPATH_CONTENT_PAGES = "/doi_records/doi_record/crossref/book/content_item/pages";
    const XPATH_CONTENT_PUBLISHER_ITEM = "/doi_records/doi_record/crossref/book/content_item/publisher_item";
    const XPATH_CONTENT_DOI_DATA = "/doi_records/doi_record/crossref/book/content_item/doi_data";
    const XPATH_CONTENT_CITATION_LIST = "/doi_records/doi_record/crossref/book/content_item/citation_list";

    // Component décrit un parent lié par 3 relations possibles (isPartOf, isReferencedBy, isRequiredBy) - Tables, figures, images, vidéos, audio tracks, etc
    const XPATH_COMP = "/doi_records/doi_record/crossref/book/content_item/component_list/component";
    const XPATH_COMP_RELATION = "/doi_records/doi_record/crossref/book/content_item/component_list/component/@parent_relation";
    const XPATH_COMP_TITLES = "/doi_records/doi_record/crossref/book/content_item/component_list/component/titles";
    const XPATH_COMP_CONTRIBUTORS = "/doi_records/doi_record/crossref/book/content_item/component_list/component/contributors";
    const XPATH_COMP_DESC = "/doi_records/doi_record/crossref/book/content_item/component_list/component/desc";
    const XPATH_COMP_DATE = "/doi_records/doi_record/crossref/book/content_item/component_list/component/publication_date";
    const XPATH_COMP_FORMAT = "/doi_records/doi_record/crossref/book/content_item/component_list/component/format";
    const XPATH_COMP_FORMAT_TYPE = "/doi_records/doi_record/crossref/book/content_item/component_list/component/format/@mime_type";
    const XPATH_COMP_DOI_DATA = "/doi_records/doi_record/crossref/book/content_item/component_list/component/doi_data";


    // Todo : à utiliser pour récupérer la littérature citée à partir du DOI
    const XPATH_CITATION_LIST = "/doi_records/doi_record/crossref/book/book_metadata/contributors";

    /**
     * @param string $id
     * @param string $xmlDom
     */
    static public function createFromXML($id, $xmlDom)
    {
        $doc = new Ccsd_Externdoc_Crossref_Book($id);
        $doc->setDomPath(new DOMXPath($xmlDom));
        return $doc;
    }

    public function getTitle($defaultLang='en')
    {
        $title = $this->getValue(self::XPATH_TITLES.self::REL_XPATH_TITLE);
        $title = empty($title) ? "" : $title;

        // Transformation du titre en tableau avec la clé comme langue
        return $this->metasToLangArray($title, $defaultLang);
    }

    public function getSubtitle()
    {
        $subtitle = $this->getValue(self::XPATH_TITLES.self::REL_XPATH_SUBTITLE);
        $subtitle = empty($subtitle) ? "" : $subtitle;
        return $subtitle;
    }

    public function getIdentifier()
    {
        return $this->getValue(self::XPATH_DOI_DATA.self::REL_XPATH_DOI);
    }

    public function getDocLang()
    {
        // Todo
        //return $this->getValue();
    }

    public function getIsbn()
    {
        $isbn = $this->getValue(self::XPATH_ISBN);
        $isbn = empty($isbn) ? "" : $isbn;
        return $isbn;
    }

    public function getIssn()
    {
        $issn = $this->getValue(self::XPATH_ISSN);
        $issn = empty($issn) ? "" : $issn;
        return $issn;
    }

    public function getDate()
    {
        $yearconst = $this->getValue(self::XPATH_PUBLICATION_DATE.self::REL_XPATH_PUBLICATION_YEAR);
        $yearconst = empty($yearconst) ? "" : $yearconst;

        $monthconst = $this->getValue(self::XPATH_PUBLICATION_DATE.self::REL_XPATH_PUBLICATION_MONTH);
        $monthconst = empty($monthconst) ? "" : $monthconst;

        $dayconst = $this->getValue(self::XPATH_PUBLICATION_DATE.self::REL_XPATH_PUBLICATION_DAY);
        $dayconst = empty($dayconst) ? "" : $dayconst;

        return $this->formateDate($yearconst, $monthconst, $dayconst);
    }

    public function getSerie()
    {
        $serie = $this->getValue(self::XPATH_SERIE_TITLES.self::REL_XPATH_TITLE);
        $serie = empty($serie) ? "" : $serie;
        return $serie;
    }

    public function getVolume()
    {
        $volume = $this->getValue(self::XPATH_VOLUME);
        $volume = empty($volume) ? "" : $volume;
        return $volume;
    }

    public function getIssue()
    {
        $issue = $this->getValue(self::XPATH_EDITION_NUMBER);
        $issue = empty($issue) ? "" : $issue;
        return $issue;
    }

    public function getPage()
    {
        $first = $this->getValue(self::XPATH_FIRSTPAGE);
        $first = empty($first) ? "" : $first;

        $last = $this->getValue(self::XPATH_LASTPAGE);
        $last = empty($last) ? "" : $last;

        return $this->formatePage($first, $last);
    }

    public function getPublisher()
    {
        $publisher = $this->getValue(self::XPATH_PUBLISHER_NAME);
        $publisher = empty($publisher) ? "" : $publisher;
        return $publisher;
    }

    public function getPubPlace()
    {
        $pubplace = $this->getValue(self::XPATH_PUBLISHER_PLACE);
        $pubplace = empty($pubplace) ? "" : $pubplace;
        return $pubplace;
    }

    public function getEditor()
    {
        // Todo
    }

    /**
     * @param $interMetas
     * @param $internames
     * @return array
     */
    public function getAuthors()
    {
        // TODO : prendre en compte les autres infos de l'auteur : affiliations, etc

        $fullNames = $this->getValue(self::XPATH_CONTRIBUTORS.self::REL_XPATH_PERS);
        $fullNames = is_array($fullNames) ? $fullNames : [$fullNames];

        $firstNames = $this->getValue(self::XPATH_CONTRIBUTORS.self::REL_XPATH_FIRSTNAMES);
        $firstNames = is_array($firstNames) ? $firstNames : [$firstNames];

        $lastNames = $this->getValue(self::XPATH_CONTRIBUTORS.self::REL_XPATH_LASTNAMES);
        $lastNames = is_array($lastNames) ? $lastNames : [$lastNames];

        $orcids = $this->getValue(self::XPATH_CONTRIBUTORS.self::REL_XPATH_ORCID);
        $orcids = is_array($orcids) ? $orcids : [$orcids];

        return $this->formateAuthors($fullNames, $firstNames, $lastNames, [], $orcids);
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

            $meta = "";

            switch ($metakey) {
                case self::META_TITLE :
                    $meta = $this->getTitle();
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
                case self::META_SERIE :
                    $meta = $this->getSerie();
                    break;
                case self::META_VOLUME :
                    $meta = $this->getVolume();
                    break;
                case self::META_ISSUE :
                    $meta = $this->getIssue();
                    break;
                case self::META_PAGE :
                    $meta = $this->getPage();
                    break;
                case self::META_PUBLISHER :
                    $meta = $this->getPublisher();
                    break;
                case self::META_PUBLOCATION :
                    $meta = $this->getPubPlace();
                    break;
                case self::META_SERIESEDITOR :
                    $meta = $this->getEditor();
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
        $this->_metas[self::META_IDENTIFIER]["doi"] = $this->_id;
        $this->_metas[self::AUTHORS] = $this->getAuthors();

        $this->_metas[self::DOC_TYPE] = $this->_type;
        return $this->_metas;
    }
}

Ccsd_Externdoc_Crossref::registerType("/doi_records/doi_record/crossref/book", "Ccsd_Externdoc_Crossref_Book");