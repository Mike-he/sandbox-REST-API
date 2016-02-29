<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\User\UserLoginController;
use Sandbox\ApiBundle\Entity\User\User;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Security\Acl\Exception\Exception;
use JMS\Serializer\SerializationContext;

/**
 * Login controller.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang <yimo.zhang@Sandbox.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientUserLoginController extends UserLoginController
{
    const ERROR_ACCOUNT_BANNED_CODE = 401001;
    const ERROR_ACCOUNT_BANNED_MESSAGE = '您的账户已经被冻结，如有疑问请联系客服：xxx-xxxxxxx';

    /**
     * Login.
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
     * @Route("/login")
     * @Method({"POST"})
     *
     * @return string
     *
     * @throws \Exception
     */
    public function postClientUserLoginAction(
        Request $request
    ) {
        $user = $this->getUser();
        if ($user->isBanned()) {
            // user is banned
            return $this->customErrorView(
                401,
                self::ERROR_ACCOUNT_BANNED_CODE,
                self::ERROR_ACCOUNT_BANNED_MESSAGE);
        }

        return $this->handleClientUserLogin($request, $user);
    }

    /**
     * @param Request $request
     * @param User    $user
     *
     * @return View
     *
     * @throws \Exception
     */
    private function handleClientUserLogin(
        Request $request,
        $user
    ) {
        try {
            $em = $this->getDoctrine()->getManager();

            // save or update user client
            $userClient = $this->saveUserClient($request);
            if (is_null($userClient->getId())) {
                $em->persist($userClient);
                $em->flush();
            }

            // force other client offline
            $userTokenAll = $this->getRepo('User\UserToken')->findByUserId($user->getId());
            foreach ($userTokenAll as $token) {
                $token->setOnline(false);
            }

            // save or refresh user token
            $userToken = $this->saveUserToken($user, $userClient);
            if (is_null($userToken->getId())) {
                $em->persist($userToken);
            }
            $em->flush();

            // handle device
            $this->handleDevice($request, $user);

            // response
            $view = new View();
            $view->setSerializationContext(SerializationContext::create()->setGroups(array('login')));

            return $view->setData(array(
                'user' => $user,
                'client' => $userClient,
                'token' => $userToken,
            ));
        } catch (Exception $e) {
            throw new \Exception('Something went wrong!');
        }
    }
}
