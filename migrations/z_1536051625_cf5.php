<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Run queries
db_query(<<<SQL
CREATE TABLE IF NOT EXISTS `api_keys` ( `id` INT(11) NOT NULL AUTO_INCREMENT , `name` VARCHAR(150) NOT NULL , `Value` VARCHAR(500) NOT NULL , `additional` VARCHAR(250) NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
SQL
);

// Run queries
db_query(<<<SQL
INSERT INTO `api_keys` (`name`, `Value`, `additional`) VALUES ('smsapi', 'ZUxxqXctw7A84H2PSYQShLCB3zmANki5wwZHeLyB', 'Info')
SQL
);

db_query(<<<SQL
INSERT INTO `api_keys` (`name`, `Value`, `additional`) VALUES ('email', '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', 'shadab.arif@ambientinfotech.com')
SQL
);
