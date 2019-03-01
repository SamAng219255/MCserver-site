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
  `name` varchar(16) NOT NULL,
  `flag` varchar(64) NOT NULL DEFAULT './img/default_flag.png',
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