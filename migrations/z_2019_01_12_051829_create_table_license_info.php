<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

db_query(<<<SQL
CREATE TABLE IF NOT EXISTS `license_info` (
`id` int(11) NOT NULL auto_increment,
`name` varchar(255) NOT NULL default '',
`type` varchar(255) NOT NULL default '',
`cost` varchar(100) NOT NULL default '',
`mini_count` int(11) NOT NULL default '0',
`standard_count` int(11) NOT NULL default '0',
`admin_count` int(11) NOT NULL default '0',
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SQL
);

db_query(<<<SQL
INSERT INTO `license_info`(`id`, `name`, `type`, `cost`, `mini_count`, `standard_count`, `admin_count`) VALUES (NULL,'pro','Package','2','','',''), (NULL,'mini','Package','2','','',''), (NULL,'expert','Package','2','','','');
SQL
);
