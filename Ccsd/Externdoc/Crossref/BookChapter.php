<?php

/**
 * Created by PhpStorm.
 * User: sdenoux
 * Date: 06/07/18
 * Time: 16:19
 */

require_once "Ccsd/Externdoc/Crossref.php";
require_once "Ccsd/Externdoc/Crossref/Book.php";

// @see https://wiki.epfl.ch/infoscience-historique/documents/crossref.pdf
class Ccsd_Externdoc_Crossref_BookChapter extends Ccsd_Externdoc_Crossref_Book
{
    /**
     * @var string
     */
    protected $_type = "COUV";

    /**
     * @param string $id
     * @param string $xmlDom
     */
    static public function createFromXML($id, $xmlDom)
    {
        $doc = new Ccsd_Externdoc_Crossref_BookChapter($id);
        $doc->setDomPath(new DOMXPath($xmlDom));
        return $doc;
    }

    public function getTitle($defaultLang='en')
    {
        $title = $this->getValue(self::XPATH_CONTENT_ITEM.self::REL_XPATH_TITLE);
        $title = empty($title) ? "" : $title;

        // Transformation du titre en tableau avec la clé comme langue
        $title = $this->metasToLangArray($title, $defaultLang);
        return $title;
    }

    public function getSubtitle()
    {
        $subtitle = $this->getValue(self::XPATH_CONTENT_ITEM.self::REL_XPATH_SUBTITLE);
        $subtitle = empty($subtitle) ? "" : $subtitle;
        return $subtitle;
    }

    public function getIdentifier()
    {
        return $this->getValue(self::XPATH_DOI_DATA.self::REL_XPATH_DOI);
    }

    public function getDate()
    {
        $yearconst = $this->getValue(self::XPATH_CONTENT_DATE.self::REL_XPATH_PUBLICATION_YEAR);
        $yearconst = empty($yearconst) ? "" : $yearconst;

        $monthconst = $this->getValue(self::XPATH_CONTENT_DATE.self::REL_XPATH_PUBLICATION_MONTH);
        $monthconst = empty($monthconst) ? "" : $monthconst;

        $dayconst = $this->getValue(self::XPATH_CONTENT_DATE.self::REL_XPATH_PUBLICATION_DAY);
        $dayconst = empty($dayconst) ? "" : $dayconst;

        return $this->formateDate($yearconst, $monthconst, $dayconst);
    }

    public function getPage()
    {
        $first = $this->getValue(self::XPATH_CONTENT_PAGES . self::REL_XPATH_FIRSTPAGE);
        $first = empty($first) ? "" : $first;

        $last = $this->getValue(self::XPATH_CONTENT_PAGES . self::REL_XPATH_LASTPAGE);
        $last = empty($last) ? "" : $last;

        return $this->formatePage($first, $last);
    }

    /**
     * @param $interMetas
     * @param $internames
     * @return array
     */
    public function getAuthors()
    {
        // TODO : prendre en compte les autres infos de l'auteur : affiliations, etc

        $fullNames = $this->getValue(self::XPATH_CONTENT_CONTRIBUTORS.self::REL_XPATH_PERS);
        $fullNames = is_array($fullNames) ? $fullNames : [$fullNames];

        $firstNames = $this->getValue(self::XPATH_CONTENT_CONTRIBUTORS.self::REL_XPATH_FIRSTNAMES);
        $firstNames = is_array($firstNames) ? $firstNames : [$firstNames];

        $lastNames = $this->getValue(self::XPATH_CONTENT_CONTRIBUTORS.self::REL_XPATH_LASTNAMES);
        $lastNames = is_array($lastNames) ? $lastNames : [$lastNames];

        $orcids = $this->getValue(self::XPATH_CONTENT_CONTRIBUTORS.self::REL_XPATH_ORCID);
        $orcids = is_array($orcids) ? $orcids : [$orcids];

        return $this->formateAuthors($fullNames, $firstNames, $lastNames, [], $orcids);
    }

    // Refaire la fonction getMetaDatas spécifique
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
                case self::META_ISBN :
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
                case self::META_BOOKTITLE :
                case self::META_ISSUE :
                    // TODO
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

Ccsd_Externdoc_Crossref::registerType("/doi_records/doi_record/crossref/book/content_item[@component_type=\"chapter\"]", "Ccsd_Externdoc_Crossref_BookChapter", 20);