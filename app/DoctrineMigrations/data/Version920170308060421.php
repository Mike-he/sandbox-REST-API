<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Sandbox\ApiBundle\Entity\Menu\Menu;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170308060421 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

    }

    public function postUp(Schema $schema)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        $mainJson = '[{"type":"main_topup","items":[{"key":"my_profile","type":"app","url_map":{"bg_url":"https://image.sandbox3.cn/menu/client/v233/ic_avatar_bg.png","bg_bottom_url":"https://image.sandbox3.cn/menu/client/v233/ic_avatar_bg_bottom.png","avatar_frame_url":"https://image.sandbox3.cn/menu/client/v233/ic_avatar_frame.png","qr_code_frame_url":"https://image.sandbox3.cn/menu/client/v233/ic_qr_code_frame.png","bg_color_code":"#d83136"},"status":"active","login_need":true}]},{"type":"icons","items":[{"key":"order","type":"web","name":"client.menu.order","web":{"url":"https://mobile.sandbox3.cn/search","cookie":[{"key":"btype","value":"recommend"}]},"icon_url":"https://image.sandbox3.cn/menu/client/v233/ic_booking.png","status":"active","login_need":false},{"key":"coffee","type":"web","name":"client.menu.coffee","web":{"url":"https://coffee.sandbox3.cn"},"icon_url":"https://image.sandbox3.cn/menu/client/v233/ic_coffee.png","status":"active","login_need":false},{"key":"event","type":"web","name":"client.menu.event","web":{"url":"https://m.sandbox3.cn/event","cookie":[{"key":"btype","value":"all"}]},"icon_url":"https://image.sandbox3.cn/menu/client/v233/ic_events.png","status":"active","login_need":false},{"key":"location","type":"app","name":"client.menu.location","icon_url":"https://image.sandbox3.cn/menu/client/v233/ic_locations.png","status":"active","login_need":false}]},{"type":"list","items":[{"key":"community","type":"app","name":"client.menu.community","icon_url":"https://image.sandbox3.cn/menu/client/v233/ic_home.png","status":"active","login_need":false}]},{"type":"list","items":[{"key":"feed","type":"app","name":"client.menu.blog","icon_url":"https://image.sandbox3.cn/menu/client/v233/ic_posts.png","status":"inactive","login_need":false},{"key":"message","type":"app","name":"client.menu.message","icon_url":"https://image.sandbox3.cn/menu/client/v233/ic_chat.png","status":"active","login_need":true},{"key":"contact","type":"app","name":"client.menu.contact","icon_url":"https://image.sandbox3.cn/menu/client/v233/ic_contacts.png","status":"active","login_need":false}]},{"type":"list","items":[{"key":"member","type":"app","name":"client.menu.member","icon_url":"https://image.sandbox3.cn/menu/client/v233/ic_members.png","status":"active","login_need":false},{"key":"company","type":"app","name":"client.menu.company","icon_url":"https://image.sandbox3.cn/menu/client/v233/ic_companies.png","status":"inactive","login_need":true},{"key":"my_company","type":"app","name":"client.menu.my_company","icon_url":"https://image.sandbox3.cn/menu/client/v233/ic_my_companies.png","status":"inactive","login_need":true}]},{"type":"list","items":[{"key":"scan","type":"app","name":"client.menu.scan_qr","icon_url":"https://image.sandbox3.cn/menu/client/v233/ic_scan_qr.png","status":"active","login_need":false}]}]';
        $profileJson = '[{"type":"profile_topup","items":[{"key":"my_profile","type":"app","status":"active","login_need":true},{"key":"recharge","type":"web","name":"client.menu.balance","web":{"url":"https://mobile.sandbox3.cn/recharge","cookie":[{"key":"","value":""}]},"status":"active","login_need":true},{"key":"my_trade","type":"items","status":"active","login_need":true,"items":[{"key":"my_space_order","type":"web","name":"client.menu.my_order","web":{"url":"https://orders.sandbox3.cn/room","cookie":[{"key":"","value":""}]},"icon_url":"https://image.sandbox3.cn/menu/client/v233/ic_space_order.png","status":"active","login_need":true},{"key":"my_lease","type":"web","name":"client.menu.my_lease","web":{"url":"https://orders.sandbox3.cn/contract","cookie":[{"key":"","value":""}]},"icon_url":"https://image.sandbox3.cn/menu/client/v233/ic_lease.png","status":"active","login_need":true},{"key":"my_long_term_appointment","type":"web","name":"client.menu.my_long_term_appointment","web":{"url":"https://orders.sandbox3.cn/longrent","cookie":[{"key":"","value":""}]},"icon_url":"https://image.sandbox3.cn/menu/client/v233/ic_appointment.png","status":"active","login_need":true},{"key":"my_shop_order","type":"web","name":"client.menu.my_shop_order","web":{"url":"https://orders.sandbox3.cn/shop","cookie":[{"key":"","value":""}]},"icon_url":"https://image.sandbox3.cn/menu/client/v233/ic_shop_order.png","status":"active","login_need":true},{"key":"my_activity_order","type":"web","name":"client.menu.my_activity_order","web":{"url":"https://orders.sandbox3.cn/event","cookie":[{"key":"","value":""}]},"icon_url":"https://image.sandbox3.cn/menu/client/v233/ic_activity.png","status":"active","login_need":true}]}]},{"type":"list","items":[{"key":"my_room","type":"app","name":"client.menu.my_room","icon_url":"https://image.sandbox3.cn/menu/client/v233/ic_used_rooms.png","status":"active","login_need":true},{"key":"my_favorite","type":"app","name":"client.menu.my_favorite","icon_url":"https://image.sandbox3.cn/menu/client/v233/ic_my_favorite.png","status":"active","login_need":true},{"key":"my_evaluation","type":"app","name":"client.menu.my_evaluation","icon_url":"https://image.sandbox3.cn/menu/client/v233/ic_my_evaluation.png","status":"active","login_need":true},{"key":"announcement","type":"app","name":"client.menu.notification","icon_url":"https://image.sandbox3.cn/menu/client/v233/ic_chat.png","status":"active","login_need":true},{"key":"my_invoice","type":"web","name":"client.menu.my_invoice","web":{"url":"https://invoice.sandbox3.cn/invoice"},"icon_url":"https://image.sandbox3.cn/menu/client/v233/ic_acc_invoice.png","status":"active","login_need":true},{"key":"member_card","type":"app","name":"client.menu.my_membership_card","icon_url":"https://image.sandbox3.cn/menu/client/v233/ic_acc_card.png","status":"active","login_need":true}]},{"type":"list","items":[{"key":"reset_password","type":"app","name":"client.menu.reset_password","icon_url":"https://image.sandbox3.cn/menu/client/v233/ic_acc_password.png","status":"active","login_need":true},{"key":"email","type":"app","name":"client.menu.email","icon_url":"https://image.sandbox3.cn/menu/client/v233/ic_acc_mail.png","status":"active","login_need":true},{"key":"phone","type":"app","name":"client.menu.phone","icon_url":"https://image.sandbox3.cn/menu/client/v233/ic_acc_phone.png","status":"active","login_need":true}]},{"type":"list","items":[{"key":"about_us","type":"web","name":"client.menu.about_us","web":{"url":"https://mobile.sandbox3.cn/about?version=2.3.6"},"icon_url":"https://image.sandbox3.cn/menu/client/v233/ic_about_sandbox.png","status":"active","login_need":false},{"key":"setting","type":"app","name":"client.menu.setting","icon_url":"https://image.sandbox3.cn/menu/client/v233/ic_settings.png","status":"active","login_need":false}]}]';
        $homeJson = '[{"type":"bannerCarousel","hidden_asserts":[{"item_key":"banner","limit":5,"offset":1}],"items":[]},{"type":"icons","hidden_asserts":[{"item_key":"room_types","limit":10,"offset":1}],"items":[{"key":"location","type":"app","name":"client.menu.location","icon_url":"https://image.sandbox3.cn/menu/client/v233/ic_locations.png","status":"active","login_need":false}]},{"type":"banner","hidden_asserts":[{"item_key":"banner","limit":100,"offset":1}],"items":[]}]';

        $menuIphone = new Menu();
        $menuIphone->setComponent(Menu::COMPONENT_CLIENT);
        $menuIphone->setPlatform(Menu::PLATFORM_IPHONE);
        $menuIphone->setMinVersion('2.3.10');
        $menuIphone->setMaxVersion('2.3.10');
        $menuIphone->setMainJson($mainJson);
        $menuIphone->setProfileJson($profileJson);
        $menuIphone->setHomeJson($homeJson);
        $em->persist($menuIphone);

        $menuAndroid = new Menu();
        $menuAndroid->setComponent(Menu::COMPONENT_CLIENT);
        $menuAndroid->setPlatform(Menu::PLATFORM_ANDROID);
        $menuAndroid->setMinVersion('2.3.10');
        $menuAndroid->setMaxVersion('2.3.10');
        $menuAndroid->setMainJson($mainJson);
        $menuAndroid->setProfileJson($profileJson);
        $menuAndroid->setHomeJson($homeJson);
        $em->persist($menuAndroid);

        $em->flush();
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
