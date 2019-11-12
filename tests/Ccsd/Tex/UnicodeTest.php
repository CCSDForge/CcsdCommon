<?php

/**
 * Created by PhpStorm.
 * User: marmol
 * Date: 08/08/17
 * Time: 15:34
 */
class Ccsd_Tex_UnicodeTest extends PHPUnit_Framework_TestCase
{

    public function testparseXelatexLingualsCommand() {
        $unicode = new Ccsd_Tex_Unicode('zh');
        $this -> assertEquals('bonjour', $unicode -> parseXelatexLingualsCommand('bonjour'));
    }

    public function testparseXelatexLingualsCommand2() {
        $text  = '\\section{Sanskrit}सर्वे मानवाः स्वतन्त्राः समुत्पन्नाः' . "\n";
        $text .= '\\section{CJK}Here\'s some Chinese:' . "\n";
        $text .= '人有悲欢离合，月有阴晴圆缺。此事古难全，但愿人长久，千里共婵娟。'. "\n";

        $resText = "\\section{Sanskrit}\\begin{sanskrit}सर्वे मानवाः स्वतन्त्राः समुत्पन्नाः\n\\end{sanskrit}\\section{CJK}Here's some Chinese:\n人有悲欢离合，月有阴晴圆缺。此事古难全，但愿人长久，千里共婵娟。\n";

        $unicode = new Ccsd_Tex_Unicode(['zh', 'sa']);
        $this -> assertEquals($resText, $unicode -> parseXelatexLingualsCommand($text));
        $headers = $unicode->headers();
        $this -> assertArrayHasKey('zh+ja+ko' , $headers); // Plusieurs header zh (donc zh-*)
        $this -> assertArrayHasKey('sa+hi', $headers);
        $this -> assertEquals('\setotherlanguages{sanskrit}', $headers['setOtherlanguagesValue' ]);
    }
}