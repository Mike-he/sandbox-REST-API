<?php

namespace Sandbox\ApiBundle\Service;

use Sandbox\ApiBundle\Entity\Lease\Lease;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
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
            /** @var ProductOrder $order */
            $customerId = $order->getCustomerId();
            $customer = $this->container->get('doctrine')
                ->getRepository('SandboxApiBundle:User\UserCustomer')
                ->find($customerId);

            $userId = $customer ? $customer->getUserId() : null;

            $user = [];
            if ($userId) {
                $user[] = $userId;
            }

            $inviteds = $order->getInvitedPeople();
            foreach ($inviteds as $invited) {
                $user[] = $invited->getUserId();
            }

            $result[] = array(
                'user' => $user,
                'start' => $order->getStartDate(),
                'end' => $order->getEndDate(),
                'building' => $order->getProduct()->getRoom()->getBuildingId(),
                'type' => 'order',
                'order_number' => $order->getOrderNumber(),
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
            /** @var Lease $lease */
            $customerId = $lease->getLesseeCustomer();

            $customer = $this->container->get('doctrine')
                ->getRepository('SandboxApiBundle:User\UserCustomer')
                ->find($customerId);

            $userId = $customer ? $customer->getUserId() : null;

            $user = [];
            if ($userId) {
                $user[] = $userId;
            }

            $inviteds = $lease->getInvitedPeopleIds();

            foreach ($inviteds as $invited) {
                $user[] = $invited;
            }

            $result[] = array(
                'user' => $user,
                'start' => $lease->getStartDate(),
                'end' => $lease->getEndDate(),
                'building' => $lease->getProduct()->getRoom()->getBuildingId(),
                'type' => 'lease',
                'order_number' => $lease->getSerialNumber(),
            );
        }

        return $result;
    }
}
