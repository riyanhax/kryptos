<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Run queries
db_query(<<<SQL
ALTER TABLE `osoby` ADD `birth_day` INT(2) NOT NULL AFTER `data_zwolnienia`, ADD `birth_month` INT(2) NOT NULL AFTER `birth_day`;
SQL
);
