<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Run queries
db_query(<<<SQL
UPDATE registry_roles SET registry_id=0, id=0 where system_name='	
default_role';
SQL
);
