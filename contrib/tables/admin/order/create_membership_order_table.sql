CREATE TABLE `MembershipOrder` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `endDate` date NOT NULL,
  `price` numeric(15,2),
  `type` enum('month','quarter','year') NOT NULL,
  `cancelledDate` datetime,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  PRIMARY KEY (`id`)
);