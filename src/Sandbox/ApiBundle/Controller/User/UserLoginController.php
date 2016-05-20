<?php

namespace Sandbox\ApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ClientApiBundle\Data\User\UserLoginData;
use Sandbox\ClientApiBundle\Data\User\UserLoginDeviceData;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\User\UserClient;
use Sandbox\ApiBundle\Entity\User\UserToken;
use Symfony\Component\HttpFoundation\Request;
use Sandbox\ApiBundle\Traits\OpenfireApi;
use FOS\RestBundle\View\View;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Sandbox\ApiBundle\Entity\Error\Error;

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

    const ERROR_ACCOUNT_BANNED_CODE = 401001;
    const ERROR_ACCOUNT_BANNED_MESSAGE = 'client.login.account_banned';

    const ERROR_ACCOUNT_NONEXISTENT_CODE = 401002;
    const ERROR_ACCOUNT_NONEXISTENT_MESSAGE = 'client.login.account_non_existent';

    /**
     * @param Request       $request
     * @param User          $user
     * @param UserLoginData $login
     *
     * @return View
     *
     * @throws \Exception
     */
    protected function handleClientUserLogin(
        Request $request,
        $user,
        $login
    ) {
        $data = array();

        $userClient = $login->getClient();
        $deviceData = $login->getDevice();

        $em = $this->getDoctrine()->getManager();

        // save or update user client
        $userClient = $this->saveUserClient($em,
                                            $request,
                                            $userClient);
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

        $em->flush();

        return $data;
    }

    /**
     * @param EntityManager $em
     * @param Request       $request
     * @param UserClient    $userClient
     *
     * @return UserClient
     */
    protected function saveUserClient(
        $em,
        $request,
        $userClient
    ) {
        // get incoming data
        $id = null;
        $name = null;
        $os = null;
        $version = null;

        if (!is_null($userClient)) {
            $id = $userClient->getId();
            $name = $userClient->getName();
            $os = $userClient->getOs();
            $version = $userClient->getVersion();
        }

        // save or update to db
        $client = null;

        if (!is_null($id)) {
            $client = $this->getRepo('User\UserClient')->find($id);
        }

        $now = new \DateTime('now');

        if (is_null($client)) {
            $client = new UserClient();
            $client->setCreationDate($now);

            $em->persist($client);
        }

        $client->setName($name);
        $client->setOs($os);
        $client->setVersion($version);
        $client->setIpAddress($request->getClientIp());
        $client->setModificationDate($now);

        // save to db
        $em->flush();

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

    /**
     * @param Error $error
     *
     * @return mixed
     */
    protected function getUserIfAuthenticated(
        $error
    ) {
        $headerKey = self::SANDBOX_CLIENT_LOGIN_HEADER;

        // get auth
        $headers = array_change_key_case(apache_request_headers(), CASE_LOWER);
        if (!array_key_exists($headerKey, $headers)) {
            return $this->getUser();
        }

        $auth = $this->getSandboxAuthorization($headerKey);
        if (is_null($auth)) {
            throw new UnauthorizedHttpException(null, self::UNAUTHED_API_CALL);
        }

        // find user by email or phone
        $username = $auth->getUsername();
        if (is_null($username)) {
            throw new UnauthorizedHttpException(null, self::UNAUTHED_API_CALL);
        }

        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $user = $this->getRepo('User\User')->findOneByEmail($username);
        } else {
            $user = $this->getRepo('User\User')->findOneByPhone($username);
        }

        if (is_null($user)) {
            $error->setCode(self::ERROR_ACCOUNT_NONEXISTENT_CODE);
            $error->setMessage(self::ERROR_ACCOUNT_NONEXISTENT_MESSAGE);

            return;
        }

        if ($auth->getPassword() != $user->getPassword()) {
            throw new UnauthorizedHttpException(null, self::UNAUTHED_API_CALL);
        }

        return $user;
    }
}
