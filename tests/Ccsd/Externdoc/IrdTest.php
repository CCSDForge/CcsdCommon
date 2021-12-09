<?php

/**
 * Class Ccsd_Externdoc_IrdTest
 */
class Ccsd_Externdoc_IrdTest extends PHPUnit\Framework\TestCase {

    /** @var Ccsd_Externdoc_Ird */
    private $_doc;
    private $_metas;

    public function setUp()
    {
        $filename = __DIR__."/../../ressources/ird.xml";
        $handle = fopen($filename, "r");
        $xmlString = fread($handle, filesize($filename));
        fclose($handle);

        $xml = new DOMDocument();
        $xml->loadXML($xmlString);
        $this->_doc = Ccsd_Externdoc_Ird::createFromXML("PAR00002533", $xml);

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
        self::assertEquals(['en' => "Significant contribution of the 18.6 year tidal cycle to regional coastal changes"], $title);
    }

    public function testGetAbstract()
    {
        $abstract = $this->_metas['metas']['abstract'];
        self::assertEquals($abstract, ['en' => "Although rising global sea levels will affect the shape of coastlines over the coming decades(1,2), the most severe and catastrophic shoreline changes occur as a consequence of local and regional-scale processes. Changes in sediment supply(3) and deltaic subsidence(4,5), both natural or anthropogenic, and the occurrences of tropical cyclones(4,5) and tsunamis(6) have been shown to be the leading controls on coastal erosion. Here, we use satellite images of South American mangrove-colonized mud banks collected over the past twenty years to reconstruct changes in the extent of the shoreline between the Amazon and Orinoco rivers. The observed timing of the redistribution of sediment and migration of the mud banks along the 1,500km muddy coast suggests the dominant control of ocean forcing by the 18.6 year nodal tidal cycle(7). Other factors affecting sea level such as global warming or El Nino and La Nina events show only secondary influences on the recorded changes. In the coming decade, the 18.6 year cycle will result in an increase of mean high water levels of 6 cm along the coast of French Guiana, which will lead to a 90 m shoreline retreat."]);
    }

    public function testgetDate()
    {
        $date = $this->_metas['metas']['date'];
        self::assertEquals("2008", $date);
    }

    public function testgetJournal()
    {
        /** @var Ccsd_Referentiels_Journal $journal */
        $journal = $this->_metas['metas']['journal'];
        self::assertEquals("Nature Geoscience", $journal->getJName());
    }

    public function testgetLang()
    {
        $lang = $this->_metas['metas']['language'];
        self::assertEquals("en", $lang);
    }

    public function testgetVolume()
    {
        $volume = $this->_metas['metas']['volume'];
        self::assertEquals("1", $volume);
    }

    public function testgetIssue()
    {
        $issue = $this->_metas['metas']['issue'];
        self::assertEquals("3", $issue);
    }

    public function testgetPage()
    {
        $page = $this->_metas['metas']['page'];
        self::assertEquals("169-172 + 6 p. h.t.", $page);
    }

    public function testgetDoi()
    {
        $doi = $this->_metas['metas']['identifier']['doi'];
        self::assertEquals("10.1038/ngeo127", $doi);
    }

    public function testgetAuthors()
    {
        $authors = $this->_metas['authors'];

        self::assertEquals($authors[0]['firstname'], 'Nicolas');
        self::assertEquals($authors[0]['lastname'], 'Gratiot');

        self::assertEquals($authors[1]['firstname'], 'J.');
        self::assertEquals($authors[1]['lastname'], 'Anthony');

        self::assertEquals($authors[2]['firstname'], 'A.');
        self::assertEquals($authors[2]['lastname'], 'Gardel');

        self::assertEquals($authors[3]['firstname'], 'C.');
        self::assertEquals($authors[3]['lastname'], 'Gaucherel');

        self::assertEquals($authors[4]['firstname'], 'Christophe');
        self::assertEquals($authors[4]['lastname'], 'Proisy');

        self::assertEquals($authors[5]['firstname'], 'J.');
        self::assertEquals($authors[5]['lastname'], 'Wells');
    }
}