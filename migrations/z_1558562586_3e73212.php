<?php
namespace migrations;
require_once __DIR__ .'/lib.inc.php';
db_query(<<<'SQL'
INSERT INTO settings (variable, value, class, fieldset) VALUES
('Logo', '/assets/images/logoKrypto.png', 'file', 'Informacje o firmie');
SQL
);
?>