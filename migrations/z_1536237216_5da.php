<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Run queries
db_query(<<<SQL
UPDATE osoby SET rights = REPLACE(rights, '\/registry":0', '\/registry":1, "perm\/registry\/all-access":0')
SQL
);
