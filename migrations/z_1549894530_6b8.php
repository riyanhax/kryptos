<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Write you code here
//
// You can use
// db_query('some sql');  for quering
// db_pdo()->...;         some pdo functions
db_query(<<<SQL
ALTER TABLE settings MODIFY variable TEXT;
UPDATE settings SET variable='Imię' WHERE id = 29;
UPDATE settings SET variable='Nazwisko' WHERE id = 30;
UPDATE settings SET variable='Email' WHERE id = 31;
UPDATE settings SET variable='Telefon' WHERE id = 32;
UPDATE settings SET variable='Kraj' WHERE id = 33;
UPDATE settings SET variable='Pełna nazwa firmy' WHERE id = 34;
UPDATE settings SET variable='Adres' WHERE id = 35;
UPDATE settings SET variable='Numer identyfikacji podatkowej' WHERE id = 36;
UPDATE settings SET variable='Potwierdzam zawarcie umowy w wersji elektronicznej poprzez akceptację Regulaminu świadczeniu usługi Kryptos72 oraz Regulaminu powierzenia danych osobowych' WHERE id = 37;
UPDATE settings SET variable='Wyrażam zgodę na przetwarzanie moich danych osobowych przez Usługodawcę w celu otrzymywania aktualności o Kryptos72za pomocą automatycznych wiadomości e-mail, zgodnie z art. 10 ust. 2 ustawy o świadczeniu usług drogą elektroniczną oraz art. 172 ust. 1 ustawy Prawo telekomunikacyjne' WHERE id = 38;
INSERT INTO settings (id, variable, value, description, class, fieldset) VALUES ('39','Logo','/assets/images/logoKrypto.png','','file','Informacje o firmie');
SQL
);