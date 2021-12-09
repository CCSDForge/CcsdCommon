<?php

/**
 * Class Ccsd_Externdoc_Crossref_JournalTest
 */
class Ccsd_Externdoc_Crossref_JournalTest extends PHPUnit\Framework\TestCase {
    /** @var Ccsd_Externdoc_Crossref_Journal  */
    private $_doc = null;

    public function setUp()
    {
        $filename = __DIR__."/../../../ressources/crossrefJournal.xml";
        $handle = fopen($filename, "r");
        $xmlString = fread($handle, filesize($filename));
        fclose($handle);

        $xml = new DOMDocument();
        $xml->loadXML((string)$xmlString);
        $this->_doc = Ccsd_Externdoc_Crossref_Journal::createFromXML("10.1016/j.yexcr.2011.10.003", $xml);
    }

    public function testDocType()
    {
        self::assertEquals("ART", $this->_doc->getType());
    }

    public function testGetTitle()
    {
        $title = $this->_doc->getTitle('');
        self::assertEquals(['en' => "Hirudin and heparin enable efficient megakaryocyte differentiation of mouse bone marrow progenitors"], $title);
    }

    public function testgetDate()
    {
        $date = $this->_doc->getDate();
        self::assertEquals("2012-01", $date);
    }
    public function testgetPage()
    {
        $page = $this->_doc->getPage();
        self::assertEquals("25-32", $page);
    }
    public function testgetJournal()
    {
        $journal = $this->_doc->getJournal();
        self::assertEquals("Experimental Cell Research", $journal->getJName());
    }

    public function testgetLang()
    {
        $lang = $this->_doc->getDocLang();
        self::assertEquals("en", $lang);
    }
    public function testgetVolume()
    {
        $volume = $this->_doc->getVolume();
        self::assertEquals("318", $volume);
    }
    public function testgetIssue()
    {
        $issue = $this->_doc->getIssue();
        self::assertEquals("1", $issue);
    }

    public function testgetAuthors()
    {
        $authors = $this->_doc->getAuthors();

        self::assertEquals($authors[0]['firstname'], 'Catherine');
        self::assertEquals($authors[0]['lastname'], 'Strassel');
        self::assertEquals($authors[0]['orcid'], 'http://orcid.org/0000-0002-7915-1666');

        self::assertEquals($authors[1]['firstname'], 'Anita');
        self::assertEquals($authors[1]['lastname'], 'Eckly');

        self::assertEquals($authors[2]['firstname'], 'Catherine');
        self::assertEquals($authors[2]['lastname'], 'Léon');

        self::assertEquals($authors[3]['firstname'], 'Sylvie');
        self::assertEquals($authors[3]['lastname'], 'Moog');

        self::assertEquals($authors[4]['firstname'], 'Jean-Pierre');
        self::assertEquals($authors[4]['lastname'], 'Cazenave');

        self::assertEquals($authors[5]['firstname'], 'Christian');
        self::assertEquals($authors[5]['lastname'], 'Gachet');

        self::assertEquals($authors[6]['firstname'], 'François');
        self::assertEquals($authors[6]['lastname'], 'Lanza');
    }
}