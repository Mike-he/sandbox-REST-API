<?php

namespace Sandbox\ClientApiBundle\Controller\MembershipCard;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\Payment\PaymentController;
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
     *
     * @Route("/membership_orders")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postMembershipOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $requestContent = json_decode($request->getContent(), true);

        $userId = $this->getUserId();

        if (!array_key_exists('card_id', $requestContent) ||
            !array_key_exists('specification_id', $requestContent) ||
            !array_key_exists('channel', $requestContent) ||
            !array_key_exists('start_date', $requestContent)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $cardId = $requestContent['card_id'];
        $specificationId = $requestContent['specification_id'];
        $channel = $requestContent['channel'];
        $startDate = new \DateTime($requestContent['start_date']);

        $orderNumber = $this->getOrderNumber(MembershipOrder::MEMBERSHIP_ORDER_LETTER_HEAD);
        $specification = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipCardSpecification')
            ->find($specificationId);
        $card = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipCard')
            ->find($cardId);

        $price = $specification->getPrice();
        $unit = $specification->getUnitPrice();
        $validPeriod = $specification->getValidPeriod();

        $endDate = clone $startDate;
        $endDate = $endDate->modify("+$unit $validPeriod");

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
            $order->setOrderNumber($orderNumber);
            $order->setCard($cardId);
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

        $charge = $this->payForOrder(
            '',
            '',
            '',
            $orderNumber,
            $price,
            $channel,
            MembershipOrder::PAYMENT_SUBJECT,
            array(
                'user_id' => $userId,
                'card_id' => $cardId,
                'specification_id' => $specificationId,
                'start_date' => $startDate,
            ),
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
     * @Route("/membership_cards_my")
     */
    public function getMyMembershipCardsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();

        $cardOrders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipOrder')
            ->getMyValidClientMembershipOrder(
                $userId
            );

        $response = $this->generateClientMembershipCardsResponse($cardOrders);

        return new View($response);
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
            $card = $cardOrder->getCard();
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

            array_push(
                $response,
                array(
                    'card' => array(
                        'card_name' => $card->getName(),
                        'card_image' => $card->getBackground(),
                        'specification' => array(
                            'specification_name' => $cardOrder->getSpecification(),
                            'valid_period' => $cardOrder->getValidPeriod(),
                            'unit_price' => $cardOrder->getUnitPrice(),
                        ),
                        'building' => $buildingArray,
                    ),
                    'order' => array(
                        'pay_channel' => $cardOrder->getPaychannel(),
                        'price' => $cardOrder->getPrice(),
                        'payment_date' => $cardOrder->getPaymentDate(),
                        'start_date' => $cardOrder->getStartDate(),
                        'end_date' => $cardOrder->getEndDate(),
                    ),
                )
            );
        }

        return $response;
    }
}
