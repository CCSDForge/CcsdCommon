<?php
/**
 * Class Ccsd_Externdoc_Crossref_BookTest
 */
class Ccsd_Externdoc_Crossref_BookTest extends PHPUnit\Framework\TestCase {

    /** @var Ccsd_Externdoc_Crossref_Book */
    private $_doc = null;

    public function setUp()
    {
        $filename = __DIR__."/../../../ressources/crossrefBook.xml";
        $handle = fopen($filename, "r");
        $xmlString = fread($handle, filesize($filename));
        fclose($handle);

        $xml = new DOMDocument();
        $xml->loadXML((string)$xmlString);
        $this->_doc = Ccsd_Externdoc_Crossref_Book::createFromXML("10.1007/978-88-470-5427-1", $xml);
    }

    public function testDocType()
    {
        self::assertEquals("OUV", $this->_doc->getType());
    }

    public function testGetTitle()
    {
        $title = $this->_doc->getTitle();
        self::assertEquals(['en' => "ECMO-Extracorporeal Life Support in Adults"], $title);
    }

    public function testGetSubtitle()
    {
        $subtitle = $this->_doc->getSubtitle();
        self::assertEquals("", $subtitle);
    }

    public function testgetLanguage()
    {
        $lang = $this->_doc->getDocLang();
        self::assertEquals("", $lang);
    }

    public function testgetIsbn()
    {
        $isbn = $this->_doc->getIsbn();
        self::assertEquals("978-88-470-5426-4", $isbn);
    }

    public function testgetIssn()
    {
        $issn = $this->_doc->getIssn();
        self::assertEquals("978-88-470-5427-1", $issn);
    }

    public function testgetDate()
    {
        $date = $this->_doc->getDate();
        self::assertEquals("2014", $date);
    }

    public function testgetSerie()
    {
        $serie = $this->_doc->getSerie();
        self::assertEquals("", $serie);
    }

    public function testgetVolume()
    {
        $volume = $this->_doc->getVolume();
        self::assertEquals("", $volume);
    }

    public function testgetIssue()
    {
        $issue = $this->_doc->getIssue();
        self::assertEquals("", $issue);
    }

    public function testgetPage()
    {
        // Pas de page, c'est l'entiereté du book
    }

    public function testgetPublisher()
    {
        $publisher = $this->_doc->getPublisher();
        self::assertEquals("Springer Milan", $publisher);
    }

    public function testgetPubPlace()
    {
        $pubPlace = $this->_doc->getPubPlace();
        self::assertEquals("Milano", $pubPlace);
    }

    public function testgetEditor()
    {
        $editor = $this->_doc->getEditor();
        self::assertEquals("", $editor);
    }

    public function testgetAuthors()
    {
        $authors = $this->_doc->getAuthors();

        self::assertEquals($authors[0]['firstname'], 'Fabio');
        self::assertEquals($authors[0]['lastname'], 'Sangalli');
        self::assertEquals($authors[0]['orcid'], 'http://orcid.org/0000-0002-8549-8199');

        self::assertEquals($authors[1]['firstname'], 'Nicolò');
        self::assertEquals($authors[1]['lastname'], 'Patroniti');

        self::assertEquals($authors[2]['firstname'], 'Antonio');
        self::assertEquals($authors[2]['lastname'], 'Pesenti');
    }
}