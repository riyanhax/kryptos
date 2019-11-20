<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Write you code here
//
// You can use
// db_query('some sql');  for quering
// db_pdo()->...;         some pdo functions
db_query(<<<SQL
CREATE TABLE IF NOT EXISTS `todo_list_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `registry_id` int(11) NOT NULL,
  `registry_title` varchar(255) NOT NULL,
  `taskName` text NOT NULL,
  `complexity` varchar(255) NOT NULL,
  `creationDate` varchar(255) NOT NULL,
  `startDate` varchar(255) NOT NULL,
  `completionDate` varchar(255) NOT NULL,
  `state` varchar(100) NOT NULL,
  `status` int(11) NOT NULL,
  `todo_created_at` varchar(30) NOT NULL,
  `todo_pending_at` varchar(30) NOT NULL,
  `todo_archived_at` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;
SQL
);