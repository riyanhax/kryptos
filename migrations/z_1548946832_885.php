<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Write you code here
//
// You can use
// db_query('some sql');  for quering
// db_pdo()->...;         some pdo functions
db_query(<<<SQL
DELETE FROM `settings` WHERE `settings`.`id` = 39;
DELETE FROM `settings` WHERE `settings`.`id` = 40;
DELETE FROM `settings` WHERE `settings`.`id` = 41;
DELETE FROM `settings` WHERE `settings`.`id` = 42;
DELETE FROM `settings` WHERE `settings`.`id` = 43;
DELETE FROM `settings` WHERE `settings`.`id` = 44;
DELETE FROM `settings` WHERE `settings`.`id` = 45;
DELETE FROM `settings` WHERE `settings`.`id` = 46;
DELETE FROM `settings` WHERE `settings`.`id` = 47;
DELETE FROM `settings` WHERE `settings`.`id` = 48;
SQL
);