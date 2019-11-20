<?php 
 
namespace migrations; 
 
require_once __DIR__ .'/lib.inc.php'; 
 
// Run queries 
db_query(<<<SQL
CREATE TABLE IF NOT EXISTS `registry_group_assignees` ( 
  `id` int(11) NOT NULL AUTO_INCREMENT, 
  `registry_id` int(11) NOT NULL, 
  `group_id` int(11) NOT NULL, 
  `registry_role_id` int(11) NOT NULL, 
  `created_at` datetime NOT NULL, 
  `updated_at` datetime DEFAULT NULL, 
  PRIMARY KEY (`id`) 
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci ROW_FORMAT=COMPACT AUTO_INCREMENT=1 ; 

CREATE TABLE IF NOT EXISTS `registry_user_group_permissions` ( 
  `id` int(11) NOT NULL AUTO_INCREMENT, 
  `registry_id` int(11) NOT NULL, 
  `group_id` int(11) NOT NULL, 
  `registry_permission_id` int(11) NOT NULL, 
  `created_at` datetime NOT NULL, 
  `updated_at` datetime DEFAULT NULL, 
  PRIMARY KEY (`id`) 
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci ROW_FORMAT=COMPACT AUTO_INCREMENT=1 ; 

ALTER TABLE users ADD recovery_key VARCHAR( 128 ) NULL AFTER login_count ;
SQL
);