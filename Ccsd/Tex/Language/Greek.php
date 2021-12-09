<?php

class Ccsd_Tex_Language_Greek extends Ccsd_Tex_Language
{

    /** @var $name */
    protected $locale = 'el';
    /** @var string[]  */
    protected $header = [];
    protected $texlangName = 'greek';
    /** @var string  */
    protected $unicodeScript = 'Greek';
    protected $replaceRegexp = '';
    /** @var string  */
    protected $replaceBy = '';
}