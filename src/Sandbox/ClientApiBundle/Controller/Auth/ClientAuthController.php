<?php

namespace Sandbox\ClientApiBundle\Controller\Auth;

use Sandbox\ApiBundle\Controller\Auth\AuthController;
use Sandbox\ApiBundle\Entity\User\User;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use JMS\Serializer\SerializationContext;

/**
 * Client Auth controller.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang <yimo.zhang@Sandbox.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientAuthController extends AuthController
{
    /**
     * Token auth.
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
     * @Route("/me")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getClientAuthMeAction(
        Request $request
    ) {
        $myUserId = $this->getUserId();
        $myUser = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->find($myUserId);

        if ($myUser->isBanned()) {
            throw new UnauthorizedHttpException(null, self::UNAUTHED_API_CALL);
        }

        $view = new View();
        $view->setData(array(
            'id' => $myUserId,
            'phone' => $myUser->getPhone(),
            'email' => $myUser->getEmail(),
        ));

        return $view;
    }

    /**
     * Get user token info.
     *
     * @param Request $request
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Route("/token")
     * @Method({"POST"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postClientAuthToken(
        Request $request
    ) {
        $requestContent = json_decode($request->getContent(), true);
        $myUserToken = $requestContent['token'];
        $userToken = $this->getRepo('User\UserToken')->findOneByToken($myUserToken);
        $this->throwNotFoundIfNull($userToken, self::NOT_FOUND_MESSAGE);

        $view = new View($userToken);
        $view->setSerializationContext(SerializationContext::create()->setGroups(array('main')));

        return $view;
    }

    /**
     * DES Encrypt Password.
     *
     * @Route("/password")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getClientAuthPasswordAction(
        Request $request
    ) {
        $myUserId = $this->getUserId();
        $myUser = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->find($myUserId);

        if ($myUser->isBanned()) {
            throw new UnauthorizedHttpException(null, self::UNAUTHED_API_CALL);
        }

        $plain = $myUser->getPassword();
        $encrypt = $this->get('sandbox_api.des_encrypt')->encrypt($plain);

        $view = new View();
        $view->setData(array(
            'xmpp_username' => $myUser->getXmppUsername(),
            'password' => $encrypt,
        ));

        return $view;
    }
}
