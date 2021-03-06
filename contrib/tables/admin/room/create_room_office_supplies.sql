CREATE TABLE `RoomSupplies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roomId` int(11) NOT NULL,
  `suppliesId` int(11) NOT NULL,
  `quantity` integer NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roomId_suppliesId_UNIQUE` (`roomId`,`suppliesId`),
  KEY `fk_RoomSupplies_roomId_idx` (`roomId`),
  CONSTRAINT `fk_RoomSupplies_roomId` FOREIGN KEY (`roomId`) REFERENCES `Room` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);