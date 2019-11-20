<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Run queries
db_query(<<<SQL
ALTER TABLE registry_entities
ADD stringify boolean DEFAULT 0 AFTER `order`;
SQL
);

db_query(<<<SQL
UPDATE registry_entities AS re, entities AS e 
SET re.stringify=1
WHERE re.entity_id=e.id AND e.system_name='varchar';
SQL
);
