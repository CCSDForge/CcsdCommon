<?php

/**
 * Class Ccsd_Externdoc_GrobidTest
 */
class Ccsd_Externdoc_GrobidTest extends PHPUnit\Framework\TestCase {
    /** @var  Ccsd_Externdoc_Grobid */
    private $_doc;
    private $_metas;

    public function setUp()
    {
        $filename = __DIR__."/../../ressources/grobid.xml";
        $handle = fopen($filename, "r");
        $xmlString = fread($handle, filesize($filename));
        fclose($handle);

        $xml = new DOMDocument();
        $xml->loadXML($xmlString);
        $this->_doc = Ccsd_Externdoc_Grobid::createFromXML("pdfgrobid.pdf", $xml);

        $this->_metas = $this->_doc->getMetadatas();
    }

    public function testDocType()
    {
        self::assertEquals("", $this->_doc->getType());
    }

    public function testGetTitle()
    {
        $title = $this->_metas['metas']['title'];
        self::assertEquals(['en' => "Simple rules can guide whether land-or ocean-based conservation will best benefit marine ecosystems"], $title);
    }

    public function testGetAbstract()
    {
        $abstract = $this->_metas['metas']['abstract'];
        self::assertEquals($abstract, ['en' => "Coastal marine ecosystems can be managed by actions undertaken both on the land and in the ocean. Quantifying and comparing the costs and benefits of actions in both realms is therefore necessary for efficient management. Here, we quantify the link between terrestrial sediment runoff and a downstream coastal marine ecosystem and contrast the cost-effectiveness of marine-and land-based conservation actions. We use a dynamic land-and sea-scape model to determine whether limited funds should be directed to 1 of 4 alternative conservation actions-protection on land, protection in the ocean, restoration on land, or restoration in the ocean-to maximise the extent of light-dependent marine benthic habitats across decadal timescales. We apply the model to a case study for a seagrass meadow in Australia. We find that marine restoration is the most cost-effective action over decadal timescales in this system, based on a conservative estimate of the rate at which seagrass can expand into a new habitat. The optimal decision will vary in different social-ecological contexts , but some basic information can guide optimal investments to counteract land-and ocean-based stressors: (1) marine restoration should be prioritised if the rates of marine ecosystem decline and expansion are similar and low; (2) marine protection should take precedence if the rate of marine ecosystem decline is high or if the adjacent catchment is relatively intact and has a low rate of vegetation decline; (3) land-based actions are optimal when the ratio of marine ecosystem expansion to decline is greater than 1:1.4, with terrestrial restoration typically the most cost-effective action; and (4) land protection should be prioritised if the catchment is relatively intact but the rate of vegetation decline is high. These PLOS Biology | https://doi.org/10.1371/journal.pbio."]);
    }

    public function testgetDate()
    {
        //todo : régler ça !!

        $date = $this->_metas['metas']['date'];
        self::assertEquals("", $date);
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
        $structures = $this->_metas['structures'];

        // Test des structures
        self::assertEquals($structures[0]['structname'], 'Centre for Biodiversity and Conservation Science');
        self::assertEquals($structures[0]['typestruct'], 'department');
        self::assertEquals($structures[0]['paysid'], 'AU');
        self::assertEquals($structures[0]['address'], 'St. Lucia Australia');

        self::assertEquals($structures[1]['structname'], 'The University of Queensland');
        self::assertEquals($structures[1]['typestruct'], 'institution');
        self::assertEquals($structures[1]['paysid'], 'AU');
        self::assertEquals($structures[1]['address'], 'St. Lucia Australia');

        self::assertEquals($structures[2]['structname'], 'Australian Research Council (ARC) Centre of Excellence in Environmental Decisions');
        self::assertEquals($structures[2]['typestruct'], 'laboratory');
        self::assertEquals($structures[2]['paysid'], 'AU');
        self::assertEquals($structures[2]['address'], 'St. Lucia Australia');

        self::assertEquals($structures[3]['structname'], 'University of Queensland');
        self::assertEquals($structures[3]['typestruct'], 'institution');
        self::assertEquals($structures[3]['paysid'], 'AU');
        self::assertEquals($structures[3]['address'], 'St. Lucia Australia');

        // Test des auteurs
        self::assertEquals($authors[0]['firstname'], 'Megan');
        self::assertEquals($authors[0]['lastname'], 'Saunders');
        self::assertEquals($authors[0]['quality'], 'aut');
        self::assertEquals($authors[0]['email'], 'm.saunders1@uq.edu.au');
        self::assertEquals($authors[0]['structures'], [0,1]);

        self::assertEquals($authors[1]['firstname'], 'Michael');
        self::assertEquals($authors[1]['lastname'], 'Bode');
        self::assertEquals($authors[1]['quality'], 'aut');
        self::assertEquals($authors[1]['structures'], [2,3]);
    }
}