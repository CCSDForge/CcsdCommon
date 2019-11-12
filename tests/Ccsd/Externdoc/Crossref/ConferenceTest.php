<?php
/**
 * Class Ccsd_Externdoc_Crossref_ConferenceTest
 */
class Ccsd_Externdoc_Crossref_ConferenceTest extends PHPUnit\Framework\TestCase {
    /**
     * @var Ccsd_Externdoc_Crossref_Conference
     */
    private $_doc = '';

    public function setUp()
    {
        $filename = __DIR__."/../../../ressources/crossrefConference.xml";
        $handle = fopen($filename, "r");
        $xmlString = fread($handle, filesize($filename));
        fclose($handle);

        $xml = new DOMDocument();
        $xml->loadXML((string)$xmlString);
        $this->_doc = Ccsd_Externdoc_Crossref_Conference::createFromXML("10.1109/ICPR.2006.193", $xml);
    }

    public function testDocType()
    {
        self::assertEquals("COMM", $this->_doc->getType());
    }

    public function testgetConfTitle()
    {
        $title = $this->_doc->getConfTitle();
        self::assertEquals("18th International Conference on Pattern Recognition (ICPR'06)", $title);
    }

    public function testgetProceedingsTitle()
    {
        $proceedings = $this->_doc->getProceedingsTitle();
        self::assertEquals("18th International Conference on Pattern Recognition (ICPR'06)", $proceedings);
    }
    public function testgetPublisher()
    {
        $publisher = $this->_doc->getPublisher();
        self::assertEquals("IEEE", $publisher);
    }
    public function testgetConfIsbn()
    {
        $isbn = $this->_doc->getConfIsbn();
        self::assertEquals("0-7695-2521-0", $isbn);
    }

    public function testgetConferenceStartDate()
    {
        $startdate = $this->_doc->getConferenceStartDate();
        self::assertEquals("", $startdate);
    }
    public function testgetConferenceEndDate()
    {
        $stopdate = $this->_doc->getConferenceEndDate();
        self::assertEquals("", $stopdate);
    }

    public function testgetCity()
    {
        $city = $this->_doc->getCity();
        self::assertEquals("Hong Kong", $city);
    }

    public function testgetCountry()
    {
        $country = $this->_doc->getCountry();
        self::assertEquals("China", $country);
    }

    public function testgetPage()
    {
        $page = $this->_doc->getPage();
        self::assertEquals("792-795", $page);
    }

    public function testgetTitle()
    {
        $title = $this->_doc->getTitle('');
        self::assertEquals(['en' => "A Unified Strategy to Deal with Different Natures of Reject"], $title);
    }

    public function testgetAuthors()
    {
        $authors = $this->_doc->getAuthors(Ccsd_Externdoc_Crossref_Conference::XPATH_CONFCONTRIBUTOR);

        self::assertEquals($authors[0]['firstname'], 'H.');
        self::assertEquals($authors[0]['lastname'], 'Mouchere');

        self::assertEquals($authors[1]['firstname'], 'E.');
        self::assertEquals($authors[1]['lastname'], 'Anquetil');
        self::assertEquals($authors[1]['orcid'], 'http://orcid.org/0000-0002-0396-3190');
    }
}