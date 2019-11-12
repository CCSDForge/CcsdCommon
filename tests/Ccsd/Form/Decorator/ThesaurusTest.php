<?php
/**
 * Created by PhpStorm.
 * User: marmol
 * Date: 23/08/17
 * Time: 16:37
 */


class Ccsd_Form_Decorator_Thesaurus_Test extends PHPUnit_Framework_TestCase
{
    public function testFilter() {
        $elem_thes = new Ccsd_Form_Element_Thesaurus("MyThesaurus");
        $th = new Ccsd_Form_Decorator_Thesaurus("MyThesaurus");
        $th -> setElement($elem_thes);
        $th ->render('');
    }

}