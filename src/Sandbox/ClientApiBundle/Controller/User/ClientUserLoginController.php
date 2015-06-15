<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\User\UserLoginController;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\User\UserToken;
use Sandbox\ApiBundle\Entity\User\UserClient;
use Sandbox\ApiBundle\Form\User\UserClientType;
use Sandbox\ClientApiBundle\Entity\User\ClientUserLogin;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Security\Acl\Exception\Exception;

/**
 * Login controller
 *
 * @category Sandbox
 * @package  Sandbox\ClientApiBundle\Controller
 * @author   Yimo Zhang <yimo.zhang@Sandbox.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 * @link     http://www.Sandbox.cn/
 */
class ClientUserLoginController extends UserLoginController
{
    const ERROR_ACCOUNT_NOT_ACTIVATED_CODE = 401001;
    const ERROR_ACCOUNT_NOT_ACTIVATED_MESSAGE = 'Account is not activated';

    const ERROR_ACCOUNT_BANNED_CODE = 401002;
    const ERROR_ACCOUNT_BANNED_MESSAGE = 'Account is banned';

    /**
     * Login
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
     * @throws \Exception
     */
    public function postClientUserLoginAction(
        Request $request
    ) {
        // get user if account is activated
        $user = $this->getUserIfActivated($this->getUser());

        return $this->handleClientUserLogin($request, $user);
    }

    /**
     * @param  Request    $request
     * @param  User       $user
     * @return View
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

            // save or refresh user token
            $userToken = $this->saveUserToken($user, $userClient);
            if (is_null($userToken->getId())) {
                $em->persist($userToken);
            }
            $em->flush();

            // response
            $view = new View();

            return $view->setData(array(
                'username' => $user->getUsername(),
                'client_id' => $userClient->getId(),
                'token' => $userToken->getToken(),
            ));
        } catch (Exception $e) {
            throw new \Exception('Something went wrong!');
        }
    }

    /**
     * @param  ClientUserLogin $userLogin
     * @return User
     */
    private function getUserIfActivated(
        $userLogin
    ) {
        if (User::STATUS_REGISTERED === $userLogin->getStatus()) {
            // user is not activated
            return $this->customErrorView(
                401,
                self::ERROR_ACCOUNT_NOT_ACTIVATED_CODE,
                self::ERROR_ACCOUNT_NOT_ACTIVATED_MESSAGE);
        } elseif (User::STATUS_BANNED === $userLogin->getStatus()) {
            // user is banned
            return $this->customErrorView(
                401,
                self::ERROR_ACCOUNT_BANNED_CODE,
                self::ERROR_ACCOUNT_BANNED_MESSAGE);
        }

        // so far, user is activated, return user
        return $this->getRepo('User\User')->find($userLogin->getId());
    }

    /**
     * @param  Request    $request
     * @return UserClient
     */
    private function saveUserClient(
        Request $request
    ) {
        $userClient = new UserClient();

        $requestContent = $request->getContent();
        if (!is_null($requestContent)) {
            // get client data from request payload
            $payload = json_decode($requestContent, true);
            $clientData = $payload['client'];

            if (!is_null($clientData)) {
                if (array_key_exists('id', $clientData)) {
                    // get existing user client
                    $userClient = $this->getRepo('User\UserClient')->find($clientData['id']);
                    if (is_null($userClient)) {
                        $userClient = new UserClient();
                        unset($clientData['id']);
                    }
                }

                // bind client data
                $form = $this->createForm(new UserClientType(), $userClient);
                $form->submit($clientData, true);
            }
        }

        // set ip address
        $userClient->setIpAddress($request->getClientIp());

        return $userClient;
    }

    /**
     * @param  User       $user
     * @param  UserClient $userClient
     * @return UserToken
     */
    private function saveUserToken(
        $user,
        $userClient
    ) {
        $userToken = $this->getRepo('User\UserToken')->findOneBy(array(
            'username' => $user->getUsername(),
            'clientId' => $userClient->getId(),
        ));

        if (is_null($userToken)) {
            $userToken = new UserToken();
            $userToken->setUsername($user->getUsername());
            $userToken->setClientId($userClient->getId());
            $userToken->setToken($this->generateRandomToken());
        }

        // refresh creation date
        $userToken->setCreationDate($this->currentTimeMillis());

        return $userToken;
    }
}
