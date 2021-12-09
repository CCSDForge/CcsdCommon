
<?php

/**
 * Class Ccsd_Externdoc_Crossref_PubmedTest
 */
class Ccsd_Externdoc_PubmedTest extends PHPUnit\Framework\TestCase {
    /** @var  Ccsd_Externdoc_Pubmed */
    private $_doc;
    private $_metas;

    public function setUp()
    {
        $filename = __DIR__."/../../ressources/pubmed.xml";
        $handle = fopen($filename, "r");
        $xmlString = fread($handle, filesize($filename));
        fclose($handle);

        $xml = new DOMDocument();
        $xml->loadXML($xmlString);
        $this->_doc = Ccsd_Externdoc_Pubmed::createFromXML("17267295", $xml);

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
        self::assertEquals(['en' => "Muscarinic receptors and alpha2-adrenoceptors interact to modulate the respiratory rhythm in mouse neonates."], $title);
    }

    public function testGetAbstract()
    {
        $abstract = $this->_metas['metas']['abstract'];
        self::assertEquals($abstract, ['en' => "The respiratory rhythm generator (RRG) is modulated by several endogenous substances, including acetylcholine (ACh) and noradrenaline (NA) that interact in several modulatory processes. To know whether ACh and NA interacted to modulate the RRG activity, we used medullary \"en bloc\" and slice preparations from neonatal mice where the RRG has been shown to receive a facilitatory modulation from A1/C1 neurons, via a continuous release of endogenous NA and activation of alpha2 adrenoceptors. Applying ACh at 25 microM activated the RRG but ACh had no effects at 50 microM. Applying the ACh receptor agonists nicotine and muscarine facilitated and depressed the RRG, respectively. After yohimbine pre-treatment that blocked the alpha2 facilitation, the nicotinic facilitation was not altered, the muscarinic depression was reversed and ACh 50 microM significantly facilitated the RRG. After L-tyrosine pre-treatment that potentiated the alpha2 facilitation, the muscarinic depression was enhanced. Thus, ACh regulates the RRG activity via nicotinic and muscarinic receptors, the muscarinic receptors interacting with alpha2 adrenoceptors."]);
    }

    public function testgetDate()
    {
        $date = $this->_metas['metas']['date'];
        self::assertEquals("2007-08-01", $date);
    }

    public function testgetVolume()
    {
        $volume = $this->_metas['metas']['volume'];
        self::assertEquals("157", $volume);
    }

    public function testgetIssue()
    {
        $issue = $this->_metas['metas']['issue'];
        self::assertEquals("2-3", $issue);
    }

    public function testgetPage()
    {
        $page = $this->_metas['metas']['page'];
        self::assertEquals("215-25", $page);
    }

    public function testgetDoi()
    {
        $doi = $this->_metas['metas']['identifier']['doi'];
        self::assertEquals("10.1016/j.resp.2006.12.007", $doi);
    }

    public function testgetMesh()
    {
        $mesh = $this->_metas['metas']['mesh'];
        self::assertEquals(["Animals, Newborn","Brain Stem","Dose-Response Relationship, Drug","Drug Interactions","In Vitro Techniques","Mice","Muscarine","Muscarinic Agonists","Periodicity","Receptors, Adrenergic, alpha-2","Receptors, Muscarinic","Respiration","Yohimbine"], $mesh);
    }

    public function testgetAuthors()
    {
        $authors = $this->_metas['authors'];

        self::assertEquals($authors[0]['firstname'], 'Sébastien');
        self::assertEquals($authors[0]['lastname'], 'Zanella');

        self::assertEquals($authors[1]['firstname'], 'Jean-Charles');
        self::assertEquals($authors[1]['lastname'], 'Viemari');

        self::assertEquals($authors[2]['firstname'], 'Gérard');
        self::assertEquals($authors[2]['lastname'], 'Hilaire');
    }
}