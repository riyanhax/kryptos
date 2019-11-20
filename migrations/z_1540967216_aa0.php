<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Write you code here

db_query(<<<SQL
ALTER TABLE `osoby` ADD `rightsPermissions` TEXT  NOT NULL  AFTER `rights`;
ALTER TABLE `admin_link` CHANGE `superadmin_login` `superadmin_login` VARCHAR(255)  NOT NULL  DEFAULT '';
ALTER TABLE `admin_link` CHANGE `type` `type` VARCHAR(50)  NOT NULL  DEFAULT '';
SQL
);

// You can use
// db_query('some sql');  for quering
// db_pdo()->...;         some pdo functions
