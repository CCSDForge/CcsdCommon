<?php

class Ccsd_Translator
{
    private $translator = null;

    public function __construct()
    {
        $this -> translator = new Zend_Translate(Zend_Translate::AN_ARRAY, __DIR__ . '/languages', null, array(
            'scan' => Zend_Translate::LOCALE_DIRECTORY,
            'disableNotices' => true
        ));
    }

    function getTranslator() {
        return $this -> translator;
    }
    /**
     * Ajoute les chemins des traductions de library au @translator
     * @param Zend_Translate 
     */
    static function addTranslations(&$translator) {
        
        // Ajout des traductions de Meta
        $translator->addTranslation(__DIR__ . '/languages');
        
    }

}
