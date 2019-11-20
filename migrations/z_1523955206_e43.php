<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Write you code here
//
// You can use
// db_query('some sql');  for quering
// db_pdo()->...;         some pdo functions

db_query(<<<SQL
ALTER TABLE `payments` MODIFY COLUMN `payment_purpose` enum('buy','deposit_balance','increase_user_limit','upgrade_version') NOT NULL AFTER `status`;
SQL
);
