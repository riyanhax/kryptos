<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Activity module schema update

db_query(<<<SQL
ALTER TABLE `acitivity_details` ADD `notes` LONGTEXT NULL AFTER `subject`;
ALTER TABLE `acitivity_details` ADD `time` VARCHAR(50) NULL AFTER `due_date`;
SQL
);