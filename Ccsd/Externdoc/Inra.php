<?php
/**
 * Created by PhpStorm.
 * User: genicot
 * Date: 07/03/19
 * Time: 16:59
 */

use LanguageDetection\Language;
use SameerShelavale\PhpCountriesArray\CountriesArray;


class Ccsd_Externdoc_Inra extends Ccsd_Externdoc
{

    /**
     * @var string
     */
    protected $_idtype = 'doi';


    const DOC_TYPE = 'typdoc';





    /**
     * Clé : Le XPATH qui permet de repérer la classe => Valeur : La classe à créer
     * @var array
     */
    static public $_existing_types = [];

    protected $_xmlNamespace = array('ns2'=>'http://record.prodinra.inra.fr',
                                     'xsi'=>'http://www.w3.org/2001/XMLSchema-instance');


    public static $NAMESPACE = array('ns2'=>'http://record.prodinra.inra.fr',
                                        'xsi'=>'http://www.w3.org/2001/XMLSchema-instance');
    /**
     * @var DOMXPath
     */
    protected $_domXPath = null;

    const META_SUBTITLE = 'subTitle';
    const META_COMMENT_INRA = 'inra_inraComment_local';

    // Metadata Génériques pour tous les documents INRA

    const ARRAY_MAPPING_ROLE = [];

    // Typdoc
    const META_OTHERTYPE = 'otherType';
    const META_ARTICLETYPE = 'otherType'; //articleType';
    const META_BOOKTYPE = 'otherType';
    const META_AUDIOTYPE = 'audioType';
    const META_DEVELOPMENTTYPE_INRA = 'inra_developmentType_local';
    const META_REPORTTYPE = 'reportType';
    const META_RESEARCHREPORTTYPE = 'reportType';

    const META_INDEXATION = 'inra_indexation_local';

    const META_PROCEEDINGSTYPE = 'proceedingsType';


    const META_BOOKTITLE = 'bookTitle';

    const META_ALTTITLE = 'alternateTitle';
    const META_SENDING = 'hal_sending';
    const META_CLASSIFICATION = 'hal_classification';
    const META_DOCUMENTLOCATION = 'inra_lieu_local';
    const META_EXPERIMENTALUNIT = 'inra_infra_local';
    const META_ISSN = 'issn';
    const META_SOURCE = 'source';
    const META_NOSPECIAL = 'inra_noSpecial_local';
    const META_TITRESPECIAL = 'inra_titreSpecial_local';
    const META_TARGETAUDIENCE = 'inra_publicVise_local';
    const EXTAUTHORS = 'extAuthors';
    const META_EUROPEANPROJECT = 'europeanProject';
    const META_FUNDING = 'funding';
    const META_DIRECTOR = 'scientificEditor';
    const META_PEERREVIEWED = 'peerReviewing';
    const META_DOI = 'doi';
    const META_PRODINRA = 'prodinra';
    const META_JELCODE = 'jel';
    const META_WOS = 'wos';

    const META_NOARTICLE_INRA = 'inra_noArticle_local';
    const META_NBPAGES_INRA = 'inra_nbPages_local';
    const META_DEPOSIT_INRA = 'inra_deposit_local';
    const META_VALIDITY_INRA = 'inra_validity_local';
    const META_SUPPORT_INRA = 'inra_support_local';
    const META_HOSTLABORATORY_INRA = 'inra_hostlaboratory_local';
    const META_TRAININGSUPERVISOR_INRA = 'inra_trainingSupervisor_local';
    const META_DIFFUSEUR_INRA  = 'inra_diffuseur_local';
    const META_EMISSION_INRA = 'inra_diffuseur_local';

    const META_SPECIALITY_INRA = 'inra_speciality_local';
    const META_GRANT_INRA = 'inra_grant_local';
    const META_AUTHORITYINSTITUTION = 'authorityInstitution';
    const META_JURYCOMPOSITION = 'committee';

    const META_CONFLOCATION     = "conferenceLocation";
    const META_CONFISBN         = "conferenceISBN";
    const META_CONFTITLE        = "conferenceTitle";
    const META_LINK = 'publisherLink';

    const META_REPORTNUMBER = 'number';


    const META_JEL = 'jel';
    const META_BOOK_DIRECTOR = 'seriesEditor';
    const META_PUBLISHED = 'published';
    const META_PUBLISHER_NAME = 'publisher';
    const META_PUBLISHER_CITY = 'city';
    const META_PUBLISHER_COUNTRY = 'country';
    const META_PUBLICATION_LOCATION = 'publicationLocation';

    const META_BOOKAUTHOR = 'director';

    const META_BOOKLINK = 'bookLink';
    const META_SPECIALTITLE = 'inra_titreSpecial_local';

    const META_SCALE = 'scale';
    const META_DESCRIPTION = 'description';

    const META_VOIRAUSSI = 'seeAlso';

    const META_CONFINVITE = 'invitedCommunication';
    const META_PAPERNUMBER = 'number';

    const META_DATEDEFENDED = 'date';
    const META_JURYCHAIR    = 'director';
    const META_THESISSCHOOL = 'thesisSchool';

    // XPATH GENERIQUES

    // Racine de l'article

    const XPATH_ROOT = '/ns2:produits/produit';

    const XPATH_ROOT_RECORD = '//produit/record';

        const REL_XPATH_RECORD_ID = '/identifier';
        const REL_XPATH_RECORD_TYPE = '/itemType';


    const REL_XPATH_RECORD_THEMATIC = '/thematic';
        const REL_XPATH_THEMATIC_IDENTIFIER = '/identifier';
        const REL_XPATH_THEMATIC_NAME = '/name';
        const REL_XPATH_THEMATIC_INRACLASSIFICATION = '/inraClassification';
            const REL_XPATH_INRACLASSIFICATION_INRACLASSIFICATIONIDENTIFIER = '/inraClassificationIdentifier';
            const REL_XPATH_INRACLASSIFICATION_USEDTERM = '/usedTerm';
            const REL_XPATH_INRACLASSIFICATION_ENGTERM = '/engTerm';

    //Classification Hal
    const REL_XPATH_HAL_CLASSIFICATION = '/halClassification';

        const REL_XPATH_HAL_CLASSIFICATION_CODE = '/code';
        const REL_XPATH_HAL_CLASSIFICATION_FR = '/french';
        const REL_XPATH_HAL_CLASSIFICATION_EN = '/english';

    const REL_XPATH_RECORD_HALIDENTIFIER = '/halIdentifier';


    const REL_XPATH_HOSTLABORATORY_INRALABORATORY = '/inraLaboratory';
    //Auteurs : inraAuthor ou externalAuthor
    const REL_XPATH_RECORD_AUT = "/creator/author[@xsi:type='ns2:inraAuthor']";
    const REL_ROOT_AUT = "/author[@xsi:type='ns2:inraAuthor']";
        const REL_XPATH_AUT_LASTNAMES = '/lastName';
        const REL_XPATH_AUT_FIRSTNAMES = '/firstName';
        const REL_XPATH_AUT_PUBLICATIONNAME = '/';
        const REL_XPATH_AUT_EMAIL = '/email';
        const REL_XPATH_AUT_ROLE = '/role';
        const REL_XPATH_AUT_INRAIDENTIFIER = '/inraIdentifier';
        const REL_XPATH_AUT_ORCID = '/orcid';
        const REL_XPATH_AUT_PEPS = '/peps';
        const REL_XPATH_AUT_FUNDINGDEPARTMENT = '/fundingDepartment';


        const REL_XPATH_AUT_NEUTRALAFFILIATION = '/affiliation';

    // Affiliations pour chaque auteur externe ou INRA
        const REL_XPATH_AUT_AFFILIATION = '/inraAffiliation';
            const REL_XPATH_AFFILIATION_NAME = '/name';
            const REL_XPATH_AFFILIATION_ACRONYM = '/acronym';

                //Description des affiliations en Unité
                const REL_XPATH_AFFILIATION_UNIT = '/unit';
                    const REL_XPATH_UNIT_NAME = '/name';
                    const REL_XPATH_UNIT_CODE = '/code';
                    const REL_XPATH_UNIT_TYPE = '/type';
                    const REL_XPATH_UNIT_LABORATORY = '/laboratory';
                    const REL_XPATH_UNIT_CITY = '/city';
                    const REL_XPATH_UNIT_COUNTRY = '/country';
                    const REL_XPATH_UNIT_RNSR = '/rnsr';
                    const REL_XPATH_UNIT_ACRONYM = '/acronym';
                    // En centre
                    const REL_XPATH_UNIT_CENTER = '/center';
                        const REL_XPATH_CENTER_CODE = '/code';
                        const REL_XPATH_CENTER_NAME = '/name';
                        const REL_XPATH_CENTER_ACRONYM = '/acronym';
                    // En département
                    const REL_XPATH_UNIT_DEPARTMENT = '/department';
                        const REL_XPATH_DEPARTMENT_CODE = '/code';
                        const REL_XPATH_DEPARTMENT_NAME = '/name';
                        const REL_XPATH_DEPARTMENT_ACRONYM = '/acronym';
                        const REL_XPATH_DEPARTMENT_AUTHORITYTYPE= '/authorityType';
                    // En partenaires
                    const REL_XPATH_UNIT_AFFILIATIONPARTNERS = '/affiliationPartners';
                    const REL_XPATH_AFFILIATIONPARTNERS_NAME = '/name';
                    const REL_XPATH_AFFILIATIONPARTNERS_ACRONYM = '/acronym';
                    const REL_XPATH_AFFILIATIONPARTNERS_COUNTRY = '/country';
                    const REL_XPATH_AFFILIATIONPARTNERS_IDENTIFIER = '/identifier';

        //Auteurs externes : meme chose
        const REL_XPATH_RECORD_EXTAUT = "/creator/author[@xsi:type='ns2:externalAuthor']";
        const REL_ROOT_EXTAUT = "/author[@xsi:type='ns2:externalAuthor']";
            const REL_XPATH_EXTAUT_LASTNAMES = '/lastName';
            const REL_XPATH_EXTAUT_FIRSTNAMES = '/firstName';
            const REL_XPATH_EXTAUT_BUPLICATIONNAME ='/publicationName';
            const REL_XPATH_EXTAUT_EMAIL = '/email';
            const REL_XPATH_EXTAUT_ROLE = '/role';
            const REL_XPATH_EXTAUT_ORCID = '/orcid';

            const REL_XPATH_EXTAUT_EXTAFFILIATION = '/externalAffiliation';
                const REL_XPATH_EXTAFFILIATION_NAME = '/name';
                const REL_XPATH_EXTAFFILIATION_ID = '/identifier';
                const REL_XPATH_EXTAFFILIATION_ACRONYM = '/acronym';
                const REL_XPATH_EXTAFFILIATION_SECTION = '/section';
                const REL_XPATH_EXTAFFILIATION_CITY = '/city';
                const REL_XPATH_EXTAFFILIATION_COUNTRY = '/country';

                const REL_XPATH_EXTAFFILIATION_PARTNERS ='/partners';
                    const REL_XPATH_PARTNERS_ID = '/identifier';
                    const REL_XPATH_PARTNERS_NAME = '/name';
                    const REL_XPATH_PARTNERS_ACRONYM = '/acronym';
                    const REL_XPATH_PARTNERS_COUNTRY = '/country';

        // Informations de base sur l'article
        const REL_XPATH_RECORD_TITLE = '/title';
        const REL_XPATH_RECORD_TITLE_LANG = '/title/@language';
        const REL_XPATH_RECORD_ALTERNATETITLE = '/alternateTitle';
        const REL_XPATH_RECORD_SUBTITLE = '/subTitle';
        const REL_XPATH_RECORD_LANGUAGE = '/language';
        const REL_XPATH_RECORD_ABSTRACT = '/abstract';
        const REL_XPATH_RECORD_ABSTRACT_LANGUAGE = '/abstract/@language';
        const REL_XPATH_RECORD_YEAR = '/year';
        const REL_XPATH_RECORD_PAGES = '/pages';
        const REL_XPATH_RECORD_PAGINATION = '/pagination';
        const REL_XPATH_RECORD_NOTE = '/notes';
        const REL_XPATH_RECORD_DOI = '/doi';
        const REL_XPATH_RECORD_LINK = '/link';
        const REL_XPATH_RECORD_UTKEY = '/utKey';
        const REL_XPATH_RECORD_ACCESSCONDITION = '/recordAccessCondiion';
        const REL_XPATH_RECORD_KEYWORDS = '/keywords/keyword';
        const REL_XPATH_RECORD_TARGETAUDIENCE = '/targetAudience';
        const REL_XPATH_RECORD_CONTRACT = '/contract';
        const REL_XPATH_RECORD_EUROPEANPROJECT = '/fp';
            const REL_XPATH_EUROPEANPROJECT_GRANTNUMBER = '/grantNumber';
        const REL_XPATH_RECORD_ITEMTYPE = '/itemType';
        const REL_XPATH_RECORD_PMID = '/pmid';
        const REL_XPATH_RECORD_JELCODE = '/jelCode';
        const REL_XPATH_RECORD_HALSENDING = '/halSending';
        const REL_XPATH_RECORD_PEERREVIEWED = '/peerReviewed';

        const REL_XPATH_RECORD_TRAININGTITLE = '/trainingTitle';
        const REL_XPATH_RECORD_COURSETITLE = '/courseTitle';
        const REL_XPATH_RECORD_DEGREE = '/degree';
        const REL_XPATH_RECORD_ORGANIZATIONDEGREENAME = '/organizationDegree/name';
        const REL_XPATH_RECORD_ORGANIZATIONDEGREEACRONYM = '/organizationDegree/acronym';
        const REL_XPATH_RECORD_ORGANIZATIONDEGREECITY = '/organizationDegree/city';
        const REL_XPATH_RECORD_ORGANIZATIONDEGREECOUNTRY = '/organizationDegree/country';
        const REL_XPATH_RECORD_GRANT = '/grant';
        const REL_XPATH_RECORD_SPECIALITY = '/speciality';

        const REL_XPATH_RECORD_THESISDIRECTOR = '/thesisDirector';
        const REL_XPATH_RECORD_JURYCOMPOSITION = '/juryComposition';
        const REL_XPATH_RECORD_GRADUATESCHOOL = '/graduateSchool';


        const REL_XPATH_RECORD_DISSERTATIONTYPE = '/dissertationType';
        const REL_XPATH_RECORD_DISSERTATIONDIRECTOR = '/dissertationDirector';
        const REL_XPATH_RECORD_INTERNSHIPSUPERVISOR = '/internshipSupervisor';
        const REL_XPATH_RECORD_DEFENSEDATE = '/defenseDate';

        const REL_XPATH_RECORD_JURYCHAIR = '/juryChair';

        const REL_XPATH_RECORD_RESEARCHREPORTTYPE = '/researchReportType';

        const REL_XPATH_RECORD_ORDER = '/order';
            const REL_XPATH_ORDER_CONTRACTNUMBER = '/contractNumber';
            const REL_XPATH_ORDER_FUNDING = '/funding';
            const REL_XPATH_ORDER_SUPERVISOR = '/supervisor';
            const REL_XPATH_ORDER_BACKER_IDENTIFIER = '/backer/identifier';

        const REL_XPATH_RECORD_REPORTDIRECTOR = '/reportDirector';
        const REL_XPATH_RECORD_REPORTNUMBER = '/reportNumber';

        const REL_XPATH_RECORD_ASSIGNEE = '/assignee';
        const REL_XPATH_RECORD_SUBMISSIONDATE = '/submissionDate';
        const REL_XPATH_RECORD_PATENTNUMBER = '/patentNumber';

        const REL_XPATH_RECORD_CLASSIFICATION = '/classification';
        const REL_XPATH_RECORD_PATENTLANDSCAPE = '/patentLandscape';

        const REL_XPATH_RECORD_AUDIOTYPE = '/audioType';
        const REL_XPATH_RECORD_BOOKTYPE = '/bookType';
        const REL_XPATH_RECORD_REPORTTYPE = '/reportType';


        const REL_XPATH_RECORD_DURATION = '/duration';
        const REL_XPATH_RECORD_MEDIA = '/media';

        const REL_XPATH_RECORD_ATTACHEDDOCUMENTS = '/attachedDocuments';
        const REL_XPATH_RECORD_SCALE = '/scale';
        const REL_XPATH_RECORD_SIZE = '/size';
        const REL_XPATH_RECORD_GEOGRAPHICSCOPE = '/geographicScope';
        const REL_XPATH_RECORD_RIGHTS = '/rights';

        const REL_XPATH_RECORD_FIRSTVERSIONYEAR ='/firstVersionYear';

        const REL_XPATH_RECORD_SOFTWARETYPE = '/softwareType';

        const REL_XPATH_RECORD_PROCEEDINGSTYPE = '/proceedingsType';
        const REL_XPATH_RECORD_PROCEEDINGSTITLE = '/proceedingsTitle';
        const REL_XPATH_RECORD_INVITEDCONFERENCE = '/invitedConference';
        const REL_XPATH_RECORD_PAPERNUMBER = '/paperNumber';

        const REL_XPATH_RECORD_HOSTLABORATORY = '/hostLaboratory';
            const REL_XPATH_HOSTLABORATORY = '/inraLaboratory';








        // Information de collection
        const REL_XPATH_RECORD_COLLECTION = '/collection';
            const REL_XPATH_COLLECTION_ID = '/idCollection';
            const REL_XPATH_COLLECTION_TITLE = '/title';
            const REL_XPATH_COLLECTION_SHORTTITLE = '/shortTitle';
            const REL_XPATH_COLLECTION_ISSN = '/issn';
            const REL_XPATH_COLLECTION_OPENACCESS = '/openAccess';
            const REL_XPATH_COLLECTION_ISSUE_NUMBER = '/issue/number';
            const REL_XPATH_COLLECTION_ISSUE_VOLUME = '/issue/volume';
            const REL_XPATH_COLLECTION_ISSUE_DIRECTOR = '/issue/directorIssue';
            const REL_XPATH_COLLECTION_JOURNALLINK = '/journalLink';
            const REL_XPATH_COLLECTION_SPECIALTITRE = '/issue/specialIssue/title';
            const REL_XPATH_COLLECTION_SPECIALTYPE = '/issue/specialIssue/type';
            const REL_XPATH_COLLECTION_DIRECTORISSUE = '/issue/directorIssue';
            const REL_XPATH_COLLECTION_ARTICLEAUTHOR = '/articleAuthor';

        const REL_XPATH_RECORD_EXPERIMENTALUNIT = '/experimentalUnit';

    // informations sur le book
        const REL_XPATH_RECORD_BOOKINFOS = '/bookInfos';
            const REL_XPATH_BOOKINFOS_TITLE = '/title';
            const REL_XPATH_BOOKINFOS_SUBTITLE = '/subtitle';
            const REL_XPATH_BOOKINFOS_BOOKAUTHOR = '/bookAuthor';
            const REL_XPATH_BOOKINFOS_BOOKDIRECTOR = '/bookDirector';
            const REL_XPATH_BOOKINFOS_BOOKLINK = '/bookLink';
            const REL_XPATH_BOOKINFOS_CHAPTERAUTHOR = '/chapterAuthor';
            const REL_XPATH_BOOKINFOS_CHAPTERTYPE = '/chapterType';
            const REL_XPATH_BOOKINFOS_PAGES = '/pages';
            const REL_XPATH_BOOKINFOS_PAGINATION = '/pagination';

    //informations sur l'article dans sa publication
        const REL_XPATH_RECORD_ARTICLEINFOS = '/articleInfos';
            const REL_XPATH_ARTICLEINFOS_TYPE = '/articleType';
            const REL_XPATH_ARTICLEINFOS_NUMBER = '/articleNumber';
            const REL_XPATH_ARTICLEINFOS_PEERREVIEWED = '/peerReviewed';
            const REL_XPATH_ARTICLEINFOS_PAGINATION = '/pagination';

    //informations sur le titre de l'ouvrage
        const REL_XPATH_RECORD_BOOKTITLEINFOS = '/bookTitleInfos';
            const REL_XPATH_BOOKTITLEINFOS_TITLE = '/title';
            const REL_XPATH_BOOKTITLEINFOS_SUBTITLE = '/subtitle';
            const REL_XPATH_BOOKTITLEINFOS_BOOKLINK = '/bookLink';
            const REL_XPATH_BOOKTITLEINFOS_CHAPTERAUTHOR = '/chapterAuthor';
            const REL_XPATH_BOOKTITLEINFOS_CHAPTERTYPE = '/chapterType';
            const REL_XPATH_BOOKTITLEINFOS_PAGINATION = '/pagination';
            const REL_XPATH_BOOKTITLEINFOS_PAGES = '/pages';

    //informations sur la localisation geographique

        const REL_XPATH_RECORD_DOCUMENTLOCATION = '/documentLocation';
            const REL_XPATH_DOCUMENTLOCATION_NAME = '/libraryName';
            const REL_XPATH_DOCUMENTLOCATION_COTE = '/libraryClassificationMark';
            const REL_XPATH_DOCUMENTLOCATION_UNIT = '/libraryUnit';
            const REL_XPATH_DOCUMENTLOCATION_CENTER = '/libraryCenter';

    //informations sur un évènement
        const REL_XPATH_RECORD_EVENT = '/event';
            const REL_XPATH_EVENT_NAME = '/name';



    //informations sur le lieu de l'event
            const REL_XPATH_EVENT_MEETING = '/meeting';
                const REL_XPATH_MEETING_COUNTRY = '/country';
                const REL_XPATH_MEETING_CITY = '/city';
                const REL_XPATH_MEETING_STARTDATE = '/period/startDate';
                const REL_XPATH_MEETING_ENDDATE = '/period/endDate';



    // Informations sur l'editeur
        const REL_XPATH_RECORD_PUBLISHING = '/publishing';
            const REL_XPATH_PUBLISHING_PUBLISHED = '/published';
            const REL_XPATH_PUBLISHING_PUBLICATION = '/publication';
            const REL_XPATH_PUBLICATION_ID = '/idPublisher';
            const REL_XPATH_PUBLICATION_NAME = '/publisherName';
            const REL_XPATH_PUBLICATION_CITY = '/publisherCity';
            const REL_XPATH_PUBLICATION_COUNTRY = '/publisherCountry';
            const REL_XPATH_PUBLICATION_ISBN = '/isbn';
            const REL_XPATH_PUBLICATION_PAGES = '/pages';

    //informations concernant la pièce jointe (le document)
    const XPATH_ROOT_ATTACHMENT = '/produit/ns2:attachment';
        const REL_XPATH_ATTACHMENT_ACCESSCONDITION = '/accessCondition';
        const REL_XPATH_ATTACHMENT_TITLE = '/title';
        const REL_XPATH_ATTACHMENT_FILENAME = '/fileName';
        const REL_XPATH_ATTACHMENT_RIGHTS = '/rights';
        const REL_XPATH_ATTACHMENT_ORIGINAL = '/original';
        const REL_XPATH_ATTACHMENT_MIMETYPE = '/fileMimeType';
        const REL_XPATH_ATTACHMENT_ATTACHMENTID = '/attachmentId';
        const REL_XPATH_ATTACHMENT_VERSION = '/version';


    const META_COLLECTION_SHORTTITLE = 'collectionShortTitle';
    const META_VULGARISATION = 'vulgarisation';

    protected $_wantedTags = array(
        self::ERROR,
        self::META_TITLE,
        self::META_ALTTITLE,
        self::META_SUBTITLE,
        self::META_LANG,
        self::META_DATE,
        self::META_KEYWORD,
        self::META_INDEXATION,
        self::META_JOURNAL,
        self::META_MESH,
        self::META_SERIE,
        self::META_VOLUME,
        self::META_PAGE,
        self::META_ISSUE,
        self::META_IDENTIFIER,
        self::META_COMMENT,
        self::META_DOMAIN,
        self::META_ISSUE,
        self::META_CITY,
        self::META_COUNTRY,
        self::META_BOOKTITLE,
        self::META_CONFTITLE,
        self::META_CONFDATESTART,
        self::META_CONFDATEEND,
        self::META_BIBLIO,
        self::META_BIBLIO_TITLE,
        self::META_PUBLISHER,
        self::META_PUBLISHER_COUNTRY,
        self::META_PUBLISHER_CITY,
        self::META_DOCUMENTLOCATION,
        self::META_CONFLOCATION,
        self::META_CONFISBN,
        self::META_ISBN,
        self::META_ABSTRACT,
        self::META_CLASSIFICATION,
        self::META_SENDING,
        self::META_EXPERIMENTALUNIT,
        self::META_ISSN,
        self::META_SOURCE,
        self::META_TARGETAUDIENCE,
        self::META_EUROPEANPROJECT,
        self::META_FUNDING,
        self::META_DIRECTOR,
        self::META_COMMENT,
        self::META_LINK,
        self::META_JELCODE,
        self::META_VOIRAUSSI
    );

    /**
     * @param $xmlDom
     * @return DOMXPath
     */
    public static function dom2xpath($xmlDom): DOMXPath
    {
        $domxpath = new DOMXPath($xmlDom);
        foreach (self::$NAMESPACE as $key => $value) {
            $domxpath->registerNamespace($key, $value);
        }
        return $domxpath;
    }


    /**
     * @param $node
     * @return DOMXPath
     */
    public function getDomXPath($node) : DOMXPath
    {

        $dom = new DOMDocument();
        if ($node instanceof DOMNode){
            $dom->appendChild($dom->importNode($node,true));
        }
        else if ($node instanceof DOMElement) {
            $dom->appendChild($node);
        }
        return self::dom2xpath($dom);

    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getRecordAccessCondition()
    {

        $recordAccessCondition = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_ACCESSCONDITION);
        $recordAccessCondition = empty($recordAccessCondition) ? '' : $recordAccessCondition;
        return $recordAccessCondition;

    }

    /**
     * @return DOMNodeList|DOMNodeList[]
     */
    public function getHalSending()
    {
        return $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_HALSENDING);
    }


    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getHalDomain()
    {
        $classif = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_HAL_CLASSIFICATION.self::REL_XPATH_HAL_CLASSIFICATION_CODE);
        if (is_array($classif)){
            foreach($classif as $key=>$item) {
                $classif[$key] = strtolower(str_replace(':', '.', $item));
                $classif[$key] = strtolower(str_replace('_','-',$classif[$key]));
                if ($classif[$key]==='sde.be.evo') $classif[$key] = 'sde.be';
                if ($classif[$key]==='sde.be.eco') $classif[$key] = 'sde.be';
                if ($classif[$key]==='sde.mcg.agro') $classif[$key] = 'sde.mcg';
                if ($classif[$key]==='shs.ec') $classif[$key] = 'shs';
            }
        }
        else {
            $classif = strtolower(str_replace(':', '.', $classif));
            $classif = strtolower(str_replace('_','-',$classif));
            if ($classif==='sde.be.evo') $classif = 'sde.be';
            if ($classif==='sde.be.eco') $classif = 'sde.be';
            if ($classif==='sde.mcg.agro') $classif= 'sde.mcg';
            if ($classif==='shs.ec') $classif= 'shs';
        }


        $classif = empty($classif) ?  'sdv' : $classif;
        return $classif;
    }


    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getIsbn()
    {
        $isbn = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_PUBLISHING.self::REL_XPATH_PUBLISHING_PUBLICATION.self::REL_XPATH_PUBLICATION_ISBN);
        if (is_array($isbn)){
            $isbn = implode(' ',$isbn);
        }
        $isbn = trim(preg_replace('/\s+/',' ',$isbn));
        $isbn = empty($isbn) ? '' : $isbn;
        if (strlen($isbn)>17) {
            $isbn = '';
        }
        return $isbn;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getIssn()
    {
        $issn = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_COLLECTION.self::REL_XPATH_COLLECTION_ISSN);
        if (is_array($issn)){
            foreach ($issn as $key=>$is){
                $issn[$key] = str_replace('x','X',$is);
            }
        }
        else {
            $issn = str_replace('x','X',$issn);
        }

        $issn = empty($issn) ? '' : $issn;
        return $issn;
    }

    public function getJournalLink()
    {
        $jl = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_COLLECTION.self::REL_XPATH_COLLECTION_JOURNALLINK);
        $jl = empty($jl) ? '' : $jl;
        return $jl;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getLink(){
        if (!empty($this->getJournalLink())) return $this->getJournalLink();
        if (!empty($this->getRecordLink())) return $this->getRecordLink();
        if (!empty($this->getBookLink())) return $this->getBookLink();

    }


    /**
     * @return string
     */
    public function getRecordLink()
    {
        $link = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_LINK);
        $link = empty($link) ? '' : $link;
        return $link;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getCollectionShortTitle(){
        $shortTitle = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_COLLECTION.self::REL_XPATH_COLLECTION_SHORTTITLE);
        $shortTitle = empty($shortTitle) ? '' : $shortTitle;
        return $shortTitle;
    }

    /**
     * @return string
     */

    public function getOriginalAuthor()
    {
        $oAuth = $this->getVlaue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_COLLECTION.self::REL_XPATH_COLLECTION_ARTICLEAUTHOR);
        $oAuth = empty($oAuth) ? '' : $oAuth;
        return $oAuth;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getPaperNumber()
    {
        $pn = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_PAPERNUMBER);
        $pn = empty($pn) ? '' : $pn;
        return $pn;
    }


    //Todo

    /**
     * @return string
     */
    public function getDate()
    {

        $yearconst = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_YEAR);
        $yearconst = empty($yearconst) ? '' : $yearconst;

        /**
        $monthconst = $this->getValue(self::XPATH_PUBLICATION_DATE.self::REL_XPATH_PUBLICATION_MONTH);
        $monthconst = empty($monthconst) ? '' : $monthconst;

        $dayconst = $this->getValue(self::XPATH_PUBLICATION_DATE.self::REL_XPATH_PUBLICATION_DAY);
        $dayconst = empty($dayconst) ? '' : $dayconst;

        return $this->formateDate($yearconst, $monthconst, $dayconst);
         **/



        return $yearconst;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getSerie()
    {
        $serie = $this->getValue(self::XPATH_ROOT_RECORD . self::REL_XPATH_RECORD_COLLECTION . self::REL_XPATH_COLLECTION_TITLE);
        $serie = empty($serie) ? '' : $serie;
        return $serie;

    }

    public function getDirector(){

        $director = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_COLLECTION.self::REL_XPATH_COLLECTION_DIRECTORISSUE);
        $director = empty($director) ? '' : $director;
        return $director;

    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getVolume()
    {
        $volume = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_COLLECTION.self::REL_XPATH_COLLECTION_ISSUE_VOLUME);
        $volume = empty($volume) ? '' : $volume;
        return $volume;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getIssue()
    {
        $issue = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_COLLECTION.self::REL_XPATH_COLLECTION_ISSUE_NUMBER);
        $issue = empty($issue) ? '' : $issue;
        return $issue;
    }


    public function getAuthorityInstitution()
    {
        $ai = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_GRADUATESCHOOL);
        $ai = empty($ai) ? '' : $ai;
        return $ai;
    }

    /**
     * @return string
     */
    public function getPage() : string
    {

        $page = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_ARTICLEINFOS.self::REL_XPATH_ARTICLEINFOS_PAGINATION);
        $page = empty($page) ? '' : $page;

        return $page;
    }

    /**
     * return string
     */

    public function getNbPage() : string
    {
        $nbPage = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_PAGES );
        $nbPage = empty($nbPage) ?  '' : $nbPage ;

        return $nbPage;

    }

    /**
     * @return string
     */
    public function getConferenceInvite() : string
    {
        $confInvit = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_INVITEDCONFERENCE);
        $confInvit = empty($confInvit) ? '' : $confInvit;

        if (!empty($confInvit) && $confInvit === 'true'){
            return true;
        }
        else if (!empty($confInvit) && $confInvit === 'false'){
            return false;
        }

        return $confInvit;

    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getProceedingType()
    {
        $pt = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_PROCEEDINGSTYPE);
        $pt = empty($pt) ? '' : $pt;

        return $pt;

    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getProceedingTitle()
    {
        $pt = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_PROCEEDINGSTITLE);
        $pt = empty($pt) ? '' : $pt;

        return $pt;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     * @Todo ( no information about publisher in echantillonnage )
     */
    public function getPublisher()
    {
        $publisher = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_PUBLISHING.self::REL_XPATH_PUBLISHING_PUBLICATION.self::REL_XPATH_PUBLICATION_NAME);
        $publisher = empty($publisher) ? '' : $publisher;
        if (is_array($publisher)){
            $publisher = implode(' ',$publisher);
        }
        return $publisher;
    }

    public function getAllPublisherInfo()
    {
        if ($this->_metas['identifier']['prodinra'] === '421259'){
            $x = 0;
        }
        $pub = $this->getPublisher();
        $pub = empty($pub) ? '' : $pub;
        $pub.=' '.$this->getPubPlace();
        return $pub;
    }


    /**
     * @return DOMNodeList|DOMNodeList[]|string
     * @Todo ( no information about publisher in echantillonnage )
     */
    public function getPubPlace()
    {
        $pubPlace = $this->getPubPlaceCity();
        if (is_array($pubPlace)) {
            $pubPlace = implode(',', $pubPlace);
        }
        if (empty($pubPlace)) {
            $pubPlace = '('.$this->getPubPlaceCountry().')';
        }
        else {
            $pubPlace.= ' ('.$this->getPubPlaceCountry().')';
        }
        if (is_array($pubPlace)) {
            $pubPlace = implode(' ', $pubPlace);
        }
        return $pubPlace;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getPubPlaceCity()
    {
        $pubPlaceCity = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_PUBLISHING.self::REL_XPATH_PUBLISHING_PUBLICATION.self::REL_XPATH_PUBLICATION_CITY);
        $pubPlaceCity = empty($pubPlaceCity) ? '' : $pubPlaceCity;
        return $pubPlaceCity;
    }

    public function getHalIdentifier()
    {
        $halIdentifier = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_HALIDENTIFIER);
        $halIdentifier = empty($halIdentifier) ? '' : $halIdentifier;
        return $halIdentifier;
    }


    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getPubPlaceCountry()
    {



        $pubPlaceCountry = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_PUBLISHING.self::REL_XPATH_PUBLISHING_PUBLICATION.self::REL_XPATH_PUBLICATION_COUNTRY);
        $pubPlaceCountry = empty($pubPlaceCountry)? '' : $pubPlaceCountry;

        if (!empty($pubPlaceCountry)){
            if ($pubPlaceCountry === 'The Netherlands'){
                $pubPlaceCountry = 'NLD';
            }
            if ($pubPlaceCountry === 'France' || $pubPlaceCountry === 'FR'){
                $pubPlaceCountry = 'FRA';
            }
            if ($pubPlaceCountry === 'UK'){
                $pubPlaceCountry = 'GBR';
            }
            if ($pubPlaceCountry === 'HR'){
                $pubPlaceCountry = 'HRV';
            }
            $arrayCountries = CountriesArray::get('alpha3','alpha2');
            foreach($arrayCountries as $key=>$country){
                if (is_array($pubPlaceCountry)){
                    foreach ($pubPlaceCountry as $keypub=>$pub){
                        if ($pub === 'The Netherlands'){
                            $pub = 'NLD';
                        }
                        if ($pub === 'UK'){
                            $pub = 'GBR';
                        }
                        if ($pub === 'HR'){
                            $pub = 'HRV';
                        }
                        if ($pub === 'France' || $pub === 'FR'){
                            $pub = 'FRA';
                        }
                        if ($pub === $key){
                            $pubPlaceCountry[$keypub]= strtolower($country);
                        }
                    }
                }
                else {
                    if ($pubPlaceCountry === $key) {
                        $pubPlaceCountry = strtolower($country);
                        break;
                    }
                }
            }

        }

        if ($pubPlaceCountry === 'INT'){
            $pubPlaceCountry = '';
        }

        $pubPlaceCountry = empty($pubPlaceCountry)? '' : $pubPlaceCountry;
        return $pubPlaceCountry;
    }


    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getPublished()
    {
        $isbn = $this->getIsbn();
        if (!empty($isbn)) {
            return 1 ;
        }
        else {
            return 0;
        }
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     *
     */
    public function getKeywords()
    {

        $langArray=['fr','en'];
        $langDoc = $this->formateLang($this->getDocLang(),$this->getDocLang());
        if (!in_array($langDoc,$langArray,true)){
            $langArray[] = $langDoc;
        }
        $keywords = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_KEYWORDS);
        $keywords = Ccsd_Tools::space_clean($keywords);

        if (!is_array($keywords)) {
            if (trim($keywords) !== '') {
                $keywords = [$keywords];
            } else if (trim($keywords) === '') {
                $keywords = [];
            }
        }

       $ld = new Language($langArray);
       $ld->setMaxNgrams(9000);

        $array_keywords=[];
        foreach ($keywords as $kw) {
            $kw = str_replace(chr(194).chr(160),'',$kw);
            if (!empty($kw)) {
                $lang = '' . $ld->detect($kw);
                //$lang = $this->getLanguage($kw);
                $array_keywords[$lang][] = $kw;
            }
        }

        return $array_keywords;
    }



    public function getLanguage($text) : string
    {
        return 'en';
    }

    /**
     * @param $domUnits
     * @return array
     */
    public function getUnit($domUnits) : array
    {


        $units = [];

        foreach($domUnits as $domUnit){
            $unit=[];
            $domXpath = $this->getDomXPath($domUnit);


            $name = $this->getValue(self::REL_XPATH_AFFILIATION_UNIT.self::REL_XPATH_UNIT_NAME, $domXpath);
            if (!empty($name)) {$unit['name'] = $name;}

            $rnsr = $this->getValue(self::REL_XPATH_AFFILIATION_UNIT.self::REL_XPATH_UNIT_RNSR, $domXpath);
            if (!empty($rnsr)) {$unit['rnsr'] = $rnsr;}

            $laboratory = $this->getValue(self::REL_XPATH_AFFILIATION_UNIT.self::REL_XPATH_UNIT_LABORATORY, $domXpath);
            if (!empty($laboratory)) {$unit['laboratory'] = $laboratory;}

            $type = $this->getValue(self::REL_XPATH_AFFILIATION_UNIT.self::REL_XPATH_UNIT_TYPE, $domXpath);
            if (!empty($type)) {$unit['type'] = $type;}

            $code = $this->getValue(self::REL_XPATH_AFFILIATION_UNIT.self::REL_XPATH_UNIT_CODE, $domXpath);
            if (!empty($code)) {$unit['code'] = $code;}

            $country = $this->getValue(self::REL_XPATH_AFFILIATION_UNIT.self::REL_XPATH_UNIT_COUNTRY, $domXpath);
            if (!empty($country)) {$unit['country'] = $country;}

            $city = $this->getValue(self::REL_XPATH_AFFILIATION_UNIT.self::REL_XPATH_UNIT_CITY, $domXpath);
            if (!empty($city)) {$unit['city'] = $city;}

            $domDepartments = $domXpath->query(self::REL_XPATH_AFFILIATION_UNIT . self::REL_XPATH_UNIT_DEPARTMENT);
            $departments = $this->getDepartments($domDepartments);
            if (!empty($departments)) {$unit['departments'] = $departments;}


            $domPartners = $domXpath->query(self::REL_XPATH_AFFILIATION_UNIT . self::REL_XPATH_UNIT_AFFILIATIONPARTNERS);
            $affiliationsPartners = $this->getAffiliationsPartners($domPartners);
            if (!empty($affiliationsPartners)) {$unit['affiliationPartners'] = $affiliationsPartners;}

            if (!empty($unit)) {$units[] = $unit;}

        }

        return $units;

    }

    /**
     * @param $domPartners
     * @return array
     */
    public function getAffiliationsPartners($domPartners) : array
    {

        $affiliationsPartners = [];

        foreach ($domPartners as $domPartner){

            $affiliationPartner = [];

            $domXpath = $this->getDomXPath($domPartner);

            $name = $this->getValue(self::REL_XPATH_AFFILIATIONPARTNERS_NAME,$domXpath);
            if (!empty($name)) $affiliationPartner['name'] = $name;

            $acronym = $this->getValue(self::REL_XPATH_AFFILIATIONPARTNERS_ACRONYM,$domXpath);
            if (!empty($acronym)) $affiliationPartner['acronym'] = $acronym;

            if (!empty($affiliationPartner)) $affiliationsPartners[]=$affiliationPartner;

        }

        return $affiliationsPartners;

    }

    /**
     * @param $domDepartments
     * @return array
     */
    public function getDepartments($domDepartments) : array
    {

        $departments = [];

        foreach ($domDepartments as $domDepartment) {

            $department = [];

            $domXpath = $this->getDomXPath($domDepartment);

            $code = $this->getValue(self::REL_XPATH_UNIT_DEPARTMENT.self::REL_XPATH_DEPARTMENT_CODE, $domXpath);
            if (!empty($code)) $department['code']=$code;


            $name = $this->getValue(self::REL_XPATH_UNIT_DEPARTMENT.self::REL_XPATH_DEPARTMENT_NAME,$domXpath);
            if (!empty($name)) $department['name'] = $name;

            $acronym = $this->getValue(self::REL_XPATH_UNIT_DEPARTMENT.self::REL_XPATH_DEPARTMENT_NAME,$domXpath);
            if (!empty($acronym)) $department['acronym'] = $acronym;

            $authType = $this->getValue(self::REL_XPATH_UNIT_DEPARTMENT.self::REL_XPATH_DEPARTMENT_AUTHORITYTYPE,$domXpath);
            if (!empty($authType)) $department['authorityType'] = $authType;

            if (!empty($department)) $departments[]=$department;

        }

        return $departments;
    }


    /**
     * @param $arrayAffiliations
     * @return array
     */
    public function getInraAffiliation($arrayAffiliations) : array
    {

        $affiliations = [];

        foreach($arrayAffiliations as $affiliationNode ){

            $affiliation = [];
            $domXpath = $this->getDomXPath($affiliationNode);

            $name = $this->getValue(self::REL_XPATH_AUT_AFFILIATION.self::REL_XPATH_AFFILIATION_NAME,$domXpath);
            if (!empty($name)) {$affiliation['name'] = $name;}

            $acronym = $this->getValue(self::REL_XPATH_AUT_AFFILIATION.self::REL_XPATH_AFFILIATION_ACRONYM,$domXpath);
            if (!empty($acronym)) {$affiliation['acronym'] = $acronym;}


            $unitNode = $domXpath->query(self::REL_XPATH_AUT_AFFILIATION.self::REL_XPATH_AFFILIATION_UNIT);
            $unit = $this->getUnit($unitNode);
            if (!empty($unit)) {$affiliation['unit'] = $unit;}

            if (!empty($affiliation)) {$affiliations[]=$affiliation;}

        }

        return $affiliations;
    }

    /**
     * @param DOMNodeList $arrayExtAffiliations
     * @return array
     */
    public function getExtAffiliation($arrayExtAffiliations) : array
    {

        $affiliations = [];

        foreach($arrayExtAffiliations as $affiliationNode ){

            $affiliation = [];
            $domXpath = $this->getDomXPath($affiliationNode);

            $name = $this->getValue(self::REL_XPATH_EXTAUT_EXTAFFILIATION.self::REL_XPATH_EXTAFFILIATION_NAME,$domXpath);
            if (!empty($name)) {$affiliation['name'] = $name;}

            $acronym = $this->getValue(self::REL_XPATH_AUT_AFFILIATION.self::REL_XPATH_EXTAFFILIATION_ACRONYM,$domXpath);
            if (!empty($acronym)) {$affiliation['acronym'] = $acronym;}

            $city = $this->getValue(self::REL_XPATH_EXTAUT_EXTAFFILIATION.self::REL_XPATH_EXTAFFILIATION_CITY,$domXpath);
            if (!empty($city)) {$affiliation['city'] = $city;}

            $country = $this->getValue(self::REL_XPATH_EXTAUT_EXTAFFILIATION.self::REL_XPATH_EXTAFFILIATION_COUNTRY,$domXpath);
            if (!empty($country)) {$affiliation['country'] = $country;}

            $id = $this->getValue(self::REL_XPATH_EXTAUT_EXTAFFILIATION.self::REL_XPATH_EXTAFFILIATION_ID,$domXpath);
            if (!empty($id)){
                {$affiliation['identifier'] = $id;}
            }

            $partners = $this->getValue(self::REL_XPATH_EXTAUT_EXTAFFILIATION.self::REL_XPATH_EXTAFFILIATION_PARTNERS,$domXpath);
            if (!empty($partners)) {$affiliation['partners'] = $partners;}

            $section = $this->getValue(self::REL_XPATH_EXTAUT_EXTAFFILIATION.self::REL_XPATH_EXTAFFILIATION_SECTION,$domXpath);
            if (!empty($section)) {$affiliation['section'] = $section;}

            if (!empty($affiliation)) {$affiliations[]=$affiliation;}

        }

        return $affiliations;
    }



    /**
     * @return string
     */
    public function getSeriesEditor() : string
    {
        return '';
    }



    /**
     * Métadonnées spécifiques VOCINRA
     **/

    public function getIndexation(){

        $vocinra = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_THEMATIC.self::REL_XPATH_THEMATIC_NAME);
        $vocinra = empty($vocinra) ? '' : $vocinra;
        return $vocinra;
    }


    /**
     * @return string
     */
    public function getTypeSpecial() : string
    {

        $typeSpecial = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_COLLECTION.self::REL_XPATH_COLLECTION_SPECIALTYPE);
        $typeSpecial = empty($typeSpecial) ? '' : $typeSpecial;
        return $typeSpecial;

    }

    /**
     * @return string
     */
    public function getTitreSpecial() : string
    {
        $titreSpecial = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_COLLECTION.self::REL_XPATH_COLLECTION_SPECIALTITRE);
        $titreSpecial = empty($titreSpecial) ? '' : $titreSpecial;
        return $titreSpecial;
    }


    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getNoArticle(){
        $noArticle = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_ARTICLEINFOS.self::REL_XPATH_ARTICLEINFOS_NUMBER);
        $noArticle = empty($noArticle) ? '' : $noArticle;
        return $noArticle;
    }



    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getArticleNumber(){
        $articleNumber = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_ARTICLEINFOS.self::REL_XPATH_ARTICLEINFOS_NUMBER);
        $articleNumber = empty($articleNumber) ? '' : $articleNumber;
        return $articleNumber;
    }


    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getArticlePeerReviewed(){
        $peerReviewed = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_ARTICLEINFOS.self::REL_XPATH_ARTICLEINFOS_PEERREVIEWED);
        $peerReviewed = ($peerReviewed === 'false') ? '0' : '1' ;
        return $peerReviewed;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getArticlePagination(){
        $articlePagination = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_ARTICLEINFOS.self::REL_XPATH_ARTICLEINFOS_PAGINATION);
        $articlePagination = empty($articlePagination) ? '' : $articlePagination;
        return $articlePagination;
    }

    /**
     * @return string
     */
    public function getRecordPagination()
    {
        $recordPagination = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_PAGINATION);
        $recordPagination = empty($recordPagination) ? '' : $recordPagination;
        return $recordPagination;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getDocumentLocation(){

        $biblio = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_DOCUMENTLOCATION.self::REL_XPATH_DOCUMENTLOCATION_NAME);
        $biblio = empty($biblio)? '':$biblio;

        $cote = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_DOCUMENTLOCATION.self::REL_XPATH_DOCUMENTLOCATION_COTE);
        $cote = empty($cote)? '' : $cote;

        $unite = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_DOCUMENTLOCATION.self::REL_XPATH_DOCUMENTLOCATION_UNIT);
        $unite = empty($unite)? '' : $unite;

        $center = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_DOCUMENTLOCATION.self::REL_XPATH_DOCUMENTLOCATION_CENTER);
        $center = empty($center) ? '' : $center;

        if (is_array($biblio)){
            $biblio = implode(' ',$biblio);
        }
        if (is_array($cote)){
            $cote = implode(' ',$cote);
        }
        if (is_array($unite)){
            $unite = implode(' ',$unite);
        }
        if (is_array($center)){
            $center = implode(' ',$center);
        }


        $retour = empty($biblio)? '': $biblio;
        if (!empty($retour)) {
            $retour = empty($unite) ? $retour : $retour . ', ' . $unite;
        }
        else {
            $retour = empty($unite) ? '' : $unite;
        }
        if (!empty($retour)){
            $retour = empty($center) ? $retour : $retour .', '. $center;
        }
        else {
            $retour = empty($center) ? '' : $center;
        }
        if (!empty($retour)) {
            $retour = empty($cote) ? $retour : $retour . ' (' . $cote.')';
        }
        else {
            $retour = empty($cote) ? '' : '('.$cote.')';
        }


        return $retour;

    }

    /**
     * @return array|DOMNodeList|DOMNodeList[]
     */
    public function getExperimentalUnit(){

        $experimentalUnit = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_EXPERIMENTALUNIT);
        if (is_string($experimentalUnit) && !empty($experimentalUnit)) {
            $experimentalUnit = [$experimentalUnit];
        }
        $experimentalUnit = empty($experimentalUnit) ? [] : $experimentalUnit;
        return $experimentalUnit;


    }

    /**
     * @return bool
     */
    public function getVulgarisation(){

        $vulgarisation = trim($this->getTargetAudience());
        if ($vulgarisation==='Grand public' || $vulgarisation==='Pouvoirs publics' || $vulgarisation==='Autre' ) return 'OUI';
        else return 'NON';

    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getTargetAudience(){

        $targetAudience = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_TARGETAUDIENCE);
        $targetAudience = empty($targetAudience) ? '' : $targetAudience;

        if (is_array($targetAudience)){
            $targetAudience=$targetAudience[0];
        }


        If ($targetAudience === 'Professionnel'){
            $targetAudience = 'TE';
        }
        else if ($targetAudience === 'Grand Public'){
            $targetAudience = 'GP';
        }
        else if ($targetAudience === 'Pouvoirs publics'){
            $targetAudience = 'PP';
        }
        else if ($targetAudience === 'Autre'){
            $targetAudience = 'AU';
        }
        else if ($targetAudience === 'Etudiants'){
            $targetAudience = 'ET';
        }
        else if ($targetAudience === 'Scientifique'){
            $targetAudience = 'SC';
        }
        else {
            $targetAudience = '0';
        }


        return $targetAudience;

    }


    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getDeposit(){
        $deposit = $this->getValue();
        $deposit = empty($deposit) ? '' : $deposit;
        return $deposit;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getValidity(){

        $validity = $this->getValue();
        $validity = empty($validity) ? '' : $validity;
        return $validity;
    }



    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getHostLaboratory(){

        $inraLaboratory = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_HOSTLABORATORY.self::REL_XPATH_HOSTLABORATORY_INRALABORATORY);
        $inraLaboratory = empty($inraLaboratory) ? '' : $inraLaboratory;

        if ($inraLaboratory === 'Oui'){

            $acronym = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_HOSTLABORATORY.self::REL_XPATH_AUT_NEUTRALAFFILIATION.self::REL_XPATH_AFFILIATION_ACRONYM);
            $acronym = empty($acronym) ? '' : $acronym;

            $acronympartner = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_HOSTLABORATORY.self::REL_XPATH_AUT_NEUTRALAFFILIATION.self::REL_XPATH_AFFILIATION_UNIT.self::REL_XPATH_UNIT_AFFILIATIONPARTNERS.self::REL_XPATH_AFFILIATION_ACRONYM);
            $acronympartner = empty($acronympartner) ? '' : $acronympartner;

            $unittype = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_HOSTLABORATORY.self::REL_XPATH_AUT_NEUTRALAFFILIATION.self::REL_XPATH_AFFILIATION_UNIT.self::REL_XPATH_UNIT_TYPE);
            $unittype = empty($unittype) ? '' : $unittype;

            $unitcode = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_HOSTLABORATORY.self::REL_XPATH_AUT_NEUTRALAFFILIATION.self::REL_XPATH_AFFILIATION_UNIT.self::REL_XPATH_UNIT_CODE);
            $unitcode = empty($unitcode) ? '' : $unitcode;

            $unitacronym = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_HOSTLABORATORY.self::REL_XPATH_AUT_NEUTRALAFFILIATION.self::REL_XPATH_AFFILIATION_UNIT.self::REL_XPATH_UNIT_ACRONYM);
            $unitacronym = empty($unitacronym) ? '' : $unitacronym;

            $unitname = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_HOSTLABORATORY.self::REL_XPATH_AUT_NEUTRALAFFILIATION.self::REL_XPATH_AFFILIATION_UNIT.self::REL_XPATH_UNIT_NAME);
            $unitname = empty($unitname) ? '' : $unitname;

            $centername = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_HOSTLABORATORY.self::REL_XPATH_AUT_NEUTRALAFFILIATION.self::REL_XPATH_AFFILIATION_UNIT.self::REL_XPATH_UNIT_CENTER.self::REL_XPATH_CENTER_NAME);
            $centername = empty($centername) ? '' : $centername;

            $unitcity = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_HOSTLABORATORY.self::REL_XPATH_AUT_NEUTRALAFFILIATION.self::REL_XPATH_AFFILIATION_UNIT.self::REL_XPATH_UNIT_CITY);
            $unitcity = empty($unitcity) ? '' : $unitcity;

            $unitcountry = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_HOSTLABORATORY.self::REL_XPATH_AUT_NEUTRALAFFILIATION.self::REL_XPATH_AFFILIATION_UNIT.self::REL_XPATH_UNIT_COUNTRY);
            $unitcountry = empty($unitcountry) ? '' : $unitcountry;

            $array = ['acronym','acronympartner','unittype','unitacronym','unitname','centername','unitcity','unitcountry'];

            foreach($array as $namevar){
                if (is_array($$namevar)){
                    $$namevar = implode(' ',$$namevar);
                }
            }

            $hostLaboratory = $acronym.' - '.$acronympartner.', '.$unittype.' '.$unitcode.' '.$unitacronym.' '.$unitname.'. '.$centername.', '.$unitcity.', '.$unitcountry.'.';

        }
        else {
            $hostLaboratory = '';
        }

        return $hostLaboratory;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getExtLaboratory(){
        $extLaboratory = $this->getValue();
        $extLaboratory = empty($extLaboratory) ? '' : $extLaboratory;
        return $extLaboratory;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getSupport(){

        $support = $this->getValue();
        $support = empty($support) ? '' : $support;
        return $support;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getReportType(){

        $reportType = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_REPORTTYPE);
        $reportType = empty($reportType)? '' : $reportType;
        return $reportType;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getTrainingSupervisor(){
        $supervisor = $this->getValue();
        $supervisor = empty($supervisor) ? '' : $supervisor;
        return $supervisor;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getDiffuseur(){
        $diffuseur = $this->getValue();
        $diffuseur = empty($diffuseur) ? '' : $diffuseur;
        return $diffuseur;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getEmission(){


        $emission = $this->getValue();
        $emission = empty($emission) ? '' : $emission;
        return $emission;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getDevelopmentType(){

        $developmentType = $this->getValue();
        $developmentType = empty($developmentType) ? '' : $developmentType;
        return $developmentType;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getInraComment(){

        $inraComment = $this->getValue();
        $inraComment = empty($inraComment) ? '' : $inraComment;
        return $inraComment;
    }

    /**
     * Fin Métadonnées spécifiques INRA
     */



    /**
     * @param $interMetas
     * @param $internames
     * @return array
     */
    public function getAuthors() : array
    {

        $authors = [];


        $nodeAuthors = $this->getDomPath()->query(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_AUT);

        foreach ($nodeAuthors as $node ){

            $author = [];
            $domxpathAut = $this->getDomXPath($node);

            $firstNames = $this->getValue(self::REL_ROOT_AUT.self::REL_XPATH_AUT_FIRSTNAMES,$domxpathAut);
            if (!empty($firstNames)){
                $author['firstname'] = $firstNames;
            }

            $lastNames = $this->getValue(self::REL_ROOT_AUT.self::REL_XPATH_AUT_LASTNAMES,$domxpathAut);
            if (!empty($lastNames)){
                $author['lastname'] = $lastNames;
            }

            $affiliationsNode = $domxpathAut->query(self::REL_ROOT_AUT.self::REL_XPATH_AUT_AFFILIATION);
            $affiliations = $this->getInraAffiliation($affiliationsNode);
            if (!empty($affiliations)){
                $author['affiliation'] = $affiliations;
            }

            $extAffiliationsNode = $domxpathAut->query(self::REL_ROOT_AUT.self::REL_XPATH_EXTAUT_EXTAFFILIATION);
            $extAffiliations = $this->getExtAffiliation($extAffiliationsNode);
            if (!empty($extAffiliations)){
                $author['affiliation externe'] = $extAffiliations;
            }

            $orcIds = $this->getValue(self::REL_ROOT_AUT.self::REL_XPATH_AUT_ORCID,$domxpathAut);
            if (!empty($orcIds)){
                $author['orcid'] = $orcIds;
            }

            $emails = $this->getValue(self::REL_ROOT_AUT.self::REL_XPATH_AUT_EMAIL,$domxpathAut);
            if (!empty($emails)){
                $author['email'] = $emails;
            }

            if (!empty($author)){
                $authors[]=$author;
            }

        }


        return $authors;
        /**
        $fullNames = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_AUT);
        $fullNames = is_array($fullNames) ? $fullNames : [$fullNames];

        $firstNames = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_AUT.self::REL_XPATH_AUT_FIRSTNAMES);
        $firstNames = is_array($firstNames) ? $firstNames : [$firstNames];

        $lastNames = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_AUT.self::REL_XPATH_AUT_LASTNAMES);
        $lastNames = is_array($lastNames) ? $lastNames : [$lastNames];

        $affiliations = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_AUT.self::REL_XPATH_AUT_AFFILIATION);
        $affiliations = is_array($affiliations) ? $affiliations : [$affiliations];

        $orcids = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_AUT.self::REL_XPATH_AUT_ORCID);
        $orcids = is_array($orcids) ? $orcids : [$orcids];

        $emails = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_AUT.self::REL_XPATH_AUT_EMAIL);
        $emails = is_array($emails) ? $emails : [$emails];

        return $this->formateAuthors($fullNames, $firstNames, $lastNames, $affiliations, $orcids,$emails);
         * **/
    }

    /**
     * @return array
     */
    public function getExtAuthors() : array
    {

        $authors = [];

        $nodeAuthors = $this->getDomPath()->query(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_EXTAUT);

        foreach ($nodeAuthors as $node ){

            $author = [];
            $domxpathAut = $this->getDomXPath($node);

            $firstNames = $this->getValue(self::REL_ROOT_EXTAUT.self::REL_XPATH_EXTAUT_FIRSTNAMES,$domxpathAut);
            if (!empty($firstNames)) $author['firstname'] = $firstNames;

            $lastNames = $this->getValue(self::REL_ROOT_EXTAUT.self::REL_XPATH_EXTAUT_LASTNAMES,$domxpathAut);
            if (!empty($lastNames)) $author['lastname'] = $lastNames;

            $extaffiliationsNode = $domxpathAut->query(self::REL_ROOT_EXTAUT.self::REL_XPATH_EXTAUT_EXTAFFILIATION);
            $extaffiliations = $this->getExtAffiliation($extaffiliationsNode);
            if (!empty($extaffiliations)) $author['affiliation externe'] = $extaffiliations;

            $orcids = $this->getValue(self::REL_ROOT_EXTAUT.self::REL_XPATH_EXTAUT_ORCID,$domxpathAut);
            if (!empty($orcids)) $author['orcid'] = $orcids;

            $emails = $this->getValue(self::REL_ROOT_EXTAUT.self::REL_XPATH_EXTAUT_EMAIL,$domxpathAut);
            if (!empty($emails)) $author['email'] = $emails;

            if (!empty($author)) $authors[]=$author;

        }


        return $authors;
        /**

        $fullNames = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_EXTAUT);
        $fullNames = is_array($fullNames) ? $fullNames : [$fullNames];

        $firstNames = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_EXTAUT.self::REL_XPATH_EXTAUT_FIRSTNAMES);
        $firstNames = is_array($firstNames) ? $firstNames : [$firstNames];

        $lastNames = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_EXTAUT.self::REL_XPATH_EXTAUT_LASTNAMES);
        $lastNames = is_array($lastNames) ? $lastNames : [$lastNames];

        $affiliations = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_EXTAUT.self::REL_XPATH_EXTAUT_EXTAFFILIATION);
        $affiliations = is_array($affiliations) ? $affiliations : [$affiliations];

        $orcids = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_EXTAUT.self::REL_XPATH_EXTAUT_ORCID);
        $orcids = is_array($orcids) ? $orcids : [$orcids];

        $emails = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_AUT.self::REL_XPATH_AUT_EMAIL);
        $emails = is_array($emails) ? $emails : [$emails];

        return $this->formateAuthors($fullNames, $firstNames, $lastNames, $affiliations, $orcids,$emails);
         **/
    }


    /**
     * @return string
     */
    public function getSource() :string
    {
        return 'Prodinra';
    }

    /**
     * Création d'un Doc INRA à partir d'un XPATH
     * L'objet INRA est seulement une factory pour un sous-type réel.
     * @param string $id
     * @param DOMDocument $xmlDom
     * @return Ccsd_Externdoc_Inra | NULL
     */
    public static function createFromXML($id,$xmlDom)
    {
        $domxpath = self::dom2xpath($xmlDom);
        // On recherche le type de document associé au DOI à partir du XPATH de référence
        foreach (self::$_existing_types as $order => $xpath2class) {
            /**
             * @var string  $xpath
             * @var Ccsd_Externdoc $type
             */
            foreach ($xpath2class as $xpath => $type) {

                if ($domxpath->query($xpath)->count() > 0) {
                    return $type::createFromXML($id,$xmlDom);
                }
            }
        }

        return null;

    }


    /**
     * On recrée les auteurs à partir des tableaux de Noms Complet / Prénoms / Noms
     * @param $fullNames
     * @param $firstNames
     * @param $lastNames
     * @param $orcids
     * @param $emails
     * @return array
     * @deprecated fonction non utilisee afin de garder la structure déjà présente dans les documents.
     */
    protected function formateAuthors($fullNames, $firstNames, $lastNames, $affiliations = [], $orcids = [],$emails = [])
    {
        $finalAuthors = [];

        // Boucle sur chaque 'auteur'
        foreach ($fullNames as $i => $fullName) {

            foreach ($firstNames as $firstname) {
                $firstname = self::cleanFirstname($firstname);

                // Le prénom doit se trouver dans l'information complète de l'auteur
                if (strpos($fullName, $firstname) !== false) {
                    $finalAuthors[$i]['firstname'] = $firstname;
                    break;
                }
            }

            foreach ($lastNames as $lastName) {
                // Le nom doit se trouver dans l'information complète de l'auteur
                if (strpos($fullName, $lastName) !== false) {
                    $finalAuthors[$i]['lastname'] = $lastName;
                    break;
                }
            }

            foreach ($orcids as $orcid) {
                // L'orcid doit se trouver dans l'information complète de l'auteur
                if (strpos($fullName, $orcid) !== false) {
                    $finalAuthors[$i]['orcid'] = $orcid;
                    break;
                }
            }

            foreach ($affiliations as $affiliation) {
                // L'affiliation doit se trouver dans l'information complète de l'auteur
                if (strpos($fullName, $affiliation) !== false) {
                    $finalAuthors[$i]['affiliation'] = $affiliation;
                    break;
                }
            }

            foreach($emails as $email){
                // L'email doit se trouver dans l'informations complète de l'auteur
                if (strpos($fullName,$email) !== false){
                    $finalAuthors[$i]['email'] = $email;
                }
            }
        }

        return $finalAuthors;
    }


    /**
     * @param string $defaultLang
     * @return array
     */
    public function getTitle($defaultLang='en'):array
    {
        $children= $this->getDomPath()->query(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_TITLE);

        $array = [];
        if (isset($children)){
            foreach ($children as $blocTitle){
                $title = Ccsd_Tools::space_clean($blocTitle->nodeValue);
                $lang = Ccsd_Tools::space_clean($blocTitle->getAttribute('language'));
                $docLang = $this->getDocLang();


                $lang = $this->formateLang($lang,$docLang,$defaultLang);

                $array[$lang] = $title;
            }
        }

        $arrayAltTitle = $this->getAltTitle();
        $array=array_merge($array,$arrayAltTitle);

        return $array;
        /**
        $title = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_TITLE);
        $title = empty($title) ? '' : $title;

        $lang = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_TITLE.self::REL_XPATH_RECORD_TITLE_LANG);

        // Transformation du titre en tableau avec la clé comme langue
        return $this->metasToLangArray($title, $defaultLang);
         * */
    }


    /**
     * @return array
     */
    public function getAltTitle():array
    {
        $children = $this->getDomPath()->query(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_ALTERNATETITLE);

        $array = [];
        if (isset($children)){
            foreach ($children as $blocTitle){
                $title = Ccsd_Tools::space_clean($blocTitle->nodeValue);
                if ($title !=='') {
                    $lang = Ccsd_Tools::space_clean($blocTitle->getAttribute('language'));
                    $docLang = $this->getDocLang();


                    $lang = $this->formateLang($lang, $docLang);

                    $array[$lang] = $title;
                }
            }
        }

        return $array;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getSubtitle()
    {
        $subtitle = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_SUBTITLE);
        $subtitle = empty($subtitle) ? '' : $subtitle;
        return $subtitle;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]
     */
    public function getIdentifier()
    {
        return $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_ID);
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getUtKey()
    {
        $utkey = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_UTKEY);
        $utkey = empty($utkey) ? '' : $utkey;
        return $utkey;
    }


    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getDOI()
    {
        $doi = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_DOI);
        $doi = empty($doi) ? '' : $doi;

        $doi = explode(' ',$doi);

        $doi = $doi[0];
        if (strlen($doi) > 100) {
            $doi = substr($doi,0,99);
        }
        return $doi;
    }


    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getDocLang()
    {
        $docLang = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_LANGUAGE);
        $docLang = empty($docLang) ? '' : $docLang;
        return $docLang;
    }


    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getAbstract()
    {

        $children= $this->getDomPath()->query(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_ABSTRACT);
        $array = [];
        if (isset($children)){
            foreach ($children as $blocTitle){
                $abstract = Ccsd_Tools::space_clean($blocTitle->nodeValue);
                $lang = Ccsd_Tools::space_clean($blocTitle->getAttribute('language'));
                $docLang = $this->getDocLang();


                $lang = $this->formateLang($lang,$docLang);

                $array[$lang] = $abstract;
            }
        }

        return $array;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getPubmedId()
    {
        $pubmed = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_PMID);
        $pubmed = empty($pubmed) ? '' : $pubmed;

        return $pubmed;
    }


    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */

    public function getJelCode()
    {
        $jelCode = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_JELCODE);
        $array_jl =[];
        if (is_array($jelCode)){
            foreach($jelCode as $value){
                if (!empty(trim($value))){
                    $inter_array = explode('/',$value);
                    $index = count($inter_array) - 1 ;
                    $code = trim(explode('-',$inter_array[$index])[0]);
                    $array_jl[]=$code;
                }
            }
        }
        else if (!empty(trim($jelCode))){
            $code = trim(explode('-',$jelCode)[0]);
            $array_jl[]=$code;

        }

        foreach ($array_jl as $key=>$jl){
            if (strlen($jl) === 2 ){
                $jcode =''.$jl[0].'.'.$jl[0].$jl[1];
                $array_jl[$key] = $jcode;
            }
            else if (strlen($jl) === 3){
                $jcode =''.$jl[0].'.'.$jl[0].$jl[1].'.'.$jl[0].$jl[1].$jl[2];
                $array_jl[$key] = $jcode;
            }

            $array_jl[$key] = str_replace('.D92','',$array_jl[$key]);

        }

        return $array_jl;
    }

    /**
     * @param $xpath
     * @param $type
     * @param $order
     */
    public static function registerType($xpath, $type, $order = 50)
    {
        self::$_existing_types[$order][$xpath] = $type;
        // Il faut trier suivant l'ordre car PHP ne tri pas numeriquement par defaut
        ksort(self::$_existing_types);
    }


    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getComment(){

        $note = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_NOTE);
        if (is_array($note)) {
            $note = implode($note);
        }
        $note = empty($note) ? '' : $note;
        return $note;

    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getEuropeanProject(){

        $dbHALV3 = Zend_Db_Table_Abstract::getDefaultAdapter();

        $ep = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_EUROPEANPROJECT.self::REL_XPATH_EUROPEANPROJECT_GRANTNUMBER);
        $ep = empty($ep) ? '' : $ep;

        if (!empty($ep)){

            $ep = $dbHALV3->query("SELECT DISTINCT PROJEUROPID FROM `REF_PROJEUROP` WHERE NUMERO ='".$ep."' and VALID = 'VALID'");
            $array_db = $ep->fetchAll();
            if (count($array_db)>0){
                $ep = new Ccsd_Referentiels_Europeanproject($array_db[0]['PROJEUROPID']);
            }
            else{
                //echo 'Pas de projet europeen trouvé avec le GrantNumber '.$ep ;
                $ep='';
            }
        }
        else {
            $ep ='';
        }
        return $ep;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getFunding(){
        $contract = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_CONTRACT);
        $contract = empty($contract) ? [] : $contract;
        if (!is_array($contract)) $contract = [$contract];
        return $contract;
    }


    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getTrainingTitle()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_TRAININGTITLE);
        $meta = empty($meta) ? '' : $meta;

        return $meta;
    }


    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getCourseTitle()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_COURSETITLE);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getDegree()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_DEGREE);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }


    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getOrganizationDegreeName()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_ORGANIZATIONDEGREENAME);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getOrganizationDegreeAcronym()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_ORGANIZATIONDEGREEACRONYM);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getOrganizationDegreeCity()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_ORGANIZATIONDEGREECITY);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getOrganizationDegreeCountry()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_ORGANIZATIONDEGREECOUNTRY);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getGrant()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_GRANT);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getSpeciality()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_SPECIALITY);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }


    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getThesisDirector()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_THESISDIRECTOR);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getJuryComposition()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_JURYCOMPOSITION);
        $meta = empty($meta) ? '' : $meta;
        if (!empty($meta)) {
            $chars = array(',', '/', '\\');
            $meta = array_map('trim', explode(';', str_replace($chars, ';', $meta)));
        }

        return $meta;
    }

    public function getJury()
    {
        $jury = array();

        $juryComposition = $this->getJuryComposition();
        if (isset($juryComposition) && !empty($juryComposition)) {
            $jury = $juryComposition;
        }

        $juryChair = $this->getJuryChair();
        if (isset($juryChair) && !empty($juryChair)) {
            $jury[] = $juryChair;
        }

        return $jury;
    }

    /** Création du Referentiel Journal
     * @return Ccsd_Referentiels_Journal
     */
    public function getJournal()
    {

        $issn = $this->getIssn();

        $fulltitle = $this->getSerie();

        $eissn = $issn;

        $abbrevtitle = $this->getCollectionShortTitle();

        return $this->formateJournal($fulltitle, $abbrevtitle, $issn, $eissn);
    }


    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getGraduateSchool()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_GRADUATESCHOOL);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getDissertationType()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_DISSERTATIONTYPE);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getDissertationDirector()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_DISSERTATIONDIRECTOR);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }


    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getInternshipSupervisor()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_INTERNSHIPSUPERVISOR);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getDefenseDate()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_DEFENSEDATE);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }


    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getJuryChair()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_JURYCHAIR);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getResearchReportType()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_RESEARCHREPORTTYPE);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getOrder()
    {
        $order = '';
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_ORDER.self::REL_XPATH_ORDER_CONTRACTNUMBER);
        if (is_array($meta)) {
            $meta = implode(' ',$meta);
        }
        if (trim($meta) !== ''){
            $order.="contrat : ".$meta;
        }

        if (trim($meta)!==''){
            $order.="\r\n";
        }

        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_ORDER.self::REL_XPATH_ORDER_FUNDING);
        if (is_array($meta)) {
            $meta = implode(' ',$meta);
        }
        if (trim($meta) !== ''){
            $order.="financement : ".$meta;
        }
        if (trim($meta)!==''){
            $order.="\r\n";
        }

        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_ORDER.self::REL_XPATH_ORDER_SUPERVISOR);

        if (is_array($meta)) {
            $meta = implode(' ',$meta);
        }
        if (trim($meta) !== ''){
            $order.="Superviseur : ".$meta;
        }
        if (trim($meta)!==''){
            $order.="\r\n";
        }

        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_ORDER.self::REL_XPATH_ORDER_BACKER_IDENTIFIER);
        if (is_array($meta)) {
            $meta = implode(' ',$meta);
        }
        if (trim($meta) !== ''){
            $order.="Autre référence : ".$meta;
        }
        if (trim($meta)!==''){
            $order.="\r\n";
        }

        $order = empty($order) ? '' : $order;
        return $order;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getReportDirector()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_REPORTDIRECTOR);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getReportNumber()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_REPORTNUMBER);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getAssignee()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_ASSIGNEE);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getSubmissionDate()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_SUBMISSIONDATE);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getPatentNumber()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_PATENTNUMBER);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getClassification()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_CLASSIFICATION);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getPatentLandscape()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_PATENTLANDSCAPE);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getAudioType()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_AUDIOTYPE);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getDuration()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_DURATION);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getMedia()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_MEDIA);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getScale()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_SCALE);
        $meta = empty($meta) ? '' : $meta;
        if (!empty($meta)){
            $meta = str_replace(['1:','1/'],'',$meta);
            $meta = str_replace(' ','',$meta);
            $meta = 1 / intval($meta);
            $meta = ''.$meta;
        }
        return $meta;
    }

    public function getDescription()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_ATTACHEDDOCUMENTS);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }


    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getSize()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_SIZE);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getGeographicScope()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_GEOGRAPHICSCOPE);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }


    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getRights()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_RIGHTS);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getFirstVersionYear()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_FIRSTVERSIONYEAR);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getSoftwareType()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_SOFTWARETYPE);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }


    // Fonctions pour les ouvrages (BookInfos)

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getBookTitle()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_BOOKINFOS.self::REL_XPATH_BOOKINFOS_TITLE);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getBookSubtitle()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_BOOKINFOS.self::REL_XPATH_BOOKINFOS_SUBTITLE);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getBookAuthor()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_BOOKINFOS.self::REL_XPATH_BOOKINFOS_BOOKAUTHOR);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */

    public function getRecordBookAuthor()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_BOOKINFOS_BOOKAUTHOR);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getBookDirector()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_BOOKINFOS.self::REL_XPATH_BOOKINFOS_BOOKDIRECTOR);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getBookLink()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_BOOKINFOS_BOOKLINK);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getChapterAuthor()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_BOOKINFOS.self::REL_XPATH_BOOKINFOS_CHAPTERAUTHOR);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }


    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getSeeAlso()
    {
        $bookLink = $this->getBookLink();
        if (empty($bookLink)) $bookLink = [];

        $journalLink = $this->getJournalLink();
        if (empty($journalLink)) $journalLink = [];

        $recordLink = $this->getRecordLink();
        if (empty($recordLink)) $recordLink = [];

        $bookLink = is_array($bookLink) ? $bookLink : [$bookLink];
        $journalLink = is_array($journalLink) ? $journalLink : [$journalLink];
        $recordLink = is_array($recordLink) ? $recordLink : [$recordLink];

        $arrayLink = $bookLink;
        foreach ($journalLink as $jl){
            if (!in_array($jl,$arrayLink,true)){
                $arrayLink[] = $jl;
            }
        }
        foreach ($recordLink as $rl){
            if (!in_array($rl,$arrayLink,true)){
                $arrayLink[] = $rl;
            }
        }

        $arrayLink = array_unique($arrayLink);


        return $arrayLink;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getChapterType()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_BOOKINFOS.self::REL_XPATH_BOOKINFOS_CHAPTERAUTHOR);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getBookPages()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_BOOKINFOS.self::REL_XPATH_BOOKINFOS_PAGES);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getBookPagination()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_BOOKINFOS.self::REL_XPATH_BOOKINFOS_PAGINATION);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    //Fonction sur les articles (ArticleInfos)

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getArticleInfosType()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_ARTICLEINFOS.self::REL_XPATH_ARTICLEINFOS_TYPE);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getArticleInfosNumber()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_ARTICLEINFOS.self::REL_XPATH_ARTICLEINFOS_NUMBER);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    public function getRecordPeerReviewed()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_ARTICLEINFOS_PEERREVIEWED);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }
    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getArticleInfosPeerReviewed()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_ARTICLEINFOS.self::REL_XPATH_ARTICLEINFOS_PEERREVIEWED);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getArticleInfosPagination()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_ARTICLEINFOS.self::REL_XPATH_ARTICLEINFOS_PAGINATION);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getBookTitleInfosTitle()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_BOOKTITLEINFOS.self::REL_XPATH_BOOKTITLEINFOS_TITLE);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getBookTitleInfosSubtitle()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_BOOKTITLEINFOS.self::REL_XPATH_BOOKTITLEINFOS_SUBTITLE);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    public function getBookTitleInfosTitleConcat()
    {
        $title = $this->getBookTitleInfosTitle();
        $subtitle = $this->getBookTitleInfosSubtitle();
        if (is_array($title)) $title = trim(implode(' ',$title));
        if (is_array($subtitle)) $subtitle = trim(implode(' ',$subtitle));

        if (!empty($title) && !empty($subtitle)){
            return $title.' : '.$subtitle;
        }
        else if (empty($title) && !empty($subtitle)){
            return $subtitle;
        }
        else if (!empty($title) && empty($subtitle)){
            return $title;
        }
        else{
            return '';
        }
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getBookTitleInfosBookLink()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_BOOKTITLEINFOS.self::REL_XPATH_BOOKTITLEINFOS_BOOKLINK);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getBookTitleInfosChapterAuthor()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_BOOKTITLEINFOS.self::REL_XPATH_BOOKTITLEINFOS_CHAPTERAUTHOR);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getBookTitleInfosChapterType()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_BOOKTITLEINFOS.self::REL_XPATH_BOOKTITLEINFOS_CHAPTERTYPE);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getBookTitleInfosPagination()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_BOOKTITLEINFOS.self::REL_XPATH_BOOKTITLEINFOS_PAGINATION);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getBookTitleInfosPages()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_BOOKTITLEINFOS.self::REL_XPATH_BOOKTITLEINFOS_PAGES);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }


    // fonctions pour les events

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getEventName()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_EVENT.self::REL_XPATH_EVENT_NAME);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getEventMeetingCountry()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_EVENT.self::REL_XPATH_EVENT_MEETING.self::REL_XPATH_MEETING_COUNTRY);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getEventMeetingCity()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_EVENT.self::REL_XPATH_EVENT_MEETING.self::REL_XPATH_MEETING_CITY);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return string
     */

    public function getOtherType()
    {
        return '';
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getEventMeetingStartDate()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_EVENT.self::REL_XPATH_EVENT_MEETING.self::REL_XPATH_MEETING_STARTDATE);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getEventMeetingEndDate()
    {
        $meta = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_EVENT.self::REL_XPATH_EVENT_MEETING.self::REL_XPATH_MEETING_ENDDATE);
        $meta = empty($meta) ? '' : $meta;
        return $meta;
    }




    public function getAttachmentInfos()
    {
        //$attachment = $this->getValue(self::XPATH_ROOT_ATTACHMENT);
        $attachment = $this->getDomPath()->query(self::XPATH_ROOT_ATTACHMENT);
        $result = [];
        if (!empty($attachment)) {

            foreach ($attachment as $node) {

                $domxpathAut = $this->getDomXPath($node);

                $attachmentId = $this->getValue('/ns2:attachment' . self::REL_XPATH_ATTACHMENT_ATTACHMENTID, $domxpathAut);
                $attachmentId = empty($attachmentId) ? '' : $attachmentId;

                $fileName = $this->getValue('/ns2:attachment' . self::REL_XPATH_ATTACHMENT_FILENAME, $domxpathAut);
                $fileName = empty($fileName) ? '' : $fileName;

                $version = $this->getValue('/ns2:attachment' . self::REL_XPATH_ATTACHMENT_VERSION, $domxpathAut);
                $version = empty($version) ? '' : $version;

                $fileMimeType = $this->getValue('/ns2:attachment' . self::REL_XPATH_ATTACHMENT_MIMETYPE, $domxpathAut);
                $fileMimeType = empty($fileMimeType) ? '' : $fileMimeType;

                $original = $this->getValue('/ns2:attachment' . self::REL_XPATH_ATTACHMENT_ORIGINAL, $domxpathAut);
                $original = empty($original) ? '' : $original;

                $accessCondition = $this->getValue('/ns2:attachment' . self::REL_XPATH_ATTACHMENT_ACCESSCONDITION, $domxpathAut);
                $accessCondition = empty($accessCondition) ? '' : $accessCondition;

                $arrayValue = ['attachmentId', 'fileName', 'version', 'fileMimeType', 'original', 'accessCondition'];
                foreach ($arrayValue as $value) {
                    if (is_array($$value)) {
                        $$value = implode(' ', $$value);
                    }
                }

                $result[] = [$attachmentId, $fileName, $version, $fileMimeType, $original, $accessCondition];
            }
            return $result;
        }
        else {
            return '';
        }
    }

     /**
     * @param $value
     * @param $domxpath
     * @return DOMNodeList[]|DOMNodeList
     */
    protected function getValue($value,$domxpath = null)
    {
        if ($domxpath){
            $children = $domxpath->query($value);
        }
        else {
            $children = $this->getDomPath()->query($value);
        }


        if (isset($children)) {
            // Children : tableau de DOMElements
            // Unique élément : l'élément est une string
            if ($children->length === 1) {
                return Ccsd_Tools::space_clean($children[0]->nodeValue);
                // Multiple éléments : ajoutés dans un tableau
            } else if ($children->length > 1) {
                $values = [];
                foreach ($children as $child) {
                    $values[] = Ccsd_Tools::space_clean($child->nodeValue);
                }
                return $values;
            }
        }

        return [];
    }


    /**
     *
     */
    public function getHalTypology(){
        return $this->_type;
    }


    public function getMetadatas()
    {

        $this->_metas[self::META] = [];
        $this->_metas[self::AUTHORS] = [];

        foreach ($this->_wantedTags as $metakey) {

            $meta = "";



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
                case self::META_JOURNAL :
                    $meta = $this->getJournal();
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
                case self::META_LINK :
                    $meta = $this->getLink();
                    break;
                case self::META_PAGE :
                    $meta = $this->getPage();
                    break;
                case self::META_PUBLISHER :
                    $meta = $this->getPublisher();
                    break;
                case self::META_PUBLISHER_CITY :
                    $meta = $this->getPubPlaceCity();
                    break;
                case self::META_PUBLISHER_COUNTRY:
                    $meta = $this->getPubPlaceCountry();
                    break;
                case self::META_DOCUMENTLOCATION :
                    $meta = $this->getDocumentLocation();
                    break;
                case self::META_SERIESEDITOR :
                    $meta = $this->getSeriesEditor();
                    break;
                case self::META_ABSTRACT :
                    $meta = $this->getAbstract();
                    break;
                case self::META_INDEXATION :
                    $meta = $this->getIndexation();
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
                case self::META_SOURCE:
                    $meta = $this->getSource();
                    break;
                case self::META_EUROPEANPROJECT:
                    $meta = $this->getEuropeanProject();
                    break;
                case self::META_FUNDING :
                    $meta = $this->getFunding();
                    break;
                case self::META_JELCODE :
                    $meta = $this->getJelCode();
                    break;
                case self::META_BOOKTITLE :
                    $meta = $this->getBookTitleInfosTitleConcat();
                    break;
                case self::META_VOIRAUSSI :
                    $meta = $this->getSeeAlso();
                    break;
                default:
                    break;
            }

            if (!is_array($meta) && $meta === '0'){
                $this->_metas[self::META][$metakey] = $meta;
            }

            if (!empty($meta)) {
                $this->_metas[self::META][$metakey] = $meta;
            }
        }


        //suppression du lien principal des liens annexes
        if (isset($this->_metas[self::META][self::META_VOIRAUSSI], $this->_metas[self::META][self::META_LINK])) {
            $array_link = $this->_metas[self::META][self::META_VOIRAUSSI];
            $link = $this->_metas[self::META][self::META_LINK];
            if (in_array($link, $array_link, true)) {
                $key = array_search($link, $array_link, true);
                unset($array_link[$key]);
                $this->_metas[self::META][self::META_VOIRAUSSI] = array_values($array_link);
            }
        }

        // Récupération de la langue du premier titre
        $titleLang = isset($this->_metas[self::META_TITLE]) ? array_keys($this->_metas[self::META_TITLE])[0] : '';

        // Ajout de la langue
        $this->_metas[self::META_LANG] = $this->formateLang($this->getDocLang(), $titleLang);


        // Gestion des identifiants en tableau
        if (!empty($this->getDOI())) $this->_metas[self::META_IDENTIFIER][self::META_DOI] = $this->getDOI();
        if (!empty($this->getIdentifier())) $this->_metas[self::META_IDENTIFIER][self::META_PRODINRA] = $this->getIdentifier();
        if (!empty($this->getUtKey())) $this->_metas[self::META_IDENTIFIER][self::META_WOS] = $this->getUtKey();
        if (!empty($this->getIssn())) $this->_metas[self::META_IDENTIFIER][self::META_ISSN] = $this->getIssn();
        if (!empty($this->getIsbn())) $this->_metas[self::META_IDENTIFIER][self::META_CONFISBN] = $this->getIsbn();

        // Construction des auteurs avec auteurs externes
        if (!empty($this->getAuthors())) $this->_metas[self::AUTHORS] = $this->getAuthors();
        if (!empty($this->getExtAuthors())) $this->_metas[self::EXTAUTHORS] = $this->getExtAuthors();


        return $this->_metas;
    }

}

foreach (glob(__DIR__.'/Inra/*.php') as $filename)
{
    require_once($filename);
}