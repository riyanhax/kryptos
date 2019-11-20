<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Write you code here


// Run queries
db_query(<<<SQL
CREATE TABLE `registry_user_permissions` (   `id` int(11) NOT NULL AUTO_INCREMENT,   `registry_id` int(11) NOT NULL,   `user_id` int(11) NOT NULL,   `registry_permission_id` int(11) NOT NULL,   `created_at` datetime NOT NULL,   `updated_at` datetime DEFAULT NULL,   PRIMARY KEY (`id`) ) ENGINE = InnoDB;
SQL
);

// Run queries
db_query(<<<SQL
ALTER TABLE `registry_assignees` ADD `user_permissions_id` VARCHAR(50)  NOT NULL  DEFAULT ''  AFTER `user_id`;;
SQL
);
// You can use
// db_query('some sql');  for quering
// db_pdo()->...;         some pdo functions
