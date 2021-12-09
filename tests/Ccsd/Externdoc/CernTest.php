<?php
/**
 * Class Ccsd_Externdoc_Crossref_CernTest
 */

class Ccsd_Externdoc_CernTest extends PHPUnit\Framework\TestCase {
    /** @var  Ccsd_Externdoc_Cern */
    private $_doc;
    private $_metas;

    public function setUp()
    {
        $filename = __DIR__."/../../ressources/cern.xml";
        $handle = fopen($filename, "r");
        $xmlString = fread($handle, filesize($filename));
        fclose($handle);

        $xml = new DOMDocument();
        $xml->loadXML($xmlString);
        $this->_doc = Ccsd_Externdoc_Cern::createFromXML("1027481", $xml);

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
        self::assertEquals(['en' => "The Level-0 muon trigger for the LHCb experiment"], $title);
    }

    public function testGetAbstract()
    {
        $abstract = $this->_metas['metas']['abstract'];
        self::assertEquals($abstract, ['en' => "The Level-0 Muon Trigger looks for straight tracks crossing the five muon stations of the LHCb muon detector and measures their transverse momentum. The tracking uses a road algorithm relying on the projectivity of the muon detector. The architecture of the Level-0 muon trigger is pipeline and massively parallel. Receiving 130 GBytes/s of input data, it reconstructs muon candidates for each bunch crossing (25 ns) in less than 1.2 μs. It relies on an intensive use of high speed multigigabit serial links where high speed serializers/deserializers are embedded in Field Programmable Gate Arrays (FPGAs)."]);
    }

    public function testgetDate()
    {
        $date = $this->_metas['metas']['date'];
        self::assertEquals("2007", $date);
    }

    public function testgetDoi()
    {
        $doi = $this->_metas['metas']['identifier']['doi'];
        self::assertEquals("10.5170/CERN-2007-001.302", $doi);
    }

    public function testgetLang()
    {
        $lang = $this->_metas['metas']['language'];
        self::assertEquals("en", $lang);
    }

    public function testgetAuthors()
    {
        $authors = $this->_metas['authors'];

        self::assertEquals($authors[0]['firstname'], 'Jean Pierre');
        self::assertEquals($authors[0]['lastname'], 'Cachemiche');

        self::assertEquals($authors[1]['firstname'], 'E');
        self::assertEquals($authors[1]['lastname'], 'Aslanides');

        self::assertEquals($authors[2]['firstname'], 'J');
        self::assertEquals($authors[2]['lastname'], 'Cogan');

        self::assertEquals($authors[3]['firstname'], 'P Y');
        self::assertEquals($authors[3]['lastname'], 'Duval');

        self::assertEquals($authors[4]['firstname'], 'R');
        self::assertEquals($authors[4]['lastname'], 'Le Gac');

        self::assertEquals($authors[5]['firstname'], 'O');
        self::assertEquals($authors[5]['lastname'], 'Leroy');

        self::assertEquals($authors[6]['firstname'], 'P L');
        self::assertEquals($authors[6]['lastname'], 'Liotard');

        self::assertEquals($authors[7]['firstname'], 'F');
        self::assertEquals($authors[7]['lastname'], 'Marin');

        self::assertEquals($authors[8]['firstname'], 'S');
        self::assertEquals($authors[8]['lastname'], 'Favard');

        self::assertEquals($authors[9]['firstname'], 'A');
        self::assertEquals($authors[9]['lastname'], 'Tsaregorodtsev');
    }
}