<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Write you code here
//
// You can use
// db_query('some sql');  for quering
// db_pdo()->...;         some pdo functions
db_query(<<<SQL
INSERT INTO `resources` (`id`, `module`, `resource`, `privilege`, `created_at`, `created_by`, `ghost`) VALUES (NULL, 'default', 'registryentries', 'bulkactions', CURRENT_TIMESTAMP, NULL, '0');
SQL
);
db_query(<<<SQL
INSERT INTO role_resources (id_role, id_resource) VALUES
((SELECT id FROM roles WHERE code = 'user'), (SELECT id FROM resources WHERE module = 'default' AND resource = 'registryentries' AND privilege = 'bulks'));
SQL
);
db_query(<<<SQL
INSERT INTO role_resources (id_role, id_resource) VALUES
((SELECT id FROM roles WHERE code = 'user'), (SELECT id FROM resources WHERE module = 'default' AND resource = 'registryentries' AND privilege = 'bulkactions'));
SQL
);