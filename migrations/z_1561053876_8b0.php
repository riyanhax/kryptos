<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Run queries
db_query("ALTER TABLE registry_entries_entities_relations ADD COLUMN ghost boolean DEFAULT false;");
