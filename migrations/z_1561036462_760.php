<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Run queries
db_query("INSERT INTO role_resources (id_role, id_resource) VALUES ((SELECT id FROM roles WHERE code = 'user'), (SELECT id FROM resources WHERE module = 'default' AND resource = 'home' AND privilege = 'index'));");
