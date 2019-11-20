<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Run queries
db_query(<<<'SQL'
CREATE TABLE resources (
id int NOT NULL PRIMARY KEY AUTO_INCREMENT,
module text,
resource text,
privilege text,
created_at timestamp DEFAULT NOW(),
created_by int,
ghost boolean DEFAULT false
);    
SQL
);

db_query(<<<'SQL'
INSERT INTO resources (module, resource, privilege) VALUES
('default', 'home', 'welcome'),
('default', 'home', 'index'),
('default', 'home', 'termsaccepted'),
('default', 'home', 'error403'),
('default', 'home', 'previewdocument');
SQL
);

db_query(<<<'SQL'
CREATE TABLE roles (
id int NOT NULL PRIMARY KEY AUTO_INCREMENT,
code text,
role_name text,
id_role_parent int,
is_system boolean DEFAULT true,
created_at timestamp DEFAULT NOW(),
created_by int,
ghost boolean DEFAULT false
);
SQL
);

db_query(<<<'SQL'
INSERT INTO roles (code, role_name, id_role_parent, is_system) VALUES ('guest', 'Gość', null, true);
SQL
);

db_query(<<<'SQL'
INSERT INTO roles (code, role_name, id_role_parent, is_system) VALUES 
('user', 'Użytkownik', (SELECT id FROM (SELECT * FROM roles WHERE code = 'guest') AS t), true);
SQL
);

db_query(<<<'SQL'
INSERT INTO roles (code, role_name, id_role_parent, is_system) VALUES 
('admin', 'Admin', (SELECT id FROM (SELECT * FROM roles WHERE code = 'user') AS t), true);
SQL
);

db_query(<<<'SQL'
INSERT INTO roles (code, role_name, id_role_parent, is_system) VALUES 
('superadmin', 'Superadmin', (SELECT id FROM (SELECT * FROM roles WHERE code = 'admin') AS t), true);
SQL
);

db_query(<<<'SQL'
CREATE TABLE role_resources (
id int NOT NULL PRIMARY KEY AUTO_INCREMENT,
id_role int,
id_resource int,
created_at timestamp DEFAULT NOW(),
created_by int,
ghost boolean DEFAULT false
);
SQL
);

db_query(<<<'SQL'
INSERT INTO role_resources (id_role, id_resource) VALUES
((SELECT id FROM roles WHERE code = 'user'), (SELECT id FROM resources WHERE module = 'default' AND resource = 'home' AND privilege = 'welcome')),
((SELECT id FROM roles WHERE code = 'user'), (SELECT id FROM resources WHERE module = 'default' AND resource = 'home' AND privilege = 'index')),
((SELECT id FROM roles WHERE code = 'user'), (SELECT id FROM resources WHERE module = 'default' AND resource = 'home' AND privilege = 'termsaccepted')),
((SELECT id FROM roles WHERE code = 'user'), (SELECT id FROM resources WHERE module = 'default' AND resource = 'home' AND privilege = 'error403'));
SQL
);

db_query(<<<'SQL'
ALTER TABLE users ADD COLUMN id_role int;
SQL
);

db_query(<<<'SQL'
UPDATE users SET id_role = (SELECT id FROM roles WHERE code = 'user') WHERE (NOT isAdmin AND NOT isSuperAdmin);
SQL
);

db_query(<<<'SQL'
UPDATE users SET id_role = (SELECT id FROM roles WHERE code = 'admin') WHERE (isAdmin IS TRUE);
SQL
);

db_query(<<<'SQL'
UPDATE users SET id_role = (SELECT id FROM roles WHERE code = 'superadmin') WHERE (isSuperAdmin IS TRUE);
SQL
);
