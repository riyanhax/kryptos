<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Write you code here
//
// You can use
// db_query('some sql');  for quering
// db_pdo()->...;         some pdo functions

$stm = db_pdo()->prepare(
	"
	INSERT INTO `entities` ( `author_id`, `system_name`, `title`, `config`, `created_at`, `updated_at`) VALUES
( 0, 'surveys', 'Ankiety - zarządzanie', '{\"type\":\"int\",\"baseModel\":\"Surveys\",\"element\":{\"tag\":\"bs.typeahead\",\"url\":\"/surveys/addmini/addmini/?useProcess=true\",\"model\":\"Surveys\"}}', '0000-00-00 00:00:00', NULL)
	"
);
$stm->execute();

db_query(<<<SQL
CREATE TABLE IF NOT EXISTS `systems` (
  `id` int(11) NOT NULL,
  `subdomain` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

SQL
);

