<?php

namespace Sandbox\SalesApiBundle\Controller\Reservation;

use Sandbox\ApiBundle\Entity\Room\RoomTypeTags;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sandbox\ApiBundle\Entity\Reservation\Reservation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;

class AdminReservationController extends SalesRestController
{
    /**
     * @Route("/reservation/{reservationId}")
     *
     * @param Request $request
     *
     * @return mixed
     * @Method({"PATCH"})
     */
    public function grabReservationAction(Request $request, $reservationId)
    {
        $adminId = $this->getAdminId();
        $reservation = $this->getDoctrine()->getRepository('SandboxApiBundle:Reservation\Reservation')->findOneBy(array(
            'id' => $reservationId,
            'status' => Reservation::UNGRABED,
        ));

        $this->throwNotFoundIfNull($reservation, self::NOT_FOUND_MESSAGE);

        $reservation->setAdminId($adminId);
        $reservation->setStatus(Reservation::GRABED);

        $now = new \DateTime();
        $reservation->setGrabDate($now);

        $em = $this->getDoctrine()->getManager();
        $em->persist($reservation);
        $em->flush();

        $seriaLNumber = $reservation->getSerialNumber();

        $view = new View();
        $view->setData(
            array(
                'serial_number' => $seriaLNumber,
            )
        );

        return $view;
    }

    /**
     * Reservation.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="pageIndex",
     *     default=1,
     *     nullable=true
     * )
     *
     * @Annotations\QueryParam(
     *     name="pageLimit",
     *     default=20,
     *     nullable=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="keyword",
     *    default=null,
     *    nullable=true,
     *    description="userName,userPhone,contectName,contectPhone,adminName,adminPhone"
     * )
     *
     * @Annotations\QueryParam(
     *    name="keyword_search",
     *    default=null,
     *    nullable=true,
     *    description="search query"
     * )
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
     * * @Annotations\QueryParam(
     *    name="view_start",
     *    default=null,
     *    nullable=true,
     *    description="create start date"
     * )
     *
     * @Annotations\QueryParam(
     *    name="view_end",
     *    default=null,
     *    nullable=true,
     *    description="create end date"
     * )
     *
     * * @Annotations\QueryParam(
     *    name="grab_start",
     *    default=null,
     *    nullable=true,
     *    description="create start date"
     * )
     *
     * @Annotations\QueryParam(
     *    name="grab_end",
     *    default=null,
     *    nullable=true,
     *    description="create end date"
     * )
     *
     * @Annotations\QueryParam(
     *    name="status",
     *    default=null,
     *    nullable=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="building",
     *    default=null,
     *    nullable=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="sort_column",
     *    default=null,
     *    nullable=true,
     *    description="sort column"
     * )
     *
     * @Annotations\QueryParam(
     *    name="direction",
     *    default=null,
     *    nullable=true,
     *    description="sort direction"
     * )
     *
     * @Route("/reservation/list")
     * @Method({"GET"})
     *
     * @return mixed
     */
    public function getReservationAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];
        $pageIndex = $paramFetcher->get('pageIndex');
        $pageLimit = $paramFetcher->get('pageLimit');
        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');
        $viewStart = $paramFetcher->get('view_start');
        $viewEnd = $paramFetcher->get('view_end');
        $createStart = $paramFetcher->get('create_start');
        $createEnd = $paramFetcher->get('create_end');
        $grabStart = $paramFetcher->get('grab_start');
        $grabEnd = $paramFetcher->get('grab_end');
        $status = $paramFetcher->get('status');
        $buildingId = $paramFetcher->get('building');
        $limit = $pageLimit;
        $offset = ($pageIndex - 1) * $pageLimit;

        //sort
        $sortColumn = $paramFetcher->get('sort_column');
        $direction = $paramFetcher->get('direction');

        $productIds = array();
        if ($buildingId) {
            $products = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\Product')
                ->findProductIdsByCompanyAndBuilding($salesCompanyId, $buildingId);
            foreach ($products as $product) {
                $productIds[] = $product['id'];
            }
        }

        $reservations = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Reservation\Reservation')
            ->findBySearch(
                $salesCompanyId,
                $buildingId,
                $keyword,
                $keywordSearch,
                $productIds,
                $status,
                $viewStart,
                $viewEnd,
                $createStart,
                $createEnd,
                $grabStart,
                $grabEnd,
                $limit,
                $offset,
                $sortColumn,
                $direction
            );

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Reservation\Reservation')
            ->getCountBySearch(
                $salesCompanyId,
                $keyword,
                $keywordSearch,
                $productIds,
                $status,
                $viewStart,
                $viewEnd,
                $createStart,
                $createEnd,
                $grabStart,
                $grabEnd
            );

        $result = [];
        foreach ($reservations as $k => $reservation) {
            $result[$k] = $this->getProductInfo($reservation);
        }

        if ($sortColumn == 'price') {
            $price = [];
            foreach ($result as $k => $v) {
                if (array_key_exists('leasing', $v['product'])) {
                    $price[] = $v['product']['leasing']['base_price'];
                } else {
                    $price[] = $v['product']['rent']['rent_price'];
                }
            }
            if ($direction == 'asc') {
                array_multisort($price, SORT_ASC, $result);
            } elseif ($direction == 'desc') {
                array_multisort($price, SORT_DESC, $result);
            }
        }

        $view = new View();
        $view->setData(
            array(
                'current_page_number' => $pageIndex,
                'num_items_per_page' => (int) $pageLimit,
                'items' => $result,
                'total_count' => (int) $count,
            )
        );

        return $view;
    }

    /**
     * @Route("/reservation/ungrabed/list")
     * @Method({"GET"})
     *
     * @param Request $request
     *
     * @return View
     */
    public function getUngrabedReservationAction(Request $request)
    {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $now = new \DateTime();
        $reservations = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Reservation\Reservation')
            ->findCompanyUngrabedReservation(
                $salesCompanyId,
                $now
            );

        $result = [];
        foreach ($reservations as $k => $reservation) {
            $result[$k] = $this->getProductInfo($reservation);
        }

        $count = count($reservations);

        $view = new View();
        $view->setData(
            array(
                'items' => $result,
                'total_count' => (int) $count,
            )
        );

        return $view;
    }

    /**
     * @param $reservation
     *
     * @return mixed
     */
    private function getProductInfo($reservation)
    {
        $viewTime = $reservation->getViewTime();
        $status = $reservation->getStatus();
        $now = new \DateTime();

        if ($now > $viewTime) {
            $status = 'expired';
        }

        $data = [];
        /* @var Reservation $reservation */
        $data['id'] = $reservation->getId();
        $data['userId'] = $reservation->getUserId();
        $data['adminId'] = $reservation->getAdminId();
        $data['productId'] = $reservation->getProductId();
        $data['serialNumber'] = $reservation->getSerialNumber();
        $data['contectName'] = $reservation->getContectName();
        $data['phone'] = $reservation->getPhone();
        $data['comment'] = $reservation->getComment();
        $data['viewTime'] = $reservation->getViewTime();
        $data['creationDate'] = $reservation->getCreationDate();
        $data['modificationDate'] = $reservation->getModificationDate();
        $data['status'] = $status;
        $data['grabDate'] = $reservation->getGrabDate();
        $data['companyId'] = $reservation->getCompanyId();

        $customer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomer')
            ->findOneBy(array('userId' => $data['userId'], 'companyId' => $data['companyId']));
        if (!is_null($customer)) {
            $data['customerId'] = $customer->getId();
        }

        $productId = $reservation->getProductId();
        $product = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\Product')
            ->findProductByProductId($productId);

        $attachment = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomAttachmentBinding')
            ->findAttachmentsByRoom($product['room_id'], 1);

        $typeTagDescription = $this->get('translator')->trans(RoomTypeTags::TRANS_PREFIX.$product['type_tag']);
        $data['product'] = $product;
        $data['product']['content'] = $attachment[0]['content'];
        $data['product']['type_tag_description'] = $typeTagDescription;

        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomer')
            ->findOneBy(array('userId' => $reservation->getUserId()));
        if ($user) {
            $data['userName'] = $user->getName();
        }

        if ($reservation->getAdminId()) {
            $admin = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdminProfiles')
                ->findOneBy(array('userId' => $reservation->getAdminId(), 'salesCompanyId' => $reservation->getCompanyId()));

            if (is_null($admin)) {
                $admin = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdminProfiles')
                    ->findOneBy(array('userId' => $reservation->getAdminId()));
            }
            $data['adminName'] = $admin->getNickname();
        }

        $rent = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductRentSet')
            ->findOneBy(array('product' => $productId));
        if (!is_null($rent)) {
            $data['product']['rent']['rent_price'] = $rent->getbasePrice();
            $data['product']['rent']['unit_price'] = $rent->getUnitPrice();
        }

        $leasing = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductLeasingSet')
            ->findOneBy(array('product' => $productId));

        if (!is_null($leasing)) {
            $data['product']['leasing']['base_price'] = $leasing->getbasePrice();
            $data['product']['leasing']['unit_price'] = $leasing->getUnitPrice();
        }

        return $data;
    }
}
