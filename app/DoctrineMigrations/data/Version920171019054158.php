<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminProfiles;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920171019054158 extends AbstractMigration implements ContainerAwareInterface
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

        $companies = $em->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->findAll();

        foreach ($companies as $company) {
            $companyId = $company->getId();

            $positions = $em->getRepository('SandboxApiBundle:Admin\AdminPosition')
                ->findBy([
                    'salesCompanyId' => $companyId,
                ]);

            $positionIds = [];
            foreach ($positions as $position) {
                array_push($positionIds, $position->getId());
            }

            $userIds = $em->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                ->getUserIdsByPosition($positionIds);

            foreach ($userIds as $userId) {
                $profile = $em->getRepository('SandboxApiBundle:User\UserProfile')
                    ->findOneBy(['userId' => $userId]);

                $adminProfile = new SalesAdminProfiles();
                $adminProfile->setNickname($profile->getName());
                $adminProfile->setUserId($userId);
                $adminProfile->setSalesCompanyId($companyId);
                $em->persist($adminProfile);
            }
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
