use mcstuff;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(16) NOT NULL,
  `password` varchar(64) NOT NULL,
  `uuid` char(32) NOT NULL,
  `forecolor` char(6) NOT NULL DEFAULT '000000',
  `backcolor` char(6) NOT NULL DEFAULT 'C0C0C0',
  `ip` varchar(45) NOT NULL,
  `laston` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `nation` varchar(32) NOT NULL DEFAULT '',
  `character` varchar(32) NOT NULL DEFAULT '',
  `prefix` varchar(32) NOT NULL DEFAULT '',
  `suffix` varchar(32) NOT NULL DEFAULT '',
  `permissions` int NOT NULL DEFAULT '0',
  `skin` varchar(128) NOT NULL DEFAULT './img/steve.png',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `posts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(16) NOT NULL,
  `topic` varchar(16) NOT NULL DEFAULT 'general',
  `tags` varchar(255) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `nations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `ruler` varchar(16) NOT NULL,
  `showruler` enum('show','hide','false') NOT NULL DEFAULT 'false',
  `parent` varchar(32) NOT NULL DEFAULT '',
  `showparent` enum('show','hide','false') NOT NULL DEFAULT 'false',
  `hasflag` int(2) NOT NULL DEFAULT '0',
  `showflag` enum('show','hide','false') NOT NULL DEFAULT 'false',
  `population` int NOT NULL DEFAULT '0',
  `showpopul` enum('show','hide','false') NOT NULL DEFAULT 'false',
  `desc` text NOT NULL,
  UNIQUE (`name`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `resources` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nation` varchar(16) NOT NULL,
  `unit` varchar(16) NOT NULL DEFAULT '',
  `type` varchar(16) NOT NULL DEFAULT 'Gold',
  `ntnlwlth` int NOT NULL DEFAULT '0',
  `ctznwlth` int NOT NULL DEFAULT '0',
  `ntnlincome` int NOT NULL DEFAULT '0',
  `ctznincome` int NOT NULL DEFAULT '0',
  `tax` int NOT NULL DEFAULT '0',
  `showwlth` enum('show','hide','false') NOT NULL DEFAULT 'false',
  `showncm` enum('show','hide','false') NOT NULL DEFAULT 'false',
  `showntnl` enum('show','hide','false') NOT NULL DEFAULT 'false',
  `showctzn` enum('show','hide','false') NOT NULL DEFAULT 'false',
  `showtax` enum('show','hide','false') NOT NULL DEFAULT 'false',
  `desc` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `nationpages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `path` varchar(255) NOT NULL,
  `name` varchar(16) NOT NULL,
  `title` varchar(64) NOT NULL,
  `content` text NOT NULL,
  `banner` varchar(64) NOT NULL DEFAULT './img/default_flag.png',
  `icon` varchar(64) NOT NULL DEFAULT './img/icon.png',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;