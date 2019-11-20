<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Write you code here
//
// You can use
// db_query('some sql');  for quering
// db_pdo()->...;         some pdo functions
db_query(<<<SQL
CREATE TABLE IF NOT EXISTS `permission_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `registry_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `registry_entry_id` int(11) NOT NULL,
  `document_pending_id` int(11) NOT NULL,
  `reason_content` text NOT NULL,
  `withdrawal_date_time` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;
SQL
);