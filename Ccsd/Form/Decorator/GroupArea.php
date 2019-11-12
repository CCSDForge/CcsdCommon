<?php

class Ccsd_Form_Decorator_GroupArea extends Ccsd_Form_Decorator_Group
{
    protected $_decorators = array (
        array ('decorator' => 'HtmlTag',     'options' => array ('tag' => 'div', 'class' => 'textarea-group', 'style' => 'margin-bottom : 10px;', 'openOnly' => true,'placement' => Zend_Form_Decorator_Abstract::PREPEND)),
        array ('decorator' => 'CViewHelper', 'options' => array ('class' => 'form-control input-sm', 'style' => 'border-bottom-right-radius: 0;')),
        array ('decorator' => 'HtmlTag',     'options' => array ('tag' => 'div', 'class' => 'pull-right', 'openOnly' => true)),
        array ('decorator' => 'HtmlTag',     'options' => array ('tag' => 'div', 'class' => 'input-group', 'style' => 'display: table-cell', 'openOnly' => true)),
        array ('decorator' => 'Multi',       'options' => array ('class' => 'btn btn-sm btn-primary', 'style' => 'border-top-left-radius:0; border-top-right-radius:0; border-top: 0; height: 30px; padding-top:0; padding-bottom: 0;')),
        array ('decorator' => 'HtmlTag',     'options' => array ('tag' => 'div', 'closeOnly' => true)),
        array ('decorator' => 'HtmlTag',     'options' => array ('tag' => 'div', 'closeOnly' => true)),
        array ('decorator' => 'HtmlTag',     'options' => array ('tag' => 'div', 'closeOnly' => true,'placement' => Zend_Form_Decorator_Abstract::APPEND))
    );
    
    public function loadDefaultDecorators ()
    {
    	$this->_decorators = array (
	        array ('decorator' => 'HtmlTag',     'options' => array ('tag' => 'div', 'class' => 'textarea-group', 'style' => 'margin-bottom : 10px;', 'openOnly' => true,'placement' => Zend_Form_Decorator_Abstract::PREPEND)),
	        array ('decorator' => 'CViewHelper', 'options' => array ('class' => 'form-control input-sm', 'style' => 'border-bottom-right-radius: 0;')),
	        array ('decorator' => 'HtmlTag',     'options' => array ('tag' => 'div', 'class' => 'pull-right', 'openOnly' => true)),
	        array ('decorator' => 'HtmlTag',     'options' => array ('tag' => 'div', 'class' => 'input-group', 'style' => 'display: table-cell', 'openOnly' => true)),
	        array ('decorator' => 'Multi',       'options' => array ('class' => 'btn btn-sm btn-primary', 'style' => 'border-top-left-radius:0; border-top-right-radius:0; border-top: 0; height: 30px; padding-top:0; padding-bottom: 0;')),
	        array ('decorator' => 'HtmlTag',     'options' => array ('tag' => 'div', 'closeOnly' => true)),
	        array ('decorator' => 'HtmlTag',     'options' => array ('tag' => 'div', 'closeOnly' => true)),
	        array ('decorator' => 'HtmlTag',     'options' => array ('tag' => 'div', 'closeOnly' => true,'placement' => Zend_Form_Decorator_Abstract::APPEND))
	    );
    	return $this;
    }
    
    public function setWrappers () 
    {
        parent::setWrappers();

        $this->_decorators[0]['options']['class'] = 'textarea-group advanced';
        $this->_decorators[1]['options']['style']= 'font-size: inherit; display: inline-block; text-align: justify; white-space: normal; padding: 1px  0px 1px 10px;';
    }

}