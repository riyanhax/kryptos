<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

db_query(<<<SQL
ALTER TABLE `documenttemplates` ADD `registry_id` INT(11) NULL AFTER `updated_at`;
ALTER TABLE `registry_entries` ADD `osoby_id` INT(11) NOT NULL AFTER `author_id`;
ALTER TABLE `documents` ADD `content` TEXT NULL AFTER `recall_author`;
ALTER TABLE `documents` ADD `new_content` TEXT NULL AFTER `content`;
SQL
);