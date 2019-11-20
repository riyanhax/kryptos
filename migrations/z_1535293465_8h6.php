<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

db_query(<<<SQL
ALTER TABLE `documenttemplatesosoby` ADD `worker_id` INT(11) NOT NULL AFTER `osoba_id`;
ALTER TABLE `documents` ADD `worker_id` INT(11) NOT NULL AFTER `osoba_id`;
ALTER TABLE `documents_pending` ADD `worker_id` INT(11) NOT NULL AFTER `user_id`;
ALTER TABLE `registry_entries` ADD `worker_id` INT(11) NOT NULL AFTER `osoby_id`;
SQL
);
