<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Sandbox\ApiBundle\Entity\Product\Product;
use Sandbox\ApiBundle\Entity\Room\Room;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Sandbox\ApiBundle\Entity\User\UserCustomer;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170713072412 extends AbstractMigration implements ContainerAwareInterface
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

        $salesUsers = $em->getRepository('SandboxApiBundle:SalesAdmin\SalesUser')
            ->getDistinctUsers();

        foreach ($salesUsers as $salesUser) {
            $userId = $salesUser['userId'];
            $companyId = $salesUser['companyId'];

            $user = $em->getRepository('SandboxApiBundle:User\User')->find($userId);

            if (!$user) {
                continue;
            }

            $customer = $em->getRepository('SandboxApiBundle:User\UserCustomer')
                ->findOneBy(array(
                    'userId' => $userId,
                    'companyId' => $companyId,
                ));

            if (!$customer) {
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
            }
        }
        $em->flush();


        $groupUserHasUser = $em->getRepository('SandboxApiBundle:User\UserGroupHasUser')
            ->findAll();

        foreach ($groupUserHasUser as $groupHasUser) {
            $groupId = $groupHasUser->getGroupId();
            $group = $em->getRepository('SandboxApiBundle:User\UserGroup')->find($groupId);

            $customer = $em->getRepository('SandboxApiBundle:User\UserCustomer')
                ->findOneBy(
                    array(
                        'userId'=>$groupHasUser->getUserId(),
                        'companyId'=>$group->getCompanyId()
                    )
                );
            if ($customer) {
                $groupHasUser->setCustomerId($customer->getId());
            }
        }
        $em->flush();

        $leases = $em->getRepository('SandboxApiBundle:Lease\Lease')
            ->findAll();
        foreach ($leases as $lease) {
            /**
             * @var Lease
             * @var Room  $room
             */
            $room = $lease->getProduct()->getRoom();
            $salesCompanyId = $room->getBuilding()->getCompanyId();

            $lease->setBuildingId($room->getBuildingId());
            $lease->setCompanyId($salesCompanyId);
            $lease->setLesseeType(Lease::LEASE_LESSEE_TYPE_PERSONAL);

            $status = $lease->getStatus();
            if ($status == Lease::LEASE_STATUS_CONFIRMING ||
                $status == Lease::LEASE_STATUS_CONFIRMED ||
                $status == Lease::LEASE_STATUS_RECONFIRMING
            ) {
                $lease->setStatus(Lease::LEASE_STATUS_PERFORMING);
            }

            if ($status == Lease::LEASE_STATUS_EXPIRED) {
                $lease->setStatus(Lease::LEASE_STATUS_CLOSED);
            }

            if ($lease->getSupervisor()) {
                $userId = $lease->getSupervisor()->getId();
                $myCustomer = $em->getRepository('SandboxApiBundle:User\UserCustomer')
                    ->findOneBy(array(
                        'userId' => $userId,
                        'companyId' => $salesCompanyId,
                    ));

                if (!$myCustomer) {
                    $user = $em->getRepository('SandboxApiBundle:User\User')->find($userId);
                    $userProfile = $em->getRepository('SandboxApiBundle:User\UserProfile')
                        ->findOneBy(array('userId' => $userId));
                    $userName = $userProfile ? $userProfile->getName() : null;

                    $myCustomer = new UserCustomer();
                    $myCustomer->setUserId($userId);
                    $myCustomer->setCompanyId($salesCompanyId);
                    $myCustomer->setName($userName);
                    $myCustomer->setPhoneCode($user->getPhoneCode());
                    $myCustomer->setPhone($user->getPhone());
                    $myCustomer->setEmail($user->getEmail());
                    $myCustomer->setIsAutoCreated(true);
                    $em->persist($myCustomer);
                    $em->flush();
                }

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

            if (!$myCustomer) {
                $user = $em->getRepository('SandboxApiBundle:User\User')->find($userId);
                $userProfile = $em->getRepository('SandboxApiBundle:User\UserProfile')
                    ->findOneBy(array('userId' => $userId));
                $userName = $userProfile ? $userProfile->getName() : null;

                $myCustomer = new UserCustomer();
                $myCustomer->setUserId($userId);
                $myCustomer->setCompanyId($salesCompanyId);
                $myCustomer->setName($userName);
                $myCustomer->setPhoneCode($user->getPhoneCode());
                $myCustomer->setPhone($user->getPhone());
                $myCustomer->setEmail($user->getEmail());
                $myCustomer->setIsAutoCreated(true);
                $em->persist($myCustomer);
                $em->flush();
            }

            $bill->setCustomerId($myCustomer->getId());
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