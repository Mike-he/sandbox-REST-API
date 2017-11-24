<?php

namespace Sandbox\ClientPropertyApiBundle\Controller\Lease;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Constants\CustomErrorMessagesConstants;
use Sandbox\ApiBundle\Constants\LeaseConstants;
use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Entity\Product\Product;
use Sandbox\ApiBundle\Traits\GenerateSerialNumberTrait;
use Sandbox\ApiBundle\Traits\HasAccessToEntityRepositoryTrait;
use Sandbox\ApiBundle\Traits\LeaseNotificationTrait;
use Sandbox\ApiBundle\Traits\LeaseTrait;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;

class ClientLeaseController extends SalesRestController
{
    use GenerateSerialNumberTrait;
    use HasAccessToEntityRepositoryTrait;
    use LeaseNotificationTrait;
    use LeaseTrait;

    /**
     * Get Lease Detail.
     *
     * @param $id
     *
     * @Route("/leases/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getLeaseAction(
        $id
    ) {
        $lease = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')->find($id);

        $this->throwNotFoundIfNull($lease, CustomErrorMessagesConstants::ERROR_LEASE_NOT_FOUND_MESSAGE);

        $this->setLeaseAttributions($lease);

        $view = new View();
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(['main'])
        );
        $view->setData($lease);

        return $view;
    }

    /**
     * Get List of Lease.
     *
     *
     * @Annotations\QueryParam(
     *    name="status",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="status of lease"
     * )
     *
     * @Annotations\QueryParam(
     *    name="lessee_type",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="status of lease"
     * )
     *
     * @Annotations\QueryParam(
     *    name="keyword",
     *    default=null,
     *    nullable=true,
     *    description="applicant, room, number"
     * )
     *
     * @Annotations\QueryParam(
     *    name="keyword_search",
     *    default=null,
     *    nullable=true,
     *    description="search query"
     * )
     *
     *
     * @Annotations\QueryParam(
     *    name="create_start",
     *    default=null,
     *    nullable=true,
     *    description="create start date"
     * )
     *
     * @Annotations\QueryParam(
     *    name="create_end",
     *    default=null,
     *    nullable=true,
     *    description="create end date"
     * )
     *
     * @Annotations\QueryParam(
     *    name="rent_filter",
     *    default=null,
     *    nullable=true,
     *    description="rent time filter keywords"
     * )
     *
     * @Annotations\QueryParam(
     *    name="start_date",
     *    default=null,
     *    nullable=true,
     *    description="appointment start date"
     * )
     *
     * @Annotations\QueryParam(
     *    name="end_date",
     *    default=null,
     *    nullable=true,
     *    description="appointment end date"
     * )
     *
     *
     * @Annotations\QueryParam(
     *    name="building",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by building id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="building",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="Filter by building id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="product",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by product id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="source",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by lease source"
     * )
     *
     * @Annotations\QueryParam(
     *    name="limit",
     *    array=false,
     *    default="10",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="limit for the page"
     * )
     *
     * @Annotations\QueryParam(
     *    name="offset",
     *    array=false,
     *    default="0",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="start of the page"
     * )
     *
     * @Route("/leases")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getLeasesAction(
        ParamFetcherInterface $paramFetcher,
        Request $request
    ) {
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $status = $paramFetcher->get('status');
        $lesseeType = $paramFetcher->get('lessee_type');
        $source = $paramFetcher->get('source');

        // search keyword and query
        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');

        // creation date filter
        $createStart = $paramFetcher->get('create_start');
        $createEnd = $paramFetcher->get('create_end');

        // rent date filter
        $rentFilter = $paramFetcher->get('rent_filter');
        $startDate = $paramFetcher->get('start_date');
        $endDate = $paramFetcher->get('end_date');

        $building = $paramFetcher->get('building');
        $product = $paramFetcher->get('product');

        //get my buildings list
        $myBuildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            array(
                AdminPermission::KEY_SALES_BUILDING_LONG_TERM_LEASE,
            )
        );

        $defaultStatusSort = [
            Lease::LEASE_STATUS_DRAFTING,
            Lease::LEASE_STATUS_PERFORMING,
            Lease::LEASE_STATUS_MATURED,
            Lease::LEASE_STATUS_TERMINATED,
            Lease::LEASE_STATUS_END,
            Lease::LEASE_STATUS_CLOSED,
        ];

        if (is_null($status) || empty($status)) {
            $statusSorts = $defaultStatusSort;
        } else {
            $statusSorts = array_intersect($defaultStatusSort, $status);
        }
        $ids = [];
        foreach ($statusSorts as $statusSort) {
            $leaseIds = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Lease\Lease')
                ->findLeasesForPropertyClient(
                    $myBuildingIds,
                    $building,
                    $product,
                    $statusSort,
                    $lesseeType,
                    $keyword,
                    $keywordSearch,
                    $createStart,
                    $createEnd,
                    $rentFilter,
                    $startDate,
                    $endDate,
                    $source
                );

            $ids = array_merge($ids, $leaseIds);
        }

        $ids = array_unique($ids);

        $leases = $this->handleLeaseData($ids, $limit, $offset);

        $view = new View();
        $view->setData($leases);

        return $view;
    }

    /**
     * @param Lease $leaseIds
     * @param $limit
     * @param $offset
     *
     * @return array
     */
    private function handleLeaseData(
        $leaseIds,
        $limit,
        $offset
    ) {
        $ids = array();
        for ($i = $offset; $i < $offset + $limit; ++$i) {
            if (isset($leaseIds[$i])) {
                $ids[] = $leaseIds[$i];
            }
        }

        $result = [];
        foreach ($ids as $id) {
            $lease = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Lease\Lease')
                ->find($id);

            $customer = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserCustomer')
                ->find($lease->getLesseeCustomer());

            $status = $this->get('translator')
                ->trans(LeaseConstants::TRANS_LEASE_STATUS.$lease->getStatus());

            /** @var Product $product */
            $product = $lease->getProduct();
            $room = $product->getRoom();
            $building = $room->getBuilding();

            $attachment = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomAttachmentBinding')
                ->findAttachmentsByRoom($room->getId(), 1);

            $roomType = $this->get('translator')->trans(ProductOrderExport::TRANS_ROOM_TYPE.$room->getType());

            $paidBillsCount = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Lease\LeaseBill')
                ->countBills(
                    $lease,
                    null,
                    LeaseBill::STATUS_PAID
                );

            $totalBillsCount = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Lease\LeaseBill')
                ->countBills(
                    $lease
                );

            $paidBillsAmount = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Lease\LeaseBill')
                ->sumBillsFees(
                    $lease,
                    LeaseBill::STATUS_PAID
                );

            $result[] = [
                'id' => $id,
                'serial_number' => $lease->getSerialNumber(),
                'creation_date' => $lease->getCreationDate(),
                'status' => $status,
                'start_date' => $lease->getStartDate(),
                'end_date' => $lease->getEndDate(),
                'room_type' => $roomType,
                'room_name' => $room->getName(),
                'room_attachment' => $attachment,
                'building_name' => $building->getName(),
                'total_rent' => (float) $lease->getTotalRent(),
                'paid_amount' => (float) $paidBillsAmount,
                'paid_bills_count' => $paidBillsCount,
                'total_bills_count' => $totalBillsCount,
                'customer' => array(
                    'id' => $lease->getLesseeCustomer(),
                    'name' => $customer ? $customer->getName() : '',
                    'avatar' => $customer ? $customer->getAvatar() : '',
                ),
            ];
        }

        return $result;
    }
}
