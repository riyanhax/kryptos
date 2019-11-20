<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Run queries
db_query("ALTER TABLE documents ADD COLUMN id_document_pending int;");
