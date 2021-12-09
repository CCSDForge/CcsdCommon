<?php

/**
 * @category   Ccsd
 * @package    Ccsd_Form_Filter
 *
 * Configuration INI :
 *
 * elements.abstract.options.filters.0.filter = "CleanXssAbstract"
 *
 */

class Ccsd_Form_Filter_CleanXss implements Zend_Filter_Interface
{

    /** @var \Ccsd\HTMLPurifier */
    private $purifier;
    /**
     * Returns the result of filtering $value
     *
     * @param string|string[] $value
     * @return string|string[]
     * @throws Zend_Filter_Exception If filtering $value is impossible
     */

    /**
     *
     * @var array Tags HTML à garder par le filtre CleanXssTitle, CleanXssAbstract,...
     */
    protected static $HTML_ALLOWED_ELEMENTS=[
        'dd' ,'dt' ,'dl'   ,'em'  ,'ul' ,'ol' ,'li' ,
        'h1' ,'h2' ,'h3'   ,'h4'  ,'h5' ,'h6' ,
        'b'  ,'br' ,'cite' ,'div' ,
        'i'  ,'p'  ,'span' ,'strong' ,'sub' ,'sup' ,'u'
    ];
    /**
     * Pour ces tags, on supprime aussi le contenu!
     * @var string[]
     */
    public static $CORE_HIDDENELEMENTS = [
        'embed',
        'style',
        'applet',
        'canvas',
        'object',
        'script',
    ];

    public function __construct() {
        $this->purifier = new \Ccsd\HTMLPurifier(
            [
                'HTML.AllowedElements' => self::$HTML_ALLOWED_ELEMENTS,
                'Core.HiddenElements'  => self::$CORE_HIDDENELEMENTS,
            ]
        );
    }

    /**
     * @param string|string[] $value
     * @return string|string[]
     */
    public function filter($value)
    {
        if (is_array($value)) {
            $ret=[];
            foreach ($value as $k => $v) {
                $ret[$k] = $this->purifier->purifyHtml($v);
            }
            return $ret;
        }
        if (is_string($value)) {
            return $this->purifier->purifyHtml($value);
        }
        return '';
    }
}

