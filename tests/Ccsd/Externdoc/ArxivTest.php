<?php

/**
 * Class Ccsd_Externdoc_Crossref_ArxivTest
 */
class Ccsd_Externdoc_ArxivTest extends PHPUnit\Framework\TestCase {
    /** @var  Ccsd_Externdoc_Arxiv */
    private $_doc;
    private $_metas;

    public function setUp()
    {
        $filename = __DIR__."/../../ressources/arxivArt.xml";
        $handle = fopen($filename, "r");
        $xmlString = fread($handle, filesize($filename));
        fclose($handle);

        $xml = new DOMDocument();
        $xml->loadXML($xmlString);
        $this->_doc = Ccsd_Externdoc_Arxiv::createFromXML("cond-mat/9704221", $xml);

        // On a pas accès à la base de données depuis la librairie
        //$this->_doc->setAdapter(Zend_Db_Table_Abstract::getDefaultAdapter());

        $this->_metas = $this->_doc->getMetadatas();
    }

    public function testDocType()
    {
        self::assertEquals("UNDEFINED", $this->_doc->getType());
    }

    public function testGetTitle()
    {
        $title = $this->_metas['metas']['title'];
        self::assertEquals(['en' => "Weak Randomness for large q-State Potts models in Two Dimensions"], $title);
    }

    public function testGetAbstract()
    {
        $abstract = $this->_metas['metas']['abstract'];
        self::assertEquals($abstract, ['en' => "We have studied the effect of weak randomness on q-state Potts models for q > 4 by measuring the central charges of these models using transfer matrix methods. We obtain a set of new values for the central charges and then show that some of these values are related to one another by a factorization law."]);
    }

    public function testgetDate()
    {
        $date = $this->_metas['metas']['date'];
        self::assertEquals("1997-04-27", $date);
    }

    public function testgetComment()
    {
        $comment = $this->_metas['metas']['comment'];
        self::assertEquals("8 pages, Latex, no figures", $comment);
    }

    public function testgetLang()
    {
        $lang = $this->_metas['metas']['language'];
        self::assertEquals("en", $lang);
    }

    public function testgetDomain()
    {
        // Il faudrait trouver le moyen de tester la traduction des domaines en base.
    }

    public function testgetAuthors()
    {
        $authors = $this->_metas['authors'];

        self::assertEquals($authors[0]['firstname'], 'Marco');
        self::assertEquals($authors[0]['lastname'], 'Picco');
    }
}