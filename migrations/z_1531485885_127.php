<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Run queries
db_query(<<<SQL
CREATE TABLE IF NOT EXISTS `document_users` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `document_id` int(11) DEFAULT NULL,
 `user_id` int(11) DEFAULT NULL,
 `created_at` datetime DEFAULT NULL,
 `updated_at` datetime DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;
ALTER TABLE `documents_versioned` ADD `users_type` INT NOT NULL AFTER `title`;
ALTER TABLE `documents_versioned` ADD `send_notification_email` TINYINT NOT NULL AFTER `users_type`;
ALTER TABLE `documents_versioned` CHANGE `send_notification_email` `send_notification_email` TINYINT(4) NULL;
ALTER TABLE `documents_versioned` ADD `send_notification_message` TINYINT NULL AFTER `send_notification_email`;
ALTER TABLE `documents_versioned` CHANGE `users_type` `users_type` INT(11) NULL;
SQL
);
