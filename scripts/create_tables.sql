DROP TABLE IF EXISTS `mail_log`;
DROP TABLE IF EXISTS `plane_selection`;
DROP TABLE IF EXISTS `plane`;
DROP TABLE IF EXISTS `attendance`;
DROP TABLE IF EXISTS `user`;

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `password_hash` char(255) DEFAULT NULL,
  `password_email` varchar(50) DEFAULT NULL,
  `google_user_id` char(25) DEFAULT NULL,
  `login_token` char(64) DEFAULT NULL,
  `login_token_time` datetime DEFAULT NULL,
  `remember_me_token` char(64) DEFAULT NULL,
  `remember_me_token_time` datetime DEFAULT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT 0,
  `is_moderator` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT now(),
  `inserted_at` timestamp NOT NULL DEFAULT now(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `password_email` (`password_email`),
  UNIQUE KEY `login_token` (`login_token`),
  UNIQUE KEY `remember_me_token` (`remember_me_token`),
  UNIQUE KEY `google_user_id` (`google_user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mail_log` (
  `id` int(50) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(50) unsigned DEFAULT NULL,
  `to_mail` varchar(50) DEFAULT NULL,
  `to_name` varchar(50) DEFAULT NULL,
  `subject` varchar(50) DEFAULT NULL,
  `error` varchar(50) DEFAULT NULL,
  `inserted_at` timestamp NOT NULL DEFAULT now(),
  PRIMARY KEY (`id`),
  KEY `FK_mail_log__user_id` (`user_id`),
  CONSTRAINT `FK_mail_log__user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `plane` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `model` varchar(50) NOT NULL,
  `lfz` char(6) NOT NULL DEFAULT 'D-XXXX',
  `wkz` varchar(50) NOT NULL DEFAULT '',
  `alias` varchar(50) NOT NULL,
  `available` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` timestamp NOT NULL DEFAULT now(),
  `inserted_at` timestamp NOT NULL DEFAULT now(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `attendance` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `flight_day` date NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `time` time DEFAULT NULL,
  `pos_latitude` varchar(20) NOT NULL,
  `pos_longitude` varchar(20) NOT NULL,
  `manual_entry` varchar(50) DEFAULT NULL,
  `is_planned` tinyint(1) NOT NULL DEFAULT 0,
  `role` tinyint(1) NOT NULL DEFAULT 0,
  `first` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT now(),
  `inserted_at` timestamp NOT NULL DEFAULT now(),
  PRIMARY KEY (`id`),
  KEY `FK_attendance__user_id` (`user_id`) USING BTREE,
  CONSTRAINT `FK_attendence__user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `plane_selection` (
  `attendance_id` int(10) unsigned NOT NULL,
  `plane_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`attendance_id`,`plane_id`),
  KEY `FK_plane_selection__plane_id` (`plane_id`),
  CONSTRAINT `FK_plane_selection__attendance_id` FOREIGN KEY (`attendance_id`) REFERENCES `attendance` (`id`),
  CONSTRAINT `FK_plane_selection__plane_id` FOREIGN KEY (`plane_id`) REFERENCES `plane` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;