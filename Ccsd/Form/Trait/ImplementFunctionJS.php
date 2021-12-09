<?php

trait Ccsd_Form_Trait_ImplementFunctionJS {
    
    protected $_javascript = array ('var' => array(), 'function' => array(), 'ready' => array ());

    public function addDocumentReady ($code)
    {
    	array_push ($this->_javascript['ready'], $code);
    }
    
    public function addFunction($function)
    {
    	$name = array_search ($function, $this->_javascript['function']);
    
    	if ($name) {
    		return $name;
    	}
    
    	$name = uniqid('fct');
    
    	while(array_key_exists ($name, $this->_javascript['function'])) {
    		$name = uniqid('fct');
    	}
    
    	$this->_javascript['function'][$name] = $function;
    
    	return $name;
    }
    
    public function getJavascript ($type = null, $name = null)
    {
    	if (null !== $type) {
    		if (null !== $name) {
    			if (isset ($this->_javascript[$type][$name])) {
    				return $this->_javascript[$type][$name];
    			} else return false;
    		}
    	}
    
    	return $this->_javascript;
    }
    
    public function setJavascript ($js, $type = null, $name = null)
    {
    	if (null !== $type) {
    		if (null !== $name) {
    			if (isset ($this->_javascript[$type][$name])) {
    				$this->_javascript[$type][$name] = $js;
    			}
    		}
    	} else {
    		$this->_javascript = $js;
    	}
    
    	return $this;
    }
    
    public function clearJavascript ()
    {
    	$this->_javascript = array ('var' => array(), 'function' => array(), 'ready' => array ());
    }

 }