<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Mail & SMS config functions

db_query(<<<SQL
CREATE TABLE `apiconfiguration` 
( `id` INT(50) NOT NULL AUTO_INCREMENT , 
  `apiurl` VARCHAR(255) NOT NULL , 
  `accesskey` VARCHAR(255) NOT NULL , 
  `username` VARCHAR(100) NOT NULL , 
  `password` VARCHAR(100) NOT NULL , 
   PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;
SQL
);
 