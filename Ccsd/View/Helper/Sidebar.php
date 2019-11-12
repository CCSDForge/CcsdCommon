<?php

/**
 * Class Ccsd_View_Helper_Sidebar
 *
 * Helper de view pour afficher un menu dependant du style de menu souhaite: tabs, list accodeon,...
 * Attention: seul vraiment teste: tabs! :-(
 */
class Ccsd_View_Helper_Sidebar extends Zend_View_Helper_Abstract
{
    /**
     * @param Hal_View $view
     * @param string $type
     * @param Hal_Website_Navigation $nav  ??
     * @param string $prefix
     */
    public function sidebar($view, $type, $nav, $prefix = '/') {
        require_once __DIR__ . '/Sidebar/' . $type . '.phtml';
    }
}