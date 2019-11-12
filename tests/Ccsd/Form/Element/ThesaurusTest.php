<?php
/**
 * Created by PhpStorm.
 * User: marmol
 * Date: 23/08/17
 * Time: 16:15
 */


class Ccsd_Form_Element_Thesaurus_Test extends PHPUnit_Framework_TestCase
{
    public function testFilter() {
        $th = new Ccsd_Form_Element_Thesaurus("Ccsd_Form_Element_Thesaurus");

        $th->addPrefixPath('ZendX_JQuery_Form_Decorator', 'ZendX/JQuery/Form/Decorator', Zend_Form::DECORATOR);
        $th->addPrefixPath('Ccsd_Form_Decorator', 'Ccsd/Form/Decorator', Zend_Form::DECORATOR);
        $th->addPrefixPath('Ccsd_Form_Decorator_Bootstrap', 'Ccsd/Form/Decorator/Bootstrap/', Zend_Form::DECORATOR);
        $th->addPrefixPath('Ccsd_Form', 'Ccsd/Form');

        $th -> setPrefix_inter("interPrefix");
        $options = [
            'label' => "jel",
            'description' => "Renseignez des mots-clés issus du <a target='_blank' href='https://www.aeaweb.org/econlit/jelCodes.php'>JEL</a>",
            'required' => "0",
            'data' => [ "one" => [], "two"=> [], "three"=> [], "four"=> [] ],
            'typeahead_label' => "Filtrer par nom",
            'typeahead_description' => "Saisissez un mot pour accélérer votre recherche",
            'list_title' => "",
            'list_values' => "",
            'prefix_translation' => "jel_",
            'option_collapse_msg'   => "Réduire la liste des mots-clés JEL",
            'option_expand_msg'      => "Ajouter un mot-clé JEL",
            'typeahead_height' => 400,
        ];
        $th -> setOptions($options);
        $view = new Ccsd_View();
        $th -> setView($view);
        $html = $th -> render();
        $this -> assertRegExp('/<label.*jel/', $html);
        $this -> assertRegExp("|<input id='two' value='two'[^>]*>.*<label  for='two'|", $html);
    }

}