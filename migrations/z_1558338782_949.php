<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Write you code here
//
// You can use
// db_query('some sql');  for quering
// db_pdo()->...;         some pdo functions
db_query(<<<SQL
ALTER TABLE `users` ADD  channelid int(11)  NULL  DEFAULT '0'  AFTER `company_confirmation`;
SQL
);
