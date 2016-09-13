CREATE TABLE `UserProfileMyOrders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `icon` longtext COLLATE utf8_unicode_ci,
  `url` varchar(2048) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
)

INSERT INTO `UserProfileMyOrders` (`name`, `icon`, `url`) VALUES('room_order', 'https://image.sandbox3.cn/icon/profile_orders_room_order.png', 'https://orders.sandbox3.cn/room');
INSERT INTO `UserProfileMyOrders` (`name`, `icon`, `url`) VALUES('shop_order', 'https://image.sandbox3.cn/icon/profile_orders_shop_order.png', 'https://orders.sandbox3.cn/shop');
INSERT INTO `UserProfileMyOrders` (`name`, `icon`, `url`) VALUES('event_order', 'https://image.sandbox3.cn/icon/profile_orders_event_order.png', 'https://orders.sandbox3.cn/event');