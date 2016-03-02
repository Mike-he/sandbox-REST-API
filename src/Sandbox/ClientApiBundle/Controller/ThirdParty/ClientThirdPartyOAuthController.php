<?php

namespace Sandbox\ClientApiBundle\Controller\ThirdParty;

use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sandbox\ApiBundle\Entity\ThirdParty\WeChat;
use Sandbox\ClientApiBundle\Data\ThirdParty\ThirdPartyOAuthLoginData;
use Sandbox\ClientApiBundle\Form\ThirdParty\ThirdPartyOAuthLoginType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sandbox\ClientApiBundle\Data\ThirdParty\ThirdPartyOAuthWeChatData;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use JMS\Serializer\SerializationContext;

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
     * @Route("/login")
     * @Method({"POST"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function postThirdPartyOAuthLoginAction(
        Request $request
    ) {
        $login = new ThirdPartyOAuthLoginData();

        $form = $this->createForm(new ThirdPartyOAuthLoginType(), $login);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        // get third party resources
        $weChatData = $login->getWeChat();

        // for third party oauth login,
        // currently, we are supporting WeChat
        $weChat = null;
        $user = null;

        if (!is_null($weChatData)) {
            $weChat = $this->authenticateWithWeChat($weChatData);

            if (is_null($weChat)) {
                throw new UnauthorizedHttpException(self::UNAUTHED_API_CALL);
            }

            $user = $weChat->getUser();
        }

        $responseArray = $this->handleClientUserLogin($request, $user, $login, $weChat);

        // response
        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(array('login')));

        return $view->setData($responseArray);
    }

    /**
     * @param ThirdPartyOAuthWeChatData $weChatData
     *
     * @return WeChat
     */
    private function authenticateWithWeChat(
        $weChatData
    ) {
        $code = $weChatData->getCode();
        if (is_null($code) || empty($code)) {
            throw new UnauthorizedHttpException(self::UNAUTHED_API_CALL);
        }

        // TODO do oauth with wechat api with code
        $openId = 'test1';
        $accessToken = 'accessToken1';
        $refreshToken = 'refreshToken1';
        $expiresIn = 'expiresIn1';
        $scope = 'scope1';
        $unionId = 'unionId1';

        // save WeChat auth info from response
        $weChat = $this->getRepo('ThirdParty\WeChat')->findOneByOpenid($openId);

        $em = $this->getDoctrine()->getManager();
        $now = new \DateTime();

        if (is_null($weChat)) {
            $weChat = new WeChat();
            $weChat->setOpenId($openId);
            $weChat->setCreationDate($now);

            $em->persist($weChat);
        }

        $weChat->setAccessToken($accessToken);
        $weChat->setRefreshToken($refreshToken);
        $weChat->setExpiresIn($expiresIn);
        $weChat->setScope($scope);
        $weChat->setUnionId($unionId);
        $weChat->setModificationDate($now);

        $em->flush();

        return $weChat;
    }
}
