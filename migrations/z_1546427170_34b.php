<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Write you code here
//
// You can use
// db_query('some sql');  for quering
// db_pdo()->...;         some pdo functions

db_query(<<<SQL
CREATE TABLE `countries` (
`id` int(11) NOT NULL auto_increment,
`country_code` varchar(2) NOT NULL default '',
`country_name` varchar(100) NOT NULL default '',
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SQL
);

db_query(<<<SQL
INSERT INTO `countries` VALUES (null, 'AF', 'Afghanistan');
INSERT INTO `countries` VALUES (null, 'AL', 'Albania');
INSERT INTO `countries` VALUES (null, 'DZ', 'Algeria');
INSERT INTO `countries` VALUES (null, 'DS', 'American Samoa');
INSERT INTO `countries` VALUES (null, 'AD', 'Andorra');
INSERT INTO `countries` VALUES (null, 'AO', 'Angola');
INSERT INTO `countries` VALUES (null, 'AI', 'Anguilla');
INSERT INTO `countries` VALUES (null, 'AQ', 'Antarctica');
INSERT INTO `countries` VALUES (null, 'AG', 'Antigua and Barbuda');
INSERT INTO `countries` VALUES (null, 'AR', 'Argentina');
INSERT INTO `countries` VALUES (null, 'AM', 'Armenia');
INSERT INTO `countries` VALUES (null, 'AW', 'Aruba');
INSERT INTO `countries` VALUES (null, 'AU', 'Australia');
INSERT INTO `countries` VALUES (null, 'AT', 'Austria');
INSERT INTO `countries` VALUES (null, 'AZ', 'Azerbaijan');
INSERT INTO `countries` VALUES (null, 'BS', 'Bahamas');
INSERT INTO `countries` VALUES (null, 'BH', 'Bahrain');
INSERT INTO `countries` VALUES (null, 'BD', 'Bangladesh');
INSERT INTO `countries` VALUES (null, 'BB', 'Barbados');
INSERT INTO `countries` VALUES (null, 'BY', 'Belarus');
INSERT INTO `countries` VALUES (null, 'BE', 'Belgium');
INSERT INTO `countries` VALUES (null, 'BZ', 'Belize');
INSERT INTO `countries` VALUES (null, 'BJ', 'Benin');
INSERT INTO `countries` VALUES (null, 'BM', 'Bermuda');
INSERT INTO `countries` VALUES (null, 'BT', 'Bhutan');
INSERT INTO `countries` VALUES (null, 'BO', 'Bolivia');
INSERT INTO `countries` VALUES (null, 'BA', 'Bosnia and Herzegovina');
INSERT INTO `countries` VALUES (null, 'BW', 'Botswana');
INSERT INTO `countries` VALUES (null, 'BV', 'Bouvet Island');
INSERT INTO `countries` VALUES (null, 'BR', 'Brazil');
INSERT INTO `countries` VALUES (null, 'IO', 'British Indian Ocean Territory');
INSERT INTO `countries` VALUES (null, 'BN', 'Brunei Darussalam');
INSERT INTO `countries` VALUES (null, 'BG', 'Bulgaria');
INSERT INTO `countries` VALUES (null, 'BF', 'Burkina Faso');
INSERT INTO `countries` VALUES (null, 'BI', 'Burundi');
INSERT INTO `countries` VALUES (null, 'KH', 'Cambodia');
INSERT INTO `countries` VALUES (null, 'CM', 'Cameroon');
INSERT INTO `countries` VALUES (null, 'CA', 'Canada');
INSERT INTO `countries` VALUES (null, 'CV', 'Cape Verde');
INSERT INTO `countries` VALUES (null, 'KY', 'Cayman Islands');
INSERT INTO `countries` VALUES (null, 'CF', 'Central African Republic');
INSERT INTO `countries` VALUES (null, 'TD', 'Chad');
INSERT INTO `countries` VALUES (null, 'CL', 'Chile');
INSERT INTO `countries` VALUES (null, 'CN', 'China');
INSERT INTO `countries` VALUES (null, 'CX', 'Christmas Island');
INSERT INTO `countries` VALUES (null, 'CC', 'Cocos (Keeling) Islands');
INSERT INTO `countries` VALUES (null, 'CO', 'Colombia');
INSERT INTO `countries` VALUES (null, 'KM', 'Comoros');
INSERT INTO `countries` VALUES (null, 'CG', 'Congo');
INSERT INTO `countries` VALUES (null, 'CK', 'Cook Islands');
INSERT INTO `countries` VALUES (null, 'CR', 'Costa Rica');
INSERT INTO `countries` VALUES (null, 'HR', 'Croatia (Hrvatska)');
INSERT INTO `countries` VALUES (null, 'CU', 'Cuba');
INSERT INTO `countries` VALUES (null, 'CY', 'Cyprus');
INSERT INTO `countries` VALUES (null, 'CZ', 'Czech Republic');
INSERT INTO `countries` VALUES (null, 'DK', 'Denmark');
INSERT INTO `countries` VALUES (null, 'DJ', 'Djibouti');
INSERT INTO `countries` VALUES (null, 'DM', 'Dominica');
INSERT INTO `countries` VALUES (null, 'DO', 'Dominican Republic');
INSERT INTO `countries` VALUES (null, 'TP', 'East Timor');
INSERT INTO `countries` VALUES (null, 'EC', 'Ecuador');
INSERT INTO `countries` VALUES (null, 'EG', 'Egypt');
INSERT INTO `countries` VALUES (null, 'SV', 'El Salvador');
INSERT INTO `countries` VALUES (null, 'GQ', 'Equatorial Guinea');
INSERT INTO `countries` VALUES (null, 'ER', 'Eritrea');
INSERT INTO `countries` VALUES (null, 'EE', 'Estonia');
INSERT INTO `countries` VALUES (null, 'ET', 'Ethiopia');
INSERT INTO `countries` VALUES (null, 'FK', 'Falkland Islands (Malvinas)');
INSERT INTO `countries` VALUES (null, 'FO', 'Faroe Islands');
INSERT INTO `countries` VALUES (null, 'FJ', 'Fiji');
INSERT INTO `countries` VALUES (null, 'FI', 'Finland');
INSERT INTO `countries` VALUES (null, 'FR', 'France');
INSERT INTO `countries` VALUES (null, 'FX', 'France, Metropolitan');
INSERT INTO `countries` VALUES (null, 'GF', 'French Guiana');
INSERT INTO `countries` VALUES (null, 'PF', 'French Polynesia');
INSERT INTO `countries` VALUES (null, 'TF', 'French Southern Territories');
INSERT INTO `countries` VALUES (null, 'GA', 'Gabon');
INSERT INTO `countries` VALUES (null, 'GM', 'Gambia');
INSERT INTO `countries` VALUES (null, 'GE', 'Georgia');
INSERT INTO `countries` VALUES (null, 'DE', 'Germany');
INSERT INTO `countries` VALUES (null, 'GH', 'Ghana');
INSERT INTO `countries` VALUES (null, 'GI', 'Gibraltar');
INSERT INTO `countries` VALUES (null, 'GK', 'Guernsey');
INSERT INTO `countries` VALUES (null, 'GR', 'Greece');
INSERT INTO `countries` VALUES (null, 'GL', 'Greenland');
INSERT INTO `countries` VALUES (null, 'GD', 'Grenada');
INSERT INTO `countries` VALUES (null, 'GP', 'Guadeloupe');
INSERT INTO `countries` VALUES (null, 'GU', 'Guam');
INSERT INTO `countries` VALUES (null, 'GT', 'Guatemala');
INSERT INTO `countries` VALUES (null, 'GN', 'Guinea');
INSERT INTO `countries` VALUES (null, 'GW', 'Guinea-Bissau');
INSERT INTO `countries` VALUES (null, 'GY', 'Guyana');
INSERT INTO `countries` VALUES (null, 'HT', 'Haiti');
INSERT INTO `countries` VALUES (null, 'HM', 'Heard and Mc Donald Islands');
INSERT INTO `countries` VALUES (null, 'HN', 'Honduras');
INSERT INTO `countries` VALUES (null, 'HK', 'Hong Kong');
INSERT INTO `countries` VALUES (null, 'HU', 'Hungary');
INSERT INTO `countries` VALUES (null, 'IS', 'Iceland');
INSERT INTO `countries` VALUES (null, 'IN', 'India');
INSERT INTO `countries` VALUES (null, 'IM', 'Isle of Man');
INSERT INTO `countries` VALUES (null, 'ID', 'Indonesia');
INSERT INTO `countries` VALUES (null, 'IR', 'Iran (Islamic Republic of)');
INSERT INTO `countries` VALUES (null, 'IQ', 'Iraq');
INSERT INTO `countries` VALUES (null, 'IE', 'Ireland');
INSERT INTO `countries` VALUES (null, 'IL', 'Israel');
INSERT INTO `countries` VALUES (null, 'IT', 'Italy');
INSERT INTO `countries` VALUES (null, 'CI', 'Ivory Coast');
INSERT INTO `countries` VALUES (null, 'JE', 'Jersey');
INSERT INTO `countries` VALUES (null, 'JM', 'Jamaica');
INSERT INTO `countries` VALUES (null, 'JP', 'Japan');
INSERT INTO `countries` VALUES (null, 'JO', 'Jordan');
INSERT INTO `countries` VALUES (null, 'KZ', 'Kazakhstan');
INSERT INTO `countries` VALUES (null, 'KE', 'Kenya');
INSERT INTO `countries` VALUES (null, 'KI', 'Kiribati');
INSERT INTO `countries` VALUES (null, 'KP', 'Korea, Democratic People''s Republic of');
INSERT INTO `countries` VALUES (null, 'KR', 'Korea, Republic of');
INSERT INTO `countries` VALUES (null, 'XK', 'Kosovo');
INSERT INTO `countries` VALUES (null, 'KW', 'Kuwait');
INSERT INTO `countries` VALUES (null, 'KG', 'Kyrgyzstan');
INSERT INTO `countries` VALUES (null, 'LA', 'Lao People''s Democratic Republic');
INSERT INTO `countries` VALUES (null, 'LV', 'Latvia');
INSERT INTO `countries` VALUES (null, 'LB', 'Lebanon');
INSERT INTO `countries` VALUES (null, 'LS', 'Lesotho');
INSERT INTO `countries` VALUES (null, 'LR', 'Liberia');
INSERT INTO `countries` VALUES (null, 'LY', 'Libyan Arab Jamahiriya');
INSERT INTO `countries` VALUES (null, 'LI', 'Liechtenstein');
INSERT INTO `countries` VALUES (null, 'LT', 'Lithuania');
INSERT INTO `countries` VALUES (null, 'LU', 'Luxembourg');
INSERT INTO `countries` VALUES (null, 'MO', 'Macau');
INSERT INTO `countries` VALUES (null, 'MK', 'Macedonia');
INSERT INTO `countries` VALUES (null, 'MG', 'Madagascar');
INSERT INTO `countries` VALUES (null, 'MW', 'Malawi');
INSERT INTO `countries` VALUES (null, 'MY', 'Malaysia');
INSERT INTO `countries` VALUES (null, 'MV', 'Maldives');
INSERT INTO `countries` VALUES (null, 'ML', 'Mali');
INSERT INTO `countries` VALUES (null, 'MT', 'Malta');
INSERT INTO `countries` VALUES (null, 'MH', 'Marshall Islands');
INSERT INTO `countries` VALUES (null, 'MQ', 'Martinique');
INSERT INTO `countries` VALUES (null, 'MR', 'Mauritania');
INSERT INTO `countries` VALUES (null, 'MU', 'Mauritius');
INSERT INTO `countries` VALUES (null, 'TY', 'Mayotte');
INSERT INTO `countries` VALUES (null, 'MX', 'Mexico');
INSERT INTO `countries` VALUES (null, 'FM', 'Micronesia, Federated States of');
INSERT INTO `countries` VALUES (null, 'MD', 'Moldova, Republic of');
INSERT INTO `countries` VALUES (null, 'MC', 'Monaco');
INSERT INTO `countries` VALUES (null, 'MN', 'Mongolia');
INSERT INTO `countries` VALUES (null, 'ME', 'Montenegro');
INSERT INTO `countries` VALUES (null, 'MS', 'Montserrat');
INSERT INTO `countries` VALUES (null, 'MA', 'Morocco');
INSERT INTO `countries` VALUES (null, 'MZ', 'Mozambique');
INSERT INTO `countries` VALUES (null, 'MM', 'Myanmar');
INSERT INTO `countries` VALUES (null, 'NA', 'Namibia');
INSERT INTO `countries` VALUES (null, 'NR', 'Nauru');
INSERT INTO `countries` VALUES (null, 'NP', 'Nepal');
INSERT INTO `countries` VALUES (null, 'NL', 'Netherlands');
INSERT INTO `countries` VALUES (null, 'AN', 'Netherlands Antilles');
INSERT INTO `countries` VALUES (null, 'NC', 'New Caledonia');
INSERT INTO `countries` VALUES (null, 'NZ', 'New Zealand');
INSERT INTO `countries` VALUES (null, 'NI', 'Nicaragua');
INSERT INTO `countries` VALUES (null, 'NE', 'Niger');
INSERT INTO `countries` VALUES (null, 'NG', 'Nigeria');
INSERT INTO `countries` VALUES (null, 'NU', 'Niue');
INSERT INTO `countries` VALUES (null, 'NF', 'Norfolk Island');
INSERT INTO `countries` VALUES (null, 'MP', 'Northern Mariana Islands');
INSERT INTO `countries` VALUES (null, 'NO', 'Norway');
INSERT INTO `countries` VALUES (null, 'OM', 'Oman');
INSERT INTO `countries` VALUES (null, 'PK', 'Pakistan');
INSERT INTO `countries` VALUES (null, 'PW', 'Palau');
INSERT INTO `countries` VALUES (null, 'PS', 'Palestine');
INSERT INTO `countries` VALUES (null, 'PA', 'Panama');
INSERT INTO `countries` VALUES (null, 'PG', 'Papua New Guinea');
INSERT INTO `countries` VALUES (null, 'PY', 'Paraguay');
INSERT INTO `countries` VALUES (null, 'PE', 'Peru');
INSERT INTO `countries` VALUES (null, 'PH', 'Philippines');
INSERT INTO `countries` VALUES (null, 'PN', 'Pitcairn');
INSERT INTO `countries` VALUES (null, 'PL', 'Poland');
INSERT INTO `countries` VALUES (null, 'PT', 'Portugal');
INSERT INTO `countries` VALUES (null, 'PR', 'Puerto Rico');
INSERT INTO `countries` VALUES (null, 'QA', 'Qatar');
INSERT INTO `countries` VALUES (null, 'RE', 'Reunion');
INSERT INTO `countries` VALUES (null, 'RO', 'Romania');
INSERT INTO `countries` VALUES (null, 'RU', 'Russian Federation');
INSERT INTO `countries` VALUES (null, 'RW', 'Rwanda');
INSERT INTO `countries` VALUES (null, 'KN', 'Saint Kitts and Nevis');
INSERT INTO `countries` VALUES (null, 'LC', 'Saint Lucia');
INSERT INTO `countries` VALUES (null, 'VC', 'Saint Vincent and the Grenadines');
INSERT INTO `countries` VALUES (null, 'WS', 'Samoa');
INSERT INTO `countries` VALUES (null, 'SM', 'San Marino');
INSERT INTO `countries` VALUES (null, 'ST', 'Sao Tome and Principe');
INSERT INTO `countries` VALUES (null, 'SA', 'Saudi Arabia');
INSERT INTO `countries` VALUES (null, 'SN', 'Senegal');
INSERT INTO `countries` VALUES (null, 'RS', 'Serbia');
INSERT INTO `countries` VALUES (null, 'SC', 'Seychelles');
INSERT INTO `countries` VALUES (null, 'SL', 'Sierra Leone');
INSERT INTO `countries` VALUES (null, 'SG', 'Singapore');
INSERT INTO `countries` VALUES (null, 'SK', 'Slovakia');
INSERT INTO `countries` VALUES (null, 'SI', 'Slovenia');
INSERT INTO `countries` VALUES (null, 'SB', 'Solomon Islands');
INSERT INTO `countries` VALUES (null, 'SO', 'Somalia');
INSERT INTO `countries` VALUES (null, 'ZA', 'South Africa');
INSERT INTO `countries` VALUES (null, 'GS', 'South Georgia South Sandwich Islands');
INSERT INTO `countries` VALUES (null, 'SS', 'South Sudan');
INSERT INTO `countries` VALUES (null, 'ES', 'Spain');
INSERT INTO `countries` VALUES (null, 'LK', 'Sri Lanka');
INSERT INTO `countries` VALUES (null, 'SH', 'St. Helena');
INSERT INTO `countries` VALUES (null, 'PM', 'St. Pierre and Miquelon');
INSERT INTO `countries` VALUES (null, 'SD', 'Sudan');
INSERT INTO `countries` VALUES (null, 'SR', 'Suriname');
INSERT INTO `countries` VALUES (null, 'SJ', 'Svalbard and Jan Mayen Islands');
INSERT INTO `countries` VALUES (null, 'SZ', 'Swaziland');
INSERT INTO `countries` VALUES (null, 'SE', 'Sweden');
INSERT INTO `countries` VALUES (null, 'CH', 'Switzerland');
INSERT INTO `countries` VALUES (null, 'SY', 'Syrian Arab Republic');
INSERT INTO `countries` VALUES (null, 'TW', 'Taiwan');
INSERT INTO `countries` VALUES (null, 'TJ', 'Tajikistan');
INSERT INTO `countries` VALUES (null, 'TZ', 'Tanzania, United Republic of');
INSERT INTO `countries` VALUES (null, 'TH', 'Thailand');
INSERT INTO `countries` VALUES (null, 'TG', 'Togo');
INSERT INTO `countries` VALUES (null, 'TK', 'Tokelau');
INSERT INTO `countries` VALUES (null, 'TO', 'Tonga');
INSERT INTO `countries` VALUES (null, 'TT', 'Trinidad and Tobago');
INSERT INTO `countries` VALUES (null, 'TN', 'Tunisia');
INSERT INTO `countries` VALUES (null, 'TR', 'Turkey');
INSERT INTO `countries` VALUES (null, 'TM', 'Turkmenistan');
INSERT INTO `countries` VALUES (null, 'TC', 'Turks and Caicos Islands');
INSERT INTO `countries` VALUES (null, 'TV', 'Tuvalu');
INSERT INTO `countries` VALUES (null, 'UG', 'Uganda');
INSERT INTO `countries` VALUES (null, 'UA', 'Ukraine');
INSERT INTO `countries` VALUES (null, 'AE', 'United Arab Emirates');
INSERT INTO `countries` VALUES (null, 'GB', 'United Kingdom');
INSERT INTO `countries` VALUES (null, 'US', 'United States');
INSERT INTO `countries` VALUES (null, 'UM', 'United States minor outlying islands');
INSERT INTO `countries` VALUES (null, 'UY', 'Uruguay');
INSERT INTO `countries` VALUES (null, 'UZ', 'Uzbekistan');
INSERT INTO `countries` VALUES (null, 'VU', 'Vanuatu');
INSERT INTO `countries` VALUES (null, 'VA', 'Vatican City State');
INSERT INTO `countries` VALUES (null, 'VE', 'Venezuela');
INSERT INTO `countries` VALUES (null, 'VN', 'Vietnam');
INSERT INTO `countries` VALUES (null, 'VG', 'Virgin Islands (British)');
INSERT INTO `countries` VALUES (null, 'VI', 'Virgin Islands (U.S.)');
INSERT INTO `countries` VALUES (null, 'WF', 'Wallis and Futuna Islands');
INSERT INTO `countries` VALUES (null, 'EH', 'Western Sahara');
INSERT INTO `countries` VALUES (null, 'YE', 'Yemen');
INSERT INTO `countries` VALUES (null, 'ZR', 'Zaire');
INSERT INTO `countries` VALUES (null, 'ZM', 'Zambia');
INSERT INTO `countries` VALUES (null, 'ZW', 'Zimbabwe');
SQL
);

db_query(<<<SQL
CREATE TABLE `license_info` (
`id` int(11) NOT NULL auto_increment,
`name` varchar(255) NOT NULL default '',
`type` varchar(255) NOT NULL default '',
`cost` varchar(100) NOT NULL default '',
`mini_count` int(11) NOT NULL default '0',
`standard_count` int(11) NOT NULL default '0',
`admin_count` int(11) NOT NULL default '0',
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SQL
);

db_query(<<<SQL
INSERT INTO `license_info`(`id`, `name`, `type`, `cost`, `mini_count`, `standard_count`, `admin_count`) VALUES (NULL,'pro','Package','2','','',''), (NULL,'mini','Package','2','','',''), (NULL,'expert','Package','2','','','');
SQL
);

db_query(<<<SQL
ALTER TABLE `free_trials` ADD `post_data` TEXT NOT NULL AFTER `status`;
ALTER TABLE `licenses` ADD `is_trial` INT(11)  NULL  DEFAULT '0'  AFTER `status`;
ALTER TABLE `license_subscriptions` CHANGE `end_date` `end_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
INSERT INTO `settings` (`id`, `variable`, `value`, `description`, `class`, `fieldset`) VALUES (NULL, 'Name', '', '', '', 'Informacje o firmie'), (NULL, 'Surname', '', '', '', 'Informacje o firmie'), (NULL, 'Email', '', '', '', 'Informacje o firmie'), (NULL, 'Phone', '', '', '', 'Informacje o firmie'), (NULL, 'Country', '', '', 'selectbox', 'Informacje o firmie'), (NULL, 'Name of company', '', '', '', 'Informacje o firmie'), (NULL, 'Adresses', '', '', '', 'Informacje o firmie'), (NULL, 'VAT Number', '', '', '', 'Informacje o firmie'), (NULL, 'Confirm agreement checkbox', '', '', 'checkbox', 'Informacje o firmie'), (NULL, 'Confirm marketing rules checkbox', '', '', 'checkbox', 'Informacje o firmie');
ALTER TABLE `users` ADD `company_confirmation` INT(11)  NULL  DEFAULT '0'  AFTER `recovery_key`;
INSERT INTO `licenses` (`id`, `name`, `description`, `period`, `period_unit`, `trial_period`, `trial_period_unit`, `user_type`, `price`, `currency`, `status`, `is_trial`, `external_id`, `expert_count`, `pro_count`, `mini_count`, `created_at`, `updated_at`) VALUES (NULL, 'Free Trial', 'Free Trial for 14 days', 1, 2, 14, 4, 1, 0, 'USD', 1, 1, 'Free_Trial', 2, 3, 4, '2018-12-18 13:09:59', '2018-12-18 14:05:31');
SQL
);
