<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

db_query(<<<SQL
ALTER TABLE documents ADD registry_entry_id int DEFAULT 0 NOT NULL AFTER worker_id;
ALTER TABLE documents_pending ADD registry_entry_id int DEFAULT 0 NOT NULL AFTER worker_id;
SQL
);