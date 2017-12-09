<?php

namespace Sandbox\ClientPropertyApiBundle\Controller\Order;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Entity\MembershipCard\MembershipCard;
use Sandbox\ApiBundle\Entity\MembershipCard\MembershipOrder;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;

class ClientMembershipCardOrderController extends SalesRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="channel",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    description="payment channel"
     * )
     *
     * @Annotations\QueryParam(
     *    name="keyword",
     *    default=null,
     *    nullable=true,
     *    description="search query"
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
     *    name="building",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Filter by building id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="create_date_range",
     *    default=null,
     *    nullable=true,
     *    description="create_date_range"
     * )
     *
     * @Annotations\QueryParam(
     *    name="create_start",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="start date. Must be YYYY-mm-dd"
     * )
     *
     *  @Annotations\QueryParam(
     *    name="create_end",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$",
     *    strict=true,
     *    description="end date. Must be YYYY-mm-dd"
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
     * @Method({"GET"})
     * @Route("/membership/cards")
     *
     * @return View
     */
    public function getAdminMembershipCardOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $platform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $companyId = $platform['sales_company_id'];

        $channel = $paramFetcher->get('channel');
        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');
        $buildingId = $paramFetcher->get('building');
        $createDateRange = $paramFetcher->get('create_date_range');
        $createStart = $paramFetcher->get('create_start');
        $createEnd = $paramFetcher->get('create_end');

        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $orders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipOrder')
            ->getAdminOrders(
                $channel,
                $keyword,
                $keywordSearch,
                $buildingId,
                $createDateRange,
                $createStart,
                $createEnd,
                $limit,
                $offset,
                $companyId
            );

        $orderLists = [];
        foreach ($orders as $order) {
            $orderLists[] = $this->handleOrderData($order);
        }

        $view = new View();
        $view->setData($orderLists);

        return $view;
    }

    /**
     * @param MembershipOrder $order
     *
     *  @return array
     */
    private function handleOrderData(
        $order
    ) {
        $card = $order->getCard();
        $userId = $order->getUser();

        $customer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomer')
            ->findOneBy(array(
                'userId' => $userId,
                'companyId' => $card->getCompanyId(),
            ));

        $result = array(
            'id' => $order->getId(),
            'order_number' => $order->getOrderNumber(),
            'payment_date' => $order->getPaymentDate(),
            'name' => $card->getName(),
            'specification' => $order->getSpecification(),
            'start_date' => $order->getStartDate(),
            'end_date' => $order->getEndDate(),
            'price' => $order->getPrice(),
            'pay_channel' => $order->getPayChannel() ? '秒租钱包' : '',
            'status' => '已付款',
            'background' => $card->getBackground(),
            'customer' => array(
                'id' => $customer ? $customer->getId() : '',
                'name' => $customer ? $customer->getName() : '',
                'avatar' => $customer ? $customer->getAvatar() : '',
            ),
        );

        return $result;
    }

    /**
     * @param Request $request
     * @param int     $id
     *
     * @Method({"GET"})
     * @Route("/membership/cards/{id}")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdminMembershipCardOrderByIdAction(
        Request $request,
        $id
    ) {
        $platform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $companyId = $platform['sales_company_id'];

        $order = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipOrder')
            ->getAdminOrderById(
                $id,
                $companyId
            );

        if (is_null($order)) {
            return new View();
        }

        /** @var MembershipCard $card */
        $card = $order->getCard();

        $groupDoors = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserGroupDoors')
            ->findBy([
                'card' => $card,
            ]);

        $buildingIds = [];
        foreach ($groupDoors as $door) {
            array_push($buildingIds, $door->getBuilding());
        }

        $card->setBuildingIds($buildingIds);

        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->find($order->getUser());

        $customer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomer')
            ->findOneBy(array(
                'userId' => $order->getUser(),
                'companyId' => $card->getCompanyId(),
            ));

        $info = [
            'id' => $customer ? $customer->getId() : '',
            'name' => $customer ? $customer->getName() : '',
            'avatar' => $customer ? $customer->getAvatar() : '',
            'phone' => $customer ? $customer->getPhone() : '',
            'user_card_no' => $user ? $user->getCardNo() : '',
        ];

        $order->setUserInfo($info);

        $view = new View($order);

        return $view;
    }
}
