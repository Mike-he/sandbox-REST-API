<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionGroupMap;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionGroups;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170612063805 extends AbstractMigration implements ContainerAwareInterface
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

        $officialMessageGroup = $em->getRepository('SandboxApiBundle:Admin\AdminPermissionGroups')
            ->findOneBy(array(
                'groupKey' => AdminPermissionGroups::GROUP_KEY_MESSAGE,
                'platform' => AdminPermissionGroups::GROUP_PLATFORM_OFFICIAL,
            ));
        $officialMessageGroup->setGroupName('创合服务号');

        $messagePermission = $em->getRepository('SandboxApiBundle:Admin\AdminPermission')
            ->findOneBy(array(
                'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_MESSAGE,
            ));
        $em->remove($messagePermission);
        $em->flush();

        $messagePermissionNew = new AdminPermission();
        $messagePermissionNew->setKey(AdminPermission::KEY_OFFICIAL_PLATFORM_MESSAGE);
        $messagePermissionNew->setPlatform('official');
        $messagePermissionNew->setLevel('global');
        $messagePermissionNew->setName('推送消息');
        $messagePermissionNew->setOpLevelSelect('2');
        $messagePermissionNew->setMaxOpLevel('2');
        $em->persist($messagePermissionNew);

        $messageConsultationPermision = new AdminPermission();
        $messageConsultationPermision->setKey(AdminPermission::KEY_OFFICIAL_PLATFORM_MESSAGE_CONSULTATION);
        $messageConsultationPermision->setPlatform('official');
        $messageConsultationPermision->setLevel('global');
        $messageConsultationPermision->setName('用户咨询');
        $messageConsultationPermision->setOpLevelSelect('2');
        $messageConsultationPermision->setMaxOpLevel('2');
        $em->persist($messageConsultationPermision);

        $map1 = new AdminPermissionGroupMap();
        $map1->setGroup($officialMessageGroup);
        $map1->setPermission($messagePermissionNew);
        $em->persist($map1);

        $map2 = new AdminPermissionGroupMap();
        $map2->setGroup($officialMessageGroup);
        $map2->setPermission($messageConsultationPermision);
        $em->persist($map2);

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
