<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Run queries
db_query(<<<SQL
CREATE TABLE IF NOT EXISTS admin_link ( `id` INT(10) NOT NULL AUTO_INCREMENT , `osoby_login` VARCHAR(255) NOT NULL , `superadmin_login` INT(255) NOT NULL , `type` INT(50) NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
SQL
);

db_query(<<<SQL
CREATE TABLE IF NOT EXISTS `license_validation` ( `id` INT(11) NOT NULL AUTO_INCREMENT , `osoby_id` VARCHAR(150) NOT NULL , `license_type` VARCHAR(150) NOT NULL , `date_of_expiry` VARCHAR(150) NOT NULL , `pro` INT(10) NOT NULL , `mini` INT(10) NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;

SQL
);
