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
class Version920170612090129 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

    }

    /**
     * @param Schema $schema
     */
    public function postUp(Schema $schema)
    {
        parent::postUp($schema);

        $em = $this->container->get('doctrine.orm.entity_manager');

        $mainJson = '{"main":[{"type":"main_topup","items":[{"key":"my_profile","type":"app","status":"active","login_need":true}]},{"type":"list","items":[{"key":"my_order","type":"web","web":{"url":"{{orders}}/","cookie":[{"key":"","value":""}]},"name":"client.menu.my_order","icon_url":"{{image}}/menu/client/latest/ic_my_orders.png","status":"active","login_need":true},{"key":"activity","type":"web","name":"client.menu.event","web":{"url":"{{m}}/event","cookie":[{"key":"btype","value":"all"}]},"icon_url":"{{image}}/menu/client/latest/ic_activity.png","status":"active","login_need":false},{"key":"coffee","type":"web","name":"client.menu.coffee","web":{"url":"{{coffee}}"},"icon_url":"{{image}}/menu/client/latest/ic_coffee.png","status":"active","login_need":false},{"key":"message","type":"app","name":"client.menu.message","icon_url":"{{image}}/menu/client/latest/ic_chat.png","status":"active","login_need":true},{"key":"my_center","type":"app","name":"client.menu.my_center","icon_url":"{{image}}/menu/client/latest/ic_my_center.png","status":"active","login_need":true}]}],"profile":[{"type":"profile_topup","items":[{"key":"my_profile","type":"app","status":"active","login_need":true},{"key":"recharge","type":"web","name":"client.menu.balance","web":{"url":"{{mobile}}/recharge","cookie":[{"key":"","value":""}]},"status":"active","login_need":true}]},{"type":"list","items":[{"key":"my_favorite","type":"app","name":"client.menu.my_favorite","icon_url":"{{image}}/menu/client/latest/ic_my_favorite.png","status":"active","login_need":true},{"key":"my_evaluation","type":"app","name":"client.menu.my_evaluation","icon_url":"{{image}}/menu/client/latest/ic_my_evaluation.png","status":"active","login_need":true},{"key":"my_invoice","type":"web","name":"client.menu.my_invoice","web":{"url":"{{invoice}}/invoice"},"icon_url":"{{image}}/menu/client/latest/ic_acc_invoice.png","status":"active","login_need":true},{"key":"member_card","type":"app","name":"client.menu.my_membership_card","icon_url":"{{image}}/menu/client/latest/ic_acc_card.png","status":"active","login_need":true}]},{"type":"list","items":[{"key":"reset_password","type":"app","name":"client.menu.reset_password","icon_url":"{{image}}/menu/client/latest/ic_acc_password.png","status":"active","login_need":true},{"key":"payment_password","type":"app","name":"client.menu.payment_password","icon_url":"{{image}}/menu/client/latest/ic_payment_password.png","status":"active","login_need":true},{"key":"email","type":"app","name":"client.menu.email","icon_url":"{{image}}/menu/client/latest/ic_acc_mail.png","status":"active","login_need":true},{"key":"phone","type":"app","name":"client.menu.phone","icon_url":"{{image}}/menu/client/latest/ic_acc_phone.png","status":"active","login_need":true}]},{"type":"list","items":[{"key":"about_us","type":"web","name":"client.menu.about_us","web":{"url":"{{mobile}}/about?version="},"icon_url":"{{image}}/menu/client/latest/ic_about_sandbox.png","status":"active","login_need":false},{"key":"setting","type":"app","name":"client.menu.setting","icon_url":"{{image}}/menu/client/latest/ic_settings.png","status":"active","login_need":false}]}],"fast_link":[{"key":"scan","type":"app","name":"client.menu.scan_qr","icon_url":"{{image}}/menu/client/latest/ic_scan_qr.png","status":"active","login_need":false},{"key":"add_buddy","type":"app","name":"client.menu.add_buddy","icon_url":"{{image}}/menu/client/latest/ic_add_buddy.png","status":"active","login_need":true},{"key":"my_qr","type":"app","name":"client.menu.my_qr","icon_url":"{{image}}/menu/client/latest/ic_my_qr.png","status":"active","login_need":true},{"key":"announcement","type":"app","name":"client.menu.notification","icon_url":"{{image}}/menu/client/latest/ic_notification.png","status":"active","login_need":true},{"key":"my_room","type":"app","name":"client.menu.my_room","icon_url":"{{image}}/menu/client/latest/ic_my_room.png","status":"active","login_need":true}]}';
        $profileJson = '';
        $homeJson = '';

        $menuIphone = new Menu();
        $menuIphone->setComponent(Menu::COMPONENT_CLIENT);
        $menuIphone->setPlatform(Menu::PLATFORM_IPHONE);
        $menuIphone->setMinVersion('3.0.0');
        $menuIphone->setMaxVersion('3.0.99');
        $menuIphone->setMainJson($mainJson);
        $menuIphone->setProfileJson($profileJson);
        $menuIphone->setHomeJson($homeJson);
        $em->persist($menuIphone);

        $menuAndroid = new Menu();
        $menuAndroid->setComponent(Menu::COMPONENT_CLIENT);
        $menuAndroid->setPlatform(Menu::PLATFORM_ANDROID);
        $menuAndroid->setMinVersion('3.0.0');
        $menuAndroid->setMaxVersion('3.0.99');
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
