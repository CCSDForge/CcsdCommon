<?php
/**
 * Created by PhpStorm.
 * User: marmol
 * Date: 23/03/17
 * Time: 08:23
 */

class Ccsd_Tools_Test extends PHPUnit_Framework_TestCase
{

    /** @dataProvider provideGetEmailDomain
     * @param $email string
     * @param $res string
     */
    public function testGetEmailDomain($email, $res)
    {
        $this->assertEquals($res, Ccsd_Tools::getEmailDomain($email));
    }

    /**
     * provider for testGetEmailDomain
     * @return array2000ABCD1234
     */
    public function provideGetEmailDomain()
    {
        return [
            't2' => ['mail.sansdomain', ''],
            't3' => ['mail@domain', 'domain'],
            't4' => ['mail@domain.fr', 'domain.fr'],
            // doit on accepter cela?
            't5' => ['mail@domain@fr', 'domain@fr'],
            't6' => ['@domain.edu', 'domain.edu'],
        ];
    }

    /**
     * @param string $text
     * @param string $unaccentedText
     * @dataProvider provideStripAccents
     */
    public function teststripAccents($text, $unaccentedText) {
        $this -> assertEquals($unaccentedText, Ccsd_Tools::stripAccents($text));
    }

    /**
     * provider for teststripAccents
     * @return array
     */
    public function provideStripAccents() {
        return [
            'vide'  => ['', ''],
            'numerique' => ['123', '123'],
            'ascii' => ["bonjour, il n'y a pas d'accent ici","bonjour, il n'y a pas d'accent ici"],
            'french' => ["C'est l'été, on mange de l'aoïli, ça c'est sûr!","C'est l'ete, on mange de l'aoili, ca c'est sur!"],
            'tcheque' => ['áéěíóúčňřšťž','aeeioucnrstz'],
            'mixed' => ["C'est l'été, en tchéquie: óúč","C'est l'ete, en tchequie: ouc"],
        ];
    }
    
    /**
     * @dataProvider provideFilter_multiarray
     * @param $input array
     * @param $filter string
     * @param $res array
     */
    public function testFilter_multiarray($input, $filter, $res)
    {
        $this -> assertEquals($res, Ccsd_Tools::filter_multiarray ($input, $filter));
    }

    /**
     * provider for testFilter_multiarray
     * @return array
     */
    public function  provideFilter_multiarray() {
        return [
            't vide' => [ [], 'filtre', []],
            't2' => [ ['a' => 1, 'b' => 'deux' ,'c' => -6] , 'filtrer', ['a' => 1, 'b' => 'deux' ,'c' => -6] ],
            't3' => [ [ 1,2,3,4,5,6,3,4,5], 3, [ 0=> 1,1=>2, 3=>4, 4=>5,5=> 6,7=>4,8=>5]],
            // doit on accepter cela?
            't4'     => [ ['a' => 1, 'b' => 'deux' ,'c' => -6], 'deux', ['a' => 1 ,'c' => -6]],
            't5'     => [ ['a' => 1, 'b' => [ 3 , -6, [ 5, -6]] , 'c' => []  ,'d' => -6], -6 ,   ['a' => 1, 'b' => [ 0 => 3, 2 => [ 5]] ] ]  ,
            // Doit toujours etre identite si string
            'string1' => [ 'bonjour', 'toto'   , 'bonjour'],
            'string2' => [ 'bonjour', 'bonjour', 'bonjour'],
            'string3' => [ 'bonjour', ''       , 'bonjour'],
            'string4' => [ 'bonjour', 'bon'    , 'bonjour'],
            'string5' => [ 'bonjour', 'bonjour monsieur', 'bonjour'],
        ];
    }

    /**
     * @dataProvider provideHtmlToTex
     * @param $text string
     * @param $res string
     * @param $success bool // indique si on doit tester l'egalite ou la non egalite
     */
    public  function testHtmlToTex($text, $res, $success) {
        if ($success) {
            $this->assertEquals($res, Ccsd_Tools::htmlToTex($text));
        } else {
            $this->assertNotEquals($res, Ccsd_Tools::htmlToTex($text));
        }
        
    }

    /**
     * provider for testHtmlToTex
     * @return array
     */
    public  function  provideHtmlToTex() {
        return [
            't1' => [
                'Ce texte est en <b style="coco">Html</b> et il est: <ul><li style="titi">afrreux</li><li>inutile</li></ul>',
                'Ce texte est en \\textbf{Html} et il est: \\begin{itemize}\item[$\\bullet$] afrreux\item[$\\bullet$] inutile\\end{itemize}',
                true
                ],
            't2' => [
                "<p>Un text avec des &lt; et des &gt; </p> est souvent a mettre au <sup>carre</sup>",
                "\\\\ Un text avec des < et des >  \\\\ est souvent a mettre au \\textsuperscript{carre}",
                true
            ],

            // TODO Ce test devrait fonctionner...
            't3' => [
                'Ce texte est en <b style="coco">Html <sub>--</sub></b>',
                'Ce texte est en \\textbf{Html \\extsubscr{--}}',
                false
            ],
            'Espaces insecable' => [
                'Un texte avec deux point : voire avec un point virgule ; mais des maths $f:x \right x^2$ et des entities &#234; et un <NNT>762354</NNT>',
                'Un texte avec deux point\,: voire avec un point virgule\,; mais des maths $f:x \right x^2$ et des entities {\^e} et un <NNT>762354</NNT>',
                true
            ]


        ];
    }

    /**
     * @dataProvider providePreg_in_array_get_key
     */
    public function test_preg_in_array_get_key($needle,$array,$begin,$end, $result) {
        $this -> assertEquals($result, Ccsd_Tools::preg_in_array_get_key($needle,$array,$begin,$end));

    }

    /**
     * provider for test_preg_in_array_get_key
     * @return array
     */
    public function providePreg_in_array_get_key() {
        return [
            '1' => [ 'a', [], null,null, null ],
            '2' => [ 'a', [-1 => 'a'], null,null, -1],
            '3' => [ 'a', [ '1','2','3','4' ], null,null,null],
            '4' => ['a',  [4 =>'4', 'trouve' => 'a', 6=>'6'], null,null, 'trouve'],
            '5' => [ '(aa|bb)',  [5, 'b' => '7', 'hal' => 'aa', 'a' => '5'], null,null, 'hal'],
            '6' => [ '(aaa|bb)',  [5, 'b' => '7', 'hal' => 'aa', 'a' => '5'],'r',null, null],
        ];
    }

    /**
     * @dataProvider provideTest_in_next_array
     * @param      $needle
     * @param      $array
     * @param      $key
     * @param bool $all
     * @param      $result
     */
    public function test_in_next_array($needle, $array, $key, $all, $result) {
        $this -> assertEquals($result, Ccsd_Tools::in_next_array($needle, $array, $key, $all));

    }

    /**
     * Provider for test_in_next_array
     * @return array
     */
    public function provideTest_in_next_array() {
        return [
            '1' => [ 'a', [], null,null, null ],
            '2' => [ 'a', [ [], []], null,null,null],
            '3' => [ 'a', [ [], [ '1','2','3','4' ]], null,null,null],
            '4' => [ 'a', [[ '1','2','3','4'], [5, 'b' => '7', 'hal' => 'aa', 'a' => '5'], [4 =>'4', 'trouve' => 'a', 6=>'6']], 'trouve' ,null, 2],
        ];
    }

    /**
     * @dataProvider provideTest_htmlToTex
     */
    public function test_Citation($success, $stripBefore, $string, $result) {
        if ($success) {
            if ($stripBefore) {
                $this->assertEquals($result, Ccsd_Tools::htmlToTex(strip_tags($string)));
            } else {
                $this->assertEquals($result, Ccsd_Tools::htmlToTex($string));
            }
        } else {
            if ($stripBefore) {
                $this->assertNotEquals($result, Ccsd_Tools::htmlToTex(strip_tags($string)));
            } else {
                $this->assertNotEquals($result, Ccsd_Tools::htmlToTex($string));
            }
        }
    }

    public function provideTest_htmlToTex() {
        return [
            1 => [ true, true,
                "Thomas Recht. Étude de l'écoconception de maisons à énergie positive. Eco-conception. PSL Research University, 2016. Français. <a target='_blank' href='http://www.theses.fr/2016PSLEM024'>&lt; NNT : 2016PSLEM024&gt;</a>. <a target='_blank' href='https://pastel.archives-ouvertes.fr/tel-01545437'>&lt; tel-01545437&gt;</a>",
                "Thomas Recht. {\\'E}tude de l'{\\'e}coconception de maisons {\\`a} {\\'e}nergie positive. Eco-conception. PSL Research University, 2016. Fran{\\c c}ais. < NNT\\,: 2016PSLEM024>. < tel-01545437>"
            ],
            2 => [ true, false,
                "Un test N<sub>a<sup>2</sup></sub> pour un &lt; <a href='http://fr.wikipedia.org/alpha'>&alpha;</a> &gt;",
                'Un test N\\textsubscr{a\textsuperscript{2}} pour un < $\alpha$ >',
            ],
            'Nettoyage de blancs' => [ true, false,
                "    Y a des blancs partout    ",
                "Y a des blancs partout",
                ],
            3 => [ true, false,
                'Saïd Assous. text: text 2. Computer Science [cs]. Arts et Métiers ParisTech, 2005. English. <a target="_blank" href="http://www.theses.fr/2000ABCD1234">&lt;NNT : 2000ABCD1234&gt;</a>. <a target="_blank" href="https://pastel.archives-ouvertes.fr/pastel-0000XXXX">&lt;pastel-0000XXXX&gt;</a>',
                'Sa{\"i}d Assous. text: text 2. Computer Science [cs]. Arts et M{\\\'e}tiers ParisTech, 2005. English. <NNT\\,: 2000ABCD1234>. <pastel-0000XXXX>',
            ],
            // Si tex en entree ?
            100 => [ true, false,
                "Thomas Recht. {\\'E}tude de \$a < b\$", // Il y a des blanc autour du <
                "Thomas Recht. {\\'E}tude de \$a < b\$"

            ],
            101 => [ true, false,
                "Measurement of the deuteron spin structure function \$g^{d}_1(x)\$ for \$1\\ (GeV/c)^2 < Q^2 < 40\\ (GeV/c)^2\$.",
                "Measurement of the deuteron spin structure function \$g^{d}\\_1(x)\$ for \$1\\ (GeV/c)^2 < Q^2 < 40\\ (GeV/c)^2\$.",
            ],
            102 => [ true, false,
                "Tex avec un superieur: \$A < B\$", // Il y a des blancs autour du <
                "Tex avec un superieur: \$A < B\$",
            ],
            103 => [ false, false,  // ECHEC de resultat
                "Tex avec un superieur: \$A<B\$", // Il n'y a pas de blancs autour du <
                "Tex avec un superieur: \$A<B\$",
            ],
        ];
    }
    /**
     * @dataProvider provide_getFromNormalized
     * @param string $fullname
     * @param string $email
     * @param string $result
     */
    public function test_getFromNormalized($fullname, $email, $result) {
        $this -> assertEquals($result, Ccsd_Tools::getFromNormalized($fullname, $email));
    }

    public function provide_getFromNormalized() {
        return [
            1 => ["Jon Doe","Jon.Doe@labas.com",'"Jon Doe" <Jon.Doe@labas.com>' ],
            2 => ["Jon H. Doe","Jon.Doe@labas.com",'"Jon Doe" <Jon.Doe@labas.com>'],
            3 => ["Jon Doe-K.","Jon.Doe@labas.com",'"Jon Doe-K." <Jon.Doe@labas.com>'],
            4 => ["J. Doe","Jon.Doe@labas.com",'"J. Doe" <Jon.Doe@labas.com>'],
            5 => ["Jon-K. Doe","Jon.Doe@labas.com",'"Jon Doe" <Jon.Doe@labas.com>'],
        ];
    }
}