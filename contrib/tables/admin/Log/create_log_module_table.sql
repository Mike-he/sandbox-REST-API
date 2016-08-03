CREATE TABLE `LogModules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `description` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
 );

 INSERT INTO LogModules(`name`,`description`) VALUES('admin','管理员');
 INSERT INTO LogModules(`name`,`description`) VALUES('building','大楼');
 INSERT INTO LogModules(`name`,`description`) VALUES('invoice','发票');
 INSERT INTO LogModules(`name`,`description`) VALUES('room','房间');
 INSERT INTO LogModules(`name`,`description`) VALUES('room_order','房间订单');
 INSERT INTO LogModules(`name`,`description`) VALUES('room_order_reserve','预留');
 INSERT INTO LogModules(`name`,`description`) VALUES('room_order_preorder','预定');
 INSERT INTO LogModules(`name`,`description`) VALUES('user','用户');
 INSERT INTO LogModules(`name`,`description`) VALUES('product','商品');


--  INSERT INTO LogModules(`name`,`description`) VALUES('event','活动');
--  INSERT INTO LogModules(`name`,`description`) VALUES('price_rule','价格模版');

