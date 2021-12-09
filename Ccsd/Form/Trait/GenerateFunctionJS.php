<?php

trait Ccsd_Form_Trait_GenerateFunctionJS {
    
    protected $_prefix;

    private function generate ($prefix, $s)
    {
        $prefixPath = $this->getElement()->pathDir . "/" .  $this->getElement()->relPublicDirPath;

        $sJSFile = realpath( $prefixPath . '/js/form/decorator/' . $prefix . "$s.js" );

        if (!file_exists($sJSFile)) {
            return false;
        }
        
        $sJS = file_get_contents($sJSFile);
        
        $sJS = preg_replace_callback ("/%%([^%]+)%%/", function($matches) {
            if (isset ($this->{strtolower($matches[1])})) {
                return $this->{strtolower($matches[1])};
            }
            return "%%" . $matches[1] . "%%";
        }, $sJS);

        return  $sJS;
    }
    
    public function __call ($name , $arguments)
    {
    	$element = $this->getElement();

    	if (!$element instanceof Ccsd_Form_Interface_Javascript) {
        	throw new Exception("This element is not a valid Ccsd_Form_Interface_Javascript object");
        }

        if ($element instanceof Ccsd_Form_Element_MultiText) {
            $this->length = $element->getLength();
        }

        if ('buildJS' == $name) {
            foreach ($arguments[1] as $i => $t) {
                foreach ($t as $action) {
                    $sJS = $this->generate($arguments[0], $action);
         
                    if ($sJS) {
                        $a = $element->{"add$i"} ($sJS);
                        if (!in_array ($i, array('documentReady', 'var'))) {
                        	$this->$action = $a;
                        }
                    }
                }
                foreach ($t as $action) {
                    $sJS = $element->getJavascript($i, $this->$action);
                    
                    if ($sJS && !is_array($sJS)) {
                        $sJS = preg_replace_callback ("/%%([^%]+)%%/", function($matches) {
                            if (isset ($this->{strtolower($matches[1])}) && is_string($this->{strtolower($matches[1])})) {
                                return $this->{strtolower($matches[1])};
                            }
                            return "%%" . $matches[1] . "%%";
                        }, $sJS);
                        
                        $element->setJavascript($sJS, $i, $this->$action);
                    }
                }
            }
            return $element;
        }
        
        return false;
    }
    
    public function __get ($name)   
    {
        if (substr ($name, 0,1) == '_' && !isset ($this->$name)) {
            return "%%" . strtoupper(substr($name, 1)) . "%%";
        }
    }

 }