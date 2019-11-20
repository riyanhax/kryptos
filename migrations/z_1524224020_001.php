<?php
/**
* Alter meta_content filed of 'registry_filters table
**/
namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Trigger functions

db_query(<<<SQL
ALTER TABLE `registry_filters` CHANGE `meta_content` `meta_content` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
SQL
);
?>