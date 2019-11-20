<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Write you code here
//
// You can use
// db_query('some sql');  for quering
// db_pdo()->...;         some pdo functions
db_query(<<<SQL
INSERT INTO `settings` (`id`, `variable`, `value`, `description`, `class`, `fieldset`) VALUES (NULL, 'Name', '', '', '', 'Informacje o firmie'), (NULL, 'Surname', '', '', '', 'Informacje o firmie'), (NULL, 'Email', '', '', '', 'Informacje o firmie'), (NULL, 'Phone', '', '', '', 'Informacje o firmie'), (NULL, 'Country', '', '', 'selectbox', 'Informacje o firmie'), (NULL, 'Name of company', '', '', '', 'Informacje o firmie'), (NULL, 'Adresses', '', '', '', 'Informacje o firmie'), (NULL, 'VAT Number', '', '', '', 'Informacje o firmie'), (NULL, 'Confirm agreement checkbox', '', '', 'checkbox', 'Informacje o firmie'), (NULL, 'Confirm marketing rules checkbox', '', '', 'checkbox', 'Informacje o firmie');
ALTER TABLE `users` ADD `company_confirmation` INT(11)  NULL  DEFAULT '0'  AFTER `recovery_key`;
INSERT INTO `licenses` (`id`, `name`, `description`, `period`, `period_unit`, `trial_period`, `trial_period_unit`, `user_type`, `price`, `currency`, `status`, `is_trial`, `external_id`, `expert_count`, `pro_count`, `mini_count`, `created_at`, `updated_at`) VALUES (NULL, 'Free Trial', 'Free Trial for 14 days', 1, 2, 14, 4, 1, 0, 'USD', 1, 1, 'Free_Trial', 2, 3, 4, '2018-12-18 13:09:59', '2018-12-18 14:05:31');
SQL
);