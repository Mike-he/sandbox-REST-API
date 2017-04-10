<?php

namespace Sandbox\ApiBundle\Service;

use Sandbox\ApiBundle\Entity\Lease\Lease;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class OrderService.
 */
class OrderService
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getAllOrders(
        $buildingIds
    ) {
        $now = new \DateTime('now');

        $orders = $this->findOrders($buildingIds, $now);

        $leases = $this->findLeases($buildingIds, $now);

        $result = array_merge($orders, $leases);

        return $result;
    }

    /**
     * @param $buildingIds
     * @param $now
     *
     * @return array
     */
    private function findOrders(
        $buildingIds,
        $now
    ) {
        $orders = $this->container->get('doctrine')
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->getUsingOrder(
                $buildingIds,
                $now
            );

        $result = array();

        foreach ($orders as $order) {
            $user = array($order->getUserId());
            $inviteds = $order->getInvitedPeople();
            $invitedPeople = array();
            foreach ($inviteds as $invited) {
                $invitedPeople[] = $invited->getUserId();
            }

            $result[] = array(
                'user' => array_merge($user, $invitedPeople),
                'start' => $order->getStartDate(),
                'end' => $order->getEndDate(),
                'building' => $order->getProduct()->getRoom()->getBuildingId(),
                'type' => 'order',
            );
        }

        return $result;
    }

    /**
     * @param $buildingIds
     * @param $now
     *
     * @return array
     */
    private function findLeases(
        $buildingIds,
        $now
    ) {
        $status = array(
            Lease::LEASE_STATUS_CONFIRMED,
            Lease::LEASE_STATUS_RECONFIRMING,
            Lease::LEASE_STATUS_PERFORMING,
        );

        $leases = $this->container->get('doctrine')
            ->getRepository('SandboxApiBundle:Lease\Lease')
            ->getUsingLease(
                $buildingIds,
                $now,
                $status
            );

        $result = array();
        foreach ($leases as $lease) {
            $user = array($lease->getSupervisorId());

            $inviteds = $lease->getInvitedPeopleIds();

            $invitedPeople = array();
            foreach ($inviteds as $invited) {
                $invitedPeople[] = $invited;
            }

            $result[] = array(
                'user' => array_merge($user, $invitedPeople),
                'start' => $lease->getStartDate(),
                'end' => $lease->getEndDate(),
                'building' => $lease->getProduct()->getRoom()->getBuildingId(),
                'type' => 'lease',
            );
        }

        return $result;
    }
}
