<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

use Application_Model_Entities as EntityModel;

$pdo = db_pdo();

$pdo->prepare('INSERT INTO `entities` (`id`, `author_id`, `system_name`, `title`, `config`, `created_at`) VALUES (?, ?, ?, ?, ?, ?)')
    ->execute(array(EntityModel::ID_SMART_MULTI_SELECT, 0, 'smartMultiSelect', 'Smart Multi Select', json_encode([
        'type' => 'smartMultiSelect',
        'element' => ['tag' => 'bs.smartMultiSelect'],
    ]), '0000-00-00 00:00:00'));
