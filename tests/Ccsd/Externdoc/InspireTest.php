<?php

/**
 * Class Ccsd_Externdoc_Crossref_InspireTest
 */
class Ccsd_Externdoc_InspireTest extends PHPUnit\Framework\TestCase {
    /** @var  Ccsd_Externdoc_Inspire*/
    private $_doc;
    private $_metas;

    public function setUp()
    {
        $filename = __DIR__."/../../ressources/inspire.xml";
        $handle = fopen($filename, "r");
        $xmlString = fread($handle, filesize($filename));
        fclose($handle);

        $xml = new DOMDocument();
        $xml->loadXML($xmlString);
        $this->_doc = Ccsd_Externdoc_Inspire::createFromXML("1206913", $xml);

        // On a pas accès à la base de données depuis la librairie
        //$this->_doc->setAdapter(Zend_Db_Table_Abstract::getDefaultAdapter());

        $this->_metas = $this->_doc->getMetadatas();
    }

    public function testDocType()
    {
        self::assertEquals("", $this->_doc->getType());
    }

    public function testGetTitle()
    {
        $title = $this->_metas['metas']['title'];
        self::assertEquals(['en' => "Quantum critical lines in holographic phases with (un)broken symmetry"], $title);
    }

    public function testGetAbstract()
    {
        $abstract = $this->_metas['metas']['abstract'];
        self::assertEquals($abstract, ['en' => "All possible scaling IR asymptotics in homogeneous, translation invariant holographic phases preserving or breaking a U(1) symmetry in the IR are classified. Scale invariant geometries where the scalar extremizes its effective potential are distinguished from hyperscaling violating geometries where the scalar runs logarithmically. It is shown that the general critical saddle-point solutions are characterized by three critical exponents (θ, z, ζ). Both exact solutions as well as leading behaviors are exhibited. Using them, neutral or charged geometries realizing both fractionalized or cohesive phases are found. The generic global IR picture emerging is that of quantum critical lines, separated by quantum critical points which correspond to the scale invariant solutions with a constant scalar."]);
    }

    public function testgetDate()
    {
        $date = $this->_metas['metas']['date'];
        self::assertEquals("2012-12-10", $date);
    }

    public function testgetJournal()
    {
        /** @var Ccsd_Referentiels_Journal $journal */
        $journal = $this->_metas['metas']['journal'];
        self::assertEquals("JHEP 1304 (2013) 053", $journal->getJName());
    }

    public function testgetLang()
    {
        $lang = $this->_metas['metas']['language'];
        self::assertEquals("en", $lang);
    }

    public function testgetKeywords()
    {
        $keywords = $this->_metas['metas']['keyword'];
        self::assertEquals(["en" => ["General Physics", "Theory-HEP"]], $keywords);
    }

    public function testgetAuthors()
    {
        $authors = $this->_metas['authors'];
    
        self::assertEquals($authors[0]['firstname'], 'B.');
        self::assertEquals($authors[0]['lastname'], 'Gouteraux');

        self::assertEquals($authors[1]['firstname'], 'E.');
        self::assertEquals($authors[1]['lastname'], 'Kiritsis');
    }
}