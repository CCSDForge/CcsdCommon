<?php

class Ccsd_Externdoc_Pubmedcentral extends Ccsd_Externdoc_Pubmed
{
    /**
     * @var string
     */
    protected $_idtype = "pubmedcentral";

    const INTER_YEAR = "year";
    const INTER_MONTH = "month";
    const INTER_DAY = "day";
    const INTER_ISSN = "issn";
    const INTER_EISSN = "eissn";
    const INTER_ARTICLE_TITLE = "articleTitle";
    const INTER_JOURNAL_TITLE = "journalTitle";
    const INTER_COMPLETEAUTHOR = "author";
    const INTER_FIRSTNAME = "firstname";
    const INTER_LASTNAME = "lastname";
    const INTER_INITIALS = "initials";

    protected $_traductionArray = array(
        self::META_LANG => '/pmc-articleset/article/MedlineCitation/Article/Language',
        self::META_VOLUME => '/pmc-articleset/article/front/article-meta/volume',
        self::META_ISSUE => '/pmc-articleset/article/front/article-meta/issue',
        self::META_TITLE => '/pmc-articleset/article/front/article-meta/title-group/article-title',
        self::META_ABSTRACT => '/pmc-articleset/article/front/article-meta/abstract',
        self::META_PAGE => '/pmc-articleset/article/front/article-meta/counts/page-count/@count',
        self::META_MESH => '/PubmedArticleSet/PubmedArticle/MedlineCitation/MeshHeadingList/MeshHeading/DescriptorName',
        self::META_IDENTIFIER => '/pmc-articleset/article/front/article-meta/article-id',
        self::META_KEYWORD => '/pmc-articleset/article/front/article-meta/kwd-group/kwd',
        self::INTER_ISSN => '/pmc-articleset/article/front/journal-meta/issn[@pub-type="ppub"]',
        self::INTER_EISSN => '/pmc-articleset/article/front/journal-meta/issn[@pub-type="epub"]',
        self::INTER_YEAR => '/pmc-articleset/article/front/article-meta/pub-date/year',
        self::INTER_MONTH => '/pmc-articleset/article/front/article-meta/pub-date/month',
        self::INTER_DAY => '/pmc-articleset/article/front/article-meta/pub-date/day',
        self::INTER_JOURNAL_TITLE => '/pmc-articleset/article/front/journal-meta/journal-title-group/journal-title',
        self::INTER_COMPLETEAUTHOR => '/pmc-articleset/article/front/article-meta/contrib-group/contrib',
        self::INTER_FIRSTNAME => '/pmc-articleset/article/front/article-meta/contrib-group/contrib/name/given-names',
        self::INTER_LASTNAME => '/pmc-articleset/article/front/article-meta/contrib-group/contrib/name/surname',
    );

    // Tableau de correspondance Meta => Metas Intermediaires
    protected $_interToMetas = array(
        self::META_DATE => [self::INTER_YEAR, self::INTER_MONTH, self::INTER_DAY],
        self::META_JOURNAL => [self::INTER_ISSN, self::INTER_EISSN, self::INTER_JOURNAL_TITLE]
    );


    /**
     * Ccsd_Externdoc_Document constructor.
     * @param string $id
     * @param DOMDocument $metas
     */
    static public function createFromXML($id, $xmlDom)
    {
        $doc = new self($id);
        $doc->setDomPath(new DOMXPath($xmlDom));
        $doc->registerNamespace();

        if (!$doc->buildMetadatas()) {
            return null;
        }

        return $doc;
    }
}