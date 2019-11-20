<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

db_query("ALTER TABLE error_log ADD COLUMN server_name text;");
