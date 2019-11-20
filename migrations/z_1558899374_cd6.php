<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Run queries
db_query(<<<'SQL'
INSERT INTO role_resources (id_role, id_resource) VALUES
((SELECT id FROM roles WHERE code = 'user'), (SELECT id FROM resources WHERE module = 'default' AND resource = 'service' AND privilege = 'heartbeat'));
SQL
);

db_query(<<<'SQL'
INSERT INTO role_resources (id_role, id_resource)
SELECT
(SELECT id FROM roles WHERE code = 'user'), 
r.id
FROM resources r
WHERE r.resource = 'ajax';
SQL
);
