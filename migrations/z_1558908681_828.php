<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Run queries
db_query("ALTER TABLE users MODIFY COLUMN id_role int DEFAULT 2;");
