<?php

namespace Sandbox\ClientApiBundle\Controller\MembershipCard;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

class ClientMembershipCardController extends SandboxRestController
{
    /**
     * @param Request $request
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
            ->getCardsByIds(
                $cardIds
            );

        $response = array();
        foreach ($cards as $card) {
            $specification = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:MembershipCard\MembershipCardSpecification')
                ->getMinCardSpecification(
                    $card
                );

            array_push($response, array(
                'id' => $card->getId(),
                'card_name' => $card->getName(),
                'card_image' => $card->getBackground(),
                'min_price' => $specification->getPrice(),
                'min_valid_period' => $specification->getValidPeriod(),
                'min_unit_price' => $specification->getUnitPrice(),
            ));
        }

        return new View($response);
    }

    /**
     * @param Request $request
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

    }
}