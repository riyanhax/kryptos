<?php
namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Run queries
db_query(<<<SQL
DROP TABLE IF EXISTS `registry_action`;
SQL
);

// Run queries
db_query(<<<SQL
CREATE TABLE `registry_action` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `module_id` int(11) DEFAULT NULL,
 `module_type` varchar(100) DEFAULT NULL,
 `action` varchar(100) DEFAULT NULL,
 `action_on` varchar(100) DEFAULT NULL,
 `action_name` varchar(100) DEFAULT NULL,
 `previous_value` text,
 `new_value` text,
 `user_id` int(11) DEFAULT NULL,
 `record_id` int(11) DEFAULT NULL,
 `module_id_value` varchar(250) NULL,
 `user_id_value` varchar(250) NULL,
 `insert_date` datetime NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT COLLATE utf8_polish_ci
SQL
);

db_query(<<<SQL
ALTER TABLE `registry_filters` ADD `filter_table` varchar(250) NULL AFTER `created_at`;
SQL
);
db_query(<<<SQL
DELETE FROM `menu` WHERE label='Registry Action' AND path='/registry/audit' AND parent_id='8';
SQL
);
db_query(<<<SQL
DELETE FROM `menu` WHERE label='Registry log' AND path='/registry/log' AND parent_id='8';
SQL
);

