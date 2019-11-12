<?php

class Ccsd_Form_Decorator_Picture extends Zend_Form_Decorator_Abstract
{
    protected $_uid;

    public function setUID ($uid)
    {
        $this->_uid = $uid;
        return $this;
    }
    
    public function getUID ()
    {
        $uid = $this->getOption('uid');
        
        if (isset($uid))
            $this->_uid = $uid;
            
        if (!isset ($this->_uid)) { 
            return Ccsd_Auth::getUid();
        } else return $this->_uid;
    }
    
    /**
     * Render a form image
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $element = $this->getElement();
        
        $view    = $element->getView();
        if (null === $view) {
            return $content;
        }
        
        if (!Ccsd_User_Models_User::hasPhoto($this->getUID())) {
            return $content;
        }
        
        $placement     = $this->getPlacement();
        
        $image  = "";
        $image .= "<div class='col-md-3' style='padding-left: 0px;'>";
        $image .= '<img src="' . CV_URL . '/photo/' . $this->getUID() . '/large?v=' . time() . '"class="user-photo img-thumbnail img-responsive"/>';
        $image .= "<br>";
        $image .= "<div style='text-align:center;margin-top:0.5em;'>";
        $image .= "<a id='delete-photo' role='button' href='#' class='btn btn-default btn-xs' attr-uid='" . $this->getUID() . "'>";
        $image .= "<span class='glyphicon glyphicon-trash'></span>&nbsp;" . Ccsd_Form::getDefaultTranslator()->translate('Supprimer');
        $image .= "</a>";
        $image .= "</div>";
        $image .= "</div>";

        switch ($placement) {
            case self::PREPEND:
                return $image . $content;
            case self::APPEND:
            default:
                return $content . $image;
        }
    }
}