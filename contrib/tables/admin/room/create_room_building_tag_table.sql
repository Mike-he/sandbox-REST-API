CREATE TABLE `RoomBuildingTag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `icon` longtext NOT NULL,
  PRIMARY KEY (`id`)
);

INSERT INTO `RoomBuildingTag` (`name`, `icon`) VALUES ('sandbox3_manage', 'https://image.sandbox3.cn/icon/building_tag_sandbox_manage.png');
INSERT INTO `RoomBuildingTag` (`name`, `icon`) VALUES ('round_the_clock_service', 'https://image.sandbox3.cn/icon/building_tag_7*24.png');
INSERT INTO `RoomBuildingTag` (`name`, `icon`) VALUES ('certification_of_incubator', 'https://image.sandbox3.cn/icon/building_tag_auth_incubator.png');
INSERT INTO `RoomBuildingTag` (`name`, `icon`) VALUES ('food_and_beverage', 'https://image.sandbox3.cn/icon/building_tag_food.png');
INSERT INTO `RoomBuildingTag` (`name`, `icon`) VALUES ('vip_space', 'https://image.sandbox3.cn/icon/building_tag_vip_space.png');
INSERT INTO `RoomBuildingTag` (`name`, `icon`) VALUES ('fast_preorder', 'https://image.sandbox3.cn/icon/building_tag_fast_preorder.png');
