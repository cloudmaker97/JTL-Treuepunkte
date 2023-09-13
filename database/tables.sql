CREATE TABLE IF NOT EXISTS `dh_bonus_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text` longtext DEFAULT NULL,
  `points` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `orderId` int(11) DEFAULT NULL,
  `createdAt` datetime DEFAULT NULL,
  `valuedAt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `dh_bonus_last_rewarded` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) DEFAULT NULL,
  `visitAt` datetime DEFAULT NULL,
  `loginAt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
