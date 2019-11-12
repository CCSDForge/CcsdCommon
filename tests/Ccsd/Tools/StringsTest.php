<?php

use Ccsd_Tools_String as CTS;

class Ccsd_Tools_String_Test extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideStripCtrlChars
     * @param string $string
     * @param string $result
     */
    public function testStripCtrlChars($string, $result, $all, $preserveNL) {
        $this->assertEquals($result, Ccsd_Tools_String::stripCtrlChars($string ,'', $all, $preserveNL));
    }

    public function provideStripCtrlChars() {
        return [
            '1' => ['abcdefghijkmnop', 'abcdefghijkmnop' , true, false],
            '2' =>  ["abcdef\npoi", "abcdefpoi" , true, false],
            '3' =>  ["a \t
", 'a ', true, false],
            '4' =>  ['asdfg<br>qwe', 'asdfgqwe', true, true]
        ];
    }

    /**
     * @dataProvider provideTruncate
     * @param string $inputString
     * @param string $result
     * @param int $stringMaxLength
     * @param string $postTruncateString
     * @param boolean $cutAtSpace
     */
    public function testTruncate($inputString, $result, $stringMaxLength, $postTruncateString, $cutAtSpace) {
        $this->assertEquals($result, Ccsd_Tools_String::truncate($inputString, $stringMaxLength, $postTruncateString, $cutAtSpace));
    }

    public function provideTruncate() {
        return [
            '1' => ['Bonjour', 'Bonjour', 40, '', false],
            '2' => ['Bonjour le monde', 'Bonjour', 7, '', false],
            '3' => ['Bonjour le monde' , '', 0, '', true],
            '4' => ['Bonjour le monde' , '', 0, '', false],
            '5' => ['Bonjour le monde' , 'Bonjour le', 13, '', true],
            '6' => ['Bonjour le monde' , 'Bonj', 4, '', true],
            '61' => ['Bonjour le monde' , 'Bonjour', 9, '', true],
            '62' => ['Bonjour le monde' , 'Bonjour le', 10, '', true],
            '7' => ['Bonjour le monde' , 'Bonjour...', 8, '...', false],
            '8' => ['Bonjour               ' , 'Bonjour...', 8, '...', false],
        ];
    }

    /**
     * @dataProvider provideGetAlphaLetter(
     * @param $string
     * @param $result
     */
    public function testGetAlphaLetter($string, $result) {
        $this -> assertEquals($result, Ccsd_Tools_String::getAlphaLetter($string));
    }

    public function provideGetAlphaLetter() {
        return [
            '1' => ['hello','H'],
            '2' => ['    hello    ','H'],
            '3' => ['éléphant','E'],
            '4' => ['','other'],
            '5' => ["  \n365 jours",'other'],
            '6' => ['  [Tous les jours...]','T']
        ];
    }

    /**
     * @dataProvider provideGetHalDomainPaths
     * @param $domain
     * @param $result
     */
    public function testGetHalDomainPaths($domain, $result) {
        $this -> assertEquals($result, Ccsd_Tools_String::getHalDomainPaths($domain));
    }

    public function provideGetHalDomainPaths() {
        return [
            '1' => ['math.phy.yop',['math', 'math.phy', 'math.phy.yop']],
            '2' => ['math',['math']],
            '3' => ['sdv.bdd.mor',['sdv','sdv.bdd','sdv.bdd.mor']],

        ];
    }

    /**
     * @dataProvider provide_teststringToIso8601
     * @param $date
     * @param $result
     */
    public function teststringToIso8601($date, $result)
    {
        $this->assertEquals($result, Ccsd_Tools_String::stringToIso8601($date));
    }

    public function provide_teststringToIso8601() {
        return [
            'ok  0' => ['', ''],
            // Good date bad date
            'ok  1' => ['2010-12-01 05:22:06', '2010-12-01T05:22:06Z'],
            'nok 2' => ['2010-13-01 05:22:06', ''],
            'nok 3' => ['2010-13-01 43:22:06', ''],
            'nok 4' => ['2010-13', ''],
            // Padding
            'ok  4' => ['2010-12-01'         , '2010-12-01T00:00:00Z'],
            'ok  5' => ['2010', '2010-01-01T00:00:00Z'],
            'ok  6' => ['2008-00-00',   '2008-01-01T00:00:00Z' ],
        ];
    }

    /**
     * @dataProvider provide_testCleanString
     * @param string $s
     * @param int    $mode
     * @param string $r
     */
    public function testCleanString($s, $mode, $r) {
        $this -> assertEquals($r, CTS::cleanString($s, $mode));
    }


    public function provide_testCleanString() {
        return [
            ['abcd efgh'      ,  CTS::CLEAN_SPACES, 'abcd efgh'],
            ["\n  abc def \n ",  CTS::CLEAN_SPACES, 'abc def'],
            ["  \t\naaa"      ,  CTS::CLEAN_SPACES, 'aaa'],
            ["   a \n b  \n  ",  CTS::CLEAN_SPACES, "a \n b"],
            ["   a \n b  \n  ",  CTS::CLEAN_ALL_SPACES, 'ab'],
            ["\n  abc def \n ",  CTS::CLEAN_BEG_SPACE, "abc def \n "],
            ['   abc  ert  ', CTS::CLEAN_SPACES | CTS::CLEAN_CTRL, 'abc  ert'],
            ['  467 absh été' ,  CTS::CLEAN_EXCEPT_AZ, '467abshété']
        ];
    }

    /**
     * @param $date
     * @param $mysqlDate
     * @dataProvider provide_testStringToMysqlDate
     */
    public function testStringToMysqlDate($date, $mysqlDate) {
        $this -> assertEquals($mysqlDate, Ccsd_Tools_String::stringToMysqlDate($date));
    }

    public function provide_testStringToMysqlDate() {
        return [
            [ null, null ],
            ['', null],
            ['2007-04-03','2007-04-03' ],
            ['2007-00-00', '2007-01-01'],
            ['0000-00-00', null ],
            ['2000-64-43', null ],
        ];
    }
}