<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Run queries
db_query("ALTER TABLE registry_entries ADD COLUMN ghost boolean DEFAULT false;");
db_query("UPDATE registry_entries SET ghost = true WHERE status_of_worker = 1;");
