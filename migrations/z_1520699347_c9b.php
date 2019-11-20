<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Write you code here
//
// You can use
// db_query('some sql');  for quering
// db_pdo()->...;         some pdo functions

db_query(<<<SQL
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `amount` DECIMAL(12,2) NOT NULL DEFAULT '0',
  `fees` DECIMAL(10,2) NOT NULL DEFAULT '0',
  `currency_id` int(11) NOT NULL,
  `payment_method` ENUM('paypal', 'dotpay', 'platnosci24') NOT NULL,
  `hash` VARCHAR(255) NOT NULL,
  `status` VARCHAR(255) DEFAULT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `external_payment_id` VARCHAR(255) DEFAULT NULL,
  `details` TEXT DEFAULT NULL,
  `approved` TINYINT(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `balances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `debit` DECIMAL(12,2) NOT NULL DEFAULT '0',
  `credit` DECIMAL(12,2) NOT NULL DEFAULT '0',
  `balance` DECIMAL(12,2) NOT NULL,
  `currency_id` int(11) NOT NULL,
  `reference_type` ENUM('payment', 'spend'),
  `reference_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `currencies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` char(3) NOT NULL,
  `order` int(11) DEFAULT '0',
  `fractional_units` int(11) DEFAULT NULL,
  `type` enum('real','virtual') NOT NULL,
  `iso_code` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  UNIQUE KEY `iso_code` (`iso_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `currencies` VALUES (1,'USD',1,100,'real',840),(2,'EUR',2,100,'real',978),(3,'GBP',3,100,'real',826),(4,'CAD',16,100,'real',124),(5,'AUD',8,100,'real',36),(6,'TRY',64,100,'real',949),(7,'ILS',34,100,'real',376),(8,'INR',35,100,'real',356),(9,'DKK',22,100,'real',208),(10,'NOK',47,100,'real',578),(11,'ZAR',72,100,'real',710),(12,'SEK',59,100,'real',752),(13,'CHF',17,100,'real',756),(14,'AED',4,100,'real',784),(15,'BHD',12,1000,'real',48),(16,'CLP',18,100,'real',152),(17,'COP',20,100,'real',170),(18,'CZK',21,100,'real',203),(19,'DOP',23,100,'real',214),(20,'EGP',26,100,'real',818),(21,'GTQ',28,100,'real',320),(22,'HKD',29,100,'real',344),(23,'IDR',33,100,'real',360),(24,'JOD',36,1000,'real',400),(25,'MXN',44,100,'real',484),(26,'MYR',45,100,'real',458),(27,'OMR',49,1000,'real',512),(28,'PEN',51,100,'real',604),(29,'PLN',53,100,'real',985),(30,'QAR',54,100,'real',634),(31,'RUB',57,100,'real',643),(32,'SAR',58,100,'real',682),(33,'SGD',60,100,'real',702),(34,'SYP',62,100,'real',760),(35,'THB',63,100,'real',764),(36,'TWD',65,100,'real',901),(37,'VND',69,1,'real',704),(38,'YER',71,100,'real',886),(39,'CNY',19,100,'real',156),(40,'NZD',48,100,'real',554),(41,'HRK',31,100,'real',191),(42,'HUF',32,100,'real',348),(44,'BRL',14,100,'real',986),(45,'BGN',11,100,'real',975),(48,'LTL',40,100,'real',440),(49,'MKD',43,100,'real',807),(50,'RSD',56,100,'real',941),(51,'DZD',24,100,'real',12),(52,'MAD',42,100,'real',504),(53,'ALL',5,100,'real',8),(54,'ARS',7,100,'real',32),(55,'UYU',67,100,'real',858),(56,'VEF',68,100,'real',937),(57,'PHP',52,100,'real',608),(58,'AMD',6,100,'real',51),(59,'AZN',9,100,'real',944),(60,'BOB',13,100,'real',68),(61,'GEL',27,100,'real',981),(62,'NGN',46,100,'real',566),(63,'UAH',66,100,'real',980),(64,'KZT',39,100,'real',398),(65,'BYR',15,100,'real',974),(66,'KRW',38,1,'real',410),(67,'BAM',10,100,'real',977),(68,'HNL',30,100,'real',340),(69,'KES',37,100,'real',404),(70,'XAF',70,100,'real',950),(71,'PAB',50,100,'real',590),(72,'RON',55,100,'real',946),(73,'JPY',73,1,'real',392),(74,'MDL',74,100,'real',498),(75,'PKR',75,100,'real',586),(76,'CRC',76,100,'real',188),(80,'IQD',80,1000,'real',368),(81,'BDT',81,100,'real',50),(82,'JMD',82,100,'real',388),(83,'LBP',83,100,'real',422),(84,'LKR',84,100,'real',144),(85,'LYD',85,1000,'real',434),(86,'NIO',86,100,'real',558),(87,'PYG',87,100,'real',600),(88,'SDG',88,100,'real',938),(89,'SVC',89,100,'real',222),(90,'TND',90,1000,'real',788),(92,'KWD',92,1000,'real',414),(93,'XOF',93,100,'real',952),(95,'IRR',95,100,'real',364),(96,'ETB',96,100,'real',230),(97,'BYN',97,100,'real',933),(98,'MMK',98,100,'real',104);
SQL
);
