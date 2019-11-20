<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Run queries
db_query(<<<SQL
DROP TABLE IF EXISTS `registry_entries_entities_relations`;
DROP TABLE IF EXISTS `registry_entries_entities_relation_items`;
CREATE TABLE `registry_entries_entities_relations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `registry_entity_id` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL, 
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `entry_entity`(`registry_entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE `registry_entries_entities_relation_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `relation_id` int(11) NOT NULL,
  `entry_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `relation_id` (`relation_id`),
  KEY `entry_id`(`entry_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL
);
