<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

$pdo = db_pdo();

$pdo->prepare('INSERT INTO `entities` (`id`, `author_id`, `system_name`, `title`, `config`, `created_at`) VALUES (?, ?, ?, ?, ?, ?)')
    ->execute(array(\Application_Model_Entities::ID_HYPERLINK, 0, 'hyperlink', 'Hyperlink', json_encode([
        'type' => 'hyperlink',
        'element' => ['tag' => 'bs.hyperlink'],
    ]), '0000-00-00 00:00:00'));
