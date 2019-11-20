<?php

namespace migrations;

require_once __DIR__ .'/lib.inc.php';

// Run queries
<<<SQL
CREATE TABLE watson_init_phrases (
id int NOT NULL PRIMARY KEY AUTO_INCREMENT,
phrase text,
created_at timestamp DEFAULT NOW(),
created_by int,
ghost boolean DEFAULT false
);

CREATE TABLE watson_phrase_display (
id int NOT NULL PRIMARY KEY AUTO_INCREMENT,
id_watson_init_phrase int,
controller text,
action text,
is_admin boolean DEFAULT false,
created_at timestamp DEFAULT NOW(),
created_by int,
ghost boolean DEFAULT false
);

CREATE TABLE watson_user_phrases_displayed (
id int NOT NULL PRIMARY KEY AUTO_INCREMENT,
id_watson_init_phrase int,
id_user int,
created_at timestamp DEFAULT NOW(),
created_by int,
ghost boolean DEFAULT false
);

INSERT INTO watson_init_phrases (phrase) VALUES 
('home_welcome_admin'),
('home_welcome_user');

INSERT INTO watson_phrase_display (id_watson_init_phrase, controller, action, is_admin) VALUES
((SELECT id FROM watson_init_phrases WHERE phrase = 'home_welcome_admin'), 'home', 'welcome', 1),
((SELECT id FROM watson_init_phrases WHERE phrase = 'home_welcome_user'), 'home', 'welcome', 0);
SQL;
