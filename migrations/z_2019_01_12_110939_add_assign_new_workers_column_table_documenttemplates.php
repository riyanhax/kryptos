<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Write you code here
//
// You can use
// db_query('some sql');  for quering
// db_pdo()->...;         some pdo functions

db_query(<<<SQL
ALTER TABLE `documenttemplates` ADD `assign_new_workers` TINYINT(4)  NULL  DEFAULT '0'  AFTER `signature_required`;
SQL
);
