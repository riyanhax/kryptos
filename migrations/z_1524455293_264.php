<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Activity Module

db_query(<<<SQL
CREATE TABLE IF NOT EXISTS `acitivity_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_type_id` varchar(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `subject` text,
  `deal` varchar(20) DEFAULT NULL,
  `contact_person` varchar(50) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `assigned_user_id` varchar(50) DEFAULT NULL,
  `organization` varchar(50) DEFAULT NULL,
  `due_date` varchar(50) DEFAULT NULL,
  `created_date` varchar(50) DEFAULT NULL,
  `duration` time DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL
);
