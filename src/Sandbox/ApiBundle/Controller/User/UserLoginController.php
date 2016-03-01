<?php

namespace Sandbox\ApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\ThirdParty\WeChat;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\User\UserClient;
use Sandbox\ApiBundle\Entity\User\UserToken;
use Sandbox\ApiBundle\Form\User\UserClientType;
use Symfony\Component\HttpFoundation\Request;
use Sandbox\ApiBundle\Traits\OpenfireApi;
use FOS\RestBundle\View\View;

/**
 * User Login Controller.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang <yimo.zhang@Sandbox.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class UserLoginController extends SandboxRestController
{
    use OpenfireApi;

    const PLATFORM_IPHONE = 'iphone';
    const PLATFORM_ANDROID = 'android';

    /**
     * @param Request $request
     * @param User    $user
     * @param WeChat  $weChat
     *
     * @return View
     *
     * @throws \Exception
     */
    protected function handleClientUserLogin(
        Request $request,
        $user,
        $weChat = null
    ) {
        try {
            $data = array();

            if (!is_null($user)) {
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

                $data['user'] = $user;
                $data['client'] = $userClient;
                $data['token'] = $userToken;
            }

            if (!is_null($weChat)) {
                $data['wechat'] = $weChat;
            }

            return $data;
        } catch (\Exception $e) {
            throw new \Exception('Something went wrong!');
        }
    }

    /**
     * @param Request $request
     *
     * @return UserClient
     */
    protected function saveUserClient(
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
    protected function getUserClientIfExist(
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
    protected function saveUserToken(
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
    protected function handleDevice(
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
    protected function disableXmppOtherApns(
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
