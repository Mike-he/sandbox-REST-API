<?php

namespace Sandbox\ClientApiBundle\Controller\MembershipCard;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

class ClientMembershipCardController extends SandboxRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="building",
     *    default=null,
     *    nullable=true,
     *    description="building id"
     * )
     *
     * @Route("/membership_cards")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMembershipCardsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $buildingId = $paramFetcher->get('building');

        $cardIds = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserGroupDoors')
            ->getMembershipCard(
                $buildingId
            );

        $cards = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipCard')
            ->getClientCardsByIds(
                $cardIds
            );

        $response = array();
        foreach ($cards as $card) {
            $specification = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:MembershipCard\MembershipCardSpecification')
                ->getMinCardSpecification(
                    $card
                );

            $unitPrice = $this->get('translator')->trans(ProductOrderExport::TRANS_ROOM_UNIT.$specification->getUnitPrice());
            $ordersUrl = $this->container->getParameter('orders_url');
            $url = $ordersUrl.'/member?ptype=productDetail&productId='.$card->getId();

            array_push($response, array(
                'id' => $card->getId(),
                'card_name' => $card->getName(),
                'card_image' => $card->getBackground(),
                'min_price' => $specification->getPrice(),
                'min_valid_period' => $specification->getValidPeriod(),
                'min_unit_price' => $unitPrice,
                'order_url' => $url,
            ));
        }

        return new View($response);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/membership_cards/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMembershipCardAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $card = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipCard')
            ->find($id);
        $this->throwNotFoundIfNull($card, self::NOT_FOUND_MESSAGE);

        $specifications = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipCardSpecification')
            ->getCardSpecifications(
                $card
            );

        $specificationsArray = array();
        foreach ($specifications as $specification) {
            array_push($specificationsArray, array(
                'id' => $specification->getId(),
                'specification' => $specification->getSpecification(),
                'price' => $specification->getPrice(),
                'valid_period' => $specification->getValidPeriod(),
                'unit_price' => $specification->getUnitPrice(),
            ));
        }

        $doors = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserGroupDoors')
            ->findBy(array(
                'card' => $card,
            ));

        $buildingIdsArray = array();
        foreach ($doors as $door) {
            array_push($buildingIdsArray, $door->getBuilding());
        }

        $response = array(
            'id' => $card->getId(),
            'card_name' => $card->getName(),
            'card_image' => $card->getBackground(),
            'phone' => $card->getPhone(),
            'description' => $card->getDescription(),
            'instructions' => $card->getInstructions(),
            'specifications' => $specificationsArray,
            'building_ids' => $buildingIdsArray,
        );

        return new View($response);
    }
}
