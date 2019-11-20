#!/usr/bin/env php
<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

chdir(__DIR__);
$subname = isset($argv[1])?$argv[1]:null;
$name = new_migration($subname);

file_put_contents($name, <<<TEMPLATE
<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Write you code here
//
// You can use
// db_query('some sql');  for quering
// db_pdo()->...;         some pdo functions

TEMPLATE
);

printf("New migration %s\n", $name);
