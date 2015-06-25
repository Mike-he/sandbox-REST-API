CREATE TABLE `RoomSupplies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roomId` int(11) NOT NULL,
  `name` text NOT NULL,
  `quantity` integer NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_RoomSupplies_roomId_idx` (`roomId`),
  CONSTRAINT `fk_RoomSupplies_roomId` FOREIGN KEY (`roomId`) REFERENCES `Room` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);