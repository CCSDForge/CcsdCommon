<?php

/**
 * Class Ccsd_Externdoc_BibcodeTest
 */
class Ccsd_Externdoc_BibcodeTest extends PHPUnit\Framework\TestCase {
    /** @var  Ccsd_Externdoc_Bibcode */
    private $_doc;
    private $_metas;

    public function setUp()
    {
        $filename = __DIR__."/../../ressources/bibcode.xml";
        $handle = fopen($filename, "r");
        $xmlString = fread($handle, filesize($filename));
        fclose($handle);

        $xml = new DOMDocument();
        $xml->loadXML($xmlString);
        $this->_doc = Ccsd_Externdoc_Bibcode::createFromXML("2013JLwT...31..830B", $xml);

        // On a pas accès à la base de données depuis la librairie
        //$this->_doc->setAdapter(Zend_Db_Table_Abstract::getDefaultAdapter());

        $this->_metas = $this->_doc->getMetadatas();
    }

    public function testDocType()
    {
        self::assertEquals("ART", $this->_doc->getType());
    }

    public function testGetTitle()
    {
        $title = $this->_metas['metas']['title'];
        self::assertEquals(['en' => "Single-Mode, Large Mode Area, Solid-Core Photonic BandGap Fiber With Hetero-Structured Cladding"], $title);
    }

    public function testGetAbstract()
    {
        $abstract = $this->_metas['metas']['abstract'];
        self::assertEquals($abstract, ['en' => "Not Available"]);
    }

    public function testgetDate()
    {
        $date = $this->_metas['metas']['date'];
        self::assertEquals("2013-03", $date);
    }

    public function testgetVolume()
    {
        $volume = $this->_metas['metas']['volume'];
        self::assertEquals("31", $volume);
    }

    public function testgetPage()
    {
        $page = $this->_metas['metas']['page'];
        self::assertEquals("830 - 835", $page);
    }

    public function testgetDoi()
    {
        $doi = $this->_metas['metas']['identifier']['doi'];
        self::assertEquals("10.1109/JLT.2012.2237542", $doi);
    }

    public function testgetLang()
    {
        $lang = $this->_metas['metas']['language'];
        self::assertEquals("en", $lang);
    }

    public function testgetJournal()
    {
        /** @var Ccsd_Referentiels_Journal $journal */
        $journal = $this->_metas['metas']['journal'];
        self::assertEquals("Journal of Lightwave Technology", $journal->getJName());
    }

    public function testgetAuthors()
    {
        $authors = $this->_metas['authors'];

        self::assertEquals($authors[0]['firstname'], 'Assaad');
        self::assertEquals($authors[0]['lastname'], 'Baz');

        self::assertEquals($authors[1]['firstname'], 'Laurent');
        self::assertEquals($authors[1]['lastname'], 'Bigot');

        self::assertEquals($authors[2]['firstname'], 'Géraud');
        self::assertEquals($authors[2]['lastname'], 'Bouwmans');

        self::assertEquals($authors[3]['firstname'], 'Yves');
        self::assertEquals($authors[3]['lastname'], 'Quiquempois');
    }
}