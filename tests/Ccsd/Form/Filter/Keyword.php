<?php
/**
 * Created by PhpStorm.
 * User: marmol
 * Date: 23/03/17
 * Time: 08:23
 */

class Ccsd_Form_Filter_Keyword_Test extends PHPUnit_Framework_TestCase
{

    function arrays_are_similar($a, $b) {
        // Comparaison partielle du tableau
        // Le premier niveau est normalement avec des clefs significative
        // Le deuxieme niveau, avec clef numerique, les meme valeurs doivent se retrouver

        // ATTENTION: si un tableau a un mixe de clef num et alpha... CA MARCHE PAS
        foreach ($a as $l => $kwlist) {
            if (is_int($l)) {
                //Tableau a un seul niveau et clef numerique
                //on compare seulement les valeur
                return count(array_diff($a,$b)) == 0;
            }
            // Sinon les clefs sont alpha, les valeurs sont comparees
            // Si la valeur est un tableau, on ne regarde pas les clefs dans l'egalite!
            if (array_key_exists($l, $b)) {
                if (is_array($kwlist)) {
                    if (count(array_diff($kwlist, $b[$l])) != 0) {
                        return false;
                    }
                } else {
                    if ($kwlist != $b[$l]) {
                        return false;
                    }
                }
            } else {
                return false;
            }
        }
        return true;
    }

    /** @dataProvider provideKeywords
     * @param $values string
     * @param $res    string
     */
    public function testFilter($values, $res)
    {
        $kwfilter = new Ccsd_Form_Filter_Keyword();
        $filtered = $kwfilter -> filter($values);
        if (is_array($res) && is_array($filtered)) {
            $this->assertTrue($this -> arrays_are_similar($res, $filtered));
        } else {
            $this->assertEquals($res, $filtered);
        }
    }

    public function provideKeywords() {
        return [
            't1' => [ false,
                      false ],
            't2' => [ '',
                      ''],
            't3' => [ "a,b,c,d",
                      ['a','b','c','d']
            ],
            't4' => [ ['fr' => [ 'a','b','c;d;e' ]],
                      ['fr' => ['a','b','c','d','e']]
            ],
            't5' => [ ['fr' => [ 'a','b','c;b;e' ]],
                      ['fr' => ['a','b','c','e'  ]]
            ],
            't6' => [ ['fr' => [ 'a','b','c;b;e' ], 'en' => 'q,w,e,r,t'],
                      ['fr' => ['a','b','c','e'  ], 'en' => ['q','w','e','r','t' ]]
            ] ,
            't7' => [ ['fr' => [ 'a','b','c;b;e' ], 'en' => ['q,w,e,r,t', 'z','x','w,r']],
                      ['fr' => ['a','b','c','e'  ], 'en' => ['q','w','e','r','t', 'z','x']]
            ] ,
            't8' => [ ['fr' => 'q,w,e,r,t', 'en' => [ 'a','b','c;b;e' ]],
                      ['fr' => ['q','w','e','r','t' ], 'en' => ['a','b','c','e'  ]]
            ] ,
            
        ];
    }
}