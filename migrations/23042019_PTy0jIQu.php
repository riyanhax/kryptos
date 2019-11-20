<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

$sql = <<<'SQL'
CREATE TABLE error_log (
id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
controller text,
action text,
url text,
error_code int,
error_message text,
error_file text,
error_line int,
stack_trace text,
created_at timestamp,
created_by int,
ghost boolean DEFAULT false
);    
SQL;

db_query($sql);
