<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Write you code here
//
// You can use
// db_query('some sql');  for quering
// db_pdo()->...;         some pdo functions
db_query(
  'UPDATE `registry_entities` SET `default_value` = \'This is for Bhavin’s testing\', `multiform_data` = \'{\"type\":\"date\",\"label\":\"Data\",\"className\":\"form-control\",\"name\":\"date-1542452727456\",\"visibleIf\":\"{\\\"expression\\\":\\\"true\\\",\\\"bindings\\\":{}}\",\"enableIf\":\"{\\\"expression\\\":\\\"true\\\",\\\"bindings\\\":{}}\",\"value\":\"This is for Bhavin’s testing\"}\' WHERE `registry_entities`.`id` = 1100'
);
