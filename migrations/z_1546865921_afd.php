<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Write you code here
db_query(<<<SQL
INSERT INTO `menu` (`id`, `label`, `path`, `icon`, `rel`, `parent_id`, `activate-routes`) VALUES 
(NULL, 'Activity', '/activity', 'icon-home', 'home', NULL, NULL), 
(NULL, 'Konfiguracja powiadomień', '/notificationsConfiguration', 'fa fa-gear', 'notification-configuration', NULL, NULL), 
(NULL, 'Administracja', 'javascript:;', 'icon-cogs', 'administracja', NULL, NULL);
SQL
);

db_query(<<<SQL
INSERT INTO `menu` (`id`, `label`, `path`, `icon`, `rel`, `parent_id`, `activate-routes`) VALUES 
(NULL, 'Konfiguracja komunikatów', '/config/komadm', 'icon-wrench', 'admin', LAST_INSERT_ID(), NULL);
SQL
);
// You can use
// db_query('some sql');  for quering
// db_pdo()->...;         some pdo functions
