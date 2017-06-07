<?php

namespace Sandbox\ClientApiBundle\Controller\ThirdParty;

use Sandbox\ApiBundle\Controller\ThirdParty\ThirdPartyController;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Sandbox\ApiBundle\Entity\User\User;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * Client Third Party controller.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang <yimo.zhang@Sandbox.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientThirdPartyController extends ThirdPartyController
{
    /**
     * Third party auth.
     *
     * @param Request $request the request object
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Route("/auth")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getClientThirdPartyAuthAction(
        Request $request
    ) {
        $user = $this->getUser();
        if ($user->isBanned()) {
            // user is banned
            throw new UnauthorizedHttpException(null, self::UNAUTHED_API_CALL);
        }

        $userId = $user->getId();
        $now = new \DateTime();

        // membership order
        $membershipOrder = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipOrder')
            ->countValidOrder(
                $userId,
                $now
            );

        if ($membershipOrder > 0) {
            return new View(array('id' => $userId));
        }

        // product order
        $productOrder = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->countValidOrder(
                $userId,
                $now
            );

        if ($productOrder > 0) {
            return new View(array('id' => $userId));
        }

        // event order
        $eventOrder = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Event\EventOrder')
            ->countValidOrder(
                $userId,
                $now
            );

        if ($eventOrder > 0) {
            return new View(array('id' => $userId));
        }

        // lease
        $status = array(
            Lease::LEASE_STATUS_CONFIRMING,
            Lease::LEASE_STATUS_CONFIRMED,
            Lease::LEASE_STATUS_RECONFIRMING,
            Lease::LEASE_STATUS_PERFORMING,
        );

        $lease = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')
            ->countValidOrder(
                $userId,
                $now,
                $status
            );

        if ($lease > 0) {
            return new View(array('id' => $userId));
        }

        throw new UnauthorizedHttpException(null, self::UNAUTHED_API_CALL);
    }
}
