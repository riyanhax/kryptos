<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Trigger functions

db_query(<<<SQL
CREATE TABLE IF NOT EXISTS 
`registry_trigger` (
  `id` int(11) NOT NULL COMMENT 'pk',
  `registry_id` int(11) NOT NULL COMMENT 'Registry ID',
  `trigger_name` varchar(100) DEFAULT NULL,
  `cond` int(11) NOT NULL,
  `disable` text NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;
SQL
);
