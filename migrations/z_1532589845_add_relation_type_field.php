<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Run queries
db_query(<<<SQL
ALTER TABLE `registry_entries_entities_relations` ADD `relation_type` TINYINT NOT NULL DEFAULT 0 AFTER `registry_entity_id`;
UPDATE `registry_entries_entities_relations` SET `relation_type`=2;
CREATE INDEX `registry_entries_entities_relations_type` ON `registry_entries_entities_relations` (`relation_type`);
SQL
);
