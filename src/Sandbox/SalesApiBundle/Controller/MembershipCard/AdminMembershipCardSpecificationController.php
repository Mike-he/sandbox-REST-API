<?php

namespace Sandbox\SalesApiBundle\Controller\MembershipCard;

use Sandbox\ApiBundle\Entity\MembershipCard\MembershipCardSpecification;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;

/**
 * Admin MembershipCard Controller.
 */
class AdminMembershipCardSpecificationController extends SalesRestController
{
    /**
     * @param Request $request
     *
     *
     * @Method({"GET"})
     * @Route("/membership/cards/{id}/specification")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdminMembershipCardSpecificationAction(
        Request $request,
        $id
    ) {
        $specification = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipCardSpecification')
            ->findBy(array('card' => $id));

        return new View($specification);
    }

    /**
     * @param Request $request
     *
     *
     * @Method({"POST"})
     * @Route("/membership/cards/{id}/specification")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postAdminMembershipCardSpecificationAction(
        Request $request,
        $id
    ) {
        $em = $this->getDoctrine()->getManager();
        $membershipCard = $em->getRepository('SandboxApiBundle:MembershipCard\MembershipCard')->find($id);
        $this->throwNotFoundIfNull($membershipCard, self::NOT_FOUND_MESSAGE);

        $payload = json_decode($request->getContent(), true);

        if (isset($payload['add'])) {
            foreach ($payload['add'] as $add) {
                if (isset($add['specification']) &&
                    isset($add['price']) &&
                    isset($add['valid_period'])
                ) {
                    $specification = new MembershipCardSpecification();
                    $specification->setCard($membershipCard);
                    $specification->setSpecification($add['specification']);
                    $specification->setPrice($add['price']);
                    $specification->setValidPeriod($add['valid_period']);
                    $specification->setUnitPrice(MembershipCardSpecification::UNIT_MONTH);

                    $em->persist($specification);
                }
            }
        }

        if (isset($payload['modify'])) {
            foreach ($payload['modify'] as $modify) {
                $specification = $em->getRepository('SandboxApiBundle:MembershipCard\MembershipCardSpecification')
                    ->find($modify['id']);
                if ($specification) {
                    $specification->setSpecification($modify['specification']);
                    $specification->setPrice($modify['price']);
                    $specification->setValidPeriod($modify['valid_period']);

                    $em->persist($specification);
                }
            }
        }

        if (isset($payload['remove'])) {
            foreach ($payload['remove'] as $remove) {
                $specification = $em->getRepository('SandboxApiBundle:MembershipCard\MembershipCardSpecification')
                    ->find($remove['id']);
                if ($specification) {
                    $em->remove($specification);
                }
            }
        }

        $em->flush();

        return new View();
    }

    /**
     * Check user permission.
     */
    private function checkMembershipCardPermission(
        $OpLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
//                ['key' => AdminPermission::KEY_SALES_BUILDING_LONG_TERM_LEASE],
            ],
            $OpLevel
        );
    }
}
