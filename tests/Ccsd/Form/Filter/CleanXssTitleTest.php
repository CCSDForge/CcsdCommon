<?php

class Ccsd_Form_Filter_CleanXssTitle_Test extends PHPUnit_Framework_TestCase
{
    /**
     * @throws Zend_Filter_Exception
     */
//    private $_inputTitle = '';
    protected static $_inputTitle = '<HTML><HEAD><TITLE>Your Title Here</TITLE></HEAD><BODY BGCOLOR="FFFFFF"><CENTER><IMG SRC="clouds.jpg" ALIGN="BOTTOM"> </CENTER><HR>
<a href="http://somegreatsite.com">Link Name</a>
is a link to another nifty site
<H1>This is a Header</H1><H2>This is a Medium Header</H2>
Send me mail at <a href="mailto:support@yourcompany.com">
support@yourcompany.com</a>.
<P> This is a new paragraph!<P> <B>This is a new paragraph!</B><BR> <B><I>This is a new sentence without a paragraph break, in bold italics.</I></B><HR></BODY></HTML>';

    public function testCleanXssTitle()
    {
        $inputTitle = '<!DOCTYPE html><html><body><h2 class="frame" style="font-family: Monaco, Consolas, &quot;Courier New&quot;, monospace; font-size: 12px; background-color: rgb(245, 245, 245);">The JavaScript String() Method</h2><EmbeD>Corps emBed</EmbeD><p style="padding:0;margin:0;">The String() method can convert a number to a string.</p><Object>Ici est le corps de l\'oBject</Object><p id="demo"></p><Script>var x = 123;document.getElementById("demo").innerHTML = String(x) + "<br>" + String(123) + "<br>" + String(100 + 23);</Script><Script>var x = 124;document.getElementById("demo2").innerHTML = String(x) + "<br>" + String(124) + "<br>" + String(100 + 24);</Script></body></html>';
        echo "\n0-1. Titre avant le filtrage:  resInputAbstract=\n[", self::$_inputTitle, "]";
        $xssTitleFilter = new Ccsd_Form_Filter_CleanXssTitle();
        $resInputTitle = $xssTitleFilter -> filter(self::$_inputTitle);
        echo "\n0-2. Titre après le filtrage:   resInputTitle=\n[", $resInputTitle, "]";
    }
}
