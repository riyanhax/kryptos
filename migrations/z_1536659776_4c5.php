<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Run queries
db_query(<<<SQL
CREATE TABLE IF NOT EXISTS `type_rights` ( `id` INT(11) NOT NULL AUTO_INCREMENT , `type` VARCHAR(50) NOT NULL , `rights` VARCHAR(1500) NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
SQL
);

// Run queries
db_query(<<<SQL
INSERT INTO `type_rights` (`id`, `type`, `rights`) VALUES (NULL, 'mini', '{\"perm\\/risk-assessment\":0,\"perm\\/surveys\":0,\"perm\\/gus-ajax\":0,\"perm\\/inspections\":0,\"perm\\/kominfoadm\":0,\"perm\\/tickets\":0,\"perm\\/shared-users\":0,\"perm\\/kontabankowe\":0,\"perm\\/osobyinne\":0,\"perm\\/podpisy\":0,\"perm\\/osoby\":0,\"perm\\/permissions\":0,\"perm\\/user-profile\":0,\"perm\\/reports\":0,\"perm\\/registration-data\":0,\"perm\\/registry-phone-calls\":0,\"perm\\/registry\":1, \"perm/registry/all-access\":0,\"perm\\/public-registry\":0,\"perm\\/zbiory-changelog\":0,\"perm\\/numberingschemes\":0,\"perm\\/systemy-teleinformacyjne\":0,\"perm\\/verifications\":0,\"perm\\/proposals\":0,\"perm\\/tasks\":0,\"perm\\/public-procurements\":0,\"perm\\/kopiezapasowe\":0,\"perm\\/sites\":0,\"perm\\/zbiory\":0,\"perm\\/legalacts\":0,\"perm\\/persons\":0,\"perm\\/pomieszczenia\":0,\"perm\\/persontypes\":0,\"perm\\/zabezpieczenia\":0,\"perm\\/groups\":0,\"perm\\/config\":0,\"perm\\/data-transfers\":0,\"perm\\/companies\":0,\"perm\\/company-employees\":0,\"perm\\/documents\":0,\"perm\\/documentsversioned\":0,\"perm\\/documenttemplates\":0,\"perm\\/file-sources\":0,\"perm\\/course-categories\":0,\"perm\\/exam-categories\":0,\"perm\\/admin\":0,\"perm\\/flows\":0,\"perm\\/flows-roles\":0,\"perm\\/courses\":0,\"perm\\/exams\":0,\"perm\\/flows-types\":0,\"perm\\/flows-events\":0,\"perm\\/aplikacje-moduly\":0,\"perm\\/computer\":0,\"perm\\/aplikacje\":0,\"perm\\/budynki\":0,\"perm\\/fielditems\":0,\"perm\\/fielditemscategories\":0,\"perm\\/fieldscategories\":0,\"perm\\/contacts\":0,\"perm\\/fields\":0,\"perm\\/giodo\":0,\"perm\\/eventscompanies\":0,\"perm\\/eventspersons\":0,\"perm\\/eventscars\":0,\"perm\\/events\":0,\"perm\\/eventspersonstypes\":0}');
SQL
);

// Run queries
db_query(<<<SQL
INSERT INTO `type_rights` (`id`, `type`, `rights`) VALUES (NULL, 'pro', '{\"perm\\/risk-assessment\":0,\"perm\\/surveys\":0,\"perm\\/gus-ajax\":0,\"perm\\/inspections\":0,\"perm\\/kominfoadm\":0,\"perm\\/tickets\":0,\"perm\\/shared-users\":0,\"perm\\/kontabankowe\":0,\"perm\\/osobyinne\":0,\"perm\\/podpisy\":0,\"perm\\/osoby\":0,\"perm\\/permissions\":0,\"perm\\/user-profile\":0,\"perm\\/reports\":0,\"perm\\/registration-data\":0,\"perm\\/registry-phone-calls\":0,\"perm\\/registry\":1, \"perm/registry/all-access\":0,\"perm\\/public-registry\":0,\"perm\\/zbiory-changelog\":0,\"perm\\/numberingschemes\":0,\"perm\\/systemy-teleinformacyjne\":0,\"perm\\/verifications\":0,\"perm\\/proposals\":0,\"perm\\/tasks\":0,\"perm\\/public-procurements\":0,\"perm\\/kopiezapasowe\":0,\"perm\\/sites\":0,\"perm\\/zbiory\":0,\"perm\\/legalacts\":0,\"perm\\/persons\":0,\"perm\\/pomieszczenia\":0,\"perm\\/persontypes\":0,\"perm\\/zabezpieczenia\":0,\"perm\\/groups\":0,\"perm\\/config\":0,\"perm\\/data-transfers\":0,\"perm\\/companies\":0,\"perm\\/company-employees\":0,\"perm\\/documents\":0,\"perm\\/documentsversioned\":0,\"perm\\/documenttemplates\":0,\"perm\\/file-sources\":0,\"perm\\/course-categories\":0,\"perm\\/exam-categories\":0,\"perm\\/admin\":0,\"perm\\/flows\":0,\"perm\\/flows-roles\":0,\"perm\\/courses\":0,\"perm\\/exams\":0,\"perm\\/flows-types\":0,\"perm\\/flows-events\":0,\"perm\\/aplikacje-moduly\":0,\"perm\\/computer\":0,\"perm\\/aplikacje\":0,\"perm\\/budynki\":0,\"perm\\/fielditems\":0,\"perm\\/fielditemscategories\":0,\"perm\\/fieldscategories\":0,\"perm\\/contacts\":0,\"perm\\/fields\":0,\"perm\\/giodo\":0,\"perm\\/eventscompanies\":0,\"perm\\/eventspersons\":0,\"perm\\/eventscars\":0,\"perm\\/events\":0,\"perm\\/eventspersonstypes\":0}');
SQL
);

// Run queries
db_query(<<<SQL
INSERT INTO `type_rights` (`id`, `type`, `rights`) VALUES (NULL, 'expert', '{\"perm\\/risk-assessment\":0,\"perm\\/surveys\":0,\"perm\\/gus-ajax\":0,\"perm\\/inspections\":0,\"perm\\/kominfoadm\":0,\"perm\\/tickets\":0,\"perm\\/shared-users\":0,\"perm\\/kontabankowe\":0,\"perm\\/osobyinne\":0,\"perm\\/podpisy\":0,\"perm\\/osoby\":0,\"perm\\/permissions\":0,\"perm\\/user-profile\":0,\"perm\\/reports\":0,\"perm\\/registration-data\":0,\"perm\\/registry-phone-calls\":0,\"perm\\/registry\":1, \"perm/registry/all-access\":0,\"perm\\/public-registry\":0,\"perm\\/zbiory-changelog\":0,\"perm\\/numberingschemes\":0,\"perm\\/systemy-teleinformacyjne\":0,\"perm\\/verifications\":0,\"perm\\/proposals\":0,\"perm\\/tasks\":0,\"perm\\/public-procurements\":0,\"perm\\/kopiezapasowe\":0,\"perm\\/sites\":0,\"perm\\/zbiory\":0,\"perm\\/legalacts\":0,\"perm\\/persons\":0,\"perm\\/pomieszczenia\":0,\"perm\\/persontypes\":0,\"perm\\/zabezpieczenia\":0,\"perm\\/groups\":0,\"perm\\/config\":0,\"perm\\/data-transfers\":0,\"perm\\/companies\":0,\"perm\\/company-employees\":0,\"perm\\/documents\":0,\"perm\\/documentsversioned\":0,\"perm\\/documenttemplates\":0,\"perm\\/file-sources\":0,\"perm\\/course-categories\":0,\"perm\\/exam-categories\":0,\"perm\\/admin\":0,\"perm\\/flows\":0,\"perm\\/flows-roles\":0,\"perm\\/courses\":0,\"perm\\/exams\":0,\"perm\\/flows-types\":0,\"perm\\/flows-events\":0,\"perm\\/aplikacje-moduly\":0,\"perm\\/computer\":0,\"perm\\/aplikacje\":0,\"perm\\/budynki\":0,\"perm\\/fielditems\":0,\"perm\\/fielditemscategories\":0,\"perm\\/fieldscategories\":0,\"perm\\/contacts\":0,\"perm\\/fields\":0,\"perm\\/giodo\":0,\"perm\\/eventscompanies\":0,\"perm\\/eventspersons\":0,\"perm\\/eventscars\":0,\"perm\\/events\":0,\"perm\\/eventspersonstypes\":0}');
SQL
);
