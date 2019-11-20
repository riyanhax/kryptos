<?php

namespace migrations;

if (!defined('__DIR__')) {
   define('__DIR__', dirname(__FILE__));
}

require_once __DIR__ .'/lib.inc.php';

// Create New Table 'registry_filters'

db_query(<<<SQL
CREATE TABLE IF NOT EXISTS `registry_filters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `meta_content` longtext CHARACTER SET utf8,
  `user_id` int(10) DEFAULT NULL,
  `filter_name` varchar(255) DEFAULT NULL,
  `filter_score` varchar(255) DEFAULT NULL,
  `created_at` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL
);
