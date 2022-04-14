use mcstuff;

CREATE TABLE IF NOT EXISTS `mappoints` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(16) NOT NULL,
  `name` varchar(64) NOT NULL,
  `desc` text NOT NULL,
  `x` int(11) NOT NULL,
  `z` int(11) NOT NULL,
  `dimension` int(8) NOT NULL DEFAULT '0',
  `type` enum('default','custom','hidden') NOT NULL DEFAULT 'default',
  `icondata` varchar(32) NOT NULL DEFAULT 'FF0000',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4

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
  `scrollmode` int NOT NULL DEFAULT 0,
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
  `troopresource` varchar(16) NOT NULL DEFAULT 'Gold',
  UNIQUE (`name`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `resources` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nation` varchar(32) NOT NULL,
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
  `hide` int NOT NULL DEFAULT '0',
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

CREATE TABLE IF NOT EXISTS `troops` (
  `id` int NOT NULL AUTO_INCREMENT,
  `owner` varchar(16) NOT NULL,
  `nation` varchar(32) NOT NULL,
  `name` varchar(64) NOT NULL,
  `size` int NOT NULL,
  `power` int NOT NULL,
  `health` float(6,3) NOT NULL DEFAULT '100.00',
  `x` int NOT NULL,
  `y` int NOT NULL,
  `move` int NOT NULL,
  `moveleft` int NOT NULL,
  `sprite` int NOT NULL,
  `mobile` int NOT NULL,
  `ranged` int NOT NULL,
  `state` int NOT NULL DEFAULT 0,
  `cost` int NOT NULL,
  `origsize` int NOT NULL,
  `customsprite` int NOT NULL,
  `xp` int NOT NULL DEFAULT 0,
  `bonuses` set('combat','defense','open','mobility','ranged','healing','fortify','nomanleft','lucky','helpful') NOT NULL DEFAULT '',
  `aiding` varchar(64) NOT NULL DEFAULT '',
  `battle` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `sprites` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `type` enum('army','pin') NOT NULL,
  `width` int NOT NULL,
  `height` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `commanders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `owner` varchar(32) NOT NULL,
  `special` set('combat','defense','open','mobility','ranged','healing','fortify','nomanleft','lucky','helpful') NOT NULL,
  `xp` int NOT NULL,
  `army` int NOT NULL,
  `nation` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `relations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nation1` varchar(32) NOT NULL,
  `nation2` varchar(32) NOT NULL,
  `relation` enum('Allies','Friends','Friendly','Neutral','Unfriendly','Enemies','War') NOT NULL DEFAULT 'Neutral',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


