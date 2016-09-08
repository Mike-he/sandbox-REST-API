CREATE TABLE `RoomBuildingServices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `icon` longtext NOT NULL,
  PRIMARY KEY (`id`)
);

INSERT INTO `RoomBuildingServices` (`key`, `icon`) VALUES ('free_wifi', 'https://image.sandbox3.cn/icon/building_service_free_wifi.png');
INSERT INTO `RoomBuildingServices` (`key`, `icon`) VALUES ('printing_devices', 'https://image.sandbox3.cn/icon/building_service_print_device.png');
INSERT INTO `RoomBuildingServices` (`key`, `icon`) VALUES ('tea', 'https://image.sandbox3.cn/icon/building_service_tea.png');
INSERT INTO `RoomBuildingServices` (`key`, `icon`) VALUES ('coffee', 'https://image.sandbox3.cn/icon/building_service_coffee.png');
INSERT INTO `RoomBuildingServices` (`key`, `icon`) VALUES ('enterprise_incubation', 'https://image.sandbox3.cn/icon/building_service_enterprise_incubation.png');
INSERT INTO `RoomBuildingServices` (`key`, `icon`) VALUES ('register_agent', 'https://image.sandbox3.cn/icon/building_service_register_agent.png');
INSERT INTO `RoomBuildingServices` (`key`, `icon`) VALUES ('cleaning', 'https://image.sandbox3.cn/icon/building_service_cleaning.png');
INSERT INTO `RoomBuildingServices` (`key`, `icon`) VALUES ('telephone_booth', 'https://image.sandbox3.cn/icon/building_service_telephone_booth.png');
INSERT INTO `RoomBuildingServices` (`key`, `icon`) VALUES ('meeting_service', 'https://image.sandbox3.cn/icon/building_service_meeting_service.png');
INSERT INTO `RoomBuildingServices` (`key`, `icon`) VALUES ('community_manager', 'https://image.sandbox3.cn/icon/building_service_community_manager.png');
INSERT INTO `RoomBuildingServices` (`key`, `icon`) VALUES ('enterprise_service', 'https://image.sandbox3.cn/icon/building_service_enterprise_service.png');
