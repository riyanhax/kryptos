<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Run queries
db_query(<<<SQL
CREATE TABLE IF NOT EXISTS  `notification_control` (
  `id` int(10) NOT NULL,
  `user_id` int(11) NOT NULL,
  `task_email` tinyint(1) NOT NULL DEFAULT '1',
  `task_sms` tinyint(1) NOT NULL DEFAULT '1',
  `activity_email` tinyint(1) NOT NULL DEFAULT '1',
  `activity_sms` tinyint(1) NOT NULL DEFAULT '1',
  `tickets_email` tinyint(1) NOT NULL DEFAULT '1',
  `tickets_sms` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL
);

db_query(<<<SQL
ALTER TABLE  `notification_control`
  ADD PRIMARY KEY (`id`);
SQL
);

db_query(<<<SQL
ALTER TABLE `notification_control`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
SQL
);
