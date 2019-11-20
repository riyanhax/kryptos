<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Run queries
db_query(<<<SQL
DROP TABLE `documents_versioned`;	
CREATE TABLE `documents_versioned` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `title` varchar(255) DEFAULT NULL,
 `status` tinyint(3) unsigned DEFAULT NULL,
 `created_at` datetime DEFAULT NULL,
 `updated_at` datetime DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE `documents_versioned_versions`;
CREATE TABLE `documents_versioned_versions` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `status` int(11) DEFAULT NULL,
 `document_id` int(11) DEFAULT NULL,
 `version` varchar(255) DEFAULT NULL,
 `date_from` date DEFAULT NULL,
 `date_to` date DEFAULT NULL,
 `file_id` int(11) DEFAULT NULL,
 `author_id` int(11) DEFAULT NULL,
 `authorize_user_id` int(11) DEFAULT NULL,
 `authorize_date` date DEFAULT NULL,
 `version_description` varchar(255) DEFAULT NULL,
 `version_legal_basis` varchar(255) DEFAULT NULL,
 `created_at` datetime DEFAULT NULL,
 `updated_at` datetime DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

SQL
);
