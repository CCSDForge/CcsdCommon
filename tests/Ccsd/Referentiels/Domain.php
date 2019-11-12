<?php

class Ccsd_Referentiels_Domain_Test extends PHPUnit_Framework_TestCase
{


    public function setUp()
    {

    }

    /**
     * @param array  $a_domains
     * @param string $result
     * @dataProvider creeArborescenceJsonProvider
     */
    public function testCreeArborescenceJson($a_domains, $result)
    {
        $domain = new Ccsd_Referentiels_Domain(1);
        $this->assertEquals($result, $domain->creeArborescenceJson($a_domains));

    }

    /**
     *
     */
    public function testCreeArborescenceJsonTotal()
    {
        $domain = new Ccsd_Referentiels_Domain(1);
        $tableauDomain = $domain->arborescence();
        $jsonDomain = $domain->creeArborescenceJson($tableauDomain);
        $this->assertEquals(self::$json_domains, $jsonDomain);
    }

    public function creeArborescenceJsonProvider()
    {
        return [
            'vide' => [
                [['CODE' => 'math']],
                '{"math":[]}'
            ],
            1      => [
                [['CODE' => 'math'],
                    ['CODE' => 'math.CO'],
                    ['CODE' => 'math.ST']],
                '{"math":{"math.CO":[],"math.ST":[]}}'
            ],
        ];
    }

    public function testArborescence()
    {
        $domain = new Ccsd_Referentiels_Domain(1);
        $this->assertEquals([1 => [1 => [], 2 => [], 3 => []]], $domain->arborescence());
    }

    public function testTableauDomaineTrie()
    {
        $domain = new Ccsd_Referentiels_Domain(1);
        $domainDomaineTrie = $domain->tableauDomaineTrie([6, 1, 2, 3, 4, 5,  7, 8, 9, 10, 11]);
        $res = [6  => ['ID' => '6', 'CODE' => 'chim.cris', 'PARENT' => '1', 'LEVEL' => '1', 'HAVENEXT' => '0',        ],
                1  => ['ID' => '1', 'CODE' => 'chim', 'PARENT' => '0', 'LEVEL' => '0', 'HAVENEXT' => '1',        ],
                2  => ['ID' => '2', 'CODE' => 'chim.anal', 'PARENT' => '1', 'LEVEL' => '1', 'HAVENEXT' => '0'                ],
                3  => ['ID' => '3', 'CODE' => 'chim.cata', 'PARENT' => '1', 'LEVEL' => '1', 'HAVENEXT' => '0'                ],
                4  => ['ID' => '4', 'CODE' => 'chim.chem', 'PARENT' => '1', 'LEVEL' => '1', 'HAVENEXT' => '0'                ],
                5  => ['ID' => '5', 'CODE' => 'chim.coor', 'PARENT' => '1', 'LEVEL' => '1', 'HAVENEXT' => '0'                ],
                7  => ['ID' => '7', 'CODE' => 'chim.geni', 'PARENT' => '1', 'LEVEL' => '1', 'HAVENEXT' => '0'                ],
                8  => ['ID' => '8', 'CODE' => 'chim.inor', 'PARENT' => '1', 'LEVEL' => '1', 'HAVENEXT' => '0'                ],
                9  => ['ID' => '9', 'CODE' => 'chim.mate', 'PARENT' => '1', 'LEVEL' => '1', 'HAVENEXT' => '0'                ],
                10 => ['ID' => '10', 'CODE' => 'chim.orga', 'PARENT' => '1', 'LEVEL' => '1', 'HAVENEXT' => '0'                ],
                11 => ['ID' => '11', 'CODE' => 'chim.othe', 'PARENT' => '1', 'LEVEL' => '1', 'HAVENEXT' => '0'                ],
        ];
        $this->assertEquals($res, $domainDomaineTrie);
    }

    public static $json_domains = '{"chim":{"chim.othe":[],"chim.cata":[],"chim.chem":[],"chim.anal":[],"chim.coor":[],"chim.inor":[],"chim.orga":[],"chim.theo":[],"chim.ther":[],"chim.cris":[],"chim.geni":[],"chim.mate":[],"chim.poly":[],"chim.radio":[]},"info":{"info.info-ds":[],"info.info-na":[],"info.info-lg":[],"info.info-ar":[],"info.info-ao":[],"info.info-au":[],"info.info-oh":[],"info.info-db":[],"info.info-dl":[],"info.info-bi":[],"info.info-bt":[],"info.info-sc":[],"info.info-dc":[],"info.info-cc":[],"info.info-cr":[],"info.eiah":[],"info.info-se":[],"info.info-cg":[],"info.info-im":[],"info.info-cl":[],"info.info-gt":[],"info.info-mc":[],"info.info-iu":[],"info.info-ia":[],"info.info-ce":[],"info.info-ai":[],"info.info-hc":[],"info.info-pl":[],"info.info-gl":[],"info.info-ms":[],"info.info-lo":[],"info.info-dm":[],"info.info-mo":[],"info.info-mm":[],"info.info-cy":[],"info.info-pf":[],"info.info-ir":[],"info.info-ro":[],"info.info-rb":[],"info.info-ne":[],"info.info-ni":[],"info.info-si":[],"info.info-sd":[],"info.info-gr":[],"info.info-os":[],"info.info-ma":[],"info.info-es":[],"info.info-sy":[],"info.info-et":[],"info.info-it":[],"info.info-fl":[],"info.info-ti":[],"info.info-ts":[],"info.info-tt":[],"info.info-cv":[],"info.info-wb":[]},"math":{"math.math-ac":[],"math.math-oa":[],"math.math-qa":[],"math.math-ca":[],"math.math-fa":[],"math.math-na":[],"math.math-ra":[],"math.math-ct":[],"math.math-co":[],"math.math-ap":[],"math.math-ag":[],"math.math-dg":[],"math.math-mg":[],"math.math-sg":[],"math.math-ho":[],"math.math-kt":[],"math.math-lo":[],"math.math-gm":[],"math.math-oc":[],"math.math-mp":[],"math.math-pr":[],"math.math-st":[],"math.math-ds":[],"math.math-it":[],"math.math-gr":[],"math.math-nt":[],"math.math-rt":[],"math.math-sp":[],"math.math-at":[],"math.math-gn":[],"math.math-gt":[],"math.math-cv":[]},"phys":{"phys.hist":[],"phys.astr":{"phys.astr.ga":[],"phys.astr.sr":[],"phys.astr.co":[],"phys.astr.im":[],"phys.astr.he":[],"phys.astr.ep":[]},"phys.cond":{"phys.cond.cm-gen":[],"phys.cond.cm-sce":[],"phys.cond.gas":[],"phys.cond.cm-scm":[],"phys.cond.cm-sm":[],"phys.cond.cm-ms":[],"phys.cond.cm-s":[],"phys.cond.cm-ds-nn":[],"phys.cond.cm-msqhe":[]},"phys.meca":{"phys.meca.acou":[],"phys.meca.biom":[],"phys.meca.geme":[],"phys.meca.msmeca":[],"phys.meca.mefl":[],"phys.meca.mema":[],"phys.meca.solid":[],"phys.meca.stru":[],"phys.meca.ther":[],"phys.meca.vibr":[]},"phys.nexp":[],"phys.nucl":[],"phys.qphy":[],"phys.phys":{"phys.phys.phys-atm-ph":[],"phys.phys.phys-data-an":[],"phys.phys.phys-bio-ph":[],"phys.phys.phys-chem-ph":[],"phys.phys.phys-flu-dyn":[],"phys.phys.phys-ed-ph":[],"phys.phys.phys-geo-ph":[],"phys.phys.phys-hist-ph":[],"phys.phys.phys-ins-det":[],"phys.phys.phys-optics":[],"phys.phys.phys-pop-ph":[],"phys.phys.phys-ao-ph":[],"phys.phys.phys-atom-ph":[],"phys.phys.phys-class-ph":[],"phys.phys.phys-gen-ph":[],"phys.phys.phys-med-ph":[],"phys.phys.phys-comp-ph":[],"phys.phys.phys-space-ph":[],"phys.phys.phys-acc-ph":[],"phys.phys.phys-plasm-ph":[],"phys.phys.phys-soc-ph":[]},"phys.hexp":[],"phys.hphe":[],"phys.hlat":[],"phys.hthe":[],"phys.mphy":[],"phys.grqc":[]},"sdu":{"sdu.astr":{"sdu.astr.ga":[],"sdu.astr.sr":[],"sdu.astr.co":[],"sdu.astr.im":[],"sdu.astr.he":[],"sdu.astr.ep":[]},"sdu.other":[],"sdu.envi":[],"sdu.ocean":[],"sdu.stu":{"sdu.stu.cl":[],"sdu.stu.gl":[],"sdu.stu.gc":[],"sdu.stu.ag":[],"sdu.stu.gm":[],"sdu.stu.gp":[],"sdu.stu.hy":[],"sdu.stu.mi":[],"sdu.stu.me":[],"sdu.stu.oc":[],"sdu.stu.pg":[],"sdu.stu.pl":[],"sdu.stu.pe":[],"sdu.stu.st":[],"sdu.stu.te":[],"sdu.stu.vo":[]}},"nlin":{"nlin.nlin-ao":[],"nlin.nlin-cg":[],"nlin.nlin-cd":[],"nlin.nlin-ps":[],"nlin.nlin-si":[]},"scco":{"scco.comp":[],"scco.ling":[],"scco.neur":[],"scco.psyc":[]},"shs":{"shs.anthro-bio":[],"shs.anthro-se":[],"shs.archi":[],"shs.archeo":[],"shs.art":[],"shs.droit":[],"shs.demo":[],"shs.eco":[],"shs.edu":[],"shs.class":[],"shs.envir":[],"shs.genre":[],"shs.gestion":[],"shs.geo":[],"shs.hist":[],"shs.hisphilso":[],"shs.museo":[],"shs.langue":[],"shs.litt":[],"shs.musiq":[],"shs.stat":[],"shs.phil":[],"shs.psy":[],"shs.relig":[],"shs.scipo":[],"shs.info":[],"shs.socio":[]},"sde":{"sde.be":[],"sde.es":[],"sde.ie":[],"sde.mcg":[]},"spi":{"spi.acou":[],"spi.auto":[],"spi.other":[],"spi.elec":[],"spi.tron":[],"spi.nrj":[],"spi.gciv":{"spi.gciv.cd":[],"spi.gciv.ch":[],"spi.gciv.dv":[],"spi.gciv.ec":[],"spi.gciv.gcn":[],"spi.gciv.geotech":[],"spi.gciv.it":[],"spi.gciv.mat":[],"spi.gciv.risq":[],"spi.gciv.rhea":[],"spi.gciv.struct":[]},"spi.gproc":[],"spi.mat":[],"spi.nano":[],"spi.fluid":[],"spi.meca":{"spi.meca.biom":[],"spi.meca.geme":[],"spi.meca.msmeca":[],"spi.meca.mefl":[],"spi.meca.mema":[],"spi.meca.solid":[],"spi.meca.stru":[],"spi.meca.ther":[],"spi.meca.vibr":[]},"spi.opti":[],"spi.plasma":[],"spi.signal":[]},"sdv":{"sdv.aen":[],"sdv.ot":[],"sdv.bibs":[],"sdv.bbm":{"sdv.bbm.bc":[],"sdv.bbm.bm":[],"sdv.bbm.bs":[],"sdv.bbm.bp":[],"sdv.bbm.gtp":[],"sdv.bbm.mn":[]},"sdv.bid":{"sdv.bid.evo":[],"sdv.bid.spt":[]},"sdv.ba":{"sdv.ba.mvsa":[],"sdv.ba.zi":[],"sdv.ba.zv":[]},"sdv.bc":{"sdv.bc.ic":[],"sdv.bc.bc":[]},"sdv.bdlr":{"sdv.bdlr.ra":[],"sdv.bdlr.rs":[]},"sdv.bdd":{"sdv.bdd.eo":[],"sdv.bdd.gam":[],"sdv.bdd.mor":[]},"sdv.bv":{"sdv.bv.ap":[],"sdv.bv.bot":[],"sdv.bv.pep":[]},"sdv.bio":[],"sdv.can":[],"sdv.ee":{"sdv.ee.bio":[],"sdv.ee.eco":[],"sdv.ee.ieo":[],"sdv.ee.sant":[]},"sdv.eth":[],"sdv.gen":{"sdv.gen.ga":[],"sdv.gen.gpl":[],"sdv.gen.gpo":[],"sdv.gen.gh":[]},"sdv.imm":{"sdv.imm.all":[],"sdv.imm.ia":[],"sdv.imm.ii":[],"sdv.imm.imm":[],"sdv.imm.vac":[]},"sdv.ib":{"sdv.ib.bio":[],"sdv.ib.ima":[],"sdv.ib.mn":[]},"sdv.ida":[],"sdv.mp":{"sdv.mp.bac":[],"sdv.mp.myc":[],"sdv.mp.par":[],"sdv.mp.pro":[],"sdv.mp.vir":[]},"sdv.mhep":{"sdv.mhep.aha":[],"sdv.mhep.csc":[],"sdv.mhep.chi":[],"sdv.mhep.derm":[],"sdv.mhep.em":[],"sdv.mhep.geo":[],"sdv.mhep.geg":[],"sdv.mhep.hem":[],"sdv.mhep.heg":[],"sdv.mhep.mi":[],"sdv.mhep.me":[],"sdv.mhep.os":[],"sdv.mhep.phy":[],"sdv.mhep.psr":[],"sdv.mhep.psm":[],"sdv.mhep.ped":[],"sdv.mhep.rsoa":[],"sdv.mhep.un":[]},"sdv.neu":{"sdv.neu.nb":[],"sdv.neu.pc":[],"sdv.neu.sc":[]},"sdv.spee":[],"sdv.sa":{"sdv.sa.aep":[],"sdv.sa.agro":[],"sdv.sa.hort":[],"sdv.sa.spa":[],"sdv.sa.sds":[],"sdv.sa.sta":[],"sdv.sa.stp":[],"sdv.sa.sf":[],"sdv.sa.zoo":[]},"sdv.sp":{"sdv.sp.med":[],"sdv.sp.pg":[],"sdv.sp.pharma":[]},"sdv.tox":{"sdv.tox.eco":[],"sdv.tox.tca":[],"sdv.tox.tvm":[]}},"stat":{"stat.ap":[],"stat.ot":[],"stat.co":[],"stat.ml":[],"stat.me":[],"stat.th":[]},"qfin":{"qfin.st":[],"qfin.gn":[],"qfin.cp":[],"qfin.pm":[],"qfin.rm":[],"qfin.tr":[],"qfin.pr":[]}}';

}
