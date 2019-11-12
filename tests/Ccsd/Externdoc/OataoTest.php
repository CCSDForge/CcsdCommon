<?php

/**
 * Class Ccsd_Externdoc_OataoTest
 */
class Ccsd_Externdoc_OataoTest extends PHPUnit\Framework\TestCase {
    /** @var  Ccsd_Externdoc_Oatao */
    private $_doc;
    private $_metas;

    public function setUp()
    {
        $filename = __DIR__."/../../ressources/OATAO.xml";
        $handle = fopen($filename, "r");
        $xmlString = fread($handle, filesize($filename));
        fclose($handle);

        $xml = new DOMDocument();
        $xml->loadXML($xmlString);
        $this->_doc = Ccsd_Externdoc_Oatao::createFromXML("10031", $xml);

        // On a pas accès à la base de données depuis la librairie
        //$this->_doc->setAdapter(Zend_Db_Table_Abstract::getDefaultAdapter());

        $this->_metas = $this->_doc->getMetadatas();
    }

    public function testDocType()
    {
        self::assertEquals("COMM", $this->_doc->getType());
    }

    public function testGetTitle()
    {
        $title = $this->_metas['metas']['title'];
        self::assertEquals(['en' => "Evaluation of microplasma discharges as active components for reconfigurable antennas"], $title);
    }

    public function testGetAbstract()
    {
        $abstract = $this->_metas['metas']['abstract'];
        self::assertEquals($abstract, ['en' => "This paper presents an experimental setup for the wideband RF characterization of plasma Micro Hollow Cathode Sustained Discharge (MHCD). This microdischarge is studied as a candidate for integrated active component in antennas, using the change of permittivity caused by the presence of plasma. The measurement setup consists of a 50 Ω microstrip line with a single MHCD placed in its center. The measurement of the scattering parameters of the experimental device permits the evaluation of the complex permittivity and conductivity of the MHCD."]);
    }

    public function testgetDate()
    {
        $date = $this->_metas['metas']['date'];
        self::assertEquals("2012", $date);
    }

    public function testgetDoi()
    {
        $doi = $this->_metas['metas']['identifier']['doi'];
        self::assertEquals("10.1109/EuCAP.2012.6206117", $doi);
    }

    public function testgetKeyword()
    {
        $keyword = $this->_metas['metas']['keyword'];
        self::assertEquals(["en" => ["Traitement du signal et de l'image","Plasma discharge","RF/microplasma interaction"]], $keyword);
    }

    public function testgetAuthors()
    {
        $authors = $this->_metas['authors'];

        self::assertEquals($authors[0]['firstname'], 'Francisco');
        self::assertEquals($authors[0]['lastname'], 'Pizarro');

        self::assertEquals($authors[1]['firstname'], 'Romain');
        self::assertEquals($authors[1]['lastname'], 'Pascaud');

        self::assertEquals($authors[2]['firstname'], 'Olivier');
        self::assertEquals($authors[2]['lastname'], 'Pascal');

        self::assertEquals($authors[3]['firstname'], 'Thierry');
        self::assertEquals($authors[3]['lastname'], 'Callegari');

        self::assertEquals($authors[4]['firstname'], 'Laurent');
        self::assertEquals($authors[4]['lastname'], 'Liard');
    }
}