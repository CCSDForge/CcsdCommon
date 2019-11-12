
<?php

/**
 * Class Ccsd_Externdoc_Crossref_PubmedcentralTest
 */
class Ccsd_Externdoc_PubmedcentralTest extends PHPUnit\Framework\TestCase {
    /** @var  Ccsd_Externdoc_Pubmedcentral */
    private $_doc;
    private $_metas;

    public function setUp()
    {
        $filename = __DIR__."/../../ressources/pubmedcentral.xml";
        $handle = fopen($filename, "r");
        $xmlString = fread($handle, filesize($filename));
        fclose($handle);

        $xml = new DOMDocument();
        $xml->loadXML($xmlString);
        $this->_doc = Ccsd_Externdoc_Pubmedcentral::createFromXML("PMC4440767", $xml);

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
        self::assertEquals(['en' => "A Multilaboratory Comparison of Calibration Accuracy and the Performance of External References in Analytical Ultracentrifugation"], $title);
    }

    public function testGetAbstract()
    {
        $abstract = $this->_metas['metas']['abstract'];
        self::assertEquals($abstract, ['en' =>'Analytical ultracentrifugation (AUC) is a first principles based method to determine absolute sedimentation coefficients and buoyant molar masses of macromolecules and their complexes, reporting on their size and shape in free solution.']);
    }

    public function testgetDate()
    {
        $date = $this->_metas['metas']['date'];
        self::assertEquals("2015-05-21", $date);
    }

    public function testgetVolume()
    {
        $volume = $this->_metas['metas']['volume'];
        self::assertEquals("10", $volume);
    }

    public function testgetIssue()
    {
        $issue = $this->_metas['metas']['issue'];
        self::assertEquals("5", $issue);
    }

    public function testgetPage()
    {
        $page = $this->_metas['metas']['page'];
        self::assertEquals("30", $page);
    }

    public function testgetDoi()
    {
        $doi = $this->_metas['metas']['identifier']['doi'];
        self::assertEquals("10.1371/journal.pone.0126420", $doi);
    }

    public function testgetJournal()
    {
        /** @var Ccsd_Referentiels_Journal $journal */
        $journal = $this->_metas['metas']['journal'];
        self::assertEquals("PLoS ONE", $journal->getJName());
    }

    public function testgetAuthors()
    {
        $authors = $this->_metas['authors'];

        self::assertEquals($authors[0]['firstname'], 'Huaying');
        self::assertEquals($authors[0]['lastname'], 'Zhao');

        self::assertEquals($authors[1]['firstname'], 'Rodolfo');
        self::assertEquals($authors[1]['lastname'], 'Ghirlando');

        self::assertEquals($authors[2]['firstname'], 'Carlos');
        self::assertEquals($authors[2]['lastname'], 'Alfonso');
    }
}