<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Run queries
db_query(<<<SQL
ALTER TABLE `admin_link` ADD `count_mini` INT(25) NOT NULL AFTER `type`, ADD `count_pro` INT(25) NOT NULL AFTER `count_mini`, ADD `count_expert` INT(25) NOT NULL AFTER `count_pro`;
SQL
);

db_query(<<<SQL
ALTER TABLE `type_rights` CHANGE `rights` `rights` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
SQL
);
