<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

db_query('ALTER TABLE registry ADD `steps_mode` TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER `is_visible`');
