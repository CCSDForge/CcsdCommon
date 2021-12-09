<?php

class Ccsd_Externdoc_Pubmed extends Ccsd_Externdoc
{
    /**
     * @var string
     */
    protected $_idtype = "pubmed";

    /**
     * @var string
     */
    protected $_type = "ART";

    const INTER_YEAR = "year";
    const INTER_MONTH = "month";
    const INTER_DAY = "day";
    const INTER_ISSN = "issn";
    const INTER_EISSN = "eissn";
    const INTER_JOURNAL_TITLE = "journalTitle";
    const INTER_COMPLETEAUTHOR = "author";
    const INTER_FIRSTNAME = "firstname";
    const INTER_LASTNAME = "lastname";
    const INTER_INITIALS = "initials";

    protected $_traductionArray = array(
        self::META_LANG => '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/Language',
        self::META_VOLUME => '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/Journal/JournalIssue/Volume',
        self::META_ISSUE => '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/Journal/JournalIssue/Issue',
        self::META_TITLE => '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/ArticleTitle',
        self::META_ABSTRACT => '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/Abstract/AbstractText',
        self::META_PAGE => '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/Pagination/MedlinePgn',
        self::META_MESH => '/PubmedArticleSet/PubmedArticle/MedlineCitation/MeshHeadingList/MeshHeading/DescriptorName',
        self::META_IDENTIFIER => '/PubmedArticleSet/PubmedArticle/PubmedData/ArticleIdList/ArticleId',
        self::META_KEYWORD => '/PubmedArticleSet/PubmedArticle/MedlineCitation/KeywordList/Keyword',
        self::INTER_ISSN => '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/Journal/ISSN[@IssnType="Print"]',
        self::INTER_EISSN => '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/Journal/ISSN[@IssnType="Electronic"]',
        self::INTER_YEAR => '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/Journal/JournalIssue/PubDate/Year',
        self::INTER_MONTH => '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/Journal/JournalIssue/PubDate/Month',
        self::INTER_DAY => '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/Journal/JournalIssue/PubDate/Day',
        self::INTER_JOURNAL_TITLE => '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/Journal/Title',
        self::INTER_COMPLETEAUTHOR => '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/AuthorList/Author',
        self::INTER_FIRSTNAME => '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/AuthorList/Author/ForeName',
        self::INTER_LASTNAME => '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/AuthorList/Author/LastName',
        self::INTER_INITIALS => '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/AuthorList/Author/Initials'
    );

    // Tableau de correspondance Meta => Metas Intermediaires
    protected $_interToMetas = array(
        self::META_DATE => [self::INTER_YEAR, self::INTER_MONTH, self::INTER_DAY],
        self::META_JOURNAL => [self::INTER_ISSN, self::INTER_EISSN, self::INTER_JOURNAL_TITLE]
    );

    // Tableau de correspondance Auteur => Metas Intermediaires
    protected $_interToAuthors = array(
        self::AUTHORS => [self::INTER_COMPLETEAUTHOR, self::INTER_FIRSTNAME, self::INTER_LASTNAME, self::INTER_INITIALS]
    );

    protected $_xmlNamespace = array();

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

    /* Création du Referentiel Journal :
     *
     * @param $issns : array
     * @param $journaltitle : string
     *
     * @return Ccsd_Referentiels_Journal
     */

    public function treatjournal($interMetas, $internames)
    {
        $i=0;

        // Attention, les noms des variables INTER_? doivent correspondre aux variables du JOURNAL
        // const INTER_FULLTITLE = "JNAME"; par exemple
        while (isset($internames[$i])) {

            if (isset($interMetas[$internames[$i]])) {

                if (is_array($interMetas[$internames[$i]])) {
                    $interMetas[$internames[$i]] = $interMetas[$internames[$i]][0];
                }
            } else {
                $interMetas[$internames[$i]] = "";
            }

            $i++;
        }

        return $this->formateJournal($interMetas[$internames[2]], "", $interMetas[$internames[0]], $interMetas[$internames[1]]);
    }

    /* Traduction du tableau des auteurs :
     * Récupération des prénom/nom pour ajouter un auteur
     *
     * @param $firstnames : array
     * @param $lastnames : array
     * @param $initials : array
     *
     * @return $autors : array
     */

    public function treatauthors($interMetas, $internames)
    {
        return $this->createAuthorArray($interMetas, $internames);
    }
}