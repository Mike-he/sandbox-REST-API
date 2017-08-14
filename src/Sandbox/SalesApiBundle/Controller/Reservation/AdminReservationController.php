<?php

namespace Sandbox\SalesApiBundle\Controller\Reservation;

use Sandbox\ApiBundle\Entity\Room\RoomTypeTags;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sandbox\ApiBundle\Entity\Reservation\Reservation;
use Sandbox\ApiBundle\Repository\Reservation\ReservationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Knp\Component\Pager\Paginator;

class AdminReservationController extends SalesRestController
{
    /**
     * @Route("/reservation/{reservationId}")
     * @param Request $request
     * @return mixed
     * @Method({"PATCH"})
     */
    public function grabReservationAction(Request $request, $reservationId)
    {
        $adminId = $this->getAdminId();
        $reservation = $this->getDoctrine()->getRepository('SandboxApiBundle:Reservation\Reservation')->findOneById(
            $reservationId
        );

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
                'serial_number' => $seriaLNumber
            )
        );

        return $view;

    }

    /**
     *
     * Reservation.
     *
     * @param Request $request the request object
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
     *
     * @Route("/reservation/list")
     * @Method({"GET"})
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
        $buildingName = $paramFetcher->get('building');
        $limit = $pageLimit;
        $offset = ($pageIndex - 1) * $pageLimit;

        $buildingName = $paramFetcher->get('building');
        $productIds = array();
        if ($buildingName) {
            $buildings = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                ->findOneByName($buildingName);

            $buildingId = $buildings[0]['id'];
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
                $offset
            );

        $count = count($reservations);

        $result = [];
        foreach ($reservations as $k=>$reservation) {
            $result[$k] = $this->getProductInfo($reservation);
        }

        $view = new View();
        $view->setData(
            array(
                'current_page_number' => $pageIndex,
                'num_items_per_page' => (int)$pageLimit,
                'items' => $result,
                'total_count' => (int)$count,
            )
        );

        return $view;
    }

    /**
     *
     * @Route("/reservation/ungrabed/list")
     * @Method({"GET"})
     * @param Request $request
     * @return View
     */
    public function getUngrabedReservationAction(Request $request)
    {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $products = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\Product')
            ->findProductIdsByCompanyAndBuilding($salesCompanyId);
        $productIds = array();
        foreach ($products as $product) {
            $productIds[] = $product['id'];
        }

        $reservations = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Reservation\Reservation')
            ->findUngrabedReservation($productIds);

        $result = [];
        foreach ($reservations as $k=>$reservation) {
            $result[$k] = $this->getProductInfo($reservation);
        }

        $count = count($reservations);

        $view = new View();
        $view->setData(
            array(
                'items' => $result,
                'total_count' => (int)$count,
            )
        );

        return $view;

    }

    /**
     * @param $reservation
     * @return mixed
     */
    private function getProductInfo($reservation)
    {
        $data = [];
        /** @var Reservation $reservation */
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
        $data['status'] = $reservation->getStatus();
        $data['grabDate'] = $reservation->getGrabDate();

        $productId = $reservation->getProductId();
        $product = $this->getDoctrine()->getRepository('SandboxApiBundle:Product\Product')->findOneById($productId);

        $companyId = $product->getRoom()->getBuilding()->getCompanyId();
        $data['companyId'] = $companyId;
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
            ->getRepository('SandboxApiBundle:User\UserProfile')
            ->findByUserId($reservation->getUserId());
        $data['userName'] = $user->getName();

        if($reservation->getAdminId()){
            $admin = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserProfile')
                ->findByUserId($reservation->getAdminId());
            $data['adminName'] = $admin->getName();
        }

        $rent = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductRentSet')
            ->findOneBy(array('product' => $productId));

        $data['product']['rent_price'] = $rent->getbasePrice();
        $data['product']['unit_price'] = $rent->getUnitPrice();

        return $data;
    }
}