<?php

// autoload_classmap.php @generated by Composer

$vendorDir = dirname(dirname(__FILE__));
$baseDir = dirname($vendorDir);

return array(
    'Google\\Auth\\ApplicationDefaultCredentials' => $vendorDir . '/google/auth/src/ApplicationDefaultCredentials.php',
    'Google\\Auth\\CacheTrait' => $vendorDir . '/google/auth/src/CacheTrait.php',
    'Google\\Auth\\Cache\\InvalidArgumentException' => $vendorDir . '/google/auth/src/Cache/InvalidArgumentException.php',
    'Google\\Auth\\Cache\\Item' => $vendorDir . '/google/auth/src/Cache/Item.php',
    'Google\\Auth\\Cache\\MemoryCacheItemPool' => $vendorDir . '/google/auth/src/Cache/MemoryCacheItemPool.php',
    'Google\\Auth\\CredentialsLoader' => $vendorDir . '/google/auth/src/CredentialsLoader.php',
    'Google\\Auth\\Credentials\\AppIdentityCredentials' => $vendorDir . '/google/auth/src/Credentials/AppIdentityCredentials.php',
    'Google\\Auth\\Credentials\\GCECredentials' => $vendorDir . '/google/auth/src/Credentials/GCECredentials.php',
    'Google\\Auth\\Credentials\\IAMCredentials' => $vendorDir . '/google/auth/src/Credentials/IAMCredentials.php',
    'Google\\Auth\\Credentials\\ServiceAccountCredentials' => $vendorDir . '/google/auth/src/Credentials/ServiceAccountCredentials.php',
    'Google\\Auth\\Credentials\\ServiceAccountJwtAccessCredentials' => $vendorDir . '/google/auth/src/Credentials/ServiceAccountJwtAccessCredentials.php',
    'Google\\Auth\\Credentials\\UserRefreshCredentials' => $vendorDir . '/google/auth/src/Credentials/UserRefreshCredentials.php',
    'Google\\Auth\\FetchAuthTokenCache' => $vendorDir . '/google/auth/src/FetchAuthTokenCache.php',
    'Google\\Auth\\FetchAuthTokenInterface' => $vendorDir . '/google/auth/src/FetchAuthTokenInterface.php',
    'Google\\Auth\\HttpHandler\\Guzzle5HttpHandler' => $vendorDir . '/google/auth/src/HttpHandler/Guzzle5HttpHandler.php',
    'Google\\Auth\\HttpHandler\\Guzzle6HttpHandler' => $vendorDir . '/google/auth/src/HttpHandler/Guzzle6HttpHandler.php',
    'Google\\Auth\\HttpHandler\\HttpHandlerFactory' => $vendorDir . '/google/auth/src/HttpHandler/HttpHandlerFactory.php',
    'Google\\Auth\\Middleware\\AuthTokenMiddleware' => $vendorDir . '/google/auth/src/Middleware/AuthTokenMiddleware.php',
    'Google\\Auth\\Middleware\\ScopedAccessTokenMiddleware' => $vendorDir . '/google/auth/src/Middleware/ScopedAccessTokenMiddleware.php',
    'Google\\Auth\\Middleware\\SimpleMiddleware' => $vendorDir . '/google/auth/src/Middleware/SimpleMiddleware.php',
    'Google\\Auth\\OAuth2' => $vendorDir . '/google/auth/src/OAuth2.php',
    'Google\\Auth\\Subscriber\\AuthTokenSubscriber' => $vendorDir . '/google/auth/src/Subscriber/AuthTokenSubscriber.php',
    'Google\\Auth\\Subscriber\\ScopedAccessTokenSubscriber' => $vendorDir . '/google/auth/src/Subscriber/ScopedAccessTokenSubscriber.php',
    'Google\\Auth\\Subscriber\\SimpleSubscriber' => $vendorDir . '/google/auth/src/Subscriber/SimpleSubscriber.php',
    'Google_Service_Exception' => $vendorDir . '/google/apiclient/src/Google/Service/Exception.php',
    'Google_Service_Resource' => $vendorDir . '/google/apiclient/src/Google/Service/Resource.php',
    'RecursiveCallbackFilterIterator' => $vendorDir . '/studio-42/elfinder/php/elFinderVolumeLocalFileSystem.class.php',
    'elFinder' => $vendorDir . '/studio-42/elfinder/php/elFinder.class.php',
    'elFinderConnector' => $vendorDir . '/studio-42/elfinder/php/elFinderConnector.class.php',
    'elFinderLibGdBmp' => $vendorDir . '/studio-42/elfinder/php/libs/GdBmp.php',
    'elFinderPlugin' => $vendorDir . '/studio-42/elfinder/php/elFinderPlugin.php',
    'elFinderPluginAutoResize' => $vendorDir . '/studio-42/elfinder/php/plugins/AutoResize/plugin.php',
    'elFinderPluginAutoRotate' => $vendorDir . '/studio-42/elfinder/php/plugins/AutoRotate/plugin.php',
    'elFinderPluginNormalizer' => $vendorDir . '/studio-42/elfinder/php/plugins/Normalizer/plugin.php',
    'elFinderPluginSanitizer' => $vendorDir . '/studio-42/elfinder/php/plugins/Sanitizer/plugin.php',
    'elFinderPluginWatermark' => $vendorDir . '/studio-42/elfinder/php/plugins/Watermark/plugin.php',
    'elFinderSession' => $vendorDir . '/studio-42/elfinder/php/elFinderSession.php',
    'elFinderSessionInterface' => $vendorDir . '/studio-42/elfinder/php/elFinderSessionInterface.php',
    'elFinderVolumeBox' => $vendorDir . '/studio-42/elfinder/php/elFinderVolumeBox.class.php',
    'elFinderVolumeDriver' => $vendorDir . '/studio-42/elfinder/php/elFinderVolumeDriver.class.php',
    'elFinderVolumeDropbox' => $vendorDir . '/studio-42/elfinder/php/elFinderVolumeDropbox.class.php',
    'elFinderVolumeDropbox2' => $vendorDir . '/studio-42/elfinder/php/elFinderVolumeDropbox2.class.php',
    'elFinderVolumeFTP' => $vendorDir . '/studio-42/elfinder/php/elFinderVolumeFTP.class.php',
    'elFinderVolumeFlysystem' => $vendorDir . '/barryvdh/elfinder-flysystem-driver/elFinderVolumeFlysystem.php',
    'elFinderVolumeFlysystemGoogleDriveCache' => $vendorDir . '/studio-42/elfinder/php/elFinderFlysystemGoogleDriveNetmount.php',
    'elFinderVolumeFlysystemGoogleDriveNetmount' => $vendorDir . '/studio-42/elfinder/php/elFinderFlysystemGoogleDriveNetmount.php',
    'elFinderVolumeGoogleDrive' => $vendorDir . '/studio-42/elfinder/php/elFinderVolumeGoogleDrive.class.php',
    'elFinderVolumeGroup' => $vendorDir . '/studio-42/elfinder/php/elFinderVolumeGroup.class.php',
    'elFinderVolumeLocalFileSystem' => $vendorDir . '/studio-42/elfinder/php/elFinderVolumeLocalFileSystem.class.php',
    'elFinderVolumeMySQL' => $vendorDir . '/studio-42/elfinder/php/elFinderVolumeMySQL.class.php',
    'elFinderVolumeOneDrive' => $vendorDir . '/studio-42/elfinder/php/elFinderVolumeOneDrive.class.php',
    'elFinderVolumeTrash' => $vendorDir . '/studio-42/elfinder/php/elFinderVolumeTrash.class.php',
);