<?php
/**
 * @category   Ccsd
 * @package    Ccsd_Form_Validate
 *
 * Configuration INI :
 *
 * elements.abstract.options.validators.0.validator = "ProtectXssAbstract"
 * elements.abstract.options.validators.0.options.messages = "Résumé: ['%value%'] n'a pas passé la validation XSS ! "
 *
*/


class Ccsd_Form_Validate_ProtectXssAbstract extends Zend_Validate_Abstract
{
    const NONPROTECTXSSABSTRACT    = 'abstractNotProtectXss';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::NONPROTECTXSSABSTRACT   => "Form\'s element Abstract (textarea): ['%value%'] contains fragments that resemble a dangerous code XSS",
    );

    protected static $_badTagsHtmlAbstract = array(
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
        '<del',
        '<details',
        '<dfn',
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
        '<link',
        '<main',
        '<map',
        '<meta',
        '<meter',
        '<nav',
        '<object',
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
        '<style',
        '<summary',
        '<svg',
        '<table',
        '<tbody',
        '<td',
        '<th',
        '<thead',
        '<title',
        '<tr',
        '<track',
        '<var',
        '<video',
        '<wbr',
        '<!',
        '<?'
    );


    public function isValid($value)
    {
        foreach (self::$_badTagsHtmlAbstract as $badTag) {
            if (strripos($value, $badTag) !== false) {
                $this->_error(self::NONPROTECTXSSABSTRACT, $value);
                return false;
            }
        }

        return true;
    }
}