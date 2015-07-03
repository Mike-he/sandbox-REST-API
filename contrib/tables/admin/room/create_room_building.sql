CREATE TABLE `RoomBuilding` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `address` VARCHAR(255) NOT NULL,
  `lat` FLOAT(9,6) NOT NULL,
  `lng` FLOAT(9,6) NOT NULL,
  `cityId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_Building_cityId_idx` (`cityId`),
  CONSTRAINT `fk_Building_cityId` FOREIGN KEY (`cityId`) REFERENCES `RoomCity` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
);
