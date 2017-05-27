<?php

namespace Sandbox\ClientApiBundle\Controller\ThirdParty;

use Sandbox\ApiBundle\Controller\ThirdParty\ThirdPartyController;
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

        $groupUser = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserGroupHasUser')
            ->findOneBy(array('userId' => $user->getId()));

        if (is_null($groupUser)) {
            throw new UnauthorizedHttpException(null, self::UNAUTHED_API_CALL);
        }

        return new View(array(
            'id' => $user->getId(),
        ));
    }
}
