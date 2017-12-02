<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\User\UserLoginController;
use Sandbox\ApiBundle\Entity\Auth\Auth;
use Sandbox\ApiBundle\Entity\Error\Error;
use Sandbox\ApiBundle\Entity\ThirdParty\WeChat;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Form\User\UserCheckType;
use Sandbox\ClientApiBundle\Data\User\UserLoginData;
use Sandbox\ClientApiBundle\Form\User\UserLoginType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Sandbox\ApiBundle\Traits\WeChatApi;

/**
 * Login controller.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang <yimo.zhang@Sandbox.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @see     http://www.Sandbox.cn/
 */
class ClientUserLoginController extends UserLoginController
{
    use WeChatApi;

    const PREFIX_BASIC_AUTHORIZATION = 'Basic';
    const FRESH_TOKEN_EXPIRE_IN_TIME = '6 month';

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
     * @return View
     *
     * @throws \Exception
     */
    public function postClientUserLoginAction(
        Request $request
    ) {
        // check security & get client
        $error = new Error();
        $user = $this->getUserIfAuthenticated($error);

        if (is_null($user)) {
            return $this->customErrorView(
                401,
                $error->getCode(),
                $error->getMessage()
            );
        }

        // user is banned
        if ($user->isBanned()) {
            // get globals
            $globals = $this->getGlobals();

            $customerPhone = $globals['customer_service_phone'];
            $translated = $this->get('translator')->trans(self::ERROR_ACCOUNT_BANNED_MESSAGE);
            $bannedMessage = $translated.$customerPhone;

            return $this->customErrorView(
                401,
                self::ERROR_ACCOUNT_BANNED_CODE,
                $bannedMessage
            );
        }

        $login = new UserLoginData();

        $payload = json_decode($request->getContent(), true);

        if (!is_null($payload)) {
            $form = $this->createForm(new UserLoginType(), $login);
            $form->handleRequest($request);

            if (!$form->isValid()) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }

            $weChatData = $form['wechat']->getData();

            if (!is_null($weChatData)) {
                $weChat = $this->getRepo('ThirdParty\WeChat')->findOneByAuthCode($weChatData->getCode());
                if (is_null($weChat)) {
                    $this->throwNotFoundIfNull($weChat, self::NOT_FOUND_MESSAGE);
                }

                // do oauth with WeChat api with openId and accessToken
                $this->throwUnauthorizedIfWeChatAuthFail($weChat);

                $weChat->setUser($user);
            }
        }

        $responseArray = $this->handleClientUserLogin($request, $user, $login);

        // response
        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(array('login')));

        return $view->setData($responseArray);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/refresh")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postRefreshTokenAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $em = $this->getDoctrine()->getManager();

        $headerKey = self::HTTP_HEADER_AUTH;

        $auth = $this->getSandboxRefreshTokenAuthorization(
            $headerKey
        );

        $token = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserToken')
            ->findOneBy(array(
                'refreshToken' => $auth->getUsername(),
                'clientId' => $auth->getPassword(),
            ));

        if (is_null($token)) {
            throw new UnauthorizedHttpException(self::UNAUTHED_API_CALL);
        }

        // user is banned
        $user = $token->getUser();
        if ($user->isBanned()) {
            // get globals
            $globals = $this->getGlobals();

            $customerPhone = $globals['customer_service_phone'];
            $translated = $this->get('translator')->trans(self::ERROR_ACCOUNT_BANNED_MESSAGE);
            $bannedMessage = $translated.$customerPhone;

            return $this->customErrorView(
                401,
                self::ERROR_ACCOUNT_BANNED_CODE,
                $bannedMessage
            );
        }

        // check refresh token expire in
        $now = new \DateTime('now');
        $refreshTokenExpireIn = $token->getModificationDate()->modify('+ '.self::FRESH_TOKEN_EXPIRE_IN_TIME);
        if ($refreshTokenExpireIn < $now) {
            throw new UnauthorizedHttpException(self::UNAUTHED_API_CALL);
        }

        // refresh data
        $token->setOnline(true);
        $token->setToken($this->generateRandomToken(self::PREFIX_ACCESS_TOKEN.$user->getId()));
        $token->setRefreshToken($this->generateRandomToken(self::PREFIX_FRESH_TOKEN.$user->getId()));
        $token->setModificationDate(new \DateTime('now'));

        $em->flush();

        // response
        $view = new View(array(
            'client' => $token->getClient(),
            'user' => $token->getUser(),
            'token' => $token,
        ));
        $view->setSerializationContext(SerializationContext::create()->setGroups(array('login')));

        return $view;
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/user_check")
     * @Method({"POST"})
     *
     * @return View
     */
    public function checkUserValidationAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check authorization
        $this->checkAuthorization();

        $user = new User();

        $form = $this->createForm(new UserCheckType(), $user);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $phoneCode = $user->getPhoneCode();
        $phone = $user->getPhone();
        $email = $user->getEmail();

        if (!is_null($phoneCode) && !is_null($phone)) {
            $filters = array(
                'phone' => $phone,
                'phoneCode' => $phoneCode,
            );
        } elseif (!is_null($email)) {
            $filters = array(
                'email' => $email,
            );
        } else {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $sandboxUser = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->findOneBy($filters);

        if (is_null($sandboxUser)) {
            return new View(array(
                'user_exist' => false,
            ));
        }

        return new View(array(
            'user_exist' => true,
        ));
    }

    /**
     * @param $headerKey
     *
     * @return Auth
     */
    private function getSandboxRefreshTokenAuthorization(
        $headerKey
    ) {
        // get auth
        $headers = array_change_key_case($_SERVER, CASE_LOWER);
        $headerKey = 'http_'.$headerKey;
        if (!array_key_exists($headerKey, $headers)) {
            throw new UnauthorizedHttpException(self::UNAUTHED_API_CALL);
        }

        // get auth part of headers
        $authorization = $headers[$headerKey];
        $auth = trim(substr(strstr(
                $authorization,
                self::PREFIX_BASIC_AUTHORIZATION),
                strlen(self::PREFIX_BASIC_AUTHORIZATION))
        );

        $authString = base64_decode($auth, true);
        $authArray = explode(':', $authString);

        if (2 != count($authArray)) {
            throw new UnauthorizedHttpException(self::UNAUTHED_API_CALL);
        }

        $auth = new Auth();
        $auth->setUsername($authArray[0]);
        $auth->setPassword($authArray[1]);

        return $auth;
    }
}
