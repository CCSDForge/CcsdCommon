<?php


require(__DIR__ . '/../../../autoload.php');
define('LIBROOT', __DIR__ . '/..');
define('CCSDLIB_SRC', LIBROOT . '/public/');
// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(__DIR__ . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'testing'));

set_include_path(implode(PATH_SEPARATOR, array_merge(array(realpath(__DIR__ . '/library')), array(get_include_path()))));

require_once 'Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->setFallbackAutoloader(true);

// Pour l'instant le lien se fait en dur vers le projet HAL
// Lorsque Library sera réellement indépendante de HAL, ça pourra sauter
//require_once 'config/bddconst.php';
define('FIXTUREDIR', __DIR__ . '/fixture');

$ccsd_translator = new Ccsd_Translator();
$translator = $ccsd_translator->getTranslator();
Zend_Registry::set ( 'Zend_Translate', $translator);
Ccsd_Translator::addTranslations($translator);
Zend_Registry::set('Zend_Locale', new Zend_Locale('en'));
