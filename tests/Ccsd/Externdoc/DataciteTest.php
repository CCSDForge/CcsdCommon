<?php

/**
 * Class Ccsd_Externdoc_DataciteTest
 */
class Ccsd_Externdoc_DataciteTest extends PHPUnit\Framework\TestCase {
    /** @var Ccsd_Externdoc_Datacite  */
    private $_doc = '';

    public function setUp()
    {
        $filename = __DIR__."/../../ressources/datacite.xml";
        $handle = fopen($filename, "r");
        $xmlString = fread($handle, filesize($filename));
        fclose($handle);

        $xml = new DOMDocument();
        $xml->loadXML((string)$xmlString);
        $this->_doc = Ccsd_Externdoc_Datacite::createFromXML("", $xml);
    }

    public function testDocType()
    {
        self::assertEquals("SOFTWARE", $this->_doc->getType());
    }

    public function testGetTitle()
    {
        $title = $this->_doc->getTitle('en');
        self::assertEquals(['en' => "Yellowbrick V0.6"], $title);
    }

    public function testgetDate()
    {
        $date = $this->_doc->getDate();
        self::assertEquals("2018", $date);
    }
    public function testgetPublisher()
    {
        $publisher = $this->_doc->getPublisher();
        self::assertEquals("Zenodo", $publisher);
    }
    public function testgetKeywords()
    {
        $keywords = $this->_doc->getKeywords('en');
        self::assertEquals(["en"=>["matplotlib","sckit-learn","machine learning","visualization","python"]], $keywords);
    }

    public function testgetLang()
    {
        $lang = $this->_doc->getDocLang();
        self::assertEquals("en", $lang);
    }
    public function testgetAbstract()
    {
        $abstract = $this->_doc->getAbstract('en');
        self::assertEquals(["en"=>"Yellowbrick is an open source, pure Python project that extends the scikit-learn API with visual analysis and diagnostic tools. The Yellowbrick API also wraps matplotlib to create publication-ready figures and interactive data explorations while still allowing developers fine-grain control of figures. For users, Yellowbrick can help evaluate the performance, stability, and predictive value of machine learning models and assist in diagnosing problems throughout the machine learning workflow."], $abstract);
    }

    public function testgetAuthors()
    {
        $authors = $this->_doc->getAuthors();

        self::assertEquals($authors[0]['firstname'], 'Benjamin');
        self::assertEquals($authors[0]['lastname'], 'Bengfort');
        self::assertEquals($authors[0]['affiliation'], 'District Data Labs');

        self::assertEquals($authors[1]['firstname'], 'Nathan');
        self::assertEquals($authors[1]['lastname'], 'Danielsen');

        self::assertEquals($authors[2]['firstname'], 'Rebecca');
        self::assertEquals($authors[2]['lastname'], 'Bilbro');
        self::assertEquals($authors[2]['affiliation'], 'District Data Labs');

        self::assertEquals($authors[3]['firstname'], 'Larry');
        self::assertEquals($authors[3]['lastname'], 'Gray');

        self::assertEquals($authors[4]['firstname'], 'Kristen');
        self::assertEquals($authors[4]['lastname'], 'McIntyre');
    }
}