<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Write you code here
//
// You can use
// db_query('some sql');  for quering
// db_pdo()->...;         some pdo functions
db_query(<<<SQL
INSERT INTO role_resources (id_role, id_resource) VALUES
((SELECT id FROM roles WHERE code = 'admin'), (SELECT id FROM resources WHERE module = 'default' AND resource = 'Systemsconfiguration' AND privilege = 'index'));
SQL
);
db_query(<<<SQL
INSERT INTO role_resources (id_role, id_resource) VALUES
((SELECT id FROM roles WHERE code = 'user'), (SELECT id FROM resources WHERE module = 'default' AND resource = 'service' AND privilege = 'heartbeat'));
SQL
);
db_query(<<<SQL
INSERT INTO role_resources (id_role, id_resource) VALUES
((SELECT id FROM roles WHERE code = 'admin'), (SELECT id FROM resources WHERE module = 'default' AND resource = 'Systemconfiguration' AND privilege = 'index'));
SQL
);