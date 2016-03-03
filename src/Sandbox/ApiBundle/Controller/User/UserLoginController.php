<?php

namespace Sandbox\ApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ClientApiBundle\Data\User\UserLoginData;
use Sandbox\ClientApiBundle\Data\User\UserLoginDeviceData;
use Sandbox\ApiBundle\Entity\ThirdParty\WeChat;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\User\UserClient;
use Sandbox\ApiBundle\Entity\User\UserToken;
use Symfony\Component\HttpFoundation\Request;
use Sandbox\ApiBundle\Traits\OpenfireApi;
use FOS\RestBundle\View\View;
use Doctrine\ORM\EntityManager;

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
     * @param Request       $request
     * @param User          $user
     * @param UserLoginData $login
     * @param WeChat        $weChat
     *
     * @return View
     *
     * @throws \Exception
     */
    protected function handleClientUserLogin(
        Request $request,
        $user,
        $login,
        $weChat = null
    ) {
        try {
            $data = array();

            $userClient = $login->getClient();
            $deviceData = $login->getDevice();

            $em = $this->getDoctrine()->getManager();

            // save or update user client
            $userClient = $this->saveUserClient($em, $request, $userClient);
            $data['client'] = $userClient;

            if (!is_null($user)) {
                // force to set other token offline
                $userTokenAll = $this->getRepo('User\UserToken')->findByUserId($user->getId());
                foreach ($userTokenAll as $token) {
                    $token->setOnline(false);
                }

                // save or refresh user token
                $userToken = $this->saveUserToken($em, $user, $userClient);

                // handle device
                $this->handleDevice($user, $deviceData);

                $data['user'] = $user;
                $data['token'] = $userToken;
            }

            if (!is_null($weChat)) {
                $weChat->setUserClient($userClient);
                $data['wechat'] = $weChat;
            }

            $em->flush();

            return $data;
        } catch (\Exception $e) {
            throw new \Exception('Something went wrong!');
        }
    }

    /**
     * @param EntityManager $em
     * @param Request       $request
     * @param UserClient    $client
     *
     * @return UserClient
     */
    protected function saveUserClient(
        $em,
        Request $request,
        $client
    ) {
        $name = $client->getName();
        $os = $client->getOs();
        $version = $client->getVersion();
        $ipAddress = $request->getClientIp();

        $now = new \DateTime('now');

        $clientExist = null;
        $id = $client->getId();

        if (!is_null($id)) {
            $clientExist = $this->getRepo('User\UserClient')->find($id);

            if (!is_null($clientExist)) {
                // update existing client info
                $clientExist->setName($name);
                $clientExist->setOs($os);
                $clientExist->setVersion($version);
                $clientExist->setIpAddress($ipAddress);
                $clientExist->setModificationDate($now);

                return $clientExist;
            }
        }

        $client = new UserClient();
        $client->setName($name);
        $client->setOs($os);
        $client->setVersion($version);
        $client->setIpAddress($ipAddress);
        $client->setCreationDate($now);
        $client->setModificationDate($now);

        $em->persist($client);

        return $client;
    }

    /**
     * @param EntityManager $em
     * @param User          $user
     * @param UserClient    $userClient
     *
     * @return UserToken
     */
    protected function saveUserToken(
        $em,
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

            $em->persist($userToken);
        }

        // refresh creation date
        $userToken->setCreationDate(new \DateTime('now'));
        $userToken->setOnline(true);

        return $userToken;
    }

    /**
     * @param User                $user
     * @param UserLoginDeviceData $deviceData
     */
    protected function handleDevice(
        $user,
        $deviceData
    ) {
        try {
            if (is_null($deviceData)) {
                return;
            }

            $token = $deviceData->getToken();
            $platform = $deviceData->getPlatform();

            if ($platform === self::PLATFORM_IPHONE) {
                $jid = $this->constructXmppJid($user->getXmppUsername());
                $this->disableXmppOtherApns($jid, $token);
            }
        } catch (\Exception $e) {
            error_log('Login handle device went wrong!');
        }
    }

    /**
     * @param string $jid
     * @param string $currentToken
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
