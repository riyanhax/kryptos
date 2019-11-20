<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

$pdo = db_pdo();

$pdo->prepare('INSERT INTO `entities` (`id`, `author_id`, `system_name`, `title`, `config`, `created_at`) VALUES (?, ?, ?, ?, ?, ?)')
    ->execute(array(\Application_Model_Entities::ID_MORE_INFO, 0, 'moreInfo', 'More Info', json_encode([
        'type' => 'moreInfo',
        'element' => ['tag' => 'bs.moreInfo'],
    ]), '0000-00-00 00:00:00'));
