<?php

require_once __DIR__ . '/../library/Zend/Loader/AutoloaderFactory.php';
require_once __DIR__ . '/../library/Zend/Loader/ClassMapAutoloader.php';

   

if (!function_exists('vd')) {
    function vc () {}
    function vd () {}
    function vdl () {}
    function vdie () {}
    function vdiec () {}
    function vdfnp() {}
    function vdi() {}
}

require __DIR__.'/../web/assets/plugins/vendor/autoload.php';

defined('ROOT_PATH')
    || define('ROOT_PATH', realpath(dirname(__FILE__) . '/../'));

defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    realpath(APPLICATION_PATH . '/../Base'),
    realpath(APPLICATION_PATH . '/../application/Logic'),
    get_include_path(),
)));

Zend_Loader_AutoloaderFactory::factory(
    array(
        'Zend_Loader_ClassMapAutoloader' => array(
            __DIR__ . '/../application/autoload_classmap.php'
        ),
        'Zend_Loader_StandardAutoloader' => array(
            'prefixes' => array(
                'Zend' => __DIR__ . '/../library/Zend',
                'Base' => __DIR__ . '/../Base',
                'Logic' => __DIR__ . '/../application/Logic',
            ),
            'fallback_autoloader' => true
        )
    )
);
