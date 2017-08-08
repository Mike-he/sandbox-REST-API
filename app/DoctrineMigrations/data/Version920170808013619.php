<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170808013619 extends AbstractMigration implements ContainerAwareInterface
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
        parent::postUp($schema);

        $em = $this->container->get('doctrine.orm.entity_manager');

        $version30x = $em->getRepository('SandboxApiBundle:Menu\Menu')
            ->findBy(array(
                'minVersion' => '3.0.0',
            ));

        $mainJson = '{"main":[{"type":"main_topup","items":[{"key":"my_profile","type":"app","status":"active","login_need":true}]},{"type":"list","items":[{"key":"my_order","type":"web","web":{"url":"{{orders}}/home","cookie":[{"key":"","value":""}]},"name":"client.menu.my_order","icon_url":"{{image}}/menu/client/latest/ic_my_orders.png","status":"active","login_need":true},{"key":"activity","type":"web","name":"client.menu.event","web":{"url":"{{m}}/event","cookie":[{"key":"btype","value":"all"}]},"icon_url":"{{image}}/menu/client/latest/ic_activity.png","status":"active","login_need":false},{"key":"coffee","type":"web","name":"client.menu.coffee","web":{"url":"{{coffee}}"},"icon_url":"{{image}}/menu/client/latest/ic_coffee.png","status":"active","login_need":false},{"key":"message","type":"app","name":"client.menu.message","icon_url":"{{image}}/menu/client/latest/ic_chat.png","status":"active","login_need":true},{"key":"my_center","type":"app","name":"client.menu.my_center","icon_url":"{{image}}/menu/client/latest/ic_my_center.png","status":"active","login_need":true}]}],"profile":[{"type":"profile_topup","items":[{"key":"my_profile","type":"app","status":"active","login_need":true},{"key":"recharge","type":"web","name":"client.menu.balance","web":{"url":"{{mobile}}/recharge","cookie":[{"key":"","value":""}]},"status":"active","login_need":true}]},{"type":"list","items":[{"key":"my_application","type":"web","name":"client.menu.my_long_term_appointment","icon_url":"{{image}}/menu/client/latest/ic_my_application.png","web":{"url":"{{orders}}/longrent"},"status":"active","login_need":true},{"key":"my_favorite","type":"app","name":"client.menu.my_favorite","icon_url":"{{image}}/menu/client/latest/ic_my_favorite.png","status":"active","login_need":true},{"key":"my_evaluation","type":"app","name":"client.menu.my_evaluation","icon_url":"{{image}}/menu/client/latest/ic_my_evaluation.png","status":"active","login_need":true},{"key":"my_invoice","type":"web","name":"client.menu.my_invoice","web":{"url":"{{invoice}}/invoice"},"icon_url":"{{image}}/menu/client/latest/ic_acc_invoice.png","status":"active","login_need":true},{"key":"member_card","type":"app","name":"client.menu.my_membership_card","icon_url":"{{image}}/menu/client/latest/ic_acc_card.png","status":"active","login_need":true,"link":{"key":"member_card_order","type":"web","web":{"url":"{{orders}}/member","cookie":[{"key":"ptype","value":"history"}]}}}]},{"type":"list","items":[{"key":"reset_password","type":"app","name":"client.menu.reset_password","icon_url":"{{image}}/menu/client/latest/ic_acc_password.png","status":"active","login_need":true},{"key":"payment_password","type":"app","name":"client.menu.payment_password","icon_url":"{{image}}/menu/client/latest/ic_payment_password.png","status":"active","login_need":true},{"key":"email","type":"app","name":"client.menu.email","icon_url":"{{image}}/menu/client/latest/ic_acc_mail.png","status":"active","login_need":true},{"key":"phone","type":"app","name":"client.menu.phone","icon_url":"{{image}}/menu/client/latest/ic_acc_phone.png","status":"active","login_need":true},{"key":"bind_wx","type":"app","name":"client.menu.bind_wx","icon_url":"{{image}}/menu/client/latest/ic_bind_wx.png","status":"active","login_need":true}]},{"type":"list","items":[{"key":"about_us","type":"web","name":"client.menu.about_us","web":{"url":"{{mobile}}/about?version="},"icon_url":"{{image}}/menu/client/latest/ic_about_sandbox.png","status":"active","login_need":false},{"key":"setting","type":"app","name":"client.menu.setting","icon_url":"{{image}}/menu/client/latest/ic_settings.png","status":"active","login_need":false}]}],"fast_link":[{"key":"scan","type":"app","name":"client.menu.scan_qr","icon_url":"{{image}}/menu/client/latest/ic_scan_qr.png","status":"active","login_need":false},{"key":"add_buddy","type":"app","name":"client.menu.add_buddy","icon_url":"{{image}}/menu/client/latest/ic_add_buddy.png","status":"active","login_need":true},{"key":"my_qr","type":"app","name":"client.menu.my_qr","icon_url":"{{image}}/menu/client/latest/ic_my_qr.png","status":"active","login_need":true},{"key":"announcement","type":"app","name":"client.menu.notification","icon_url":"{{image}}/menu/client/latest/ic_notification.png","status":"active","login_need":true},{"key":"my_room","type":"app","name":"client.menu.my_room","icon_url":"{{image}}/menu/client/latest/ic_my_room.png","status":"active","login_need":true}]}';

        foreach ($version30x as $version) {
            $version->setMainJson($mainJson);
        }

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
