<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

use Application_Model_Entities as EntityModel;

$pdo = db_pdo();

$pdo->prepare('INSERT INTO `entities` (`id`, `author_id`, `system_name`, `title`, `config`, `created_at`) VALUES (?, ?, ?, ?, ?, ?)')
    ->execute(array(EntityModel::ID_SMART_RADIO, 0, 'smartRadioGroup', 'Smart Radio Group', json_encode([
        'type' => 'smartRadioGroup',
        'element' => ['tag' => 'bs.smartRadioGroup'],
    ]), '0000-00-00 00:00:00'));
