<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Write you code here
// You can use
// db_query('some sql');  for quering
// db_pdo()->...;         some pdo functions
db_query(<<<SQL
      
INSERT INTO role_resources (id_role, id_resource) VALUES
((SELECT id FROM roles WHERE code = 'guest'), (SELECT id FROM resources WHERE module = 'default' AND resource = 'api' AND privilege = 'freetrial'));
SQL
);
db_query(<<<SQL
INSERT INTO role_resources (id_role, id_resource) VALUES
((SELECT id FROM roles WHERE code = 'guest'), (SELECT id FROM resources WHERE module = 'default' AND resource = 'api' AND privilege = 'checktrialemail'));
SQL
);
db_query(<<<SQL
INSERT INTO role_resources (id_role, id_resource) VALUES
((SELECT id FROM roles WHERE code = 'guest'), (SELECT id FROM resources WHERE module = 'default' AND resource = 'api' AND privilege = 'freetrialconfirm'));
SQL
);
db_query(<<<SQL
INSERT INTO role_resources (id_role, id_resource) VALUES
((SELECT id FROM roles WHERE code = 'guest'), (SELECT id FROM resources WHERE module = 'default' AND resource = 'api' AND privilege = 'createadmin'));
SQL
);
db_query(<<<SQL
INSERT INTO role_resources (id_role, id_resource) VALUES
((SELECT id FROM roles WHERE code = 'guest'), (SELECT id FROM resources WHERE module = 'default' AND resource = 'api' AND privilege = 'subscriptioncallback'));
SQL
);
db_query(<<<SQL
INSERT INTO role_resources (id_role, id_resource) VALUES
((SELECT id FROM roles WHERE code = 'guest'), (SELECT id FROM resources WHERE module = 'default' AND resource = 'registers' AND privilege = 'index'));
SQL
);
db_query(<<<SQL
INSERT INTO role_resources (id_role, id_resource) VALUES
((SELECT id FROM roles WHERE code = 'guest'), (SELECT id FROM resources WHERE module = 'default' AND resource = 'registers' AND privilege = 'save'));
SQL
);
db_query(<<<SQL
INSERT INTO role_resources (id_role, id_resource) VALUES
((SELECT id FROM roles WHERE code = 'guest'), (SELECT id FROM resources WHERE module = 'default' AND resource = 'activation' AND privilege = 'index'));
SQL
);
db_query(<<<SQL
INSERT INTO role_resources (id_role, id_resource) VALUES
((SELECT id FROM roles WHERE code = 'guest'), (SELECT id FROM resources WHERE module = 'default' AND resource = 'activation' AND privilege = 'afterdeploy'));
SQL
);