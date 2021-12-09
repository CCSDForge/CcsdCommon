<?php

trait Ccsd_Form_Trait_Populate {
     
     protected $_populate;
     private   $_class;
     private   $_method;
     private   $_args;
     protected $_data = array ();
     
     public function setPopulate ($populate)
     {
         if (isset($populate['class']) && isset($populate['method'])) {
             if (!class_exists($populate['class'])) {
                 require_once 'Zend/Form/Exception.php';
                 throw new Zend_Form_Exception(sprintf('Class not found : ', $populate['class']));
             }
             
             $this->_class      = $populate['class'];
             
             if (!isset($populate['method'])) {
                 require_once 'Zend/Form/Exception.php';
                 throw new Zend_Form_Exception(sprintf('Need a method'));
             }
             $this->_method     = $populate['method'];
              
             if (isset ($populate['args'])) {
                 $this->_args       = $populate['args'];
             }
         } else if (!is_array ($populate)) {
             require_once 'Zend/Form/Exception.php';
             throw new Zend_Form_Exception(sprintf("Can't populate with no array"));
         } else {
             $this->setData($populate);
         }

         $this->_populate   = $populate;
         
         return $this;
     }
     
     public function isPopulate ()
     {
         return isset ($this->_populate);
     }
      
     public function setData ($data)
     {
         if (!is_array ($data)) {
             $data = array ($data);
         }
         
         $this->_data = $data;
         
         /* @var Hal_Translate $translator */
         $translator = Zend_Form::getDefaultTranslator();
         if (isset ($translator)) {
             $this->_data = array_map(function ($v) use($translator) {
                 if (is_array($v)) {
                     return $v;
                 }
                 switch ($v) {
                     case $translator->isTranslated($v) :
                         return $translator->translate($v);
                     case $translator->isTranslated('lang_' . $v) :
                         return $translator->translate('lang_' . $v);
                     default :
                         return $v;
                 }

             }, $this->_data);
         }

         return $this;
     }
     
     public function getData ()
     {
         return $this->_data;
     }
     
     public function isDefined ()
     {
         return isset ($this->_class) && isset ($this->_method);
     }
     
     public function build ()
     {
         if ($this->isDefined()) {
             try {
                 $reflectionMethod = new ReflectionMethod($this->_class, $this->_method);

                 if (isset ($this->_args)) {
                     $pass = array ();
                     foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
                         if (!array_key_exists ($reflectionParameter->name, $this->_args)) {
                             //TODO si la méthode à des parametres optionnels
                             /*
                             require_once 'Zend/Form/Exception.php';
                             throw new Zend_Form_Exception(sprintf('Paramètre requis', $reflectionParameter->name));*/
                         } elseif ($this->_args[$reflectionParameter->name] != '') {
                             $pass[] = $this->_args[$reflectionParameter->name];
                         }
                     }
                 }

                if (empty ($pass)) {
                     $this->setData ($reflectionMethod->invoke(null));
                 } else {
                     $this->setData ($reflectionMethod->invokeArgs($reflectionMethod, $pass));
                 }
             } catch (Exception $e) {
                 require_once 'Zend/Form/Exception.php';
                 throw new Zend_Form_Exception(sprintf('La méthode ne peut pas être appelée', $this->_method));
             }   
         }

         if (!isset ($this->_data)) {
             require_once 'Zend/Form/Exception.php';
             throw new Zend_Form_Exception(sprintf('Aucune donnée n\'est définie', $this->_data));
         }
     }
 }