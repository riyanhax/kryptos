<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

db_query(<<<SQL
DELETE FROM `settings` WHERE `variable` IN ('DATA WPROWADZENIA DOKUMENTACJI', 'DATA OŚWIADCZEŃ/UPOWAŻNIEŃ');
UPDATE `settings` SET `variable` = 'IOD', `value` = 'NIE WYZNACZONO' WHERE `variable` = 'ADO';
INSERT INTO `settings` (`variable`, `value`, `description`, `class`, `fieldset`) VALUES ('KRAJE', '', '', '', 'DANE ORGANIZACJI');
SQL
);
