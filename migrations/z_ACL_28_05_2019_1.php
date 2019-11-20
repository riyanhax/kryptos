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
((SELECT id FROM roles WHERE code = 'user'), (SELECT id FROM resources WHERE module = 'default' AND resource = 'documents' AND privilege = 'pending')),
((SELECT id FROM roles WHERE code = 'user'), (SELECT id FROM resources WHERE module = 'default' AND resource = 'documents' AND privilege = 'index')),
((SELECT id FROM roles WHERE code = 'user'), (SELECT id FROM resources WHERE module = 'default' AND resource = 'documents' AND privilege = 'all')),
((SELECT id FROM roles WHERE code = 'user'), (SELECT id FROM resources WHERE module = 'default' AND resource = 'documenttemplates' AND privilege = 'index')),
((SELECT id FROM roles WHERE code = 'user'), (SELECT id FROM resources WHERE module = 'default' AND resource = 'numberingschemes' AND privilege = 'index')),
((SELECT id FROM roles WHERE code = 'user'), (SELECT id FROM resources WHERE module = 'default' AND resource = 'osoby' AND privilege = 'index')),
((SELECT id FROM roles WHERE code = 'user'), (SELECT id FROM resources WHERE module = 'default' AND resource = 'coursecategories' AND privilege = 'index')),
((SELECT id FROM roles WHERE code = 'user'), (SELECT id FROM resources WHERE module = 'default' AND resource = 'examcategories' AND privilege = 'index')),
((SELECT id FROM roles WHERE code = 'user'), (SELECT id FROM resources WHERE module = 'default' AND resource = 'registryentries' AND privilege = 'index')),
((SELECT id FROM roles WHERE code = 'user'), (SELECT id FROM resources WHERE module = 'default' AND resource = 'csvimport' AND privilege = 'index')),
((SELECT id FROM roles WHERE code = 'user'), (SELECT id FROM resources WHERE module = 'default' AND resource = 'home' AND privilege = 'tickets')),
((SELECT id FROM roles WHERE code = 'user'), (SELECT id FROM resources WHERE module = 'default' AND resource = 'config' AND privilege = 'loginhistory')),
((SELECT id FROM roles WHERE code = 'user'), (SELECT id FROM resources WHERE module = 'default' AND resource = 'osoby' AND privilege = 'addmini')),
((SELECT id FROM roles WHERE code = 'user'), (SELECT id FROM resources WHERE module = 'default' AND resource = 'config' AND privilege = 'logi')),
((SELECT id FROM roles WHERE code = 'user'), (SELECT id FROM resources WHERE module = 'default' AND resource = 'systemsconfiguration' AND privilege = 'index')),
((SELECT id FROM roles WHERE code = 'superadmin'), (SELECT id FROM resources WHERE module = 'default' AND resource = 'licenses' AND privilege = 'managelicensehistory'));
SQL
);