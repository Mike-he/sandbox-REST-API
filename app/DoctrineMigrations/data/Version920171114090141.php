<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminProfiles;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920171114090141 extends AbstractMigration implements ContainerAwareInterface
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

        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        $userPositionBindingUserIds = $em->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
            ->getDistinctUserIds();

        $imageUrl = $this->container->getParameter('image_url');

        foreach ($userPositionBindingUserIds as $userId) {
            $profile = $em->getRepository('SandboxApiBundle:User\UserProfile')
                ->findOneBy(['userId' => $userId]);
            $nickname = !is_null($profile) ? $profile->getName() : '';

            $image = $imageUrl . '/person/'. $userId .'/avatar.jpg';

            $adminProfile = new SalesAdminProfiles();

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $image);
            curl_setopt($ch, CURLOPT_NOBODY, 1);
            curl_setopt($ch, CURLOPT_FAILONERROR, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            if (curl_exec($ch) !== false) {
                $adminProfile->setAvatar($image);
            }

            $adminProfile->setUserId($userId);
            $adminProfile->setNickname($nickname);

            $em->persist($adminProfile);
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
