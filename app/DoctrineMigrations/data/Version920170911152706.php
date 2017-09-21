<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdmin;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170911152706 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }

    public function postUp(Schema $schema)
    {
        parent::postUp($schema);

        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        $binds = $em->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')->getBindUser(null);

        foreach ($binds as $bind) {
            $userId = $bind['userId'];

            $salesAdmin = $em->getRepository('SandboxApiBundle:SalesAdmin\SalesAdmin')
                ->findOneBy(array('userId' => $userId));

            if (!$salesAdmin) {
                $user = $em->getRepository('SandboxApiBundle:User\User')->find($userId);

                if (!is_null($user) && !is_null($user->getPhone())) {
                    $salesAdmin = new SalesAdmin();
                    $salesAdmin->setUserId($userId);
                    $salesAdmin->setPassword($user->getPassword());
                    $salesAdmin->setXmppUsername('admin_'.$user->getXmppUsername());
                    $salesAdmin->setPhoneCode($user->getPhoneCode());
                    $salesAdmin->setPhone($user->getPhone());

                    $em->persist($salesAdmin);
                }
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
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
