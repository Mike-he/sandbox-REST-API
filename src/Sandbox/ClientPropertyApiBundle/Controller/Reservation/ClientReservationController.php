<?php

namespace Sandbox\ClientPropertyApiBundle\Controller\Reservation;

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

class ClientReservationController extends SalesRestController
{
    /**
     * ClientReservation
     *
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     * @param $id
     *
     * @Route("/reservation/{id}")
     * @Method({"PATCH"})
     * @return View
     */
    public function grabReservationAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
            $id
    ) {
        $adminId = $this->getAdminId();
        $reservation = $this->getDoctrine()->getRepository('SandboxApiBundle:Reservation\Reservation')->findOneBy(array(
            'id'=> $id,
            'status'=>Reservation::UNGRABED
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
                'serial_number' => $seriaLNumber
            )
        );

        return $view;
    }

    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
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
     * @Route("/reservation/lists")
     * @Method({"GET"})
     * @return View
     */
    public function getReservationAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $offset = $paramFetcher->get('offset');
        $limit = $paramFetcher->get('limit');

        $reservations = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Reservation\Reservation')
            ->findBy(array(
                    'companyId'=>$salesCompanyId
                ),
                array(
                    'creationDate'=>'DESC'
                ),
                $limit,
                $offset
            );

        $result = [];
        foreach ($reservations as $k=>$reservation) {
            $result[$k] = $this->getProductInfo($reservation);
        }

        $view = new View();
        $view->setData(
            array(
                'items' => $result
            )
        );

        return $view;
    }

    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
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
     * @Route("/reservation/ungrabed/list")
     * @Method({"GET"})
     * @return View
     */
    public function getUngrabedReservationAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $offset = $paramFetcher->get('offset');
        $limit = $paramFetcher->get('limit');

        $now = new \DateTime();
        $reservations = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Reservation\Reservation')
            ->findCompanyUngrabedReservation(
                $salesCompanyId,
                $now,
                $limit,
                $offset
            );

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
        $str = $reservation->getViewTime();
        $str = str_replace('.','-',$str);

        $viewTime =  new \DateTime($str);
        $status = $reservation->getStatus();
        $now = new \DateTime();

        if($now > $viewTime){
            $status = 'expired';
        }

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
        $data['viewTime'] = $viewTime;
        $data['creationDate'] = $reservation->getCreationDate();
        $data['modificationDate'] = $reservation->getModificationDate();
        $data['status'] = $status;
        $data['grabDate'] = $reservation->getGrabDate();
        $data['companyId'] = $reservation->getCompanyId();

        $customer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomer')
            ->findOneBy(array('userId'=>$data['userId'],'companyId'=>$data['companyId']));
        if(!is_null($customer)){
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
        if(!is_null($rent)){
            $data['product']['rent']['rent_price'] = $rent->getbasePrice();
            $data['product']['rent']['unit_price'] = $rent->getUnitPrice();
        }

        $leasing = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductLeasingSet')
            ->findOneBy(array('product' => $productId));

        if(!is_null($leasing)){
            $data['product']['leasing']['base_price'] = $leasing->getbasePrice();
            $data['product']['leasing']['unit_price'] = $leasing->getUnitPrice();
        }

        return $data;
    }

    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
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
     * @Route("/my/grabed/lists")
     * @Method("GET")
     * @return View
     */
    public function myGrabedListsAction
    (
        Request $request,
        ParamFetcherInterface $paramFetcher
    ){
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];
        $adminId = $this->getAdminId();

        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $reservations = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Reservation\Reservation')
            ->findBy(array(
                    'adminId'=>$adminId,
                    'companyId'=>$salesCompanyId
                ),
                array(
                    'grabDate'=>'DESC'
                ),
                $limit,
                $offset
            );

        $result = [];
        foreach ($reservations as $k=>$reservation) {
            $result[$k] = $this->getProductInfo($reservation);
        }

        $view = new View();
        $view->setData(
            array(
                'items'=>$result
            )
        );

        return $view;
    }

    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
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
     * @Route("/my/latest/grabed/lists")
     * @Method({"GET"})
     * @return View
     */
    public function myLatestGrabedListAction
    (
        Request $request,
        ParamFetcherInterface $paramFetcher
    ){
        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];
        $adminId = $this->getAdminId();

        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $grabStart = new \DateTime();
        $interval = new \DateInterval('P15D');
        $grabStart = $grabStart->sub($interval);

        $grabEnd = new \DateTime();

        $reservations = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Reservation\Reservation')
            ->getMylatestGradedLists(
                $salesCompanyId,
                $adminId,
                $grabStart,
                $grabEnd,
                Reservation::GRABED,
                $limit,
                $offset
            );

        $result = [];
        foreach ($reservations as $k=>$reservation) {
            $result[$k] = $this->getProductInfo($reservation);
        }

        $view = new View();
        $view->setData(
            array(
                'items'=>$result
            )
        );

        return $view;
    }
}