<?php
/**
 * Created by PhpStorm.
 * User: genicot
 * Date: 25/03/19
 * Time: 10:21
 */


class Ccsd_Externdoc_Inra_ArticleTest extends PHPUnit_Framework_TestCase
{


    static private function loadFile($file) {
        $docXml = new DOMDocument();
        $docXml->load($file,LIBXML_ERR_ERROR);
        return Ccsd_Externdoc_Inra_Article::createFromXML("1", $docXml);
    }
    /**
     * @param $Ccsd_Externdoc_Inra_Article
     * @dataProvider docProvider
     */
    public function testGetDOI($doc, $res)
    {
        $expected = $res['testGetDOI'];
        $this->assertEquals($expected, $doc->getDOI());
    }

    /**
     * @param $Ccsd_Externdoc_Inra_Article
     * @dataProvider docProvider
     */
    public function testGetPubmedId($doc,$res)
    {
        $expected = $res['testGetPubmedId'];
        $this->assertEquals($expected, $doc->getPubmedId());
    }

    /**
     * @param $Ccsd_Externdoc_Inra_Article
     * @dataProvider docProvider
     */
    public function testGetHalDomain($doc,$res)
    {
        $expected = $res['testGetHalClassification'];
        $this->assertEquals($expected, $doc->getHalDomain());
    }


    /**
     * @param $Ccsd_Externdoc_Inra_Article
     * @dataProvider docProvider
     */
    public function testGetIssn($doc,$res)
    {
        $expected = $res['testGetIssn'];
        $this->assertEquals($expected, $doc->getIssn());
    }

    /**
     * @param $Ccsd_Externdoc_Inra_Article
     * @dataProvider docProvider
     */
    public function testGetRecordAccessCondition($doc,$res)
    {
        $expected = $res['testGetRecordAccessCondition'];
        $this->assertEquals($expected, $doc->getRecordAccessCondition());
    }

    /**
     * @param $Ccsd_Externdoc_Inra_Article
     * @dataProvider docProvider
     */
    public function testGetAuthors($doc,$res)
    {
        $i = 0;
        $expected = $res['testGetAuthors'];
        foreach ($expected as $author) {
            $this->assertEquals($expected[$i]['firstname'], $doc->getAuthors()[$i]['firstname']);
            $this->assertEquals($expected[$i]['lastname'], $doc->getAuthors()[$i]['lastname']);
            $this->assertEquals($expected[$i]['affiliation'], $doc->getAuthors()[$i]['affiliation']);
            $this->assertEquals($expected[$i]['email'], $doc->getAuthors()[$i]['email']);
            $i++;
        }

        //var_dump($this->_doc->getAuthors());
        }

    /**
     * @param $Ccsd_Externdoc_Inra_Article
     * @dataProvider docProvider
     */
    public function testGetDocLang($doc,$res)
    {
        $expected = $res['testGetDocLang'];
        $this->assertEquals($expected, $doc->getDocLang());
    }

    /**
     * @param $Ccsd_Externdoc_Inra_Article
     * @dataProvider docProvider
     */
    public function testGetExtAuthors($doc,$res)
    {

        $expected = $res['testGetExtAuthors'];
        $i = 0;

        foreach($expected as $extAuthors){

            $this->assertEquals($extAuthors['firstname'],$doc->getExtAuthors()[$i]['firstname']);
            $this->assertEquals($extAuthors['lastname'],$doc->getExtAuthors()[$i]['lastname']);
            $this->assertEquals($extAuthors['affiliation externe'],$doc->getExtAuthors()[$i]['affiliation externe']);
            $i++;
        }

    }


    /**
     * @param $Ccsd_Externdoc_Inra_Article
     * @dataProvider docProvider
     */
    public function testGetUtKey($doc,$res)
    {
        $expected = $res['testGetUtKey'];
        $this->assertEquals($expected, $doc->getUtKey());
    }


    /**
     * @param $Ccsd_Externdoc_Inra_Article
     * @dataProvider docProvider
     */
    public function testGetHalTypology($doc,$res)
    {
        $expected = $res['testGetHalTypology'];
        $this->assertEquals($expected, $doc->getHalTypology());
    }


    /**
     * @param $Ccsd_Externdoc_Inra_Article
     * @dataProvider docProvider
     */
    public function testGetIdentifier($doc,$res)
    {
        $expected = $res['testGetIdentifier'];
        $this->assertEquals($expected, $doc->getIdentifier());
    }


    /**
     * @param $Ccsd_Externdoc_Inra_Article
     * @dataProvider docProvider
     */

    public function testGetTitle($doc,$res){

        $expected = $res['testGetTitle'];
        foreach ($expected as $lang=>$value){
            $this->assertEquals($value,$doc->getTitle()[$lang]);
        }
    }


    /**
     * @param $Ccsd_Externdoc_Inra_Article
     * @dataProvider docProvider
     */

    public function testGetSubtitle($doc,$res){

        $expected = $res['testGetSubtitle'];
        $this->assertEquals($expected, $doc->getsubTitle());
    }

    /**
     * @param $Ccsd_Externdoc_Inra_Article
     * @dataProvider docProvider
     */

    public function testGetIsbn($doc,$res){

        $expected = $res['testGetIsbn'];
        $this->assertEquals($expected, $doc->getIsbn());
    }


    /**
     * @param $Ccsd_Externdoc_Inra_Article
     * @dataProvider docProvider
     */

    public function testGetDate($doc,$res){

        $expected = $res['testGetDate'];
        $this->assertEquals($expected, $doc->getDate());
    }

    /**
     * @param $Ccsd_Externdoc_Inra_Article
     * @dataProvider docProvider
     */

    public function testGetSerie($doc,$res){

        $expected = $res['testGetSerie'];
        $this->assertEquals($expected, $doc->getSerie());
    }

    /**
     * @param $Ccsd_Externdoc_Inra_Article
     * @dataProvider docProvider
     */

    public function testGetVolume($doc,$res){

        $expected = $res['testGetVolume'];
        $this->assertEquals($expected, $doc->getVolume());
    }

    /**
     * @param $Ccsd_Externdoc_Inra_Article
     * @dataProvider docProvider
     */

    public function testGetIssue($doc,$res){

        $expected = $res['testGetIssue'];
        $this->assertEquals($expected, $doc->getIssue());
    }

    /**
     * @param $Ccsd_Externdoc_Inra_Article
     * @dataProvider docProvider
     */

    public function testGetPage($doc,$res){

        $expected = $res['testGetPage'];
        $this->assertEquals($expected, $doc->getPage());
    }

    /**
     * @param $Ccsd_Externdoc_Inra_Article
     * @dataProvider docProvider
     */

    public function testGetPublisher($doc,$res){

        $expected = $res['testGetPublisher'];
        $this->assertEquals($expected, $doc->getPublisher());
    }

    /**
     * @param $Ccsd_Externdoc_Inra_Article
     * @dataProvider docProvider
     */

    public function testGetPubPlace($doc,$res){

        $expected = $res['testGetPubPlace'];
        $this->assertEquals($expected, $doc->getPubPlace());
    }


    /**
     * @param $Ccsd_Externdoc_Inra_Article
     * @dataProvider docProvider
     */

    public function testGetSeriesEditor($doc,$res){

        $expected = $res['testGetSeriesEditor'];
        $this->assertEquals($expected, $doc->getSeriesEditor());
    }



    /**
     * @param $Ccsd_Externdoc_Inra_Article
     * @dataProvider docProvider
     */

    public function testGetAbstract($doc,$res){

        $expected = $res['testGetAbstract'];
        $this->assertEquals($expected, $doc->getAbstract());
    }



    /**
     * @param $Ccsd_Externdoc_Inra_Article
     * @dataProvider docProvider
     */

    public function testGetKeywords($doc,$res){

        $expected = $res['testGetKeywords'];

        foreach($expected as $key=>$value){
            $this->assertEquals($value,$doc->getKeywords()[$key]);
        }
    }



    /**
     * @param $Ccsd_Externdoc_Inra_Article
     * @dataProvider docProvider
     */

    public function testGetHalSending($doc,$res){
        $expected = $res['testGetHalSending'];
        $this->assertEquals($expected, $doc->getHalsending());
    }

    /**
     * @return array
     */
    public static function docProvider() {
        $docList = [ './ressources/doc_inra/Echantillon_1.xml' => [
                'testGetDOI'  => '10.3390/genes9080418',
                'testGetPubmedId' => '30127280',
                'testGetHalClassification' => 'sdv.mp',
                'testGetIssn' => '2073-4425',
                'testGetRecordAccessCondition' => '',
                'testGetAuthors'=> [['firstname' => 'Franck',
                                    'lastname' =>'Dorkeld',
                                    'affiliation' => [
                                             ['name'=>'Institut National de Recherche Agronomique',
                                              'acronym'=>'INRA',
                                              'unit'=>[
                                                  ['name'=>'Centre de Biologie pour la Gestion des Populations',
                                                   'rnsr'=>'199817893M',
                                                   'laboratory'=>'UMR : Centre de Biologie pour la Gestion des Populations, Montferrier Sur Lez',
                                                   'type'=>'UMR',
                                                   'code'=>'1062',
                                                   'country'=>'FRA',
                                                   'city'=>'Montferrier Sur Lez',
                                                   'departments'=>[
                                                       [
                                                           'code'=>'16',
                                                           'name'=>'Santé des Plantes et Environnement',
                                                           'acronym'=>'Santé des Plantes et Environnement'
                                                       ],
                                                       [
                                                           'code'=>'54',
                                                           'name'=>'Ecologie des Forêts, Prairies et Milieux Aquatiques',
                                                           'acronym'=>'Ecologie des Forêts, Prairies et Milieux Aquatiques'
                                                       ]
                                                   ]
                                                  ]
                                              ]
                                             ]
                                    ],
                                    'email' => 'franck.dorkeld@inra.fr']],
                'testGetDocLang'=> 'eng',
                'testGetExtAuthors'=> [
                    ['firstname'=>'Denis',
                        'lastname' =>'Sereno',
                        'affiliation externe'=>[['name'=>'Institut de Recherche pour le Développement',
                                                'city'=>'Montpellier',
                                                'country'=>'FRA',
                                                'identifier'=>'311174',
                                                'partners'=>'311730 Université de Montpellier UM FRA',
                                                'section'=>'InterTryp']]
                        ],
                    ['firstname'=>'Mohammad',
                    'lastname' =>'Akhoundi',
                    'affiliation externe'=>[['name'=>'Avicenne Hospital',
                                            'city'=>'Bobigny',
                                            'country'=>'FRA',
                                            'identifier'=>'331642',
                                            'section' => 'Parasitology-Mycology Department']]
                    ],
                    ['firstname'=>'Pascale',
                        'lastname' =>'Perrin',
                        'affiliation externe'=>[['name'=>'Centre National de la Recherche Scientifique',
                                                'city'=>'Montpellier',
                                                'country'=>'FRA',
                                                'identifier'=>'311417',
                                                'partners'=>['311730 Université de Montpellier UM FRA',
                                                            '311174 Institut de Recherche pour le Développement IRD FRA'],
                                                'section'=>'UMR5290 Maladies Infectieuses et Vecteurs : Ecologie, Génétique, Evolution et Contrôle (MIVEGEC)']]
                    ]]
                    ,
                'testGetUtKey'=>'000443615400048',
                'testGetHalTypology'=>'ART',
                'testGetIdentifier'=>'444645',
                'testGetTitle'=>['en'=>'Pathogen Species Identification from Metagenomes in Ancient Remains: The Challenge of Identifying Human Pathogenic Species of Trypanosomatidae via Bioinformatic Tools'],
                'testGetSubtitle' =>'',
                'testGetIsbn' => '',
                'testGetDate'=> '2018',
                'testGetSerie'=> 'Genes',
                'testGetVolume'=>'9',
                'testGetIssue'=>'8',
                'testGetPage'=>'',
                'testGetPublisher'=>'',
                'testGetPubPlace'=>'',
                'testGetSeriesEditor'=>'',
                'testGetAbstract'=>['en'=>'Accurate species identification from ancient DNA samples is a difficult task that would shed light on the evolutionary history of pathogenic microorganisms. The field of palaeomicrobiology has undoubtedly benefited from the advent of untargeted metagenomic approaches that use next-generation sequencing methodologies. Nevertheless, assigning ancient DNA at the species level is a challenging process. Recently, the gut microbiome analysis of three pre-Columbian Andean mummies (Santiago-Rodriguez et al., 2016) has called into question the identification of Leishmania in South America. The accurate assignment would be important because it will provide some key elements that are linked to the evolutionary scenario for visceral leishmaniasis agents in South America. Here, we recovered the metagenomic data filed in the metagenomics RAST server (MG-RAST) to identify the different members of the Trypanosomatidae family that have infected these ancient remains. For this purpose, we used the ultrafast metagenomic sequence classifier, based on an exact alignment of k-mers (Kraken) and Bowtie2, an ultrafast and memory-efficient tool for aligning sequencing reads to long reference sequences. The analyses, which have been conducted on the most exhaustive genomic database possible on Trypanosomatidae, show that species assignments could be biased by a lack of some genomic sequences of Trypanosomatidae species (strains). Nevertheless, our work raises the issue of possible co-infections by multiple members of the Trypanosomatidae family in these three pre-Columbian mummies. In the three mummies, we show the presence of DNA that is reminiscent of a probable co-infection with Leptomonas seymouri, a parasite of insect\'s gut, and Lotmaria.'],
                'testGetKeywords'=>['en'=>['Trypanosomatidae','kraken taxonomic assignment tool','bowtie2 fast short reads aligner','ancient DNA','parasitome','co-infection']],
                'testGetHalSending'=>'false'
            ]];
        $res = [];
        foreach ($docList as $file => $expected) {
            $res[] = [ self::loadFile($file), $expected ];
        }
        return $res;

    }







}
