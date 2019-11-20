<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

db_query(<<<SQL
ALTER TABLE `license_subscriptions` ADD `session_id` VARCHAR(100) NOT NULL AFTER `subscription_price`;
SQL
);
