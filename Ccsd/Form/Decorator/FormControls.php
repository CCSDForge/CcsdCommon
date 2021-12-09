<?php

class Ccsd_Form_Decorator_FormControls extends Zend_Form_Decorator_HtmlTag
{
    
    
    public function render($content)
    {
        $form    = $this->getElement();
        if (!$form instanceof Ccsd_Form) {
            return $content;
        }
        
        $view    = $form->getView();
        if (null === $view) {
            return $content;
        }
        
        $founded = false;

        foreach ($form->getElements() as $element) {
            if ($element instanceof Ccsd_Form_Interface_Javascript && ($element instanceof Ccsd_Form_Element_MultiTextArea || $element instanceof Ccsd_Form_Element_MultiTextAreaLang)) {
                
                if ($element->getTinymce()) {
                    $founded = true;
                    $name = $element->getName();
                    $js = <<<JAVASCRIPT
__initMCE ('#$name:first', undefined, {
    theme: "modern",
    plugins: "link image code fullscreen table",
    toolbar1: "bold italic underline"
});     
JAVASCRIPT;
                    $element->addDocumentReady($js);
                }
            }
        }
        
        if ($founded) {
            $view->jQuery()->addJavascriptFile(CCSDLIB ."/js/tinymce5/jquery.tinymce.min.js")
                           ->addJavascriptFile(CCSDLIB ."/js/tinymce5/tinymce.min.js")
                           ->addJavascriptFile(CCSDLIB ."/js/tinymce5_patch.js");
        }
        
        return $content;
    }
}
