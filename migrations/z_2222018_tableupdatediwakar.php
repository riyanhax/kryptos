<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Write you code here
//
// You can use
// db_query('some sql');  for quering
// db_pdo()->...;         some pdo functions

db_query('ALTER TABLE registry_entities ADD `default_value` VARCHAR(100) NULL AFTER `system_name`');
db_query('ALTER TABLE registry_entities ADD `set_primary` INT(11) NOT NULL AFTER `is_multiple`');