<?php

class Ccsd_View_Helper_Sidebar extends Zend_View_Helper_Abstract
{

    public function sidebar($type, $nav, $prefix = '/')
    {
        require_once __DIR__ . '/Sidebar/' . $type . '.phtml';
    }


}