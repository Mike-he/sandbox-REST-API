<?php

namespace Sandbox\SalesApiBundle\Controller\Reservation;

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
     * @Method({"PUT"})
     */
    public function grabReservationAction(Request $request,$reservationId)
    {
        $adminId = $this->getAdminId();
        $reservation = $this->getDoctrine()->getRepository('SandboxApiBundle:Reservation\Reservation')->findOneById($reservationId);

        $this->throwNotFoundIfNull($reservation, self::NOT_FOUND_MESSAGE);

        $reservation->setAdminId($adminId);
        $reservation->setStatus(Reservation::GRABED);

        $em = $this->getDoctrine()->getManager();
        $em->persist($reservation);
        $em->flush();

        $seriaLNumber = $reservation->getSerialNumber();

        $view = new View();
        $view->setData(array(
            'serial_number'=>$seriaLNumber
        ));

        return $view;

    }

    /**
     *
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
     *    name="view_time",
     *    default=null,
     *    nullable=true,
     *    strict=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="creation_date",
     *    default=null,
     *    nullable=true,
     *    array=true,
     * )
     *
     * @Annotations\QueryParam(
     *    name="status",
     *    default=null,
     *    nullable=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="admin",
     *    default=null,
     *    nullable=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="serialNumber",
     *    default=null,
     *    nullable=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="contect_name",
     *    default=null,
     *    nullable=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="phone",
     *    default=null,
     *    nullable=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="user",
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
     *    name="modification_date",
     *    default=null,
     *    nullable=true
     * )
     *
     * @Route("/reservation/list")
     * @Method({"GET"})
     * @return mixed
     */
    public function getReservationAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ){
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];
        $pageIndex = $paramFetcher->get('pageIndex');
        $pageLimit = $paramFetcher->get('pageLimit');

        $user = $paramFetcher->get('user');
        $admin = $paramFetcher->get('admin');
        $serialNumber = $paramFetcher->get('serialNumber');
        $contectName = $paramFetcher->get('contect_name');
        $phone = $paramFetcher->get('phone');
        $viewTime = $paramFetcher->get('view_time');
        $creationDate = $paramFetcher->get('creation_date');
        $modificationDate = $paramFetcher->get('modification_date');
        $status = $paramFetcher->get('status');

        $buildingName = $paramFetcher->get('building');
        $buildings = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->findOneByName($buildingName);
        $buildingId = $buildings[0]['id'];

        $products = $this->getDoctrine()
           ->getRepository('SandboxApiBundle:Product\Product')
           ->findProductIdsByCompanyAndBuilding($salesCompanyId,$buildingId);
       $productIds = array();
       foreach($products as $product){
           $productIds[] = $product['id'];
       }
       $reservations = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Reservation\Reservation')
                    ->findBySearch(
                        $user,
                        $admin,
                        $phone,
                        $contectName,
                        $serialNumber,
                        $productIds,
                        $viewTime,
                        $status,
                        $creationDate,
                        $modificationDate
                    );
        $reservations = $this->getProductInfo($reservations);

        if (is_null($pageIndex) || is_null($pageLimit)) {
            $paginator = new Paginator();
            $reservations = $paginator->paginate(
                $reservations,
                $pageIndex,
                $pageLimit
            );
        }
        return new View($reservations);
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
        foreach($products as $product){
            $productIds[] = $product['id'];
        }

        $reservations = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Reservation\Reservation')
            ->findUngrabedReservation($productIds);

        foreach($reservations as $reservation){
            $productId = $reservation->getProductId();
            $product = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\Product')
                ->findProductByProductId($productId);
            $reservation->productInfo = $product;
        }

        $number = count($reservations);
        $reservations = $this->getProductInfo($reservations);

        $number = count($reservations);
        $view = new View();
        $view->setData(array(
            'num'=>$number,
            'list'=>$reservations
        ));

        return $view;

    }

    /**
     * @param $reservations
     * @return mixed
     */
    private function getProductInfo($reservations)
    {

        foreach($reservations as $reservation){
            /** @var Reservation $reservation */
            $productId = $reservation->getProductId();
            $product = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\Product')
                ->findProductByProductId($productId);
            $reservation->setPrductInfo($product);
        }
        return $reservations;
    }
}