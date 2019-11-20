<?php
namespace migrations;

require_once __DIR__ .'/lib.inc.php';

db_query(<<<SQL
DROP TABLE IF EXISTS `free_trials`;
CREATE TABLE `free_trials` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL DEFAULT '',
  `confirmation_code` varchar(255) NOT NULL DEFAULT '',
  `status` tinyint(4) NOT NULL,
  `license_subscription_id` int(11) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY (`email`, `status`),
  KEY (`created_at`)
) ENGINE=InnoDB;
SQL
);
