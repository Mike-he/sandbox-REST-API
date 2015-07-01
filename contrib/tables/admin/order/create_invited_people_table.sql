CREATE TABLE `InvitedPeople` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_InvitedPeople_orderId_idx` (`orderId`),
  CONSTRAINT `fk_InvitedPeople_orderId` FOREIGN KEY (`orderId`) REFERENCES `ProductOrder` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);