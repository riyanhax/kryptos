<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Write you code here
//
// You can use
// db_query('some sql');  for quering
// db_pdo()->...;         some pdo functions
// db_query(<<<SQL
// CREATE TABLE IF NOT EXISTS `deleted_worker_lists` (
// `id` int(11) NOT NULL auto_increment,
// `registry_id` int(11) NOT NULL,
// `worker_id` int(11) NOT NULL,
// 'selected_permission_entry_id' int(11) NOT NULL,
// `worker_name` varchar(255) NOT NULL default '',
// `worker_surname` varchar(255) NOT NULL default '',
// PRIMARY KEY (`id`)
// ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
// SQL
// );