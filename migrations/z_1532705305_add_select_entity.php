<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

use Application_Model_Entities as EntityModel;

$fieldName = 'select';

$pdo = db_pdo();

$pdo->prepare('INSERT INTO `entities` (`id`, `author_id`, `system_name`, `title`, `config`, `created_at`) VALUES (?, ?, ?, ?, ?, ?)')
    ->execute(array(EntityModel::ID_RELATION_SELECT, 0, $fieldName, 'Select / Checkbox', json_encode(array(
        'type' => $fieldName,
        'element' => array(
            'tag' => 'bs.select',
        ),
    )), '0000-00-00 00:00:00'));
