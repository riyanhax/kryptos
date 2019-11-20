<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Flowcharts

db_query(<<<SQL
CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `act_id` varchar(10) DEFAULT NULL,
  `registryid` varchar(10) NOT NULL,
  `value` varchar(30) NOT NULL,
  `created_date` varchar(30) NOT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL
);