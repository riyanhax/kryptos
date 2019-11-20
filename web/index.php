<?php

ini_set('upload_tmp_dir','tmp/');
ini_set('display_errors', 0);

require_once __DIR__ . '/../Base/debug/debug.php';
require_once __DIR__ . '/../application/autoload.php';

if ($_SERVER['HTTP_HOST'] === 'test.kryptos24.mr.com') {
    $config = APPLICATION_PATH . '/configs/application.ini';
} elseif ($_SERVER['HTTP_HOST'] === 'testing.kryptos24.mr.com') {
    $config = APPLICATION_PATH . '/configs/application.testing.ini';
} elseif ($_SERVER['HTTP_HOST'] === 'hq_base.v2.kryptos24.mr.com') {
    $config = APPLICATION_PATH . '/configs/application.hq_base.ini';
} elseif ($_SERVER['HTTP_HOST'] === 'tmp.v2.kryptos24.mr.com') {
    $config = APPLICATION_PATH . '/configs/application.tmp.ini';
} elseif ($_SERVER['HTTP_HOST'] === 'hq_base.kryptos24.mr.com') {
    $config = APPLICATION_PATH . '/configs/application.hq_base.ini';
} else {
    $config = APPLICATION_PATH . '/configs/application.ini';
}


require_once 'Zend/Application.php';
$application = new Zend_Application(
    APPLICATION_ENV,
    $config
);

// echo zend framework version number 1.12.20
// echo Zend_Version::getLatest();

//Application_Service_Logger::log('access', date('Y-m-d H:i:s')." ".$_SERVER['REQUEST_URI']." ".@$_SERVER['REMOTE_ADDR']);
//error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE);
//ini_set('display_errors', 'on');

$application->bootstrap();
$application->run();
