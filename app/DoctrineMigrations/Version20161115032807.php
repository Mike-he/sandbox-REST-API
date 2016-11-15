<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161115032807 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql("INSERT INTO `menu`(`component`,`platform`,`mainJson`,`minVersion`,`maxVersion`,`profileJson`,`homeJson`)
            VALUES(
            'client',
            'android',
            '[{\"type\":\"icons\",\"items\":[{\"key\":\"order\",\"type\":\"web\",\"name\":\"client.menu.order\",\"web\":{\"url\":\"https://mobile.sandbox3.cn/search\",\"cookie\":[{\"key\":\"btype\",\"value\":\"recommend\"}]},\"icon_url\":\"https://image.sandbox3.cn/menu/client/v230/ic_booking.png\",\"status\":\"active\",\"login_need\":false},{\"key\":\"coffee\",\"type\":\"web\",\"name\":\"client.menu.coffee\",\"web\":{\"url\":\"https://coffee.sandbox3.cn\"},\"icon_url\":\"https://image.sandbox3.cn/menu/client/v230/ic_coffee.png\",\"status\":\"active\",\"login_need\":false},{\"key\":\"event\",\"type\":\"web\",\"name\":\"client.menu.event\",\"web\":{\"url\":\"https://m.sandbox3.cn/event\",\"cookie\":[{\"key\":\"btype\",\"value\":\"all\"}]},\"icon_url\":\"https://image.sandbox3.cn/menu/client/v230/ic_events.png\",\"status\":\"active\",\"login_need\":false},{\"key\":\"location\",\"type\":\"app\",\"name\":\"client.menu.location\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v230/ic_locations.png\",\"status\":\"active\",\"login_need\":false}]},{\"type\":\"list\",\"items\":[{\"key\":\"community\",\"type\":\"app\",\"name\":\"client.menu.community\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v230/ic_home.png\",\"status\":\"active\",\"login_need\":false}]},{\"type\":\"list\",\"items\":[{\"key\":\"feed\",\"type\":\"app\",\"name\":\"client.menu.blog\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v230/ic_posts.png\",\"status\":\"active\",\"login_need\":false},{\"key\":\"message\",\"type\":\"app\",\"name\":\"client.menu.message\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v230/ic_chat.png\",\"status\":\"active\",\"login_need\":true},{\"key\":\"contact\",\"type\":\"app\",\"name\":\"client.menu.contact\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v230/ic_contacts.png\",\"status\":\"active\",\"login_need\":false}]},{\"type\":\"list\",\"items\":[{\"key\":\"member\",\"type\":\"app\",\"name\":\"client.menu.member\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v230/ic_members.png\",\"status\":\"active\",\"login_need\":false},{\"key\":\"company\",\"type\":\"app\",\"name\":\"client.menu.company\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v230/ic_companies.png\",\"status\":\"active\",\"login_need\":true},{\"key\":\"my_company\",\"type\":\"app\",\"name\":\"client.menu.my_company\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v230/ic_my_companies.png\",\"status\":\"active\",\"login_need\":true}]},{\"type\":\"list\",\"items\":[{\"key\":\"scan\",\"type\":\"app\",\"name\":\"client.menu.scan_qr\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v230/ic_scan_qr.png\",\"status\":\"active\",\"login_need\":false}]}]',
            '2.3.0',
            '2.3.0',
            '[{\"type\":\"list\",\"menus\":[[{\"key\":\"topup\",\"type\":\"web\",\"name\":\"client.menu.balance\",\"web_url\":{\"url\":\"https://mobile.sandbox3.cn/recharge\"},\"status\":\"active\",\"login_need\":true}],[{\"key\":\"my_order\",\"type\":\"app\",\"name\":\"client.menu.my_order\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v230/ic_my_orders.png\",\"status\":\"active\",\"login_need\":true},{\"key\":\"my_room\",\"type\":\"app\",\"name\":\"client.menu.my_room\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v230/ic_used_rooms.png\",\"status\":\"active\",\"login_need\":true},{\"key\":\"announcement\",\"type\":\"app\",\"name\":\"client.menu.notification\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v230/ic_chat.png\",\"status\":\"active\",\"login_need\":true},{\"key\":\"my_invoice\",\"type\":\"web\",\"name\":\"client.menu.my_invoice\",\"web\":{\"url\":\"https://invoice.sandbox3.cn/invoice\"},\"icon_url\":\"https://image.sandbox3.cn/menu/client/v230/ic_acc_invoice.png\",\"status\":\"active\",\"login_need\":true},{\"key\":\"member_card\",\"type\":\"app\",\"name\":\"client.menu.membership_card\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v230/ic_acc_card.png\",\"status\":\"active\",\"login_need\":true}],[{\"key\":\"reset_password\",\"type\":\"app\",\"name\":\"client.menu.reset_password\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v230/ic_acc_password.png\",\"status\":\"active\",\"login_need\":true},{\"key\":\"email\",\"type\":\"app\",\"name\":\"client.menu.email\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v230/ic_acc_mail.png\",\"status\":\"active\",\"login_need\":true},{\"key\":\"phone\",\"type\":\"app\",\"name\":\"client.menu.phone\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v230/ic_acc_phone.png\",\"status\":\"active\",\"login_need\":true}],[{\"key\":\"about_us\",\"type\":\"web\",\"name\":\"client.menu.about_us\",\"web\":{\"url\":\"https://mobile.sandbox3.cn/about?version=2.3.0\"},\"icon_url\":\"https://image.sandbox3.cn/menu/client/v230/ic_about_sandbox.png\",\"status\":\"active\",\"login_need\":true},{\"key\":\"setting\",\"type\":\"app\",\"name\":\"client.menu.setting\",\"icon_url\":\"https://image.sandbox3.cn/menu/client/v230/ic_settings.png\",\"status\":\"active\",\"login_need\":true}]]}]',
            '[{\"type\":\"bannerCarousel\",\"hidden_asserts\":[{\"item_key\":\"banner\",\"limit\":5,\"offset\":1}],\"items\":[]},{\"type\":\"icons\",\"hidden_asserts\":[{\"item_key\":\"room_types\",\"limit\":10,\"offset\":1}],\"items\":[]},{\"type\":\"banner\",\"hidden_asserts\":[{\"item_key\":\"banner\",\"limit\":100,\"offset\":1}],\"items\":[]}]'
        )");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
