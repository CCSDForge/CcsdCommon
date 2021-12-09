<?php

/**
 * Class Ccsd_Form_Filter_CleanXss_Test
 */
class Ccsd_Form_Filter_CleanXss_Test extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider  provideAbstract
     */
       public function testCleanXss($abstract, $attendu)
    {
        echo $abstract;
        $xssAbstractFilter = new Ccsd_Form_Filter_CleanXss();
        $resInputAbstract = $xssAbstractFilter -> filter($abstract);
        echo $resInputAbstract;
        $this->assertEquals($attendu, $resInputAbstract);
    }

    /**
     *
     * @return array
     */
    public function provideAbstract() {
        return [
            10 => ["Je n'ai pas de Html, que\n\ndu texte normal\r\nEt évidemment on va le nettoyer par &lt;script&gt;",
                "Je n'ai pas de Html, que\n\ndu texte normal\nEt évidemment on va le nettoyer par &lt;script&gt;"
                ],
            9 => ["<b>Le petit chat\r\nest mort</b><script>let toto=3</script>",
                "<b>Le petit chat\nest mort</b>"],
            7 => ['<span-a-toto  onclick="this.delete()">coucou</span-a-toto >',
                'coucou'],
            8 => ['<span onclick="this.delete()" />',
                '<span></span>'],
            1 => [
                '<!DOCTYPE html><html lang="fr"><body><h2 class="frame" style="font-size: 12px; background-color: rgb(245, 245, 245);">The JavaScript String() Method</h2><EmbeD>Corps emBed</EmbeD><p style="padding:0;margin:0;">The String() method can convert a number to a string.</p><Object>Ici est le corps de l\'object</Object><p id="demo"></p><script>let x = 123;document.getElementById("demo").innerHTML = String(x) + "<br>" + String(123) + "<br>" + String(100 + 23);</script><<!-- -->scr<Script>let x = 124;document.getElementById("demo2").innerHTML = String(x) + "<br>" + String(124) + "<br>" + String(100 + 24);</Script>ipt>alert("Contournement du filtre !")<<!-- -->/scr<Script>let variable = 4</Script>ipt></body></html>',
                '<h2>The JavaScript String() Method</h2><p>The String() method can convert a number to a string.</p><p></p>&lt;script&gt;alert("Contournement du filtre !")&lt;/script&gt;'
                // <h2>The JavaScript String() Method</h2><p>The String() method can convert a number to a string.</p><p></p>alert("Contournement du filtre !")'
            ],
            2 => ['La <a href="https://wikipedia.fr/France">France</a> a pour capitale <a href="wikipedia.fr/Paris" title="coucou">Paris</a>',
                'La France a pour capitale Paris'
            ],
            3 => ['<html lang="fr"><head><title>Mon titre</title></head><body>coucou</body>',
                'coucou',
                ],
            4 => ['<b style="alors" onclick="this.delete()">coucou</b>',
                '<b>coucou</b>',
                ],
            5 => ['<s<![CDATA[cript> contenu ]]>toutou</s<![CDATA[cript>]]>',
                ''],
            6 => ['<xhtml xmlns="xhtml:http://www.w3.org/1999/xhtml"><xhtml:head><xhtml:title>Mon titre</xhtml:title></xhtml:head><body><b>coucou</b></body></xhtml>',
                '<b>coucou</b>',
                ],
        ];
    }
}