<?php

namespace Sandbox\ClientApiBundle\Controller\ThirdParty;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sandbox\ApiBundle\Entity\ThirdParty\WeChat;
use Sandbox\ApiBundle\Traits\WeChatApi;
use Sandbox\ClientApiBundle\Data\ThirdParty\ThirdPartyOAuthLoginData;
use Sandbox\ClientApiBundle\Form\ThirdParty\ThirdPartyOAuthLoginType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sandbox\ClientApiBundle\Data\ThirdParty\ThirdPartyOAuthWeChatData;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use JMS\Serializer\SerializationContext;
use FOS\RestBundle\Controller\Annotations;

/**
 * Client Third Party OAuth controller.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang <yimo.zhang@Sandbox.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientThirdPartyOAuthController extends ClientThirdPartyController
{
    use WeChatApi;

    /**
     * Third party OAuth login.
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
     * @Annotations\QueryParam(
     *     name="platform",
     *     array=false,
     *     nullable=true,
     *     default="official",
     *     strict=true
     * )
     *
     * @Route("/login")
     * @Method({"POST"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function postThirdPartyOAuthLoginAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $login = new ThirdPartyOAuthLoginData();

        $form = $this->createForm(new ThirdPartyOAuthLoginType(), $login);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        // get third party resources
        $weChatData = $login->getWeChat();
        $platform = $paramFetcher->get('platform');

        // for third party oauth login,
        // currently, we are supporting WeChat
        $weChat = null;

        $user = null;
        if ($this->isAuthProvided()) {
            $userId = $this->getUserId();
            $user = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\User')
                ->find($userId);
        }

        if (!is_null($weChatData)) {
            // do oauth with WeChat API with code
            $weChat = $this->authenticateWithWeChat($weChatData, $user, $platform);

            if (is_null($weChat)) {
                throw new UnauthorizedHttpException(self::UNAUTHED_API_CALL);
            }

            $user = $weChat->getUser();
        }

        $responseArray = [];
        if (!$this->isAuthProvided()) {
            $responseArray = $this->handleClientUserLogin($request, $user, $login, $weChat);
        }

        // set weChat user data
        $responseArray = array_merge($responseArray, array(
            'we_chat_sns_user_info' => $this->getWeChatSnsUserInfo($weChat),
        ));

        // response
        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(array('login')));

        return $view->setData($responseArray);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     */
    public function createMyWeChatBindAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
    }

    /**
     * @param ThirdPartyOAuthWeChatData $weChatData
     *
     * @return WeChat
     */
    private function authenticateWithWeChat(
        $weChatData,
        $user,
        $platform
    ) {
        $code = $weChatData->getCode();
        $from = $weChatData->isFrom();
        if (is_null($code) || empty($code)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        // set default from type
        if (is_null($from)) {
            $from = ThirdPartyOAuthWeChatData::DATA_FROM_APPLICATION;
        }

        // call WeChat API to get access token
        $result = $this->getWeChatAuthInfoByCode($code, $from, $platform);

        if (!isset($result['unionid'])) {
            return null;
        }

        // get WeChat by openId
        $unionId = $result['unionid'];
        $openid = $result['openid'];
        $weChat = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:ThirdParty\WeChat')
            ->findOneBy(array(
                'unionid' => $unionId
            ));

        $em = $this->getDoctrine()->getManager();
        $now = new \DateTime();

        // update existing WeChat
        if (is_null($weChat)) {
            $weChat = new WeChat();
            $weChat->setOpenId($openid);
            $weChat->setCreationDate($now);

            $em->persist($weChat);
        }

        $weChat->setAccessToken($result['access_token']);
        $weChat->setRefreshToken($result['refresh_token']);
        $weChat->setExpiresIn($result['expires_in']);
        $weChat->setScope($result['scope']);
        $weChat->setAuthCode($code);
        $weChat->setLoginFrom($from);
        $weChat->setModificationDate($now);

        if (array_key_exists('unionid', $result)) {
            $unionId = $result['unionid'];
            $weChat->setUnionId($unionId);

            // bind wechat login with current account
            if ($user) {
                $weChat->setUser($user);
            } else {
                $currentAccount = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:ThirdParty\WeChat')
                    ->findOneBy(array(
                        'unionid' => $unionId
                    ));

                if (!is_null($currentAccount)) {
                    $weChat->setUser($currentAccount->getUser());
                }
            }
        }

        $em->flush();

        return $weChat;
    }
}
