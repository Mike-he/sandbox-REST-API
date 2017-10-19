<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Sandbox\ApiBundle\Entity\Event\Event;
use Sandbox\ApiBundle\Entity\User\UserCustomer;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920171019153549 extends AbstractMigration implements ContainerAwareInterface
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

        $orders = $em->getRepository('SandboxApiBundle:Event\EventOrder')->findAll();

        foreach ($orders as $order) {
            if (is_null($order->getCustomerId())) {
                $userId = $order->getUserId();

                /** @var Event $event */
                $event = $order->getEvent();
                $companyId = $event->getSalesCompanyId();
                if ($companyId) {
                    $customer = $em->getRepository('SandboxApiBundle:User\UserCustomer')
                        ->findOneBy(array(
                            'userId' => $userId,
                            'companyId' => $companyId,
                        ));
                    if (!$customer) {
                        $user = $em->getRepository('SandboxApiBundle:User\User')->find($userId);
                        $userProfile = $em->getRepository('SandboxApiBundle:User\UserProfile')
                            ->findOneBy(array('userId' => $userId));
                        $userName = $userProfile ? $userProfile->getName() : null;

                        $customer = new UserCustomer();
                        $customer->setUserId($userId);
                        $customer->setCompanyId($companyId);
                        $customer->setName($userName);
                        $customer->setPhoneCode($user->getPhoneCode());
                        $customer->setPhone($user->getPhone());
                        $customer->setEmail($user->getEmail());
                        $customer->setIsAutoCreated(true);
                        $em->persist($customer);

                        $em->flush();
                    }
                    $order->setCustomerId($customer->getId());
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
