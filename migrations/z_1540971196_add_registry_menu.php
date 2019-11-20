<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

db_query(<<<SQL
DROP TABLE IF EXISTS `registery_menu`;
CREATE TABLE IF NOT EXISTS `registery_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `registery_id` int(11) DEFAULT NULL,
  `label` varchar(255) COLLATE utf8_polish_ci DEFAULT NULL,
  `path` varchar(255) COLLATE utf8_polish_ci DEFAULT NULL,
  `icon` varchar(255) COLLATE utf8_polish_ci DEFAULT NULL,
   `rel` varchar(255) COLLATE utf8_polish_ci DEFAULT NULL,
   `parent_id` int(11) DEFAULT NULL,
   `activate` varchar(255) COLLATE utf8_polish_ci DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;
SQL
);
