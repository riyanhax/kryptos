<?php

namespace migrations;

use Application_Model_License as License;
use Application_Model_Osoby as Person;

require_once __DIR__ .'/lib.inc.php';

$pdo = db_pdo();

$pdo->query(<<<SQL
DROP TABLE IF EXISTS licenses;
DROP TABLE IF EXISTS license_subscriptions;
DROP TABLE IF EXISTS license_subscription_activity;
CREATE TABLE `licenses` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL DEFAULT '',
  `period` smallint(6) unsigned NOT NULL,
  `period_unit` tinyint(1) unsigned NOT NULL,
  `trial_period` smallint(6) unsigned NOT NULL,
  `trial_period_unit` tinyint(1) unsigned NOT NULL,
  `user_type` tinyint(1) unsigned NOT NULL,
  `price` smallint(6) unsigned NOT NULL DEFAULT 0,
  `currency` char(3) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `external_id` varchar(255) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY (`status`),
  KEY (`name`)
) ENGINE=InnoDB;
CREATE TABLE `license_subscriptions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `license_id` int(11) unsigned NOT NULL,
  `osoby_id` int(11) unsigned NOT NULL,
  `end_date` timestamp NOT NULL,
  `status` tinyint(4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY (`license_id`),
  KEY (`osoby_id`)
) ENGINE=InnoDB;
CREATE TABLE `license_subscription_activity` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `license_subscription_id` int(11) unsigned NOT NULL,
  `event_type` tinyint(1) unsigned NOT NULL,
  `event_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY (`event_type`),
  KEY (`event_time`)
) ENGINE=InnoDB;
SQL
);


$data = [
    [
        'name' => 'Kryptos72 PRO',
        'description' => 'This is Kryptos72 PRO plan comes with a 14-day trial period and has a setup cost.',
        'status' => License::STATUS_ACTIVATED,
        'period' => 1,
        'period_unit' => License::PERIOD_MONTH,
        'trial_period' => 14,
        'trial_period_unit' => License::PERIOD_DAY,
        'price' => 2000,
        'currency' => License::CURRENCY_USD,
        'external_id' => 'PRO_Plan',
        'user_type' => Person::USER_TYPE_PRO,
    ],
    [
        'name' => 'Kryptos72 Mini',
        'description' => 'This is Kryptos72 MINI plan comes with a 14-day trial period and has a setup cost.',
        'status' => License::STATUS_ACTIVATED,
        'period' => 1,
        'period_unit' => License::PERIOD_MONTH,
        'trial_period' => 14,
        'trial_period_unit' => License::PERIOD_DAY,
        'price' => 1000,
        'currency' => License::CURRENCY_USD,
        'external_id' => 'Mini_Plan',
        'user_type' => Person::USER_TYPE_MINI,
    ],
    [
        'name' => 'Kryptos72 EXPERT',
        'description' => 'This is Kryptos72 Expert plan comes with a 14-day trial period and has a setup cost.',
        'status' => License::STATUS_ACTIVATED,
        'period' => 1,
        'period_unit' => License::PERIOD_MONTH,
        'trial_period' => 14,
        'trial_period_unit' => License::PERIOD_DAY,
        'price' => 3000,
        'currency' => License::CURRENCY_USD,
        'external_id' => 'Expert_Plan',
        'user_type' => Person::USER_TYPE_EXPERT,
    ],
];


$sql = <<<SQL
INSERT INTO licenses SET
  `name` = ?,
  `description` = ?,
  `period` = ?,
  `period_unit` = ?,
  `trial_period` = ?,
  `trial_period_unit` = ?,
  `user_type` = ?,
  `price` = ?,
  `currency` = ?,
  `status` = ?,
  `external_id` = ?,
  `created_at` = ?,
  `updated_at` = ?
SQL;

if (!ini_get('date.timezone')) {
    date_default_timezone_set('GMT');
}

$date = date('Y-m-d H:i:s');

foreach ($data as $subscription) {
    $pdo->prepare($sql)
        ->execute([
            $subscription['name'],
            $subscription['description'],
            $subscription['period'],
            $subscription['period_unit'],
            $subscription['trial_period'],
            $subscription['trial_period_unit'],
            $subscription['user_type'],
            $subscription['price'],
            $subscription['currency'],
            $subscription['status'],
            $subscription['external_id'],
            $date,
            $date,
        ]);
}
