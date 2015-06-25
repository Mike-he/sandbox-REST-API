CREATE TABLE `RoomBuilding` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `cityId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_Building_cityId_idx` (`cityId`),
  CONSTRAINT `fk_Building_cityId` FOREIGN KEY (`cityId`) REFERENCES `RoomCity` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);
