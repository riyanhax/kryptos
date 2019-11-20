<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

db_query(<<<SQL
ALTER TABLE `osoby` ADD `month_of_birthday` TINYINT(2) NULL AFTER `telefon_komorkowy`, ADD `day_of_birthday` TINYINT(2) NULL AFTER `month_of_birthday`;
ALTER TABLE `osoby` ADD `assigned_worker_id` INT(11) NULL AFTER `day_of_birthday`;
ALTER TABLE `osoby` ADD `type_of_user` TINYINT(4) NULL AFTER `assigned_worker_id`;
SQL
);
