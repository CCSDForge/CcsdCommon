<?php
/**
 * Created by PhpStorm.
 * User: genicot
 * Date: 07/03/19
 * Time: 16:59
 */

class Ccsd_Externdoc_Irstea extends Ccsd_Externdoc
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

    protected $_xmlNamespace = array('CADIC'=>'http://cadic.eu',
        'DOC'=>'http://cadic.eu',
        'EXP'=>'http://cadic.eu',
        'dc' =>'http://purl.org/dc/emlements/1.1/',
        'rdf' =>'http://www.w3.org/1999/02/22-rdf-syntax-ns#'
        );


    public static $NAMESPACE =  array('CADIC'=>'http://cadic.eu',
        'DOC'=>'http://cadic.eu',
        'EXP'=>'http://cadic.eu',
        'dc' =>'http://purl.org/dc/emlements/1.1/',
        'rdf' =>'http://www.w3.org/1999/02/22-rdf-syntax-ns#'
    );
    /**
     * @var DOMXPath
     */
    protected $_domXPath = null;

    const META_SUBTITLE = 'subTitle';
    const META_COMMENT = 'inra_comment';

    // Metadata Génériques pour tous les documents INRA

    const META_ALTTITLE = 'alternateTitle';
    const META_SENDING = 'hal_sending';
    const META_CLASSIFICATION = 'hal_classification';
    const META_DOCUMENTLOCATION = 'inra_lieu';
    const META_EXPERIMENTALUNIT = 'inra_infra';
    const META_ISSN = 'issn';
    const META_SOURCE = 'source';
    const META_NOSPECIAL = 'inra_noSpecial';
    const META_TITRESPECIAL = 'inra_titreSpecial';
    const META_TARGETAUDIENCE = 'inra_publicVise';
    const EXTAUTHORS = 'extAuthors';
    const META_EUROPEANPROJECT = 'europeanProject';
    const META_FUNDING = 'funding';
    const META_DIRECTOR = 'director';
    const META_PEERREVIEWED = 'peerReviewing';
    const META_DOI = 'doi';
    const META_PRODINRA = 'prodinra';
    const META_WOS = 'wos';
    const META_PAGE = 'page';


    // XPATH GENERIQUES

    // Racine de l'article

    const XPATH_ROOT = '/notices/notice';

    const XPATH_ROOT_RECORD = "/notices/notice";

    const XPATH_ROOT_RECORD_TYPDOC = "//DOC:DOC_TYPE";

    const REL_XPATH_RECORD_ID = '/identifier';
    const REL_XPATH_RECORD_TYPE = '/itemType';

    //Classification Hal
    const REL_XPATH_HAL_CLASSIFICATION = '/halClassification';

    const REL_XPATH_HAL_CLASSIFICATION_CODE = '/code';
    const REL_XPATH_HAL_CLASSIFICATION_FR = '/french';
    const REL_XPATH_HAL_CLASSIFICATION_EN = '/english';

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
    const REL_XPATH_RECORD_NOTE = '/notes';
    const REL_XPATH_RECORD_DOI = '/doi';
    const REL_XPATH_RECORD_UTKEY = '/utKey';
    const REL_XPATH_RECORD_ACCESSCONDITION = '/recordAccessCondiion';
    const REL_XPATH_RECORD_KEYWORDS = '/keywords/keyword';
    const REL_XPATH_RECORD_TARGETAUDIENCE = '/targetAudience';
    const REL_XPATH_RECORD_CONTRACT = '/contract';
    const REL_XPATH_RECORD_EUROPEANPROJECT = '/fp';
    const REL_XPATH_EUROPEANPROJECT_GRANTNUMBER = '/grantNumber';
    const REL_XPATH_RECORD_ITEMTYPE = '/itemType';
    const REL_XPATH_RECORD_PMID = '/pmid';
    const REL_XPATH_RECORD_HALSENDING = '/halSending';

    // Information de collection
    const REL_XPATH_RECORD_COLLECTION = '/collection';
    const REL_XPATH_COLLECTION_ID = '/idCollection';
    const REL_XPATH_COLLECTION_TITLE = '/title';
    const REL_XPATH_COLLECTION_SHORTTITLE = '/shortTitle';
    const REL_XPATH_COLLECTION_ISSN = '/issn';
    const REL_XPATH_COLLECTION_OPENACCESS = '/openAccess';
    const REL_XPATH_COLLECTION_ISSUE_NUMBER = '/issue/number';
    const REL_XPATH_COLLECTION_ISSUE_VOLUME = '/issue/volume';
    const REL_XPATH_COLLECTION_JOURNALLINK = '/journalLink';
    const REL_XPATH_COLLECTION_SPECIALTITRE = '/issue/specialIssue/title';
    const REL_XPATH_COLLECTION_SPECIALTYPE = '/issue/specialIssue/type';
    const REL_XPATH_COLLECTION_DIRECTORISSUE = '/issue/directorIssue';

    const REL_XPATH_RECORD_EXPERIMENTALUNIT = '/experimentalUnit';

    //informations sur l'article dans sa publication
    const REL_XPATH_RECORD_ARTICLEINFOS = '/articleInfos';
    const REL_XPATH_ARTICLEINFOS_TYPE = '/articleType';
    const REL_XPATH_ARTICLEINFOS_NUMBER = '/articleNumber';
    const REL_XPATH_ARTICLEINFOS_PEERREVIEWED = '/peerReviewed';
    const REL_XPATH_ARTICLEINFOS_PAGINATION = '/pagination';

    //informations sur la localisation geographique

    const REL_XPATH_RECORD_DOCUMENTLOCATION = '/documentLocation';
    const REL_XPATH_DOCUMENTLOCATION_NAME = '/libraryName';
    const REL_XPATH_DOCUMENTLOCATION_COTE = '/libraryClassificationMark';
    const REL_XPATH_DOCUMENTLOCATION_UNIT = '/libraryUnit';


    // Informations sur l'editeur
    const REL_XPATH_RECORD_PUBLISHING = '/publishing';
    const REL_XPATH_PUBLISHING_PUBLISHED = '/published';
    const REL_XPATH_PUBLISHING_PUBLICATION = '/publication';
    const REL_XPATH_PUBLICATION_ID = '/idPublisher';
    const REL_XPATH_PUBLICATION_NAME = '/publisherName';
    const REL_XPATH_PUBLICATION_CITY = '/publisherCity';
    const REL_XPATH_PUBLICATION_COUNTRY = '/country';
    const REL_XPATH_PUBLICATION_ISBN = '/isbn';
    const REL_XPATH_PUBLICATION_PAGES = '/pages';

    //informations concernant la pièce jointe (le document)
    const XPATH_ROOT_ATTACHMENT = '/produits/produit/attachment';
    const REL_XPATH_ATTACHMENT_ACCESSCONDITION = '/accessCondition';
    const REL_XPATH_ATTACHMENT_TITLE = '/title';
    const REL_XPATH_ATTACHMENT_FILENAME = '/fileName';
    const REL_XPATH_ATTACHMENT_RIGHTS = '/rights';
    const REL_XPATH_ATTACHMENT_ORIGINAL = '/original';
    const REL_XPATH_ATTACHMENT_MIMETYPE = '/fileMimeType';

    const META_ARTICLETYPE = 'otherType';
    const META_COLLECTION_SHORTTITLE = 'collectionShortTitle';
    const META_VULGARISATION = 'vulgarisation';

    protected $_wantedTags = array(
        self::ERROR,
        self::META_TITLE,
        self::META_ALTTITLE,
        self::META_SUBTITLE,
        self::META_LANG,
        self::META_DATE,
        self::META_ABSTRACT,
        self::META_KEYWORD,
        self::META_JOURNAL,
        self::META_MESH,
        self::META_SERIE,
        self::META_VOLUME,
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
        self::META_PUBLICATION,
        self::META_CONFLOCATION,
        self::META_CONFISBN,
        self::META_PROCEEDINGSTITLE,
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
        self::META_COMMENT
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
            }
        }
        else {
            $classif = strtolower(str_replace(':', '.', $classif));
        }
        $classif = empty($classif) ?  '' : $classif;
        return $classif;
    }


    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getIsbn()
    {
        $isbn = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_PUBLISHING.self::REL_XPATH_PUBLISHING_PUBLICATION.self::REL_XPATH_PUBLICATION_ISBN);
        $isbn = empty($isbn) ? '' : $isbn;
        return $isbn;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getIssn()
    {
        $issn = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_COLLECTION.self::REL_XPATH_COLLECTION_ISSN);
        $issn = empty($issn) ? '' : $issn;
        return $issn;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getJournalLink(){
        $journalLink = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_COLLECTION.self::REL_XPATH_COLLECTION_JOURNALLINK);
        $journalLink = empty($journalLink) ? '' : $journalLink;
        return $journalLink;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getCollectionShortTitle(){
        $shortTitle = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_COLLECTION.self::REL_XPATH_COLLECTION_SHORTTITLE);
        $shortTitle = empty($shortTitle) ? '' : $shortTitle;
        return $shortTitle;
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
        $serie = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_COLLECTION.self::REL_XPATH_COLLECTION_TITLE);
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

    /**
     * @return string
     */
    public function getPage() : string
    {
        /**
        $first = $this->getValue(self::XPATH_FIRSTPAGE);
        $first = empty($first) ? '' : $first;

        $last = $this->getValue(self::XPATH_LASTPAGE);
        $last = empty($last) ? '' : $last;

        return $this->formatePage($first, $last);
         * **/
        return '';
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     * @Todo ( no information about publisher in echantillonnage )
     */
    public function getPublisher()
    {
        $publisher = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_PUBLISHING.self::REL_XPATH_PUBLISHING_PUBLICATION.self::REL_XPATH_PUBLICATION_NAME);
        $publisher = empty($publisher) ? '' : $publisher;
        return $publisher;
    }
    //

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     * @Todo ( no information about publisher in echantillonnage )
     */
    public function getPubPlace()
    {
        $pubplace = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_PUBLISHING.self::REL_XPATH_PUBLISHING_PUBLICATION.self::REL_XPATH_PUBLICATION_CITY);
        $pubplace = empty($pubplace) ? '' : $pubplace;
        return $pubplace;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     *
     */
    public function getKeywords()
    {

        $lang = $this->formateLang($this->getDocLang(),$this->getDocLang());
        $keywords = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_KEYWORDS);
        $keywords = Ccsd_Tools::space_clean($keywords);
        if (!is_array($keywords)) {
            if (trim($keywords) !== '') {
                $keywords = [$keywords];
            } else if (trim($keywords) === '') {
                $keywords = [];
            }
        }
        $keywords = empty($keywords) ? '' : [$lang=>$keywords];
        return $keywords;
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
    public function getTypeArticle(){
        $typeArticle = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_ARTICLEINFOS.self::REL_XPATH_ARTICLEINFOS_TYPE);
        $typeArticle = empty($typeArticle) ? '' : $typeArticle;
        return $typeArticle;
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
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getDocumentLocation(){
        /**
        $biblio = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_DOCUMENTLOCATION.self::REL_XPATH_DOCUMENTLOCATION_NAME);
        $biblio = empty($biblio)? '':$biblio;

        $cote = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_DOCUMENTLOCATION.self::REL_XPATH_DOCUMENTLOCATION_COTE);
        $cote = empty($cote)? '' : $cote;

        $unite = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_DOCUMENTLOCATION.self::REL_XPATH_DOCUMENTLOCATION_UNIT);
        $unite = empty($unite)? '' : $unite;
         **/
        $retour = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_DOCUMENTLOCATION);
        $retour = empty($retour)? '' : $retour;

        /**
        $retour = empty($biblio)? '': $biblio;
        $retour = empty($cote)? $retour : $retour.', '.$cote;
        $retour = empty($unite)? $retour : $retour.', '.$unite;
         **/
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

        $hostLaboratory = $this->getValue();
        $hostLaboratory = empty($hostLaboratory) ? '' : $hostLaboratory;
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

        $reportType = $this->getValue();
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
        foreach (self::$_existing_types as $order => $typdoc2class) {
            /**
             * @var string  $xpath
             * @var Ccsd_Externdoc $type
             */
            foreach ($typdoc2class as $typdoc => $type) {

                if ($domxpath->query(Ccsd_Externdoc_Irstea::XPATH_ROOT_RECORD_TYPDOC)->item(0)->textContent===$typdoc) {
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
        $note = empty($note) ? '' : $note;
        return $note;

    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getEuropeanProject(){

        $ep = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_EUROPEANPROJECT.self::REL_XPATH_EUROPEANPROJECT_GRANTNUMBER);
        $ep = empty($ep) ? '' : $ep;
        return $ep;
    }

    /**
     * @return DOMNodeList|DOMNodeList[]|string
     */
    public function getFunding(){
        $contract = $this->getValue(self::XPATH_ROOT_RECORD.self::REL_XPATH_RECORD_CONTRACT);
        $contract = empty($contract) ? '' : $contract;
        return $contract;
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


    public function getHalTypology(){

    }

}

foreach (glob(__DIR__.'/Irstea/*.php') as $filename)
{
    require_once $filename;
}