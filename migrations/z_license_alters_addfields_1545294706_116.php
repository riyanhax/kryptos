<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Write you code here
//
db_query(<<<SQL
ALTER TABLE `licenses` DROP IF EXISTS expert_count;
ALTER TABLE `licenses` DROP IF EXISTS pro_count;
ALTER TABLE `licenses` DROP IF EXISTS mini_count;
ALTER TABLE `license_subscriptions` DROP IF EXISTS expert_count;
ALTER TABLE `license_subscriptions` DROP IF EXISTS pro_count;
ALTER TABLE `license_subscriptions` DROP IF EXISTS mini_count;
ALTER TABLE `licenses` ADD `expert_count` INT(11)  NULL  DEFAULT '0'  AFTER `external_id`;
ALTER TABLE `licenses` ADD `pro_count` INT(11)  NULL  DEFAULT '0'  AFTER `expert_count`;
ALTER TABLE `licenses` ADD `mini_count` INT(11)  NULL  DEFAULT '0'  AFTER `pro_count`;
ALTER TABLE license_subscriptions ADD `expert_count` INT(11)  NULL DEFAULT '0';
ALTER TABLE license_subscriptions ADD  `pro_count` INT(11)  NULL DEFAULT '0';
ALTER TABLE license_subscriptions ADD  `mini_count` INT(11)  NULL DEFAULT '0';
SQL
);
