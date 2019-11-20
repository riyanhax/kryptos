<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Run queries
db_query(<<<SQL
CREATE TABLE `registry_action` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `module_id` int(11) DEFAULT NULL,
 `module_type` varchar(100) DEFAULT NULL,
 `action` varchar(100) DEFAULT NULL,
 `action_on` varchar(100) DEFAULT NULL,
 `action_name` varchar(100) DEFAULT NULL,
 `previous_value` varchar(250) DEFAULT NULL,
 `new_value` varchar(250) DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8;
SQL
);
db_query(<<<SQL
INSERT INTO `menu` (`id`, `label`, `path`, `icon`, `rel`, `parent_id`, `activate-routes`) VALUES (NULL, 'Registry Action', '/registry/audit', NULL, NULL, '8', NULL);
SQL
);