<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Sandbox\ApiBundle\Entity\User\UserCustomer;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170713072413 extends AbstractMigration implements ContainerAwareInterface
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

        $salesUsers = $em->getRepository('SandboxApiBundle:SalesAdmin\SalesUser')
            ->getDistinctUsers();

        foreach ($salesUsers as $salesUser) {
            $userId = $salesUser['userId'];
            $companyId = $salesUser['companyId'];

            $user = $em->getRepository('SandboxApiBundle:User\User')->find($userId);

            if (!$user) {
                continue;
            }

            $userProfile = $em->getRepository('SandboxApiBundle:User\UserProfile')
                ->findOneBy(array('userId' => $userId));
            $userName = $userProfile ? $userProfile->getName() : null;

            $customer = $em->getRepository('SandboxApiBundle:User\UserCustomer')
                ->findOneBy(array(
                    'userId' => $userId,
                    'companyId' => $companyId,
                ));

            if (!$customer) {
                $customer = new UserCustomer();
                $customer->setUserId($userId);
                $customer->setCompanyId($companyId);
                $customer->setName($userName);
                $customer->setPhoneCode($user->getPhoneCode());
                $customer->setPhone($user->getPhone());
                $customer->setEmail($user->getEmail());
                $em->persist($customer);
                $em->flush();
            }

            $userGroups = $em->getRepository('SandboxApiBundle:User\UserGroup')
                ->findBy(array(
                    'companyId' => $companyId,
                ));

            foreach ($userGroups as $group) {
                $groupUserHasUser = $em->getRepository('SandboxApiBundle:User\UserGroupHasUser')
                    ->findBy(array(
                        'userId' => $userId,
                        'groupId' => $group->getId(),
                    ));

                foreach ($groupUserHasUser as $item) {
                    $item->setCustomerId($customer->getId());
                }
            }
        }

        $leases = $em->getRepository('SandboxApiBundle:Lease\Lease')
            ->findAll();
        foreach ($leases as $lease) {
            $userId = $lease->getSupervisorId();

            if (is_null($userId)) {
                continue;
            }

            $salesCompanyId = $lease->getProduct()->getRoom()->getBuilding()->getCompanyId();

            $myCustomer = $em->getRepository('SandboxApiBundle:User\UserCustomer')
                ->findOneBy(array(
                    'userId' => $userId,
                    'companyId' => $salesCompanyId,
                ));

            if ($myCustomer) {
                $lease->setLesseeCustomer($myCustomer->getId());
            }
        }

        $bills = $em->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->findAll();
        foreach ($bills as $bill) {
            $userId = $bill->getDrawee();

            if (!$userId) {
                continue;
            }

            $salesCompanyId = $bill->getLease()->getProduct()->getRoom()->getBuilding()->getCompanyId();

            $myCustomer = $em->getRepository('SandboxApiBundle:User\UserCustomer')
                ->findOneBy(array(
                    'userId' => $userId,
                    'companyId' => $salesCompanyId,
                ));

            if ($myCustomer) {
                $bill->setCustomerId($myCustomer->getId());
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
