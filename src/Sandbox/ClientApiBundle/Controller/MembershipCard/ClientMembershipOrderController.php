<?php

namespace Sandbox\ClientApiBundle\Controller\MembershipCard;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Sandbox\ApiBundle\Entity\MembershipCard\MembershipCard;
use Sandbox\ApiBundle\Entity\MembershipCard\MembershipOrder;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyServiceInfos;
use Sandbox\ClientApiBundle\Data\ThirdParty\ThirdPartyOAuthWeChatData;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ClientMembershipOrderController extends PaymentController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param int                   $specificationId
     *
     * @Route("/membership_orders/{specificationId}/pay")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postMembershipOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $specificationId
    ) {
        $requestContent = json_decode($request->getContent(), true);

        $userId = $this->getUserId();

        if (!array_key_exists('channel', $requestContent)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $channel = $requestContent['channel'];

        $orderNumber = $this->getOrderNumber(MembershipOrder::MEMBERSHIP_ORDER_LETTER_HEAD);
        $specification = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipCardSpecification')
            ->find($specificationId);
        $this->throwNotFoundIfNull($specification, self::NOT_FOUND_MESSAGE);

        $card = $specification->getCard();
        $price = $specification->getPrice();
        $unit = $specification->getUnitPrice();
        $validPeriod = $specification->getValidPeriod();
        $accessNo = $card->getAccessNo();

        // get start date
        $startDate = $this->getLastMembershipOrderEndDate($userId, $card);

        $endDate = clone $startDate;
        $endDate = $endDate->modify("+$validPeriod $unit");

        $openId = null;
        if ($channel == ProductOrder::CHANNEL_ACCOUNT) {
            // pay by account
            $balance = $this->postBalanceChange(
                $userId,
                (-1) * $price,
                $orderNumber,
                self::PAYMENT_CHANNEL_ACCOUNT,
                $price
            );
            if (is_null($balance)) {
                return $this->customErrorView(
                    400,
                    self::INSUFFICIENT_FUNDS_CODE,
                    self::INSUFFICIENT_FUNDS_MESSAGE
                );
            }

            $order = new MembershipOrder();
            $order->setUser($userId);
            $order->setOrderNumber($orderNumber);
            $order->setCard($card);
            $order->setPrice($price);
            $order->setUnitPrice($unit);
            $order->setValidPeriod($validPeriod);
            $order->setStartDate($startDate);
            $order->setEndDate($endDate);
            $order->setPayChannel($channel);
            $order->setPaymentDate(new \DateTime('now'));
            $order->setSpecification($specification->getSpecification());

            $serviceInfo = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyServiceInfos')
                ->findOneBy(array(
                    'company' => $card->getCompanyId(),
                    'tradeTypes' => SalesCompanyServiceInfos::TRADE_TYPE_MEMBERSHIP_CARD,
                ));

            if (!is_null($serviceInfo)) {
                if ($serviceInfo->getDrawer() == SalesCompanyServiceInfos::COLLECTION_METHOD_SANDBOX) {
                    $order->setSalesInvoice(false);
                }

                $order->setServiceFee($serviceInfo->getServiceFee());
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($order);
            $em->flush();

            // add user to user_group
            $this->addUserToUserGroup(
                $em,
                $userId,
                $card,
                $startDate,
                $endDate,
                $orderNumber
            );

            // add user to door access
            $this->addUserDoorAccess(
                $card,
                null,
                $accessNo,
                array($userId),
                $order->getStartDate(),
                $order->getEndDate()
            );

            return new View(array(
                'id' => $order->getId(),
            ));
        }

        if ($channel == ProductOrder::CHANNEL_WECHAT_PUB) {
            $weChat = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:ThirdParty\WeChat')
                ->findOneBy(
                    [
                        'userId' => $this->getUserId(),
                        'loginFrom' => ThirdPartyOAuthWeChatData::DATA_FROM_WEBSITE,
                    ]
                );
            $this->throwNotFoundIfNull($weChat, self::NOT_FOUND_MESSAGE);

            $openId = $weChat->getOpenId();
        }

        $body = array(
            'user_id' => $userId,
            'specification_id' => $specificationId,
        );

        $charge = $this->payForOrder(
            '',
            '',
            '',
            $orderNumber,
            $price,
            $channel,
            MembershipOrder::PAYMENT_SUBJECT,
            json_encode($body),
            $openId
        );

        $charge = json_decode($charge, true);

        return new View($charge);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="limit",
     *     array=false,
     *     default="10"
     * )
     *
     * @Annotations\QueryParam(
     *     name="offset",
     *     array=false,
     *     default="0"
     * )
     *
     * @Route("/membership_orders")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMembershipCardOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $cardOrders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipOrder')
            ->getClientMembershipOrder(
                $userId,
                $limit,
                $offset
            );

        $response = $this->generateClientMembershipCardsResponse($cardOrders);

        return new View($response);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/membership_orders/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMembershipCardOrderAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $cardOrder = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipOrder')
            ->find($id);
        $this->throwNotFoundIfNull($cardOrder, self::NOT_FOUND_MESSAGE);

        $cardArray = $this->generateClientMembershipOrderArray($cardOrder);

        return new View($cardArray);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/membership_cards_my")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMyMembershipCardsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();

        $cardIds = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipOrder')
            ->getMyValidClientMembershipCards(
                $userId
            );

        $response = array();
        foreach ($cardIds as $cardId) {
            $card = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:MembershipCard\MembershipCard')
                ->find($cardId);

            array_push($response, array(
                'card' => $this->generateClientMembershipCardArray($card),
                'order' => array(
                    'end_date' => $this->getLastMembershipOrderEndDate($userId, $card),
                ),
            ));
        }

        return new View($response);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param $id
     *
     * @Route("/membership_cards_my/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMyMembershipCardAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $userId = $this->getUserId();

        $card = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipCard')
            ->find($id);
        $this->throwNotFoundIfNull($card, self::NOT_FOUND_MESSAGE);

        return new View(array(
            'card' => $this->generateClientMembershipCardArray($card),
            'order' => array(
                'end_date' => $this->getLastMembershipOrderEndDate($userId, $card),
            ),
        ));
    }

    /**
     * @param $cardOrders
     *
     * @return array
     */
    private function generateClientMembershipCardsResponse(
        $cardOrders
    ) {
        $response = array();
        foreach ($cardOrders as $cardOrder) {
            $cardArray = $this->generateClientMembershipOrderArray($cardOrder);

            array_push($response, $cardArray);
        }

        return $response;
    }

    /**
     * @param MembershipOrder $cardOrder
     *
     * @return array
     */
    private function generateClientMembershipOrderArray(
        $cardOrder
    ) {
        $cardArray = array(
            'card' => $this->generateClientMembershipCardArray($cardOrder->getCard()),
            'order' => array(
                'id' => $cardOrder->getId(),
                'order_number' => $cardOrder->getOrderNumber(),
                'pay_channel' => $cardOrder->getPaychannel(),
                'price' => $cardOrder->getPrice(),
                'payment_date' => $cardOrder->getPaymentDate(),
                'start_date' => $cardOrder->getStartDate(),
                'end_date' => $cardOrder->getEndDate(),
                'specification' => array(
                    'specification_name' => $cardOrder->getSpecification(),
                    'valid_period' => $cardOrder->getValidPeriod(),
                    'unit_price' => $cardOrder->getUnitPrice(),
                ),
            ),
        );

        return $cardArray;
    }

    /**
     * @param MembershipCard $card
     *
     * @return array
     */
    private function generateClientMembershipCardArray(
        $card
    ) {
        $doors = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserGroupDoors')
            ->findBy(
                array(
                    'card' => $card,
                )
            );

        $buildingArray = array();
        foreach ($doors as $door) {
            $buildingId = $door->getBuilding();
            $building = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                ->find($buildingId);

            array_push(
                $buildingArray,
                array(
                    'id' => $buildingId,
                    'name' => $building->getName(),
                )
            );
        }

        $ordersUrl = $this->container->getParameter('orders_url');
        $url = $ordersUrl.'/member?ptype=productDetail&productId='.$card->getId();

        return array(
            'id' => $card->getId(),
            'card_name' => $card->getName(),
            'card_image' => $card->getBackground(),
            'phone' => $card->getPhone(),
            'description' => $card->getDescription(),
            'instructions' => $card->getInstructions(),
            'building' => $buildingArray,
            'order_url' => $url,
        );
    }
}
