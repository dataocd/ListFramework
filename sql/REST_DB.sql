-- This is the rest_user table, which holds all registered user information.
CREATE TABLE IF NOT EXISTS `user` (
  `user_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `full_name` varchar(80) NOT NULL,
  `email_address` varchar(300) NOT NULL,
  `password` char(96) NOT NULL,
  PRIMARY KEY (`user_id`),
  KEY `username_idx` (`username`,`password`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `bookmark` (
  `bookmark_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `bookmark_uri` varchar(300) NOT NULL,
  `bookmark_uri_hash` char(40) NOT NULL,
  `short_description` TINYTEXT,
  `long_description` TEXT,
  `time_created` int unsigned NOT NULL,
  `is_public` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`bookmark_id`),
  UNIQUE KEY (`bookmark_uri_hash`),
  KEY `public_uri_idx` (`is_public`,`bookmark_uri_hash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `user_bookmark` (
  `user_id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `bookmark_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`user_id`, `bookmark_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `tag` (
  `tag_id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `tag_name` char(30) NOT NULL,
  PRIMARY KEY (`tag_id`),
  KEY `tag_name_idx` (`tag_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `bookmark_tag` (
  `tag_id` smallint unsigned NOT NULL,
  `bookmark_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`tag_id`,`bookmark_id`),
  KEY `bookmark_idx` (`bookmark_id`,`tag_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;