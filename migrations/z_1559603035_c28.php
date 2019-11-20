<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Run queries
db_query(<<<SQL
ALTER TABLE registry_entries_entities_date ADD ghost boolean DEFAULT false;
SQL
);

db_query(<<<SQL
ALTER TABLE registry_entries_entities_datetime ADD ghost boolean DEFAULT false;
SQL
);

db_query(<<<SQL
ALTER TABLE registry_entries_entities_int ADD ghost boolean DEFAULT false;
SQL
);
db_query(<<<SQL
ALTER TABLE registry_entries_entities_text ADD ghost boolean DEFAULT false;
SQL
);
db_query(<<<SQL
ALTER TABLE `registry_entries_entities_varchar` ADD ghost boolean DEFAULT false;
SQL
);
db_query(<<<SQL
ALTER TABLE registry_entries ADD ghost boolean DEFAULT false;
SQL
);