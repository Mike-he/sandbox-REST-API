<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Sandbox\ApiBundle\Entity\Menu\Menu;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920171023015236 extends AbstractMigration implements ContainerAwareInterface
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

        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        $menu = new Menu();
        $menu->setComponent('property-client');
        $menu->setPlatform('iphone');
        $menu->setMinVersion('1.0.0');
        $menu->setMaxVersion('1.0.0');
        $menu->setMainJson("{\"main\":[{\"type\":\"list\",\"items\":[{\"key\":\"dashboard\",\"type\":\"app\",\"name\":\"client_property.menu.dashboard\",\"status\":\"active\",\"login_need\":true},{\"key\":\"space\",\"type\":\"app\",\"name\":\"client_property.menu.space\",\"status\":\"active\",\"login_need\":true},{\"key\":\"message\",\"type\":\"app\",\"name\":\"client_property.menu.message\",\"status\":\"active\",\"login_need\":true},{\"key\":\"apply\",\"type\":\"web\",\"web\":{\"url\":\"{{property-client}}/apply\",\"cookie\":[{\"key\":\"\",\"value\":\"\"}]},\"name\":\"client_property.menu.apply\",\"status\":\"active\",\"login_need\":true},{\"key\":\"trade\",\"type\":\"app\",\"name\":\"client_property.menu.trade\",\"status\":\"active\",\"login_need\":true},{\"key\":\"lease\",\"type\":\"app\",\"name\":\"client_property.menu.lease\",\"status\":\"active\",\"login_need\":true},{\"key\":\"customer\",\"type\":\"web\",\"web\":{\"url\":\"{{property-client}}/customer\",\"cookie\":[{\"key\":\"\",\"value\":\"\"}]},\"name\":\"client_property.menu.customer\",\"status\":\"active\",\"login_need\":true},{\"key\":\"setting\",\"type\":\"app\",\"name\":\"client_property.menu.setting\",\"status\":\"active\",\"login_need\":true}]}]}");
        $menu->setProfileJson("");
        $menu->setHomeJson("");

        $menuAndroid = new Menu();
        $menuAndroid->setComponent('property-client');
        $menuAndroid->setPlatform('android');
        $menuAndroid->setMinVersion('1.0.0');
        $menuAndroid->setMaxVersion('1.0.0');
        $menuAndroid->setMainJson("{\"main\":[{\"type\":\"list\",\"items\":[{\"key\":\"dashboard\",\"type\":\"app\",\"name\":\"client_property.menu.dashboard\",\"status\":\"active\",\"login_need\":true},{\"key\":\"space\",\"type\":\"app\",\"name\":\"client_property.menu.space\",\"status\":\"active\",\"login_need\":true},{\"key\":\"message\",\"type\":\"app\",\"name\":\"client_property.menu.message\",\"status\":\"active\",\"login_need\":true},{\"key\":\"apply\",\"type\":\"web\",\"web\":{\"url\":\"{{property-client}}/apply\",\"cookie\":[{\"key\":\"\",\"value\":\"\"}]},\"name\":\"client_property.menu.apply\",\"status\":\"active\",\"login_need\":true},{\"key\":\"trade\",\"type\":\"app\",\"name\":\"client_property.menu.trade\",\"status\":\"active\",\"login_need\":true},{\"key\":\"lease\",\"type\":\"app\",\"name\":\"client_property.menu.lease\",\"status\":\"active\",\"login_need\":true},{\"key\":\"customer\",\"type\":\"web\",\"web\":{\"url\":\"{{property-client}}/customer\",\"cookie\":[{\"key\":\"\",\"value\":\"\"}]},\"name\":\"client_property.menu.customer\",\"status\":\"active\",\"login_need\":true},{\"key\":\"setting\",\"type\":\"app\",\"name\":\"client_property.menu.setting\",\"status\":\"active\",\"login_need\":true}]}]}");
        $menuAndroid->setProfileJson("");
        $menuAndroid->setHomeJson("");

        $em->persist($menu);
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
