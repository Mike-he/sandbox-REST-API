CREATE TABLE `ClientRandomRecord` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `userId` int(11) DEFAULT NULL,
  `clientId` int(11) DEFAULT NULL,
  `entityId` int(11) DEFAULT NULL,
  `entityName` var(16) DEFAULT NULL,
  PRIMARY KEY (`id`)
);
