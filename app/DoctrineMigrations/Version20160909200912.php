<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160909200912 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql("
            INSERT INTO admin_types(`key`,`name`,`creationDate`,`modificationDate`)
            VALUES('super','超级管理员','2015-08-24 00:00:00','2015-08-24 00:00:00'),
                  ('platform','平台管理员','2015-08-24 00:00:00','2015-08-24 00:00:00');
        ");

        $this->addSql("
            INSERT INTO admin_permission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`)
            VALUES(2,'platform.order','订单管理','2015-08-24 00:00:00','2015-08-24 00:00:00'),
                  (2,'platform.user','用户管理','2015-08-24 00:00:00','2015-08-24 00:00:00'),
                  (2,'platform.admin','管理员管理','2015-08-24 00:00:00','2015-08-24 00:00:00'),
                  (2,'platform.announcement','通知管理','2015-08-24 00:00:00','2015-08-24 00:00:00'),
                  (2,'platform.dashboard','控制台管理','2015-08-24 00:00:00','2015-08-24 00:00:00'),
                  (2,'platform.event','活动管理','2015-08-24 00:00:00','2015-08-24 00:00:00'),
                  (2,'platform.banner','横幅管理','2015-08-24 00:00:00','2015-08-24 00:00:00'),
                  (2,'platform.news','新闻管理','2015-08-24 00:00:00','2015-08-24 00:00:00'),
                  (2,'platform.message','消息管理','2015-08-24 00:00:00','2015-08-24 00:00:00'),
                  (2,'platform.verify','审查管理','2015-08-24 00:00:00','2015-08-24 00:00:00'),
                  (2,'platform.sales','销售方管理','2015-08-24 00:00:00','2015-08-24 00:00:00'),
                  (2,'platform.invoice','发票管理','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                  (2,'platform.access','门禁系统','2015-08-24 00:00:00','2015-08-24 00:00:00'),
                  (2,'platform.room','空间管理','2015-08-24 00:00:00','2015-08-24 00:00:00'),
                  (2,'platform.product','商品管理','2015-08-24 00:00:00','2015-08-24 00:00:00'),
                  (2,'platform.price','价格体系管理','2015-08-24 00:00:00','2015-08-24 00:00:00'),
                  (2,'platform.building','大楼管理','2015-08-24 00:00:00','2015-08-24 00:00:00'),
                  (2,'platform.bulletin','说明发布','2015-08-24 00:00:00','2015-08-24 00:00:00'),
                  (2,'platform.order.reserve','订单预留','2015-08-24 00:00:00','2015-08-24 00:00:00'),
                  (2,'platform.order.preorder','订单预定','2015-08-24 00:00:00','2015-08-24 00:00:00'),
                  (2,'platform.product.appointment','预约审核','2016-07-08 00:00:00','2016-07-08 00:00:00'),
                  (2,'platform.log','日志管理','2016-07-08 00:00:00','2016-07-08 00:00:00'),
                  (2,'platform.advertising','广告管理','2016-07-08 00:00:00','2016-07-08 00:00:00');
        ");

        $this->addSql("
            INSERT INTO `admin`(`username`,`password`,`name`,`typeId`,`creationDate`,`modificationDate`)
            VALUES('superadmin','BA6271742C479CDF95CB7B1FFE2CB3B7','超级管理员',1,'2015-08-24 00:00:00','2015-08-24 00:00:00');
        ");

        $this->addSql("
            INSERT INTO `advertising` (`url`,`source`,`sourceId`,`visible`,`isSaved`,`isDefault`,`creationDate`,`modificationDate`)
            VALUES ('','url',null,1,0,1,'2016-08-19 15:19:34','2016-08-19 15:19:34');
        ");

        $this->addSql("
            INSERT INTO `advertising_attachment` (`advertisingId`,`content`,`attachmentType`,`filename`,`preview`,`size`,`height`,`width`)
            VALUES(1,'https://image.sandbox3.cn/advertising/1326x1080_coffee_ad.jpg','image/png','1326x1080_coffee_ad.jpg','https://image.sandbox3.cn/advertising/1326x1080_coffee_ad.jpg',3,1326,1080),
                  (1,'https://image.sandbox3.cn/advertising/1416x1080_coffee_ad.jpg','image/png','1416x1080_coffee_ad.jpg','https://image.sandbox3.cn/advertising/1416x1080_coffee_ad.jpg',3,1416,1080),
                  (1,'https://image.sandbox3.cn/advertising/1486x1080_coffee_ad.jpg','image/png','1486x1080_coffee_ad.jpg','https://image.sandbox3.cn/advertising/1486x1080_coffee_ad.jpg',3,1486,1080),
                  (1,'https://image.sandbox3.cn/advertising/1556x1080_coffee_ad.jpg','image/png','1556x1080_coffee_ad.jpg','https://image.sandbox3.cn/advertising/1556x1080_coffee_ad.jpg',3,1556,1080);
        ");

        $this->addSql("
            INSERT INTO `banner_tag`(`key`)
            VALUES('banner.tag.activity'),
                  ('banner.tag.news'),
                  ('banner.tag.product'),
                  ('banner.tag.advertisement');
        ");

        $this->addSql("
            INSERT INTO log_modules(`name`,`description`)
            VALUES('admin','管理员'),
                  ('building','大楼'),
                  ('invoice','发票'),
                  ('room','房间'),
                  ('room_order','房间订单'),
                  ('room_order_reserve','预留'),
                  ('room_order_preorder','预定'),
                  ('user','用户'),
                  ('product','商品');
        ");

        $this->addSql("
            INSERT INTO `room_city`(`name`, `key`)
            VALUES('上海(Shanghai)', 'sh'),
                  ('北京(Beijing)', 'bj'),
                  ('广州(Guangzhou)', 'gz'),
                  ('深圳(Shenzhen)', 'sz'),
                  ('厦门(Xiamen)', 'xm'),
                  ('杭州(Hangzhou)', 'hz'),
                  ('成都(Chengdu)', 'cd'),
                  ('重庆(Chongqing)', 'cq'),
                  ('青岛(Qingdao)', 'qd'),
                  (\"西安(Xi'an)\", 'xa'),
                  ('旧金山(San Francisco)', 'sf'),
                  ('波士顿(Boston)', 'bs'),
                  ('大连(Dalian)', 'dl'),
                  ('嘉兴(Jiaxing)', 'jx'),
                  ('南京(Nanjing)', 'nj'),
                  ('珠海(Zhuhai)', 'zh'),
                  ('南宁(Nanning)', 'nn'),
                  ('天津(Tianjin)', 'tj'),
                  ('佛山(Foshan)', 'fs'),
                  ('武汉(Wuhan)', 'wh'),
                  ('昆明(Kunming)', 'km'),
                  ('烟台(Yantai)', 'yt'),
                  ('泉州(Quanzhou)', 'qz'),
                  ('太原(Taiyuan)', 'ty'),
                  ('宁波(Ningbo)', 'nb'),
                  ('福州(Fuzhou)', 'fz'),
                  ('廊坊(Langfang)', 'lf'),
                  ('绍兴(Shaoxing)', 'sx'),
                  ('苏州(Suzhou)', 'suz');
        ");

        $this->addSql("
            INSERT INTO `room_building_services` (`key`, `icon`)
            VALUES('free_wifi', 'https://image.sandbox3.cn/icon/building_service_free_wifi.png'),
                  ('printing_devices', 'https://image.sandbox3.cn/icon/building_service_print_device.png'),
                  ('tea', 'https://image.sandbox3.cn/icon/building_service_tea.png'),
                  ('coffee', 'https://image.sandbox3.cn/icon/building_service_coffee.png'),
                  ('enterprise_incubation', 'https://image.sandbox3.cn/icon/building_service_enterprise_incubation.png'),
                  ('register_agent', 'https://image.sandbox3.cn/icon/building_service_register_agent.png'),
                  ('cleaning', 'https://image.sandbox3.cn/icon/building_service_cleaning.png'),
                  ('telephone_booth', 'https://image.sandbox3.cn/icon/building_service_telephone_booth.png'),
                  ('meeting_service', 'https://image.sandbox3.cn/icon/building_service_meeting_service.png'),
                  ('community_manager', 'https://image.sandbox3.cn/icon/building_service_community_manager.png'),
                  ('enterprise_service', 'https://image.sandbox3.cn/icon/building_service_enterprise_service.png');
        ");

        $this->addSql("
            INSERT INTO `room_building_tag` (`key`, `icon`)
            VALUES('sandbox3_manage', 'https://image.sandbox3.cn/icon/building_tag_sandbox_manage.png'),
                  ('round_the_clock_service', 'https://image.sandbox3.cn/icon/building_tag_7*24.png'),
                  ('certification_of_incubator', 'https://image.sandbox3.cn/icon/building_tag_auth_incubator.png'),
                  ('food_and_beverage', 'https://image.sandbox3.cn/icon/building_tag_food.png'),
                  ('vip_space', 'https://image.sandbox3.cn/icon/building_tag_vip_space.png'),
                  ('fast_preorder', 'https://image.sandbox3.cn/icon/building_tag_fast_preorder.png');
        ");

        $this->addSql("
            INSERT INTO supplies (name)
            VALUES('液晶电视'),
                  ('投影仪'),
                  ('白板'),
                  ('电子白板'),
                  ('电话会议设备'),
                  ('视频会议设备'),
                  ('音响及扩音设备'),
                  ('苹果无线投影'),
                  ('其它无线投影'),
                  ('无线网络'),
                  ('有线网路'),
                  ('咖啡茶水'),
                  ('茶歇小食'),
                  ('打印复印');
        ");

        $this->addSql("
            INSERT INTO `room_types` (`name`, `icon`)
            VALUES('office', 'https://image.sandbox3.cn/icon/room_type_office.png'),
                  ('meeting', 'https://image.sandbox3.cn/icon/room_type_meeting.png'),
                  ('flexible', 'https://image.sandbox3.cn/icon/room_type_flexible.png'),
                  ('fixed', 'https://image.sandbox3.cn/icon/room_type_fixed.png'),
                  ('studio', 'https://image.sandbox3.cn/icon/room_type_studio.png'),
                  ('space', 'https://image.sandbox3.cn/icon/room_type_space.png');
        ");

        $this->addSql("
            INSERT INTO room_type_unit(`typeId`,`unit`)
            VALUES(1,'month'),
                  (2,'hour'),
                  (3,'day'),
                  (4,'day'),
                  (4,'month'),
                  (5,'hour'),
                  (6,'hour');
        ");

        $this->addSql("
            INSERT INTO sales_admin_types(`key`,`name`,`creationDate`,`modificationDate`)
            VALUES('super','超级管理员','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                  ('platform','平台管理员','2016-03-01 00:00:00','2016-03-01 00:00:00');
        ");

        $this->addSql("
            INSERT INTO sales_admin_permission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`)
            VALUES(2,'sales.platform.dashboard','控制台管理','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                  (2,'sales.platform.admin','管理员管理','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                  (2,'sales.platform.building','项目管理','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                  (2,'sales.platform.invoice','发票管理','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                  (2,'sales.platform.event','活动管理','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                  (2,'sales.building.price','价格模板管理','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                  (2,'sales.building.order','订单管理','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                  (2,'sales.building.order.reserve','订单预留','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                  (2,'sales.building.order.preorder','订单预订','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                  (2,'sales.building.building','项目管理','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                  (2,'sales.building.user','用户管理','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                  (2,'sales.building.room','空间管理','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                  (2,'sales.building.product','商品管理','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                  (2,'sales.building.access','门禁管理','2016-03-01 00:00:00','2016-03-01 00:00:00');
        ");

        $this->addSql("
            INSERT INTO sales_company_user_card(`type`, `companyId`, `cardUrl`, `cardBackgroundUrl`, `cardNumberColor`, `creationDate`, `modificationDate`, `lostCardBackgroundUrl`)
            VALUES ('sales', '2', 'https://image.sandbox3.cn/user_card/sandbox3_user_card.png', 'https://image.sandbox3.cn/user_card/sandbox3_user_card_bg.png', '#e8d47c', '2016-07-06 15:27:00', '2016-07-06 15:27:00', 'https://image.sandbox3.cn/user_card/sandbox3_user_card_lost_bg.png');

        ");

        $this->addSql("
            INSERT INTO shop_admin_types(`key`,`name`,`creationDate`,`modificationDate`)
            VALUES('super','超级管理员','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                  ('platform','平台管理员','2016-03-01 00:00:00','2016-03-01 00:00:00');
        ");

        $this->addSql("
            INSERT INTO shop_admin_permission(`typeId`,`key`,`name`,`creationDate`,`modificationDate`)
            VALUES(2,'shop.platform.dashboard','控制台管理','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                  (2,'shop.platform.admin','管理员管理','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                  (2,'shop.platform.shop','商店新增','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                  (2,'shop.platform.spec','规格管理','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                  (2,'shop.shop.shop','商店管理','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                  (2,'shop.shop.order','订单管理','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                  (2,'shop.shop.product','商品管理','2016-03-01 00:00:00','2016-03-01 00:00:00'),
                  (2,'shop.shop.kitchen','传菜系统管理','2016-03-01 00:00:00','2016-03-01 00:00:00');
        ");

        $this->addSql("
            INSERT INTO app_info(`platform`,`version`,`url`,`date`,`environment`,`copyrightYear`)
            VALUES('ios','2.2.4','https://itunes.apple.com/cn/app/sandbox3-chuang-he-she-qu/id1015843788?l=en&mt=8','2016-04-20','production','2016'),
                  ('android','2.2.4','http://download.sandbox3.cn/Sandbox3.apk','2016-04-20','production','2016');
        ");

        $this->addSql("
            INSERT INTO company_industry(`name`,`key`,`creationDate`,`modificationDate`)
            VALUES('IT','information_technology','2015-07-28 10:00','2015-07-28 10:00'),
                  ('通信','communication','2015-07-28 10:00','2015-07-28 10:00'),
                  ('电子','electronic','2015-07-28 10:00','2015-07-28 10:00'),
                  ('互联网','internet','2015-07-28 10:00','2015-07-28 10:00'),
                  ('金融业','financial','2015-07-28 10:00','2015-07-28 10:00'),
                  ('房地产','estate','2015-07-28 10:00','2015-07-28 10:00'),
                  ('建筑业','architecture','2015-07-28 10:00','2015-07-28 10:00'),
                  ('商业服务','business_services','2015-07-28 10:00','2015-07-28 10:00'),
                  ('贸易','trading','2015-07-28 10:00','2015-07-28 10:00'),
                  ('批发','wholesale','2015-07-28 10:00','2015-07-28 10:00'),
                  ('零售','retail','2015-07-28 10:00','2015-07-28 10:00'),
                  ('租赁业','leasing','2015-07-28 10:00','2015-07-28 10:00'),
                  ('文体教育','stylistic_education','2015-07-28 10:00','2015-07-28 10:00'),
                  ('工艺美术','craft_art','2015-07-28 10:00','2015-07-28 10:00'),
                  ('仪器仪表及工业自动化','instrumentation_and_industrial_automation','2015-07-28 10:00','2015-07-28 10:00'),
                  ('交通','traffic','2015-07-28 10:00','2015-07-28 10:00'),
                  ('运输','transport','2015-07-28 10:00','2015-07-28 10:00'),
                  ('物流','logistics','2015-07-28 10:00','2015-07-28 10:00'),
                  ('仓储','warehousing','2015-07-28 10:00','2015-07-28 10:00'),
                  ('服务业','services','2015-07-28 10:00','2015-07-28 10:00'),
                  ('文化','culture','2015-07-28 10:00','2015-07-28 10:00'),
                  ('传媒','media','2015-07-28 10:00','2015-07-28 10:00'),
                  ('娱乐','entertainment','2015-07-28 10:00','2015-07-28 10:00'),
                  ('体育','sports','2015-07-28 10:00','2015-07-28 10:00'),
                  ('能源','energy','2015-07-28 10:00','2015-07-28 10:00'),
                  ('矿产','mineral','2015-07-28 10:00','2015-07-28 10:00'),
                  ('环保','environment_protection','2015-07-28 10:00','2015-07-28 10:00'),
                  ('政府','government','2015-07-28 10:00','2015-07-28 10:00'),
                  ('非营利机构','non_profit_organizations','2015-07-28 10:00','2015-07-28 10:00'),
                  ('农业','agriculture','2015-07-28 10:00','2015-07-28 10:00'),
                  ('林业','forestry','2015-07-28 10:00','2015-07-28 10:00'),
                  ('牧业','animal_husbandry','2015-07-28 10:00','2015-07-28 10:00'),
                  ('渔业','fisheries','2015-07-28 10:00','2015-07-28 10:00'),
                  ('其他','other','2015-07-28 10:00','2015-07-28 10:00');
        ");

        $this->addSql("
            INSERT INTO `features` (`id`,`name`,`type`,`url`,`ready`,`app`)
            VALUES(1,'food','web','https://coffee.sandbox3.cn/',1,'sandbox'),
                  (2,'print','web','https://print.sandbox3.cn/',0,'sandbox'),
                  (3,'coffee','web','https://coffee.sandbox3.cn/',1,'sandbox'),
                  (4,'forward','web','https://cafe.sandbox3.cn',1,'sandbox'),
                  (5,'news','web','https://m.sandbox3.cn/news',1,'sandbox'),
                  (6,'event','web','https://m.sandbox3.cn/event',1,'sandbox'),
                  (7,'reservation','web','https://mobile.sandbox3.cn/search',1,'sandbox'),
                  (8,'invoice','web','https://invoice.sandbox3.cn/invoice',1,'sandbox'),
                  (9,'about','web','https://mobile.sandbox3.cn/about-xiehe',1,'xiehe'),
                  (10,'reservation','web','https://mobile.sandbox3.cn/search-xiehe',1,'xiehe');
        ");

        $this->addSql("
            INSERT INTO `menu` (`component`, `platform`, `minVersion`, `maxVersion`, `mainJson`, `profileJson`, `homeJson`) 
            VALUES('client', 'iphone', '2.2.4', '2.2.7', '[{\"type\":\"icons\",\"items\":[{\"key\":\"order\",\"type\":\"web\",\"name\":\"client.menu.order\",\"web\":{\"url\":\"https://mobile.sandbox3.cn/search\",\"cookie\":[{\"key\":\"btype\",\"value\":\"recommend\"}]},\"icon_url\":\"https://image.sandbox3.cn/menu/client/v225/ic_booking.png\",\"status\":\"active\",\"login_need\":false},{\"key\":\"coffee\",\"type\":\"web\",\"name\":\"client.menu.coffee\",\"web\":{\"url\":\"https://coffee.sandbox3.cn\"},\"icon_url\":\"https://image.sandbox3.cn/menu/client/v225/ic_coffee.png\",\"status\":\"active\",\"login_need\":false},{\"key\":\"event\",\"type\":\"web\",\"name\":\"client.menu.event\",\"web\":{\"url\":\"https://m.sandbox3.cn/event\",\"cookie\":[{\"key\":\"btype\",\"value\":\"all\"}]},\"icon_url\":\"https://image.sandbox3.cn/menu/client/v225/ic_events.png\",\"status\":\"active\",\"login_need\":false},{\"key\":\"location\",\"type\":\"app\",\"name\":\"client.menu.location\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v225/ic_locations.png\",\"status\":\"active\",\"login_need\":false}]},{\"type\":\"list\",\"items\":[{\"key\":\"community\",\"type\":\"app\",\"name\":\"client.menu.community\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v225/ic_home.png\",\"status\":\"active\",\"login_need\":false}]},{\"type\":\"list\",\"items\":[{\"key\":\"feed\",\"type\":\"app\",\"name\":\"client.menu.blog\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v225/ic_posts.png\",\"status\":\"active\",\"login_need\":false},{\"key\":\"message\",\"type\":\"app\",\"name\":\"client.menu.message\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v225/ic_chat.png\",\"status\":\"active\",\"login_need\":true},{\"key\":\"contact\",\"type\":\"app\",\"name\":\"client.menu.contact\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v225/ic_contacts.png\",\"status\":\"active\",\"login_need\":false}]},{\"type\":\"list\",\"items\":[{\"key\":\"member\",\"type\":\"app\",\"name\":\"client.menu.member\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v225/ic_members.png\",\"status\":\"active\",\"login_need\":false},{\"key\":\"company\",\"type\":\"app\",\"name\":\"client.menu.company\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v225/ic_companies.png\",\"status\":\"active\",\"login_need\":true},{\"key\":\"my_company\",\"type\":\"app\",\"name\":\"client.menu.my_company\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v225/ic_my_companies.png\",\"status\":\"active\",\"login_need\":true}]}]', '[{\"type\":\"list\",\"menus\":[[{\"key\":\"topup\",\"type\":\"web\",\"name\":\"client.menu.balance\",\"web_url\":{\"url\":\"https://mobile.sandbox3.cn/recharge\"},\"status\":\"active\",\"login_need\":true}],[{\"key\":\"my_order\",\"type\":\"app\",\"name\":\"client.menu.my_order\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v224/ic_my_orders.png\",\"status\":\"active\",\"login_need\":true},{\"key\":\"my_room\",\"type\":\"app\",\"name\":\"client.menu.my_room\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v224/ic_used_rooms.png\",\"status\":\"active\",\"login_need\":true},{\"key\":\"announcement\",\"type\":\"app\",\"name\":\"client.menu.notification\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v224/ic_chat.png\",\"status\":\"active\",\"login_need\":true},{\"key\":\"my_invoice\",\"type\":\"web\",\"name\":\"client.menu.my_invoice\",\"web\":{\"url\":\"http://10.0.2.84:9000/invoice\"},\"icon_url\":\"https://image.sandbox3.cn/menu/client/v224/ic_acc_invoice.png\",\"status\":\"active\",\"login_need\":true},{\"key\":\"member_card\",\"type\":\"app\",\"name\":\"client.menu.membership_card\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v224/ic_acc_card.png\",\"status\":\"active\",\"login_need\":true}],[{\"key\":\"reset_password\",\"type\":\"app\",\"name\":\"client.menu.reset_password\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v224/ic_acc_password.png\",\"status\":\"active\",\"login_need\":true},{\"key\":\"email\",\"type\":\"app\",\"name\":\"client.menu.email\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v224/ic_acc_mail.png\",\"status\":\"active\",\"login_need\":true},{\"key\":\"phone\",\"type\":\"app\",\"name\":\"client.menu.phone\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v224/ic_acc_phone.png\",\"status\":\"active\",\"login_need\":true}],[{\"key\":\"about_us\",\"type\":\"web\",\"name\":\"client.menu.about_us\",\"web\":{\"url\":\"https://mobile.sandbox3.cn/about?version=2.2.4\"},\"icon_url\":\"https://image.sandbox3.cn/menu/client/v224/ic_about_sandbox.png\",\"status\":\"active\",\"login_need\":true},{\"key\":\"setting\",\"type\":\"app\",\"name\":\"client.menu.setting\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v224/ic_settings.png\",\"status\":\"active\",\"login_need\":true}]]}]', '[{\"type\":\"bannerCarousel\",\"hidden_asserts\":[{\"item_key\":\"banner\",\"limit\":5,\"offset\":1}],\"items\":[]},{\"type\":\"icons\",\"hidden_asserts\":[{\"item_key\":\"room_types\",\"limit\":10,\"offset\":1}],\"items\":[]},{\"type\":\"banner\",\"hidden_asserts\":[{\"item_key\":\"banner\",\"limit\":100,\"offset\":1}],\"items\":[]}]'),
                  ('client', 'android', '2.2.4', '2.2.7', '[{\"type\":\"icons\",\"items\":[{\"key\":\"order\",\"type\":\"web\",\"name\":\"client.menu.order\",\"web\":{\"url\":\"https://mobile.sandbox3.cn/search\",\"cookie\":[{\"key\":\"btype\",\"value\":\"recommend\"}]},\"icon_url\":\"https://image.sandbox3.cn/menu/client/v225/ic_booking.png\",\"status\":\"active\",\"login_need\":false},{\"key\":\"coffee\",\"type\":\"web\",\"name\":\"client.menu.coffee\",\"web\":{\"url\":\"https://coffee.sandbox3.cn\"},\"icon_url\":\"https://image.sandbox3.cn/menu/client/v225/ic_coffee.png\",\"status\":\"active\",\"login_need\":false},{\"key\":\"event\",\"type\":\"web\",\"name\":\"client.menu.event\",\"web\":{\"url\":\"https://m.sandbox3.cn/event\",\"cookie\":[{\"key\":\"btype\",\"value\":\"all\"}]},\"icon_url\":\"https://image.sandbox3.cn/menu/client/v225/ic_events.png\",\"status\":\"active\",\"login_need\":false},{\"key\":\"location\",\"type\":\"app\",\"name\":\"client.menu.location\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v225/ic_locations.png\",\"status\":\"active\",\"login_need\":false}]},{\"type\":\"list\",\"items\":[{\"key\":\"community\",\"type\":\"app\",\"name\":\"client.menu.community\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v225/ic_home.png\",\"status\":\"active\",\"login_need\":false}]},{\"type\":\"list\",\"items\":[{\"key\":\"feed\",\"type\":\"app\",\"name\":\"client.menu.blog\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v225/ic_posts.png\",\"status\":\"active\",\"login_need\":false},{\"key\":\"message\",\"type\":\"app\",\"name\":\"client.menu.message\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v225/ic_chat.png\",\"status\":\"active\",\"login_need\":true},{\"key\":\"contact\",\"type\":\"app\",\"name\":\"client.menu.contact\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v225/ic_contacts.png\",\"status\":\"active\",\"login_need\":false}]},{\"type\":\"list\",\"items\":[{\"key\":\"member\",\"type\":\"app\",\"name\":\"client.menu.member\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v225/ic_members.png\",\"status\":\"active\",\"login_need\":false},{\"key\":\"company\",\"type\":\"app\",\"name\":\"client.menu.company\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v225/ic_companies.png\",\"status\":\"active\",\"login_need\":true},{\"key\":\"my_company\",\"type\":\"app\",\"name\":\"client.menu.my_company\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v225/ic_my_companies.png\",\"status\":\"active\",\"login_need\":true}]}]', '[{\"type\":\"list\",\"menus\":[[{\"key\":\"topup\",\"type\":\"web\",\"name\":\"client.menu.balance\",\"web_url\":{\"url\":\"https://mobile.sandbox3.cn/recharge\"},\"status\":\"active\",\"login_need\":true}],[{\"key\":\"my_order\",\"type\":\"app\",\"name\":\"client.menu.my_order\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v224/ic_my_orders.png\",\"status\":\"active\",\"login_need\":true},{\"key\":\"my_room\",\"type\":\"app\",\"name\":\"client.menu.my_room\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v224/ic_used_rooms.png\",\"status\":\"active\",\"login_need\":true},{\"key\":\"announcement\",\"type\":\"app\",\"name\":\"client.menu.notification\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v224/ic_chat.png\",\"status\":\"active\",\"login_need\":true},{\"key\":\"my_invoice\",\"type\":\"web\",\"name\":\"client.menu.my_invoice\",\"web\":{\"url\":\"https://testinvoice.sandbox3.cn/invoice\"},\"icon_url\":\"https://image.sandbox3.cn/menu/client/v224/ic_acc_invoice.png\",\"status\":\"active\",\"login_need\":true},{\"key\":\"member_card\",\"type\":\"app\",\"name\":\"client.menu.membership_card\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v224/ic_acc_card.png\",\"status\":\"active\",\"login_need\":true}],[{\"key\":\"reset_password\",\"type\":\"app\",\"name\":\"client.menu.reset_password\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v224/ic_acc_password.png\",\"status\":\"active\",\"login_need\":true},{\"key\":\"email\",\"type\":\"app\",\"name\":\"client.menu.email\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v224/ic_acc_mail.png\",\"status\":\"active\",\"login_need\":true},{\"key\":\"phone\",\"type\":\"app\",\"name\":\"client.menu.phone\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v224/ic_acc_phone.png\",\"status\":\"active\",\"login_need\":true}],[{\"key\":\"about_us\",\"type\":\"web\",\"name\":\"client.menu.about_us\",\"web\":{\"url\":\"https://mobile.sandbox3.cn/about?version=2.2.4\"},\"icon_url\":\"https://image.sandbox3.cn/menu/client/v224/ic_about_sandbox.png\",\"status\":\"active\",\"login_need\":true},{\"key\":\"setting\",\"type\":\"app\",\"name\":\"client.menu.setting\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v224/ic_settings.png\",\"status\":\"active\",\"login_need\":true}]]}]', '[{\"type\":\"bannerCarousel\",\"hidden_asserts\":[{\"item_key\":\"banner\",\"limit\":5,\"offset\":1}],\"items\":[]},{\"type\":\"icons\",\"hidden_asserts\":[{\"item_key\":\"room_types\",\"limit\":10,\"offset\":1}],\"items\":[]},{\"type\":\"banner\",\"hidden_asserts\":[{\"item_key\":\"banner\",\"limit\":100,\"offset\":1}],\"items\":[]}]');
        ");

        $this->addSql("
            INSERT INTO we_chat_shares(`appId`, `secret`, `expiresIn`, `creationDate`, `modificationDate`)
            VALUES ('wxd0bc8ff918c8e593', 'be4ed95443a324b27d19e313ad7dd2e0', '7200', '2016-08-18 00:00:00', '2016-08-18 00:00:00');
        ");

        $this->addSql("
            INSERT INTO user_hobby(`name`,`key`,`creationDate`,`modificationDate`)
            VALUES('运动','sports','2015-07-08 10:00:00','2015-07-08 10:00:00'),
                  ('棋类','chess','2015-07-08 10:00:00','2015-07-08 10:00:00'),
                  ('旅游','tourism','2015-07-08 10:00:00','2015-07-08 10:00:00'),
                  ('登山运动','mountaineering','2015-07-08 10:00:00','2015-07-08 10:00:00'),
                  ('乐器','musical_instruments','2015-07-08 10:00:00','2015-07-08 10:00:00'),
                  ('音乐','music','2015-07-08 10:00:00','2015-07-08 10:00:00'),
                  ('舞蹈','dancing','2015-07-08 10:00:00','2015-07-08 10:00:00'),
                  ('饮茶','tea','2015-07-08 10:00:00','2015-07-08 10:00:00'),
                  ('影视','movie','2015-07-08 10:00:00','2015-07-08 10:00:00'),
                  ('阅读','reading','2015-07-08 10:00:00','2015-07-08 10:00:00'),
                  ('社交','social_activities','2015-07-08 10:00:00','2015-07-08 10:00:00');
        ");

        $this->addSql("
            INSERT INTO user_phone_codes(`cnName`,`enName`,`code`)
            VALUES('阿富汗','Afghanistan','+93'),
                  ('阿拉斯加','Alaska','+1907'),
                  ('阿尔巴尼亚','Albania','+355'),
                  ('阿尔及利亚','Algeria','+213'),
                  ('美国','America','+1'),
                  ('安道尔','Andorra','+376'),
                  ('安哥拉','Angola','+244'),
                  ('安圭拉岛','Anguilla I.','+1264'),
                  ('安提瓜和巴布达','Antigua and Barbuda','+1268'),
                  ('阿根廷','Argentina','+54'),
                  ('亚美尼亚','Armenia','+374'),
                  ('阿鲁巴岛','Aruba I.','+297'),
                  ('阿森松','Ascension','+247'),
                  ('澳大利亚','Australia','+61'),
                  ('奥地利','Austria','+43'),
                  ('阿塞拜疆','Azerbaijan','+994'),
                  ('巴林','Bahrain','+973'),
                  ('孟加拉国','Bangladesh','+880'),
                  ('巴巴多斯','Barbados','+1246'),
                  ('白俄罗斯','Belarus','+375'),
                  ('比利时','Belgium','+32'),
                  ('伯利兹','Belize','+501'),
                  ('贝宁','Benin','+229'),
                  ('百慕大群岛','Bermuda Is.','+1441'),
                  ('不丹','Bhutan','+975'),
                  ('玻利维亚','Bolivia','+591'),
                  ('波斯尼亚和黑塞哥维那','Bosnia And Herzegovina','+387'),
                  ('博茨瓦纳','Botswana','+267'),
                  ('巴西','Brazil','+55'),
                  ('保加利亚','Bulgaria','+359'),
                  ('布基纳法索','Burkinafaso','+226'),
                  ('布隆迪','Burundi','+257'),
                  ('喀麦隆','Cameroon','+237'),
                  ('加拿大','Canada','+1'),
                  ('加那利群岛','Canaries Is.','+34'),
                  ('佛得角','Cape Verde','+238'),
                  ('开曼群岛','Cayman Is.','+1345'),
                  ('中非','Central Africa','+236'),
                  ('乍得','Chad','+235'),
                  ('中华人民共和国','China','+86'),
                  ('智利','Chile','+56'),
                  ('圣诞岛','Christmas I.','+61 9164 '),
                  ('科科斯岛','Cocos I.','+61 9162 '),
                  ('哥伦比亚','Colombia','+57'),
                  ('巴哈马国','Commonwealth of The Bahamas','+1809'),
                  ('多米尼克国','Commonwealth of dominica','+1809'),
                  ('科摩罗','Comoro','+269'),
                  ('刚果','Congo','+242'),
                  ('科克群岛','Cook IS.','+682'),
                  ('哥斯达黎加','Costa Rica','+506'),
                  ('克罗地亚','Croatian','+383 385 '),
                  ('古巴','Cuba','+53'),
                  ('塞浦路斯','Cyprus','+357'),
                  ('捷克','Czech','+420'),
                  ('丹麦','Denmark','+45'),
                  ('迪戈加西亚岛','Diego Garcia I.','+246'),
                  ('吉布提','Djibouti','+253'),
                  ('多米尼加共和国','Dominican Rep.','+1809'),
                  ('厄瓜多尔','Ecuador','+593'),
                  ('埃及','Egypt','+20'),
                  ('萨尔瓦多','El Salvador','+503'),
                  ('赤道几内亚','Equatorial Guinea','+240'),
                  ('厄立特里亚','Eritrea','+291'),
                  ('爱沙尼亚','Estonia','+372'),
                  ('埃塞俄比亚','Ethiopia','+251'),
                  ('福克兰群岛','Falkland Is.','+500'),
                  ('法罗群岛','Faroe Is.','+298'),
                  ('斐济','Fiji','+679'),
                  ('芬兰','Finland','+358'),
                  ('法国','France','+33'),
                  ('法属圭亚那','French Guiana','+594'),
                  ('法属波里尼西亚','French Polynesia','+689'),
                  ('加蓬','Gabon','+241'),
                  ('冈比亚','Gambia','+220'),
                  ('格鲁吉亚','Georgia','+995'),
                  ('德国','Germany','+49'),
                  ('加纳','Ghana','+233'),
                  ('直布罗陀','Gibraltar','+350'),
                  ('希腊','Greece','+30'),
                  ('格陵兰岛','Greenland','+299'),
                  ('格林纳达','Grenada','+1809'),
                  ('瓜德罗普岛','Guadeloupe I.','+590'),
                  ('关岛','Guam','+671'),
                  ('危地马拉','Guatemala','+502'),
                  ('几内亚','Guinea','+224'),
                  ('几内亚比绍','Guinea-bissau','+245'),
                  ('圭亚那','Guyana','+592'),
                  ('海地','Haiti','+509'),
                  ('夏威夷','Hawaii','+1808'),
                  ('洪都拉斯','Honduras','+504'),
                  ('匈牙利','HunGary','+36'),
                  ('冰岛','Iceland','+354'),
                  ('印度','India','+91'),
                  ('印度尼西亚','Indonesia','+62'),
                  ('伊郎','Iran','+98'),
                  ('伊拉克','Iraq','+964'),
                  ('爱尔兰','Ireland','+353'),
                  ('以色列','Israel','+972'),
                  ('意大利','Italy','+39'),
                  ('科特迪瓦','Ivory Coast','+225'),
                  ('牙买加','Jamaica','+1876'),
                  ('日本','Japan','+81'),
                  ('约旦','Jordan','+962'),
                  ('柬埔塞','Kampuchea','+855'),
                  ('哈萨克斯坦','Kazakhstan','+7'),
                  ('肯尼亚','Kenya','+254'),
                  ('基里巴斯','Kiribati','+686'),
                  ('朝鲜','North Korea','+850'),
                  ('韩国','South Korea','+82'),
                  ('科威特','Kuwait','+965'),
                  ('吉尔吉斯斯坦','Kyrgyzstan','+7'),
                  ('老挝','Laos','+856'),
                  ('拉脱维亚','Latvia','+371'),
                  ('黎巴嫩','Lebanon','+961'),
                  ('莱索托','Lesotho','+266'),
                  ('利比里亚','Liberia','+231'),
                  ('利比亚','Libya','+218'),
                  ('列支敦士登','Liechtenstein','+41 75 '),
                  ('立陶宛','Lithuania','+370'),
                  ('卢森堡','Luxembourg','+352'),
                  ('马其顿','Macedonia','+389'),
                  ('马达加斯加','Madagascar','+261'),
                  ('马拉维','Malawi','+265'),
                  ('马来西亚','Malaysia','+60'),
                  ('马尔代夫','Maldive','+960'),
                  ('马里','Mali','+223'),
                  ('马耳他','Malta','+356'),
                  ('马里亚纳群岛','Mariana Is.','+670'),
                  ('马绍尔群岛','Marshall Is.','+692'),
                  ('马提尼克','Martinique','+596'),
                  ('毛里塔尼亚','Mauritania','+222'),
                  ('毛里求斯','Mauritius','+230'),
                  ('马约特岛','Mayotte I.','+269'),
                  ('墨西哥','Mexico','+52'),
                  ('密克罗尼西亚','Micronesia','+691'),
                  ('中途岛','Midway I.','+1808'),
                  ('摩尔多瓦','Moldova','+373'),
                  ('摩纳哥','Monaco','+377'),
                  ('蒙古','Mongolia','+976'),
                  ('蒙特塞拉特岛','Montserrat I.','+1664'),
                  ('摩洛哥','Morocco','+212'),
                  ('莫桑比克','Mozambique','+258'),
                  ('缅甸','Myanmar','+95'),
                  ('纳米比亚','Namibia','+264'),
                  ('瑙鲁','Nauru','+674'),
                  ('尼泊尔','Nepal','+977'),
                  ('荷兰','Netherlands','+31'),
                  ('荷属安的列斯群岛','Netherlandsantilles Is.','+599'),
                  ('新喀里多尼亚群岛','New Caledonia Is.','+687'),
                  ('新西兰','New Zealand','+64'),
                  ('尼加拉瓜','Nicaragua','+505'),
                  ('尼日尔','Niger','+227'),
                  ('尼日利亚','Nigeria','+234'),
                  ('纽埃岛','Niue I.','+683'),
                  ('诺福克岛','Norfolk I,','+6723'),
                  ('挪威','Norway','+47'),
                  ('阿曼','Oman','+968'),
                  ('帕劳','Palau','+680'),
                  ('巴拿马','Panama','+507'),
                  ('巴布亚新几内亚','Papua New Guinea','+675'),
                  ('巴拉圭','Paraguay','+595'),
                  ('秘鲁','Peru','+51'),
                  ('菲律宾','Philippines','+63'),
                  ('波兰','Poland','+48'),
                  ('葡萄牙','Portugal','+351'),
                  ('巴基斯坦','Pskistan','+92'),
                  ('波多黎各','Puerto Rico','+1787'),
                  ('卡塔尔','Qatar','+974'),
                  ('留尼汪岛','Reunion I.','+262'),
                  ('罗马尼亚','Rumania','+40'),
                  ('俄罗斯','Russia','+7'),
                  ('卢旺达','Rwanda','+250'),
                  ('东萨摩亚','Samoa ,Eastern','+684'),
                  ('西萨摩亚','Samoa ,Western','+685'),
                  ('圣马力诺','San.Marino','+378'),
                  ('圣皮埃尔岛及密克隆岛','San.Pierre And Miquelon I.','+508'),
                  ('圣多美和普林西比','San.Tome And Principe','+239'),
                  ('沙特阿拉伯','Saudi Arabia','+966'),
                  ('塞内加尔','Senegal','+221'),
                  ('塞舌尔','Seychelles','+248'),
                  ('新加坡','Singapore','+65'),
                  ('斯洛伐克','Slovak','+421'),
                  ('斯洛文尼亚','Slovenia','+386'),
                  ('所罗门群岛','Solomon Is.','+677'),
                  ('索马里','Somali','+252'),
                  ('南非','South Africa','+27'),
                  ('西班牙','Spain','+34'),
                  ('斯里兰卡','Sri Lanka','+94'),
                  ('圣克里斯托弗和尼维斯','St.Christopher and Nevis','+1809'),
                  ('圣赫勒拿','St.Helena','+290'),
                  ('圣卢西亚','St.Lucia','+1758'),
                  ('圣文森特岛','St.Vincent I.','+1784'),
                  ('苏丹','Sudan','+249'),
                  ('苏里南','Suriname','+597'),
                  ('斯威士兰','Swaziland','+268'),
                  ('瑞典','Sweden','+46'),
                  ('瑞士','Switzerland','+41'),
                  ('叙利亚','Syria','+963'),
                  ('塔吉克斯坦','Tajikistan','+7'),
                  ('坦桑尼亚','Tanzania','+255'),
                  ('泰国','Thailand','+66'),
                  ('阿拉伯联合酋长国','The United Arab Emirates','+971'),
                  ('多哥','Togo','+228'),
                  ('托克劳群岛','Tokelau Is.','+690'),
                  ('汤加','Tonga','+676'),
                  ('特立尼达和多巴哥','Trinidad and Tobago','+1809'),
                  ('突尼斯','Tunisia','+216'),
                  ('土耳其','Turkey','+90'),
                  ('土库曼斯坦','Turkmenistan','+993'),
                  ('特克斯和凯科斯群岛','Turks and Caicos Is.','+1809'),
                  ('图瓦卢','Tuvalu','+688'),
                  ('乌干达','Uganda','+256'),
                  ('乌克兰','Ukraine','+380'),
                  ('英国','United Kingdom','+44'),
                  ('乌拉圭','Uruguay','+598'),
                  ('乌兹别克斯坦','Uzbekistan','+7'),
                  ('瓦努阿图','Vanuatu','+678'),
                  ('梵蒂冈','Vatican','+379'),
                  ('委内瑞拉','Venezuela','+58'),
                  ('越南','Vietnam','+84'),
                  ('维尔京群岛','Virgin Is.','+1809'),
                  ('维尔京群岛和圣罗克伊','Virgin Is. and St.Croix I.','+1809'),
                  ('威克岛','Wake I.','+1808'),
                  ('瓦里斯和富士那群岛','Wallis And Futuna Is.','+681'),
                  ('西撒哈拉','Western sahara','+967'),
                  ('也门','Yemen','+967'),
                  ('南斯拉夫','Yugoslavia','+381'),
                  ('扎伊尔','Zaire','+243'),
                  ('赞比亚','Zambia','+260'),
                  ('桑给巴尔','Zanzibar','+259'),
                  ('津巴布韦','Zimbabwe','+263');
        ");

        $this->addSql("
            INSERT INTO `user_profile_my_orders` (`name`, `icon`, `url`)
            VALUES('room_order', 'https://image.sandbox3.cn/icon/profile_orders_room_order.png', 'https://orders.sandbox3.cn/room'),
                  ('shop_order', 'https://image.sandbox3.cn/icon/profile_orders_shop_order.png', 'https://orders.sandbox3.cn/shop'),
                  ('event_order', 'https://image.sandbox3.cn/icon/profile_orders_event_order.png', 'https://orders.sandbox3.cn/event');
        ");

        $this->addSql('
            CREATE OR REPLACE VIEW admin_api_auth_view AS
            SELECT
                t.id,
                t.token,
                t.clientId,
                a.id AS adminId,
                a.username
            FROM admin_token AS t
            JOIN admin AS a ON t.adminId = a.id
            WHERE
                t.creationDate > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 5 DAY)
            ;
        ');
        $this->addSql('
            CREATE OR REPLACE VIEW room_usage_view AS
            SELECT
              id,
                productId,
                status,
                startDate,
                endDate,
                userId as user,
                appointedPerson as appointedUser
            FROM product_order
            ;
        ');
        $this->addSql('
            CREATE OR REPLACE VIEW room_view AS
            SELECT
                r.*,
                o.status,
                o.startDate as orderStartDate,
                o.endDate as orderEndDate,
                up.userId as renterId,
                up.name as renterName,
                up.email as renterEmail
            FROM room r
            JOIN room_floor rf ON rf.id = r.floorId
            LEFT JOIN product p ON r.id = p.roomId
            LEFT JOIN product_order o ON p.id = o.productId
            LEFT JOIN user_profiles up ON o.userId = up.userId
            ;
        ');
        $this->addSql('
            CREATE OR REPLACE VIEW client_api_auth_view AS
            SELECT
                t.id,
                t.token,
                t.clientId,
                u.id AS userId
            FROM user_token AS t
            JOIN user AS u ON t.userId = u.id
            WHERE
                t.modificationDate > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 30 DAY)
                AND
                u.banned != 1
                AND
                t.online = 1
            ;
        ');
        $this->addSql('
            CREATE OR REPLACE VIEW feed_view AS
            SELECT DISTINCT f.*,
                   (SELECT COUNT(fc.id) FROM feed_comment fc LEFT JOIN user u1 ON u1.id = fc.authorId WHERE fc.feedId = f.id AND u1.banned = FALSE ) AS comments_count,
                   (SELECT COUNT(fl.id) FROM feed_likes fl LEFT JOIN user u2 ON u2.id = fl.authorId WHERE fl.feedId = f.id AND u2.banned = FALSE ) AS likes_count
            FROM feed AS f;
        ');
        $this->addSql('
            CREATE OR REPLACE VIEW user_view AS
            SELECT
                   u.id,
                   u.phone,
                   u.email,
                   u.banned,
                   u.authorized,
                   u.cardNo,
                   u.credentialNo,
                   u.authorizedPlatform,
                   u.authorizedAdminUsername,
                   up.name,
                     up.gender,
                     u.creationDate as userRegistrationDate
            FROM user u
            LEFT JOIN user_profiles up ON u.id = up.userId
            ;
        ');
        $this->addSql('
            CREATE OR REPLACE VIEW `sales_admin_api_auth_view` AS
            SELECT
                `t`.`id` AS `id`,
                `t`.`token` AS `token`,
                `t`.`clientId` AS `clientId`,
                `a`.`id` AS `adminId`,
                `a`.`username` AS `username`
            FROM (
                `sales_admin_token` `t`
              JOIN `sales_admin` `a`
                ON((`t`.`adminId` = `a`.`id`))
                )
            WHERE (
                `t`.`creationDate` > (now() - interval 5 day)
            );
        ');
        $this->addSql('
            CREATE OR REPLACE VIEW `shop_admin_api_auth_view` AS
            SELECT
                `t`.`id` AS `id`,
                `t`.`token` AS `token`,
                `t`.`clientId` AS `clientId`,
                `a`.`id` AS `adminId`,
                `a`.`username` AS `username`
            FROM (
                `shop_admin_token` `t`
              JOIN `shop_admin` `a`
                ON((`t`.`adminId` = `a`.`id`))
                )
            WHERE (
                `t`.`creationDate` > (now() - interval 5 day)
            );
        ');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
