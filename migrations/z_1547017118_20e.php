<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

db_query(<<<SQL
ALTER TABLE `license_subscriptions` ADD `subscription_price` INT(11) NOT NULL AFTER `mini_count`;
SQL
);

db_query(<<<SQL
INSERT INTO `menu` (`id`, `label`, `path`, `icon`, `rel`, `parent_id`, `activate-routes`) VALUES (NULL, 'Licencje', 'javascript:;', 'fa fa-cogs', 'licenses', NULL, NULL);
SQL
);


db_query(<<<SQL
INSERT INTO `menu` (`id`, `label`, `path`, `icon`, `rel`, `parent_id`, `activate-routes`) VALUES (NULL, 'Lista licencji', '/licenses', 'fa fa-list-ol', 'licenses', LAST_INSERT_ID(), NULL), (NULL, 'Lista subskrypcji', '/license-subscriptions', 'fa fa-list', 'license-subscriptions', LAST_INSERT_ID(), NULL), (NULL, 'Bezplatna wersja pr�bna', '/free-trial', 'fa fa-th-list', 'free-trial', LAST_INSERT_ID(), NULL), (NULL, 'Manage Counts Cost', '/license-info', 'fa fa-list-ol', 'license-info', LAST_INSERT_ID(), NULL), (NULL, 'Manage License History', '/licenses/manage-license-history', 'fa fa-list', 'licenses', LAST_INSERT_ID(), NULL);
SQL
);