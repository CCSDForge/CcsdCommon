<?php
namespace Ccsd;
/**
 * Class HTMLPurifier
 * @package Hal
 */
class HTMLPurifier extends \HTMLPurifier
{
    public static $CORE_ENCODING = 'UTF-8';
    public static $HTML_ALLOWED_ELEMENTS = [
        'a',
        'b',
        'blockquote',
        'br',
        'code',
        'em',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'hr',
        'i',
        'img',
        'li',
        'ol',
        'p',
        'pre',
        's',
        'span',
        'strong',
        'sub',
        'sup',
        'table',
        'tbody',
        'td',
        'th',
        'thead',
        'tr',
        'u',
        'ul',
    ];
    public static $HTML_ALLOWED_ATTRIBUTES = [
    ];
    public static $CSS_ALLOWED_PROPERTIES = [
    ];
    public static $ATTR_ALLOWED_CLASSES = [
    ];
    public static $ATTR_ALLOWED_FRAME_TARGETS = [

    ];

    public static $URI_ALLOWED_SCHEMES = [
        'http' => true,
        'https' => true,
    ];

    /**
     * HTMLPurifier constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $commonOptions = [
            'Core.Encoding' => self:: $CORE_ENCODING,
            'Cache.SerializerPath' =>'/tmp/HTMLPurifier/DefinitionCache',
        ];

        $defaultOptions = [
            'HTML.AllowedElements'     => self::$HTML_ALLOWED_ELEMENTS,
            'CSS.AllowedProperties'    => self::$CSS_ALLOWED_PROPERTIES,
            'Attr.AllowedClasses'      => self::$ATTR_ALLOWED_CLASSES,
            'Attr.AllowedFrameTargets' => self::$ATTR_ALLOWED_FRAME_TARGETS,
            'HTML.AllowedAttributes'   => self::$HTML_ALLOWED_ATTRIBUTES,
            'URI.AllowedSchemes'       => self::$URI_ALLOWED_SCHEMES,
        ];
        $allOptions = array_merge($commonOptions, $defaultOptions);
        if (! empty($options)) {
            $allOptions = array_merge($allOptions, $options);
        }
        $options = $allOptions;
        // HTMLPurifier utilise un cache interne pour les structures qu'il analyse et vide le cache dans des fichiers sur le disque.
        // Le chemin par défaut (vendor/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache)
        $cacheDirectory = $options['Cache.SerializerPath']; // Pour stocker les définitions sérialisées.
        if (!file_exists($cacheDirectory) && !mkdir($cacheDirectory, 0777, true) && !is_dir($cacheDirectory)) {
            trigger_error(sprintf('HTML purifier directory "%s" can not be created', $cacheDirectory), E_USER_ERROR);
        }

        $config = \HTMLPurifier_Config::createDefault();
        $availableOptionKeys = array_keys($config->def->info);

        foreach ($options as $key => $value) {
            if (in_array($key, $availableOptionKeys, true)) {
                $config->set($key, $value);
            }
        }

        parent::__construct($config);
    }

    /**
     * Filters an HTML snippet/document to be XSS-free and standards-compliant.
     * @param string $html
     * @return string
     */
    public function purifyHtml(string $html = ''): string
    {
        if (empty($html)) {
            return '';
        }
        return $this->purify($html, $this->config);
    }
}