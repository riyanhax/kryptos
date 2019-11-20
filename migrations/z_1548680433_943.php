<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Write you code here
//
// You can use
// db_query('some sql');  for quering
// db_pdo()->...;         some pdo functions

db_query(<<<SQL
INSERT INTO `menu`(`id`, `label`, `path`, `icon`, `rel`, `parent_id`, `activate-routes`) VALUES (NULL, 'Konfiguracja systemu', '/systemsconfiguration', '', '', '228', '');
SQL
);