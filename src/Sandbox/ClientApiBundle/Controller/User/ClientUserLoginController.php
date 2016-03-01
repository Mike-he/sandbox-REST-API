<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Controller\User\UserLoginController;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\User\UserToken;
use Sandbox\ApiBundle\Entity\User\UserClient;
use Sandbox\ApiBundle\Form\User\UserClientType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Security\Acl\Exception\Exception;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Traits\OpenfireApi;

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
    use OpenfireApi;

    const ERROR_ACCOUNT_BANNED_CODE = 401001;
    const ERROR_ACCOUNT_BANNED_MESSAGE = '您的账户已经被冻结，如有疑问请联系客服：'.SandboxRestController::CUSTOMER_SERVICE_PHONE;

    const PLATFORM_IPHONE = 'iphone';
    const PLATFORM_ANDROID = 'android';

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

    /**
     * @param Request $request
     *
     * @return UserClient
     */
    private function saveUserClient(
        Request $request
    ) {
        $userClient = new UserClient();

        // set creation date for new object
        $now = new \DateTime('now');
        $userClient->setCreationDate($now);

        // get user client if exist
        $userClient = $this->getUserClientIfExist($request, $userClient);

        // set ip address
        $userClient->setIpAddress($request->getClientIp());

        // set modification date
        $userClient->setModificationDate($now);

        return $userClient;
    }

    /**
     * @param Request    $request
     * @param UserClient $userClient
     *
     * @return UserClient
     */
    private function getUserClientIfExist(
        Request $request,
        $userClient
    ) {
        $requestContent = $request->getContent();
        if (is_null($requestContent)) {
            return $userClient;
        }

        // get client data from request payload
        $payload = json_decode($requestContent, true);
        $clientData = $payload['client'];

        if (is_null($clientData)) {
            return $userClient;
        }

        if (array_key_exists('id', $clientData)) {
            // get existing user client
            $userClientExist = $this->getRepo('User\UserClient')->find($clientData['id']);

            // if exist use the existing object
            // else remove id from client data for further form binding
            if (!is_null($userClientExist)) {
                $userClient = $userClientExist;
            } else {
                unset($clientData['id']);
            }
        }

        // bind client data
        $form = $this->createForm(new UserClientType(), $userClient);
        $form->submit($clientData, true);

        return $userClient;
    }

    /**
     * @param User       $user
     * @param UserClient $userClient
     *
     * @return UserToken
     */
    private function saveUserToken(
        $user,
        $userClient
    ) {
        $userToken = $this->getRepo('User\UserToken')->findOneBy(array(
            'user' => $user,
            'client' => $userClient,
        ));

        if (is_null($userToken)) {
            $userToken = new UserToken();
            $userToken->setUser($user);
            $userToken->setUserId($user->getId());
            $userToken->setClient($userClient);
            $userToken->setClientId($userClient->getId());
            $userToken->setToken($this->generateRandomToken());
        }

        // refresh creation date
        $userToken->setCreationDate(new \DateTime('now'));
        $userToken->setOnline(true);

        return $userToken;
    }

    /**
     * @param Request $request
     * @param User    $user
     */
    private function handleDevice(
        Request $request,
        $user
    ) {
        try {
            $requestContent = $request->getContent();
            if (is_null($requestContent)) {
                return;
            }

            // get device data from request payload
            $payload = json_decode($requestContent, true);
            $deviceData = $payload['device'];
            $token = $deviceData['token'];
            $platform = $deviceData['platform'];

            if ($platform === self::PLATFORM_IPHONE) {
                $jid = $this->constructXmppJid($user->getXmppUsername());
                $this->disableXmppOtherApns($jid, $token);
            }
        } catch (\Exception $e) {
            error_log('Login handle device went wrong!');
        }
    }

    /**
     * @param $jid
     * @param $currentToken
     */
    private function disableXmppOtherApns(
        $jid,
        $currentToken
    ) {
        try {
            // request json
            $jsonDataArray = array(
                'jid' => $jid,
                'current_token' => $currentToken,
            );
            $jsonData = json_encode($jsonDataArray);

            // call openfire APNS api
            $this->callOpenfireApnsApi('DELETE', $jsonData);
        } catch (\Exception $e) {
            error_log('Disable XMPP other APNS went wrong!');
        }
    }
}
