<?php

/**
 * @category   Ccsd
 * @package    Ccsd_Form_Validate
 *
 * Configuration INI :
 *
 * elements.title.options.validators.0.validator = "ProtectXssTitle"
 * elements.title.options.validators.0.options.messages = "Title: ['%value%'] n'a pas passé la validation XSS ! "
 *
 */

class Ccsd_Form_Validate_ProtectionXssTitle extends Zend_Validate_Abstract
{
    const NONPROTECTXSSTITLE    = 'titleNonProtectionXss';

    /**
     * Validation failure message template definitions
     * @var array
     */

    protected $_messageTemplates = array(
        self::NONPROTECTXSSTITLE  => "Form\'s element Title (textarea): ['%value%'] contains fragments that resemble a dangerous code XSS",
    );

    protected static $_badTagsHtmlTitle = array(
        '<!doctype',
        '<a',
        '<applet',
        '<area',
        '<article',
        '<audio',
        '<body',
        '<button',
        '<canvas',
        '<caption',
        '<center',
        '<code',
        '<col',
        '<colgroup',
        '<data',
        '<datalist',
        '<dd',
        '<del',
        '<details',
        '<dfn',
        '<dl',
        '<dt',
        '<embed',
        '<fieldset',
        '<figcaption',
        '<figure',
        '<footer',
        '<form',
        '<frame',
        '<head',
        '<hr',
        '<html',
        '<iframe',
        '<img',
        '<input',
        '<ins',
        '<kbd',
        '<label',
        '<legend',
        '<li',
        '<link',
        '<main',
        '<map',
        '<meta',
        '<meter',
        '<nav',
        '<object',
        '<ol',
        '<optgroup',
        '<option',
        '<output',
        '<param',
        '<picture',
        '<pre',
        '<progress',
        '<q',
        '<rp',
        '<rt',
        '<ruby',
        '<s',
        '<samp',
        '<script',
        '<section',
        '<select',
        '<source',
        '<span',
        '<style',
        '<summary',
        '<svg',
        '<table',
        '<tbody',
        '<td',
        '<th',
        '<tr',
        '<track',
        '<u',
        '<ul',
        '<var',
        '<video',
        '<wbr',
        '<!',
        '<?'
    );

    /**
     * Sets validator options
     *
     * @return void
     */

    public function isValid($value)
    {
        foreach (self::$_badTagsHtmlTitle as $badTag) {
            if (strripos($value, $badTag) !== false) {
                $this->_error(self::NONPROTECTXSSTITLE, $value);
                return false;
            }
        }
        return true;
    }
}
