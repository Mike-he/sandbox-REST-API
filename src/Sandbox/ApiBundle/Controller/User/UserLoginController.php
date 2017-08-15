<?php

namespace Sandbox\ApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ClientApiBundle\Data\User\UserLoginData;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\User\UserClient;
use Sandbox\ApiBundle\Entity\User\UserToken;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sandbox\ApiBundle\Entity\Error\Error;
use Sandbox\ApiBundle\Entity\ThirdParty\WeChat;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

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

    const PLATFORM_IPHONE = 'iphone';
    const PLATFORM_ANDROID = 'android';

    const PREFIX_ACCESS_TOKEN = 'access_token';
    const PREFIX_FRESH_TOKEN = 'fresh_token';

    const ERROR_ACCOUNT_BANNED_CODE = 401001;
    const ERROR_ACCOUNT_BANNED_MESSAGE = 'client.login.account_banned';

    const ERROR_ACCOUNT_NONEXISTENT_CODE = 401002;
    const ERROR_ACCOUNT_NONEXISTENT_MESSAGE = 'client.login.account_non_existent';

    const ERROR_ACCOUNT_WRONG_PASSWORD_CODE = 401003;
    const ERROR_ACCOUNT_WRONG_PASSWORD_MESSAGE = 'client.login.wrong_password';

    /**
     * @param Request       $request
     * @param User          $user
     * @param UserLoginData $login
     * @param WeChat        $weChat
     *
     * @return array
     *
     * @throws \Exception
     */
    protected function handleClientUserLogin(
        Request $request,
        $user,
        $login,
        $weChat = null
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

        // set weChat user client
        if (!is_null($weChat)) {
            $weChat->setUserClient($userClient);
        }

        if (!is_null($user)) {
            //            // force to set other token offline
//            $userTokenAll = $this->getRepo('User\UserToken')->findByUserId($user->getId());
//            foreach ($userTokenAll as $token) {
//                $token->setOnline(false);
//            }

            // save or refresh user token
            $userToken = $this->saveUserToken($em, $user, $userClient);

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
        $userToken = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserToken')
            ->findOneBy(array(
                'user' => $user,
                'client' => $userClient,
            ));

        if (is_null($userToken)) {
            $userToken = new UserToken();
            $userToken->setUser($user);
            $userToken->setUserId($user->getId());
            $userToken->setClient($userClient);
            $userToken->setClientId($userClient->getId());

            $em->persist($userToken);
        }

        // refresh data
        $userToken->setOnline(true);
        $userToken->setToken($this->generateRandomToken(self::PREFIX_ACCESS_TOKEN.$user->getId()));
        $userToken->setRefreshToken($this->generateRandomToken(self::PREFIX_FRESH_TOKEN.$user->getId()));
        $userToken->setModificationDate(new \DateTime('now'));

        return $userToken;
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
        $headers = array_change_key_case($_SERVER, CASE_LOWER);
        if (!array_key_exists($headerKey, $headers)) {
            return $this->getUser();
        }

        $auth = $this->getSandboxAuthorization($headerKey);
        if (is_null($auth)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        // find user by email or phone
        $username = $auth->getUsername();
        if (is_null($username)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $user = $this->getRepo('User\User')->findOneByEmail($username);
        } else {
            $usernameArray = explode('-', $username);
            if (count($usernameArray) != 2) {
                $error->setCode(self::ERROR_ACCOUNT_NONEXISTENT_CODE);
                $error->setMessage(self::ERROR_ACCOUNT_NONEXISTENT_MESSAGE);

                return;
            }

            $phoneCode = $usernameArray[0];
            $phone = $usernameArray[1];

            $user = $this->getRepo('User\User')->findOneBy(array(
                'phoneCode' => $phoneCode,
                'phone' => $phone,
            ));
        }

        if (is_null($user)) {
            $error->setCode(self::ERROR_ACCOUNT_NONEXISTENT_CODE);
            $error->setMessage(self::ERROR_ACCOUNT_NONEXISTENT_MESSAGE);

            return;
        }

        if ($auth->getPassword() != $user->getPassword()) {
            $error->setCode(self::ERROR_ACCOUNT_WRONG_PASSWORD_CODE);
            $error->setMessage(self::ERROR_ACCOUNT_WRONG_PASSWORD_MESSAGE);

            return;
        }

        return $user;
    }

    /**
     * Check authorization.
     */
    protected function checkAuthorization()
    {
        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        $authKey = $globals['sandbox_auth_key'];

        $headerKey = self::SANDBOX_CLIENT_LOGIN_HEADER;

        $headers = array_change_key_case($_SERVER, CASE_LOWER);

        if (!array_key_exists($headerKey, $headers)) {
            throw new UnauthorizedHttpException(self::UNAUTHED_API_CALL);
        }

        $auth = $headers[$headerKey];

        if ($auth != md5($authKey)) {
            throw new UnauthorizedHttpException(self::UNAUTHED_API_CALL);
        }

        return;
    }
}
