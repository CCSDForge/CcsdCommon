<?php
/**
 * This file is part of CCSD Library project
 */

class Ccsd_Tools {
    /**
     *  Fonctions statiques utilitaires
     *  Cette classe n'a pas vocation a etre instanciee
     */



    private static $_TRANS_CAR_LATEX_GREEK = array(
        //lettres grecques
        "Α" => '$A$',
        "α" => '$\\alpha$',
        "Β" => '$B$',
        "β" => '$\\beta$',
        "Γ" => '$\\Gamma$',
        "γ" => '$\\gamma$',
        "Δ" => '$\\Delta$',
        "∆" => '$\\Delta$', // different du precedent...
        "δ" => '$\\delta$',
        "Ε" => '$E$',  // Juste un E!!!
        "ε" => '$\\epsilon$',
        "Ζ" => '$Z$',
        "ζ" => '$\\zeta$',
        "Η" => '$H$',
        "η" => '$\\eta$',
        "ή" => '$\\eta$', // TODO pas encore l'accent sans utiliser un mode Text special...
        "Θ" => '$\\Theta$',
        "θ" => '$\\theta$',
        "Ι" => 'I',
        "ι" => '$\\iota$',
        "ί" => '$\\iota$', // TODO pas encore l'accent sans utiliser un mode Text special...
        "Κ" => '$K$',
        "κ" => '$\\kappa$',
        "Λ" => '$\\Lambda$',
        "λ" => '$\\lambda$',
        "Μ" => '$M$',   // \\Mu ne fonctionne pas dans notre version de latex (2014)
        "μ" => '$\\mu$',
        "µ" => '$\\mu$', # different du precedent... 
        "Ν" => '$\\Nu$',
        "ν" => '$\\nu$',
        "Ξ" => '$\\Xi$',
        "ξ" => '$\\xi$',
        "Ο" => '$O$',  // \\Omicron ne fonctionne pas dans notre version de latex (2014)
        "ο" => '$o$',  // \\omicron ne fonctionne pas dans notre version de latex (2014)
        "ό" => "$\\'{o}$",  // TODO pas encore l'accent sans utiliser un mode Text special...ή
        "Π" => '$\\Pi$',
        "π" => '$\\pi$',
        "Ρ" => '$\\Rho$',
        "ρ" => '$\\rho$',
        "Σ" => '$\\Sigma$',
        "σ" => '$\\sigma$',
        "ς" => '$\\varsigma$',
        "Τ" => '$T$',
        "τ" => '$\\tau$',
        "Υ" => '$\\Upsilon$',
        "υ" => '$\\upsilon$',
        "ύ" => '$\\upsilon$',
        "Φ" => '$\\Phi$',
        "ϕ" => '$\\Phi$',
        "φ" => '$\\phi$',
        "Χ" => '$X$',
        "χ" => '$\\chi$',
        "Ψ" => '$\\Psi$',
        "ψ" => '$\\psi$',
        "Ω" => '$\\Omega$',
        "Ω" => '$\\Omega$', // different du precedent...
        "ω" => '$\\omega$',
        "ώ" => '$\\omega$', // TODO pas encore l'accent sans utiliser un mode Text special...
    );
    /**
     * TODO:
       Il y a un probleme avec _ < et >
       Suivant que l'on soit en mode latex math ou pas, les carateres _ < > ne doivent pas etre traduit de la meme facon.
       La difficulte est que l'on peut recevoir des chaines avec du latex ou en html
     */
    private static $_TRANS_CAR_LATEX = array(
        //caracteres spéciaux latex à échaper
        "&" => "\\&",
        "#" => "\\#",
        // modif lfa car certains titres ont des caractères latex "%" => "\\%",
        // modif lfa car certains titres ont des caractères latex "$" => "\\$",
        // modif lfa car certains titres ont des caractères latex "^" => "\\^{}",
        // modif lfa car certains titres ont des caractères latex "~" => "\\~{}",
        // modif lfa car certains titres ont des caractères latex"{" => "\\{",
        // modif lfa car certains titres ont des caractères latex"}" => "\\}",
        // BUG si definie ici, alors HtmlToTex plante car il transforme les < > des tags avant de faire les tags
        // "<" => "\\textless{}",
        //">" => "\\textgreater{}",
        //exposants
        "°" => "{\\textdegree}",
        "²" => '${}^2$',
        "³" => '${}^3$',
        
        // Autre alphabet
        'ℵ' => '$\\aleph',
        //symboles mathématiques
        "ℜ" => "{\\Re}",
        "Ð" => "{\\DH}",
        "ð" => "{\\dh}",
        "Đ" => "{\\DJ}",
        "đ" => "{\\dj}",
        "Ɖ" => '$\\M{D}$',
        "ɖ" => '$\\M{d}$',
        "Þ" => "{\\TH}",
        "þ" => "{\\th}",
        "∀" => '$\\forall$',
        "∃" => '$\\exists$',
        "∅" => '$\\emptyset$',
        "∇" => '$\\nabla$',
        "∈" => '$\\in$',
        "∉" => '$\\notin$',
        "∋" => '$\\ni$',
        "−" => "--",
        "ℋ"=> '$\\mathcal{H}$',
        "∞" => '$\\infty$',
        //"∧" => '$\\wedge$',
        //"∨" => '$\\vee$',
        "∧" => '$\\land$',
        "∨" => '$\\lor$',
        "⋒"  => '$\\doublecap$',
        "⋓" => '$\\doublecup$',
        "∼" => '$\\sim$',
        "≅" => '$\\cong$',
        "≈" => '$\\approx$',
        "≠" => '$\\ne$',
        "≡" => '$\\not\equiv$',
        "≢" => '$\\equiv$',
        "≤" => '$\\le$',
        "≪" => '$\\ll$',
        "≥" => '$\\ge$',
        "⊂" => '$\\subset$',
        "⊃" => '$\\sup$',
        "⊄" => '$\\notin$',
        "⊆" => '$\\subseteq$',
        "⊇" => '$\\supseteq$',
        "⊥" => '$\\perp$',
        "⋅" => '$\\cdot$',
        "·" => '$\\times$',
        "←" => '$\\leftarrow$',
        "↑" => '$\\uparrow$',
        "→" => '$\\rightarrow$',
        "↓" => '$\\downarrow$',
        "↔" => '$\\leftrightarrow$',
        "↵" => '$\\hookleftarrow$',
        "⇐" => '$\\Leftarrow$',
        "⇑" => '$\\Uparrow$',
        "⇒" => '$\\Rightarrow$',
        "⇓" => '$\\Downarrow$',
        '⊳' => '$\\triangleleft',
        "⇔" => '$\\Leftrightarrow$',
        "±" => '$\\pm$',
        "÷" => '$\\div$',
        "•" => '$\\bullet$',
        "∂" => '$\\partial$',
        "∏" => '$\\prod$',
        "∑" => '$\\sum$',
        "∗" => '$\\star$',
        "×" => 'x',
        "√" => '$\\sqrt$',
        "∩" => '$\\cap$',
        "∪" => '$\\cup$',
        "∫" => '$\\int$',
        "∬" => '$\\iint$',
        "∭" => '$\\iiint$',
        "∮" => '$\\oint$',
        "⊕" => '$\\oplus$',
        "⊗" => '$\\otimes$',
        "⋆" => '$\\star$',
        '⁺' => '${}^{+}$',
        // caracteres spéciaux
        '…' => "{\\ldots}",
        '"' => "''",
        "“" => "``",
        "”" => "''",
        "_" => "\\_",
        "—" => "-",
        "‐" => "-",
        "‒" => "-",
        "–" => "--",
        "―" => "--",
        "ʼ" => "'",
        "’" => "'",
        "′" => "'",
        "©" => "{\\copyright}",
        "§" => '\\S',
        "〈" => "\\textless{}",
        "〉" => "\\textgreater{}",
        //pour supprimer l accent special independant de la lettre
        "́" => "",
        "«" => "``",
        "»" => "''",
        "∘" => '$\\circ$',
        "é" => "{\\'e}",
        "É" => "{\\'E}",
        "è" => "{\\`e}",
        "È" => "{\\`E}",
        "ê" => "{\\^e}",
        "Ê" => "{\\^E}",
        "ë" => "{\\\"e}",
        "Ë" => "{\\\"E}",
        "î" => "{\\^i}",
        "Î" => "{\\^I}",
        "í" => "{\\'i}",
        "Í" => "{\\'I}",
        "ì" => "{\\`i}",
        "Ì" => "{\\`I}",
        "ï" => "{\\\"i}",
        "Ï" => "{\\\"I}",
        "à" => "{\\`a}",
        "ã" => "{\\~a}",
        "À" => "{\\`A}",
        "á" => "{\\'a}",
        "á" => "{\\'a}",
        "Á" => "{\\'A}",
        "â" => "{\\^a}",
        "Â" => "{\\^A}",
        "å" => "{\\aa}",
        "Å" => "{\\AA}",
        "ä" => "{\\\"a}",
        "Ä" => "{\\\"A}",
        "û" => "{\\^u}",
        "Û" => "{\\^U}",
        "ù" => "{\\`u}",
        "Ù" => "{\\`U}",
        "ú" => "{\\'u}",
        "Ú" => "{\\'U}",
        "ü" => "{\\\"u}",
        "Ü" => "{\\\"U}",
        "ô" => "{\\^o}",
        "Ô" => "{\\^O}",
        "ó" => "{\\'o}",
        "Ó" => "{\\'O}",
        "ò" => "{\\`o}",
        "Ò" => "{\\`O}",
        "ő" => "{\\\"o}",
        "ö" => "{\\\"o}",
        "Ö" => "{\\\"O}",
        "ø" => "{{\\o}}",
        "Ø" => "{{\\O}}",
        "ý" => "{\\'y}",
        "Ý" => "{\\'Y}",
        "ć" => "{\\'c}",
        "Ć" => "{\\'C}",
        "ǵ" => "{\\'g}",
        "Ǵ" => "{\\'G}",
        "ḱ" => "{\\'k}",
        "Ḱ" => "{\\'K}",
        "ĺ" => "{\\'l}",
        "Ĺ" => "{\\'L}",
        "ḿ" => "{\\'M}",
        "Ḿ" => "{\\'M}",
        "ń" => "{\\'n}",
        "ñ" => "{\\~n}",
        "Ń" => "{\\'N}",
        "ṕ" => "{\\'p}",
        "Ṕ" => "{\\'P}",
        "ŕ" => "{\\'r}",
        "Ŕ" => "{\\'R}",
        "ś" => "{\\'s}",
        "Ś" => "{\\'S}",
        "ẃ" => "{\\'w}",
        "Ẃ" => "{\\'W}",
        "ź" => "{\\'z}",
        "Ź" => "{\\'Z}",
        "ğ" => "{\\u g}",
        "Ğ" => "{\\u G}",
        // ajout des accents tchèque
        "č" => "{\\v c}",
        "Č" => "{\\v C}",
        "š" => "{\\v s}",
        "Š" => "{\\v S}",
        "ř" => "{\\v r}",
        "Ř" => "{\\v R}",
        "ž" => "{\\v z}",
        "Ž" => "{\\v Z}",
        "ě" => "{\\v e}",
        "Ě" => "{\\v E}",
        "ǎ" => "{\\v a}",
        "Ǎ" => "{\\v A}",
        "ǔ" => "{\\v u}",
        "Ǔ" => "{\\v U}",
        "ǒ" => "{\\v o}",
        "Ǒ" => "{\\v O}",
        #macron : lettre avec un trait dessus
        "ā" => "{\\=a}",
        "Ā" => "{\\=A}",
        "ē" => "{\\=e}",
        "Ē" => "{\\=E}",
        "ḡ" => "{\\=g}",
        "Ḡ" => "{\\=G}",
        "ī" => "{\\=i}",
        "Ī" => "{\\=I}",
        "ō" => "{\\=o}",
        "Ō" => "{\\=O}",
        "ū" => "{\\=u}",
        "Ū" => "{\\=U}",
        "ȳ" => "{\\=y}",
        "Ȳ" => "{\\=Y}",
        // Autres accents mal convertit
        "ă" => "{\\u a}",
        "Ă" => "{\\u a}",
        //accent windows
        "è" => "{\\`e}", // e suivit d'accent
        "é" => "{\\'e}", //é e suivit d'accent
        "à" => "{\\`a}",
        "ů" => "{\\r u}",
        "Ů" => "{\\r U}",
        "ẙ" => "{\\r y}",
        "ẘ" => "{\\r w}",
        "ç" => "{\\c c}",
        "ç" => "{\\c c}",
        "ç̧" => "{\\c c}",
        "Ş" => "{\\c S}",
        "ş" => "{\\c s}",
        "Ç" => "{\\c C}",
        "ö̈l" => "{\\\"o}l",
        // double s allemand
        "ß" => "{\\ss}",
        //ligatures
        "ℓ" => "{\\ell}",
        "æ" => "{\\ae}",
        "Æ" => "{\\AE}",
        "œ" => "{\\oe}",
        "Œ" => "{\\OE}",
        "ﬁ" => "fi",
        "ﬀ" => "ff",
        "ﬂ" => "fl",
        "ﬃ" => "ffi",
        "ﬄ" => "ffl",
        "ł" => "l",
        "ı" => "{\\i}",
    );
    static private $_PatternCarLatex = null;
    static private $_ReplaceCarLatex = null;
    static private $_PatternCarLatexGreek  = null;
    static private $_ReplaceCarLatexGreek  = null;
    static private $_PatternCarLatexGlobal = null;
    static private $_ReplaceCarLatexGlobal = null;
    /**
     * Formatage d'un utilisateur
     *
     * @param string $lastname
     * @param string $firstname
     * @return string
     */
    public static function formatUser($lastname = "", $firstname = "") {
        return self::formatAuthor($lastname, $firstname);
    }

    /**
     * Formatage d'un auteur
     *
     * @param string $lastname
     * @param string $firstname
     * @param string $civ
     * @return string
     */
    public static function formatAuthor($lastname = "", $firstname = "", $civ = "") {
        return trim((($civ && is_string($civ)) ? $civ . " " : "")
            . (($lastname && is_string($lastname))   ? self::upperWord($lastname) . " " : "")
            . (($firstname && is_string($firstname)) ? self::upperWord($firstname) : ""));
    }

    /**
     * Formatage d'une chaine texte : maj 1ère lettre
     *
     * @param string
     * @return string
     */
    public static function upperWord($string = "") {
        if (strlen($string) == 0) {
            return '';
        }
        $string = strtolower(self::space_clean($string));
        $res = strtoupper(substr($string, 0, 1));
        for ($i = 1; $i < strlen($string); $i ++) {
            $theIchar = substr($string, ($i - 1), 1);
            if ($theIchar == "-" || $theIchar == "'" || $theIchar == " " || $theIchar == ".") {
                $res .= strtoupper(substr($string, $i, 1));
            } else {
                $res .= substr($string, $i, 1);
            }
        }
        // Particules patronymiques
        /* française : de ou le d’ sont toujours en minuscules */
        //$res = str_replace('De ', 'de ', $res);
        $res = str_replace(" D'", " d'", $res);
        /* allemandes : an, auf, von (der), zu */
        $res = str_replace(' An ', ' an ', $res);
        $res = str_replace(' Auf ', ' auf ', $res);
        $res = str_replace('Von Der ', 'von der ', $res);
        $res = str_replace(' Von ', ' von ', $res);
        $res = str_replace(' Zu ', ' zu ', $res);
        $res = str_replace("L'", "l'", $res);
        /* anglaise : of */
        $res = str_replace(' Of ', ' of ', $res);
        /* espagnoles : de, del, de la, de los, de las, y */
        // $res = str_replace('De ', 'de ', $res);
        $res = str_replace(' Del ', ' del ', $res);
        $res = str_replace(' De La ', ' de la ', $res);
        $res = str_replace(' De Los ', ' de los ', $res);
        $res = str_replace(' De Las ', ' de las ', $res);
        $res = str_replace(' Y ', ' y ', $res);
        /* néerlandaises aux Pays-Bas : de, den, t’, ten, ter, van (der/den) */
        //$res = str_replace('De ', 'de ', $res);
        $res = str_replace(' Den ', ' den ', $res);
        $res = str_replace(" T'", " t'", $res);
        $res = str_replace(' Ten ', ' ten ', $res);
        $res = str_replace(' Ter ', ' ter ', $res);
        $res = str_replace(' Van Der ', ' van der ', $res);
        $res = str_replace(' Van Den ', ' van den ', $res);
        $res = str_replace('Van ', 'van ', $res);
        /* portugaises : a, da, das, de, dos */
        $res = str_replace(' A ', ' a ', $res);
        $res = str_replace(' Da ', ' da ', $res);
        $res = str_replace(' Das ', ' das ', $res);
        //$res = str_replace('De ', 'de ', $res);
        $res = str_replace(' Dos ', ' dos ', $res);
        /* scandinaves : af, av, von */
        $res = str_replace(' Af ', ' af ', $res);
        $res = str_replace(' Av ', ' av ', $res);
        //$res = str_replace('Von ', 'von ', $res);
        // Les particules non composees
        $res = str_replace('De ', 'de ', $res);
        return $res;
    }

    /**
     * @param string|string[] $mixed
     * @param bool $strip_br
     * @param bool $allUtf8
     * @return string|string[]|null
     */
    public static function space_clean($mixed, $strip_br = true, $allUtf8=false) {
        if (is_array($mixed)) {
            $new = array();
            foreach ($mixed as $val) {
                $new [] = self::space_clean($val);
            }
            $mixed = array_filter($new);
        } else {
            $mixed = ($strip_br) ? self::br2space($mixed) : $mixed;
            // On ne change pas les espaces insécables ou demi-espace...
            //   \s changerai l'intégralité des espaces.
            $mixed = preg_replace('/[\n\t\r ]+/', ' ', $mixed);
            // Suppr control char (CR already transformed)
            $mixed = preg_replace("/[\x1-\x1f]/", "", $mixed);
            // On devrait pouvoir supprimer tous les caractères espaces Utf8 (ex: pour repec)
            // Mais ca doit etre optionnel, on doit accepter les espaces insécables par exemple
            $mixed = preg_replace('/\s\s+/u', ' ', $mixed);
            if ($allUtf8) {
                $mixed = preg_replace('/[\x7F-\xA0\xAD\x{2009}]/u','',$mixed);
            }
            $mixed = trim($mixed);
        }
        return $mixed;
    }

    /**
     * @param string|string[] $string
     * @return string|string[]|null
     */
    public static function br2space($string) {
        return (preg_replace("|<br\b[^>]*>|i", " ", $string));
    }

    /**
     * Get http response for the base64 encoded image in param
     * Add the content-type to http header and echo the content
     *
     * @param string $txt // Base64 Encoded string
     */
    public static function afficheImg($txt) {
        header("Content-type: image/jpeg");
        echo base64_decode($txt);
    }

    /**
     * @param string $date
     * @param string $locale
     * @param string $format
     * @return string
     */
    public static function formatDate($date, $locale = null, $format = Zend_Date::DATE_LONG) {
        $oDate = new Zend_Date ();
        if (!$locale) {
            $locale = Zend_Registry::get('Zend_Locale')->toString();
        }
        return $oDate->set(strtotime($date))->toString($format, $locale);
    }

    /**
     * In an array of array, return the index of subarray that contain the $key => $needle item
     * If $all parameter is true, then return all indexes of subarrays that contain an item $key => $needle
     *
     * @param string $needle
     * @param array $array
     * @param string $key
     * @param bool $all
     * @return array|int|null|string
     */
    public static function in_next_array($needle, $array, $key, $all = false) {
        $out = array();
        if (is_array($array)) {
            foreach ($array as $k => $next) {
                if (is_array($next) && array_key_exists($key, $next) && $next [$key] === $needle) {
                    if ($all) {
                        $out [] = $k;
                    } else {
                        return $k;
                    }
                }
            }
        }
        return ($all) ? $out : null;
    }

    /**
     * This is a compatibility with previous writing function
     * Search for a regexp $needle in array and return the first index that matche
     * @param $string
     * @param $array
     * @return int|string
     */
    public static function preg_get_key($string, $array) {
        $res = self::preg_in_array_get_key($string, $array);
        return ($res == null ? -1 : $res);
    }

    /**
     * Search for a regexp $needle in array and return the first index that matche
     * You can specify a $begin prefix and $end suffix for the regexp
     * @param string $needle
     * @param string[] $array
     * @param string $begin
     * @param string $end
     * @return mixed
     */
    public static function preg_in_array_get_key($needle = "", $array = array(), $begin = "", $end = "") {
        $pattern = '/' . str_replace('/', '\/', $begin . $needle . $end) . '/';
        if (is_array($array)) {
            foreach ($array as $k => $val) {
                if (preg_match($pattern, $val)) {
                    return $k;
                }
            }
        }
        return null;
    }

    /**
     * This is a compatibility with previous writing function
     * Search for a regexp $needle in array and return true if found, false if not
     * You can specify a $begin prefix and $end suffix for the regexp
     * @param string $needle
     * @param array  $array
     * @param string $begin
     * @param string $end
     * @return bool
     */
    public static function preg_in_array($needle = "", $array = array(), $begin = "", $end = "") {
        return self::preg_in_array_get_key($needle, $array, $begin, $end) != null;
    }

    /**
     * Vérifier l'existence de $var
     *
     * @param mixed $var
     *        	variable à tester
     * @param mixed $set
     *        	valeur par défaut
     * @return mixed $var s'il existe $set sinon
     * NOTE: & is important on $var: else there are warnings on non existent var or array keys...
     */
    public static function ifsetor(&$var, $set = '') {
        return isset($var) ? $var : $set;
    }
    /**
     * Vérifier l'existence de la clef key dans  le tableau et retourne null ou la valeur  set
     * On veut l'equivalent de isset($arra[$key]) ? $array[$key] : $default
     * Mais sans les warnings de $key n'existe pas...
     * Attention: si $array[$key] existe mais est null: rendra $default
     * @param mixed[] $var tableau à tester
     * @param mixed $default valeur par défaut
     * @return mixed
     */
    public static function ifarraysetor($key, $array, $default = '') {
        return self::issetarray($key, $array) ? $array[$key] : $default;
    }

    /**
     * @param $key
     * @param array $array
     * @return bool
     */
    public static function issetarray($key, $array) {
        if (is_array($array) && array_key_exists($key, $array) && isset($array[$key])) {
            return true;
        }
        return false;
    }
    /**
     * @param string $string
     * @return string
     */
    public static function unnumericentities($string) {
        $string = preg_replace('/&#x([0-9a-f]+);/ei', 'unichr(hexdec("\\1"))', $string);
        $string = preg_replace('/&#([0-9]+);/e', 'unichr(\\1)', $string);
        return $string;
    }

    /**
     * Transformation XSLT
     *
     * @param $xmlStr string
     *        	XML
     * @param $xslFile string
     *        	fichier XSL
     * @param $params array
     *        	parametres à passer à la transformation XSL
     * @return string nouvelle chaine XML
     */
    public static function xslt($xmlStr, $xslFile, array $params = array()) {
        try {
            $xml = new DOMDocument ();
            if (!$xmlStr) {
                throw new Exception('pas de données');
            }
            if (!is_file($xslFile)) {
                throw new Exception('fichier ' . $xslFile . " n'existe pas");
            }

            set_error_handler('Ccsd_Xml_Exception::HandleXmlError');
            $xml->loadXML($xmlStr);
            restore_error_handler();

            $xsl = new DOMDocument ();
            $xsl->load($xslFile);
            $proc = new XSLTProcessor ();
            $proc->registerPHPFunctions();
            foreach ($params as $key => $value) {
                $proc->setParameter('', $key, $value);
            }
            $proc->importStyleSheet($xsl);
            return $proc->transformToXML($xml);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @return string
     */
    public static function getStoredLang() {
        try {
            return Zend_Registry::get('lang');
        } catch (Exception $e) {
            return "en";
        }
    }

    /**
     * @return string[]
     */
    public static function getStoredLanguages() {
        try {
            return Zend_Registry::get('languages');
        } catch (Exception $e) {
            return array('fr','en','es','eu');
        }
    }
    /**
     * @param string $str
     * @param string $lang
     * @return string
     */
    public static function translate($str, $lang = null) {
        try {
            /** @var Zend_Translate_Adapter $translator */
            $translator = Zend_Registry::get('Zend_Translate');
            if ($lang === null) {
                $lang = Ccsd_Tools::getStoredLang();
            }
            return $translator ->translate($str, $lang);
        } catch (Zend_Exception $e) {
            Ccsd_Tools::panicMsg(__FILE__, __LINE__, "Zend registry: Zend_Translate not defined\n" . $e ->getTrace());
            return $str;
        }
    }

    /**
     * Log function for case a deprecated function is call
     * @param $file
     * @param $line
     * @param $message
     */
    public static function deprecatedMsg($file, $line, $message) {
        error_log("Deprecated: $file:$line - $message");
    }

    /**
     * Log function for case which can't arise but which arise...
     * @param $file
     * @param $line
     * @param $message
     */
    public static function panicMsg($file, $line, $message) {
        error_log("Panic: $file:$line - $message");
    }

    /**
     * Effectue une recherche xpath dans une chaine XML
     *
     * @param $xmlStr string
     *        	XML
     * @param $tag string
     *        	chemin à chercher
     * @return string[]|bool
     */
    public static function xpath($xmlStr, $tag) {
        try {
            $out = array();
            $xml = new DOMDocument();
            if (!$xmlStr || !$tag) {
                throw new Exception('pas de données');
            }
            set_error_handler('Ccsd_Xml_Exception::HandleXmlError');
            $xml->loadXML($xmlStr);
            restore_error_handler();
            $xpath = new DOMXPath($xml);
            foreach (self::getNamespaces($xml->documentElement) as $id => $ns) {
                $xpath->registerNamespace($id, $ns);
            }
            foreach ($xpath->query($tag) as $entry) {
                $out[] = $entry->nodeValue;
            }
            if (count($out) == 1) {
                return $out[0];
            } else if (count($out) == 0) {
                return false;
            } else {
                return $out;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Retourne l'ensemble des espaces de nom d'un objet DomNode
     *
     * @param DomNode
     * @return array
     */
    public static function getNamespaces(DomNode $node) {
        $namespaces = array();
        static $cpt = array();
        if ($node->namespaceURI) {
            $namespace = $node->namespaceURI;
            $prefix = $node->lookupPrefix($namespace);
            $namespaces [($prefix) ? $prefix : 'xmlns'] = $namespace;
        }
        if ($node instanceof DomElement) {
            foreach ($node->childNodes as $child) {
                $dummy = array();
                foreach (self::getNamespaces($child) as $code => $uri) {
                    if (!in_array($uri, $namespaces)) {
                        if (array_key_exists($code, $namespaces)) {
                            if (array_key_exists($code, $cpt)) {
                                $cpt[$code] ++;
                            } else {
                                $cpt[$code] = 1;
                            }
                            $dummy[$code . $cpt [$code]] = $uri;
                        } else {
                            $dummy[$code] = $uri;
                        }
                    }
                }
                $namespaces = array_merge($namespaces, $dummy);
            }
        }
        return $namespaces;
    }

    /**
     * Transforme un objet DOMDocument|DOMElement en tableau php
     * Le DOM corresponds a un fichier XML/JSON: un contenu est soit textuel, soit avec sous noeud
     * (Pas de sous noeud de type texte (ils sont ignores))
     * Un noeud ayant un contenu textuel mais ayant des attributs aura sa valeur textuelle dans la clef _valeur
     * Si pas d'attributs, alors la valeur sera directement dans la valeur du noeud
     * @param $root DOMDocument|DOMElement
     *        	à transformer
     * @return array|string  Si le noeud est un noeud texte sans attribut: le retour est une chaine
     *
     * Note BM: on ne sais pas si un noeud est multivalue...  a l'appelant de faire gaffe !!!
     */
    public static function dom2array($root) {
        $result = array();
        if ($root->hasAttributes()) {
            foreach ($root->attributes as $i => $attr) {
                $result [$attr->name] = $attr->value;
            }
        }
        $children = $root->childNodes;
        if ($children) {
            if ($children->length == 1) {
                $child = $children->item(0);
                if ($child->nodeType == XML_TEXT_NODE) {
                    $result ['_value'] = trim($child->nodeValue);
                    if (count($result) == 1) {
                        // Avec attributs
                        return $result['_value'];
                    } else {
                        // Sans attributs
                        return $result;
                    }
                }
            }
            $group = array();
            for ($i = 0; $i < $children->length; $i ++) {
                $child = $children->item($i);
                if ($child->nodeType == XML_ELEMENT_NODE) {
                    /** @var DOMElement $child */
                    if (!isset($result [$child->nodeName])) {
                        // Noeuds pour l';instant monovalue
                        $result[$child->nodeName] = self::dom2array($child);
                    } else {
                        // Un meme noeud repete:
                        if (!isset($group [$child->nodeName])) {
                            // Si premiere repetition, on transforme la valeur en tableau
                            $tmp = $result [$child->nodeName];
                            $result[$child->nodeName] = array($tmp);
                            $group [$child->nodeName] = 1;
                        }
                        // Puis on ajoute le noeud a la liste des noeuds
                        $result[$child->nodeName][] = self::dom2array($child);
                    }
                }
                // Else: les noeuds textes sont ignores
            }
        }
        return $result;
    }

    /**
     * retourne le nom du fichier à enregistrer
     * Si un fichier du même nom existe déjà, on renommera le nouveau fichier
     *
     * @param string $filename
     * @param string $dir
     * @return string
     */
    public static function getNewFileName($filename, $dir) {
        // Nettoyage du nom du fichier
        $filename = self::cleanFileName($filename);
        if (strpos($filename, '.')) {
            while (is_file($dir . $filename)) {
                $filename = preg_replace('/_?(\d*)(\.\w{0,4})$/e', "'_'.('\\1'+1).'\\2'", $filename);
            }
        }
        return $filename;
    }

    /**
     * Nettoie le nom d'un fichier
     *
     * @param string
     * @return string
     */
    public static function cleanFileName($filename = '') {
        $filename = preg_replace('/[^a-z0-9_.-\/\\\\]/i', '_', self::spaces2Space(self::stripAccents(($filename))));
        $filename = preg_replace("~\.\.*~", ".", $filename);
        return (preg_replace("/__*/", "_", $filename));
    }

    /**
     * Retire les espaces mutiples
     *
     * @param string
     * @return string
     */
    public static function spaces2Space($string) {
        return preg_replace("/  +/", " ", $string);
    }

    /**
     * Retire les accents de la chaine passée en parametre
     *
     * @param $text string
     * @return string
     */
    public static function stripAccents($text) {
        $text = str_replace(
                array('æ', 'Æ', 'œ', 'Œ', 'ý', 'ÿ', 'Ý', 'ç', 'Ç', 'ñ', 'Ñ'), array('ae', 'AE', 'oe', 'OE', 'y', 'y', 'Y', 'c', 'C', 'n', 'N'), $text);
        $text = preg_replace("/[éèëêě]/u", "e", $text);
        $text = preg_replace("/[ÈÉÊË]/u", "E", $text);
        $text = preg_replace("/[àâäáãå]/u", "a", $text);
        $text = preg_replace("/[ÀÁÂÃÄÅ]/u", "A", $text);
        $text = preg_replace("/[ïîíì]/u", "i", $text);
        $text = preg_replace("/[ÌÍÎÏ]/u", "I", $text);
        $text = preg_replace("/[üûùú]/u", "u", $text);
        $text = preg_replace("/[ÙÚÛÜ]/u", "U", $text);
        $text = preg_replace("/[ôöóòõø]/u", "o", $text);
        $text = preg_replace("/[ÒÓÔÕÖØ]/u", "O", $text);
        $text = str_replace(
                array('č', 'ć', 'ň', 'ř', 'ť', 'ž', 'š', 'Č', 'Ć', 'Ň', 'Ř', 'Ť', 'Ž', 'Š', ),
                array('c', 'c', 'n', 'r', 't', 'z', 's', 'C', 'C', 'N', 'R', 'T', 'Z', 'S', ),$text);
        return $text;
    }

    /**
     * @param $string
     * @return string
     */
    public static function nl2space($string) {
        return (preg_replace("/\\n(\\r)?/i", " ", $string));
    }

    /**
     * @param string $text
     * @return string
     */
    public static function clear_nl($text) {
        $text = str_replace("\n", "", $text);
        $text = str_replace("\r", "", $text);
        return ($text);
    }

    /**
     * @param $string
     * @param bool $pad
     * @param bool $isEndDate indicate that if only year is given , should be extended to end of the year
     * @return string
     * Verify that the input date is /^\d{4}(-\d{2}(-\d{2})?)?
     * return '' if not or 0000-00-00 if pad is true
     * return the same date if yes, and pad with 01-01 if pad is true
     *
     */
    public static function str2date($string, $pad = false, $isEndDate = false) {
        if (is_string($string)) {
            $date = '';
            list($y, $m, $d) = array_pad(explode('-', $string, 3), 3, 0);
            $y = str_pad((int) substr($y, 0, 4), 4, '0', STR_PAD_LEFT);
            if ($y != '0000') {
                $date = $y;
                $m = str_pad((int) substr($m, 0, 2), 2, '0', STR_PAD_LEFT);
                if ($m != '00') {
                    $date .= '-' . $m;
                    $d = str_pad((int) substr($d, 0, 2), 2, '0', STR_PAD_LEFT);
                    if ($d != '00') {
                        $date .= '-' . $d;
                    } else {
                        if ($pad) {
                            $date = $y . '-' . $m . '-01';
                        }
                    }
                } else {
                    if ($pad) {
                        $date = $y . ($isEndDate ? '-12-31':'-01-01');
                    }
                }
            } else {
                if ($pad) {
                    $date = '0000-00-00';
                }
            }
            return $date;
        }
        return '';
    }

    /**
     * @param $string
     * @param int $length
     * @param string $replacement
     * @return mixed
     */
    public static function truncate($string, $length = 100, $replacement = "...") {
        if (strlen($string) <= $length) {
            return $string;
        } else {
            return substr_replace($string, $replacement, $length);
        }
    }

    /**
     * Un in_array récursif permettant de parcourir un tableau multidimentionnel
     *
     * @param mixed $mixed
     * @param array $array
     * @param bool $strict
     * @return bool
     */
    public static function in_array_r($mixed, $array, $strict = false) {
        foreach ($array as $item) {
            if (($strict ? $item === $mixed : $item == $mixed) || (is_array($item) && self::in_array_r($mixed, $item, $strict))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $filepath
     * @param array $data
     * @return bool
     */
    public static function write_translations($filepath, array $data = array()) {
        if (!file_exists($filepath)) {
            return false;
        }

        file_put_contents($filepath, "<?php return array (");
        foreach ($data as $key => $value) {
            file_put_contents($filepath, "'" . addslashes($key) . "' => '" . addslashes($value) . "',\r\n", FILE_APPEND);
        }
        file_put_contents($filepath, ");", FILE_APPEND);

        return true;
    }

    /**
     *
     * Curl sur le serveur de requêtes de solr
     * Exemple : Ccsd_Tools::solrCurl('q=*:*&wt=json','ref_journal');
     *
     * @see /library/Ccsd/Search/Solr/configs/endpoints.ini
     * @param string $queryString solr query
     * @param string $core solr core
     * @param string $handler solr handler
     * @param int $timeout curl request timeout in seconds
     * @return mixed
     * @throws Exception
     */
    public static function solrCurl($queryString, $core = 'hal', $handler = 'select', $timeout = 40) {
        $options = [];
        // Doit être définit dans l'application cliente
        if (defined('APPLICATION_ENV')) {
            $options ['env'] = APPLICATION_ENV;
        }

        $options ['core'] = $core;
        $options ['handler'] = $handler;

        $s = new Ccsd_Search_Solr($options);

        $endPointUrl = $s->getEndPointUrl();


        if ($handler != 'schema') {
            $endPointUrl .= '?';
        }
        $endPointUrl .= $queryString;


        $curlHandler = curl_init();

        if (defined('SPACE_NAME') && SPACE_NAME == 'AUREHAL') {
            $curlUserAgent = 'CcsdToolsCurlAureal';
        } else {
            $curlUserAgent = 'CcsdToolsCurl';
        }

        curl_setopt($curlHandler, CURLOPT_USERAGENT, $curlUserAgent);

        curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandler, CURLOPT_CONNECTTIMEOUT, 15); // timeout in seconds

        $auth = null;

        $auth = $s->getEndPointAuth();
        if (is_array($auth)) {
            curl_setopt($curlHandler, CURLOPT_USERPWD, $auth ['username'] . ':' . $auth ['password']);
        }

        curl_setopt($curlHandler, CURLOPT_URL, $endPointUrl);
        curl_setopt($curlHandler, CURLOPT_TIMEOUT, $timeout); // timeout in seconds

        $info = curl_exec($curlHandler);

        if (curl_errno($curlHandler) == CURLE_OK) {
            return $info;
        } else {
            $errno = curl_errno($curlHandler);
            $error_message = curl_strerror($errno) . '. Query: ' . $queryString;
            curl_close($curlHandler);
            throw new Exception("cURL error ({$errno}): {$error_message}", $errno);
        }
    }

    /**
     * @param array $words   [string =>int ]
     * @param int $level
     * @return array
     */
    public static function prepareWordCloud($words, $level = 6) {
        $max = max($words);
        $cloud = array();
        foreach ($words as $word => $nb) {
            if ($nb == 0 || $word == '') {
                continue;
            }
            $cloud [$word] = ceil(($nb * $level) / $max);
        }
        return self::shuffleAssoc($cloud);
    }

    /**
     * @param array $list
     * @return array
     */
    public static function shuffleAssoc($list) {
        if (!is_array($list))
            return $list;

        $keys = array_keys($list);
        shuffle($keys);
        $random = array();
        foreach ($keys as $key)
            $random [$key] = $list [$key];

        return $random;
    }

    /**
     * Appel WS geoname pour récupérer la ville et le pays par rapport à une coordonnées GPS
     *
     * @param $lng
     * @param $lat
     * @return array|bool
     */
    public static function geoname($lng, $lat) {
        $ch = curl_init(str_replace(array('%%LNG%%', '%%LAT%%'), array($lng, $lat), "http://ws.geonames.org/findNearbyPlaceName?style=SHORT&lat=%%LAT%%&lng=%%LNG%%"));
        curl_setopt($ch, CURLOPT_USERAGENT, "CCSD - HAL Proxy");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        $return = curl_exec($ch);
        curl_close($ch);
        try {
            $simpleXML = new SimpleXMLElement($return);
            return array('city' => (string) $simpleXML->geoname->name, 'country' => (string) $simpleXML->geoname->countryCode);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param $var
     * @param bool $ret
     * @param string $format
     * @param string $title
     * @param bool $infos
     * @return string
     */
    public static function debug($var, $ret = false, $format = "pre", $title = "Debug", $infos = true) {
        $debug = debug_backtrace();
        $last_debug = $debug [0];
        $return = '<fieldset class="ccsd-debug">';
        $return .= '<legend>' . $title . '</legend>';
        if ($infos) {
            $return .= '<div class="infos">Fichier : ' . $last_debug ['file'] . '<br />Ligne : ' . $last_debug ['line'] . '</div>';
        }
        $return .= '<' . $format . '>' . print_r($var, true) . '</' . $format . '>';
        $return .= '</fieldset>';

        if (!$ret) {
            echo $return;
        }
        return $return;
    }

    /**
     * Suppression d'un répertoire et des fichiers situés à l'intérieur
     *
     * @param string $path
     * @return bool
     */
    public static function deletedir($path) {
        if (is_dir($path)) {
            $res = opendir($path);
            while (($entry = readdir($res)) !== FALSE) {
                if ($entry != "." && $entry != "..") {
                    if (is_dir(realpath($path) . '/' . $entry)) {
                        self::deletedir(realpath($path) . '/' . $entry);
                    } else {
                        unlink(realpath($path) . '/' . $entry);
                    }
                }
            }
            closedir($res);
            return rmdir(realpath($path));
        }
        return false;
    }

    /**
     * @param string $src
     * @param string $dst
     */
    // copies files and non-empty directories
    public static function rcopy($src, $dst) {
        if (file_exists($dst)) {
            self::rrmdir($dst);
        }
        if (is_dir($src)) {
            if (!is_dir($dst)) {
                mkdir($dst);
            }
            $files = scandir($src);
            foreach ($files as $file) {
                if ($file != "." && $file != "..") {
                    self::rcopy($src . DIRECTORY_SEPARATOR . $file, $dst . DIRECTORY_SEPARATOR . $file);
                }
            }
        } else if (file_exists($src)) {
            copy($src, $dst);
        }
    }

    /**
     * @param string $dir
     * @return bool
     */
    public static function rrmdir($dir) {
        if (is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $file) {
                if ($file != "." && $file != "..") {
                    self::rrmdir($dir . DIRECTORY_SEPARATOR . $file);
                }
            }
            return @rmdir($dir);
        }
        if (file_exists($dir)) {
            return @unlink($dir);
        }
        return false;
    }

    /** @deprecated " pb with Utf8  */
    public static function str2ascii($str, $md5crypt = true) {
        $str = self::stripAccents($str);
        for ($i = 0; $i < strlen($str); $i ++) {
            $hex = ord($str [$i]);
            if ($hex < 48) {
                $str [$i] = '_';
            } else if (($hex > 57) && ($hex < 65)) {
                $str [$i] = '_';
            } else if (($hex > 90) && ($hex < 97)) {
                $str [$i] = '_';
            } else if ($hex > 122) {
                $str [$i] = '_';
            }
        }
        $str = strtolower(str_replace('_', '', $str));
        return $md5crypt ? md5($str) : $str;
    }

    /**
     * @param string $text
     * @return string
     * Utilisée dans des feuilles de style...
     */
    public static function protectLatex($text) {
        /* lfarhi : on ne met pas $,^,~,_ { et } car il y a du latex dans certains champs avec ces caractères
         * */
        $text_replace = str_replace(
                array("&", "#", "%"), array("\\&", "\\#", "\\%"), $text);
        return $text_replace;
    }
    /**
     * @param string $text
     * @return string
     * Utilisée dans des feuilles de style...
     * @see file:Hal/Document/xsl/bibtex.xsl
     */
    public static function protectBibtexTitle($text) {
        if (preg_match('/(\$[^$]+\$|\\\\[a-zA-Z]+\{)/xms', $text)) {
            // Encodage des carateres, mais pas d'echappement...
            $text = self::decodeLatex($text);
            // Args, on a encode le _ dans decode latex...
            $text = preg_replace('/\\\\_/', '_', $text);
        } else {
            $text = preg_replace('/{/', '\\{', $text);
            $text = preg_replace('/}/', '\\}', $text);
            $text = preg_replace('/\$/', '\\\\$', $text);
            $text = self::decodeLatex($text);
            // la chaine ne contient pas de latex math  on echappe les caractères...
            $text = preg_replace('/%/', '\\%', $text);
            $text = preg_replace('/&/', '\\&', $text);
            $text = preg_replace('/#/', '\\#', $text);

            $text = str_replace(' ', '~', $text);
            $text = str_replace(' ', '\\,', $text);
            // Arg: & a deja ete remplace par \\&
            $text = str_replace('\\\\&nbsp;', '~', $text);
            return $text;
        }
        return $text;
    }
   /**
     * @param string $text
     * @return string
     * Utilisée dans des feuilles de style...
     */
    public static function protectCoverpage($text) {
        /*
        Les caractères déja traité par html2 text ne sont pas a retraiter
        */
        $text_replace = str_replace(
                array("%"), array("\\%"), $text);
        return $text_replace;
    }
    /**
     * @param string $text
     * @return string
     */
    public static function protectUnderscore($text) {

        $text_replace = str_replace(
                array("_"), array("\\_"), $text);
        return $text_replace;
    }
    /**
     * @param string $text
     * @return string
     */
    public static function decodeLatex($text, $greekRecode=true) {

        if (!empty($text)) {
            //si le tableau des patern n'a jamais été généré, on le fait
            if (self::$_PatternCarLatex == null) {//n'a pas encore ete genere
                foreach (self::$_TRANS_CAR_LATEX as $car_utf8 => $latex) {
                    self::$_PatternCarLatex[] = $car_utf8;
                    self::$_ReplaceCarLatex[] = $latex;
                }
                }
            if (self::$_PatternCarLatexGreek == null) {//n'a pas encore ete genere
                foreach (self::$_TRANS_CAR_LATEX_GREEK as $car_utf8 => $latex) {
                    self::$_PatternCarLatexGreek[] = $car_utf8;
                    self::$_ReplaceCarLatexGreek[] = $latex;
            }
                self::$_PatternCarLatexGlobal = array_merge(self::$_PatternCarLatexGreek, self::$_PatternCarLatex );
                self::$_ReplaceCarLatexGlobal = array_merge(self::$_ReplaceCarLatexGreek, self::$_ReplaceCarLatex);
            }
            if ($greekRecode) {
                return str_replace(self::$_PatternCarLatexGlobal, self::$_ReplaceCarLatexGlobal, $text);
            } else {
                return str_replace(self::$_PatternCarLatex, self::$_ReplaceCarLatex, $text);
            }
        } else {
            return $text;
        }
    }

    /**
     * @param string $text
     * @return string
     */
    public static function htmlToTex($text, $doGreek=true) {
        // Pour vrais < il faut absoluement un espace apres afin de ne pas etre retire par le strip_tags
        // formule math avec <
        $text = preg_replace("/<([^[:alpha:]\/])/", '&lt;\\1', $text);
        // strip_tags
        $text = trim(strip_tags($text, '<br><i><b><sup><s><sub><span><u><p><ul><li><NNT>'));
        // balise html
        $text = preg_replace('~<span style="font-weight: *bold;">([^<]+)</span>~i', '\textbf{\\1}', $text);
        $text = preg_replace('~<span style="font-style: *italic;">([^<]+)</span>~i', '\textit{\\1}', $text);
        $text = preg_replace('~<span style="text-decoration: *underline;">([^<]+)</span>~i', '\underline{\\1}', $text);
        $text = preg_replace('~<span style="text-decoration: *line-through;">([^<]+)</span>~i', '\sout{\\1}', $text);
        $text = preg_replace('~<br\s*/?>~i', '\\\\\\', $text);
        $text = preg_replace('~<ul>~i', '\begin{itemize}', $text);
        $text = preg_replace('~</ul>~i', '\end{itemize}', $text);
        $text = preg_replace('~<li( [^>]*)*>([^<]+)</li>~i', '\item[$\bullet$] \\2', $text);
        $text = preg_replace('~<i( [^>]*)*>([^<]+)</i>~i', '\textit{\\2}', $text);
        $text = preg_replace('~<b( [^>]*)*>([^<]+)</b>~i', '\textbf{\\2}', $text);
        $text = preg_replace('~<u( [^>]*)*>([^<]+)</u>~i', '\underline{\\2}', $text);
        $text = preg_replace('~<s( [^>]*)*>([^<]+)</s>~i', '\sout{\\2}', $text);
        $text = preg_replace('~<sup>([^<]+)</sup>~i', '\textsuperscript{\\1}', $text);
        $text = preg_replace('~<sub>([^<]+)</sub>~i', '\textsubscr{\\1}', $text);

        $text = strip_tags($text, '<p><NNT>');
        $text = preg_replace('~\s+~i', ' ', $text);
        $text = preg_replace('~^<p( [^>]*)*>(.*)</p>$~i', '\2', $text);
        $text = preg_replace('~<p( [^>]*)*>\s*</p>~i', '', $text);
        $text = preg_replace('~<p( [^>]*)*>([^<]+)</p>~i', '\\\\\\\ \2 \\\\\\', $text);
        // Traitement des espaces insecables avant les ponctuations
        $text = preg_replace('~ ([:;!?])~', '\,\\1', $text);
        $text = str_replace('&lt;', '<', $text);
        // protection
        $text = self::decodeLatex(html_entity_decode($text, ENT_QUOTES, 'UTF-8'),$doGreek);

        return $text;
    }
    
    /**
     * Supprime toute les valeurs correspondant a la valeur de filter
     * Et supprime toute les clefs dont la valeur est un tableau vide
     *
     * @param $input array
     * @param mixed $filter
     * @return mixed
     */
    public static function filter_multiarray(&$input, $filter = '') {
        if (is_array($input)) {
            foreach ($input as $key => &$value) {

                if (!is_array($value) && $value === $filter) {
                    unset($input [$key]);
                }

                if (is_array($value) && count($value)) {
                    self::filter_multiarray($value, $filter);
                }

                if (is_array($value) && !count($value)) {
                    unset($input [$key]);
                }
            }
        }

        return $input;
    }

    /**
     * Return https?://server , en regardant si on doit utiliser http ou https
     *
     * @return string
     */
    public static function getBaseUrl() {
        if (isset($_SERVER ['HTTPS'])) {
            $protocol = ($_SERVER ['HTTPS'] && $_SERVER ['HTTPS'] != "off") ? "https" : "http";
        } else {
            $protocol = 'http';
        }
        return $protocol . "://" . $_SERVER ['HTTP_HOST'];
    }

    /**
     * Détecte si le script est executé depuis la CLI
     *
     * @return boolean true si CLI false si HTTP
     */
    public static function isFromCli() {
        return (!isset($_SERVER ['SERVER_SOFTWARE']) && (php_sapi_name() == 'cli' || (is_numeric($_SERVER ['argc']) && $_SERVER ['argc'] > 0)));
    }

    /**
     * Retourne le domaine d'une adresse mail
     *
     * @param string $email
     * @return string
     */
    public static function getEmailDomain($email)
    {
        $arobasePosition = strpos($email, '@');
        if ($arobasePosition === false) {
            return '';
        }
        return substr($email, $arobasePosition + 1);
    }
    /**
     * @param $value
     * @return bool
     */
    public static function isEmpty($value)
    {
        if (is_object($value)) {
            try {
                $class = new ReflectionClass($value);
                if ($class->hasMethod('isEmpty')) {
                    return $value->isEmpty();
                } else {
                    return false;
                }
            } catch (ReflectionException $e) {
                return false;
            }
        }
        if ($value === 0 || $value === "0") {
            return false;
        } 
        return empty($value);
    }

    /**
     * Copy a hierarchical filesystem structutres
     * The prune arg is an array of pattern to exclude
     * The pattern a match against the whole part of filename
     *
     * @param       $source
     * @param       $dest
     * @param int   $filemode
     * @param int   $dirmode
     * @param array $prune
     * @return bool|string
     */
    public static function copy_tree($source, $dest, $filemode = 0644, $dirmode = 0755, $prune = []) {
        if (!file_exists($source)) {
            return true;
        }
        @mkdir($dest, $dirmode) ;
        $success = true;
        foreach (
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST) as $item
        ) {
            /** @var  RecursiveDirectoryIterator $iterator */
            /** @var  RecursiveDirectoryIterator $item */
            // Chaque element du tableau Prune est compare a $item, si match: on ne traite pas
            if (self::array_or(function ($pattern)  use ($item) {
                        return (preg_match($pattern, $item ) != 0);
                    }, $prune)) {
                continue;
            }
            if ($item->isDir()) {
                $dirdst = $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
                $success |= @mkdir($dirdst, $dirmode);
            } else {
                $filedst = $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
                 $success |= @copy($item, $filedst);
            }
        }
        if ($success) {
            return true;
        } else {
            return "Probleme rencontre lors de la copie des fichiers web de la collection";
        }
    }

    /**
     * @param $callable
     * @param $array
     * @return bool
     */
    public static function array_or ($callable, $array) {
        // Apply callback function to each element of array.
        // return true as soon as true is evaluated
        foreach ($array as $item) {
            if ($callable($item)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Compute a "name" <email> that Arxiv accept...
     * Some form for the first part are not accepted by Arxiv
     *
     * @return string
     */
    public static function getFromNormalized($fullname, $email) {
        $fullname = trim(strip_tags(Ccsd_Tools::stripAccents($fullname)));
        /* Prenom Machin-K. <mail@labas.fr> */
        /* Prenom K. Machin <mail@labas.fr> */
        /* Prenom Machin K. <mail@labas.fr> */
        $cleanFullname = preg_replace('/[ -].\. /', ' ', $fullname);

        return '"' . $cleanFullname  . '" <' . $email  . '>';
    }
    /**
     * Supprime un dossier et son contenu
     *
     * @param $dir string
     * @return bool
     */
    static public function removeDir($dir)
    {
        // ajout du slash a la fin du chemin s'il n'y est pas
        if( !preg_match( "#/$#", $dir ) ) {
            $dir .= '/';
        }
        $handle = opendir($dir);
        if($handle) {
            // Parcours du repertoire
            while($item = readdir($handle)) {
                if($item != "." && $item != "..") {
                    unlink($dir.$item);
                }
            }
            // Fermeture du repertoire
            closedir($handle);
            // suppression du repertoire
            $success = rmdir($dir);
        } else {
            $success = false;
        }
        return $success;
    }

    /**
     * Renvoie une ligne d'un fichier XML
     *
     * @param $fileStream
     * @param $lineNb
     * @return bool|string
     */
    static public function getFileLine($fileStream, $lineNb)
	{
		$result = false;
		for ($currentLine=1; $currentLine <= $lineNb; $currentLine++)
		{
			$buffer = fgets($fileStream);
			if (empty($buffer)) {
			    break;
            } else {
                $result = $buffer;
			}
		}
		return $result;
	}

    /**
     * Génération du mot de passe d'un compte
     *
     * @param int $min
     *            nombre min de caractères
     * @param int $max
     *            nombre max de caractères
     * @return string
     */
    static public function generatePw($min = 10, $max = 15)
    {
        $pass = "";
        $nbchar = rand($min, $max);
        $chars = array('#', '&', '@', '?', '!','%',
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        for ($i = 0; $i < $nbchar; $i++) {
            $pass .= $chars[rand(0, count($chars) - 1)];
        }
        return $pass;
    }
}
