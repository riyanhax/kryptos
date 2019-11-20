<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

use Application_Model_Entities as EntityModel;

$pdo = db_pdo();

$pdo->prepare('UPDATE `entities` SET `config`=? WHERE `id`=?')
    ->execute([
        json_encode([
            'type' => 'text',
            'baseModel' => 'RegistryEntriesEntitiesText',
            'element' => ['tag' => 'bs.texthtml', 'class' => 'ckeditor-default'],
        ]),
        EntityModel::ID_TEXT_AREA,
    ]);
