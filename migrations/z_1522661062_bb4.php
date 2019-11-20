<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Write you code here
//
// You can use
// db_query('some sql');  for quering
// db_pdo()->...;         some pdo functions

db_query(<<<SQL
ALTER TABLE `payments` ADD `payment_purpose` enum('buy','deposit_balance','increase_user_limit') NOT NULL AFTER `status`;
ALTER TABLE `payments` ADD `purpose_data` VARCHAR(255) DEFAULT NULL COMMENT 'JSON format' AFTER `payment_purpose`;


CREATE TABLE IF NOT EXISTS `licenses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(3000) DEFAULT NULL,
  `version` enum('BASIC','STANDARD','PROFESSIONAL','ENTERPRISE') NOT NULL,
  `user_limit` int(10) unsigned NOT NULL,
  `months` int(10) unsigned NOT NULL,
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `status` tinyint(4) NOT NULL COMMENT '-1: Deactivated, 0: Inactive , 1: Activated',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
SQL
);
