<?php

namespace Sandbox\ClientApiBundle\Controller\MembershipCard;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

class ClientMembershipOrderController extends SandboxRestController
{
    /**
     * @param Request $request
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
     * @param Request $request
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