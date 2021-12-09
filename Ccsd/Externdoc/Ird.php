<?php

/**
 * Class Ccsd_Externdoc_Ird
 * Document XML recupere de l'IRD
 */
class Ccsd_Externdoc_Ird extends Ccsd_Externdoc
{
    /**
     * @var string
     */
    protected $_idtype = "ird";

    const INTER_AUTEUR = "auteur";
    const INTER_NOM = "lastname";
    const INTER_PRENOM = "firstname";
    const INTER_JOURNAL = "journal";
    const INTER_KEYWORDS = "keyword";

    protected $_traductionArray = array(
        self::META_LANG => '/notice/LANGUE',
        self::META_TITLE => '/notice/TITLE',
        self::META_ABSTRACT => '/notice/ABSTRACT',
        self::META_DATE => '/notice/REFERENCE_BIBLIO/DATEPUB',
        self::META_VOLUME => '/notice/REFERENCE_BIBLIO/VOLUME',
        self::META_ISSUE => '/notice/REFERENCE_BIBLIO/ISSUE',
        self::META_PAGE => '/notice/REFERENCE_BIBLIO/PAGE',
        self::INTER_AUTEUR => '/notice/AUTEURS/AUTEUR',
        self::INTER_NOM => '/notice/AUTEURS/AUTEUR/NOM',
        self::INTER_PRENOM => '/notice/AUTEURS/AUTEUR/PRENOM',
        self::INTER_JOURNAL => '/notice/REFERENCE_BIBLIO/JOURNAL',
        self::INTER_KEYWORDS => '/notice/KEYWORD',

        self::META_IDENTIFIER => '/notice/REFERENCE_BIBLIO/URL_DOI',
        self::META_BOOKTITLE => '/notice/REFERENCE_BIBLIO/TITOUV',
        self::META_PUBLISHER => '/notice/REFERENCE_BIBLIO/EDCOM',
        self::META_CONFTITLE => '/notice/REFERENCE_BIBLIO/TITCONF',
        //self::META_CONFDATESTART => '/notice/REFERENCE_BIBLIO/TITCONF',
        //self::META_CONFDATEEND => '/notice/REFERENCE_BIBLIO/TITCONF',
        self::META_CITY => '/notice/REFERENCE_BIBLIO/VILLE',
        self::META_COUNTRY => '/notice/REFERENCE_BIBLIO/PAYS',
    );

    // Tableau de correspondance Meta => Metas Intermediaires
    protected $_interToMetas = array(
        self::META_KEYWORD => [self::INTER_KEYWORDS],
        self::META_JOURNAL => [self::INTER_JOURNAL]
    );

    // Tableau de correspondance Auteur => Metas Intermediaires
    protected $_interToAuthors = array(
        self::AUTHORS => [self::INTER_AUTEUR, self::INTER_PRENOM, self::INTER_NOM]
    );

    protected $_xmlNamespace = array();

    /**
     * Ccsd_Externdoc_Document constructor.
     * @param string $id
     * @param DOMDocument $xmlDom
     * @return Ccsd_Externdoc_Ird
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

    /** Traduction du tableau des mots-clés :
     *
     * @param string[][] $interMetas    (strings['keyword'][])
     * @param string[]   $internames
     * @return $keywords : array
     */

    public function treatkeyword($interMetas, $internames)
    {
        if(isset($interMetas[$internames[0]]))
            return explode(' ; ', $interMetas[$internames[0]]);

        return "";
    }

    /** Création du Referentiel Journal :
     *
     * @param $journal : array
     *
     * @return Ccsd_Referentiels_Journal
     */

    public function treatjournal($interMetas, $internames)
    {
        if (isset($interMetas[$internames[0]])) {

            // On prend le premier journal trouvé s'il y en a plusieurs
            if (is_array($interMetas[$internames[0]]))
                $interMetas[$internames[0]] =  $interMetas[$internames[0]][0];

        } else {
            $interMetas[$internames[0]] = "";
        }

        return $this->formateJournal($interMetas[$internames[0]], "", "", "");
    }

    /** Traduction du tableau des auteurs :
     * Récupération des prénom/nom pour ajouter un auteur
     *
     * @param $firstnames : array
     * @param $lastnames : array
     *
     * @return $autors : array
     */

    public function treatauthors($interMetas, $internames)
    {
        return $this->createAuthorArray($interMetas, $internames);
    }
}