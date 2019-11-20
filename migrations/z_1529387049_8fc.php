<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Run queries
db_query(<<<SQL
  ALTER TABLE `registry_entities`
  ADD COLUMN `column` int(255) NOT NULL DEFAULT 0 AFTER `config`;
  ALTER TABLE `registry_entities`
  ADD COLUMN `name` varchar(255) NULL AFTER `column`;
SQL
);
