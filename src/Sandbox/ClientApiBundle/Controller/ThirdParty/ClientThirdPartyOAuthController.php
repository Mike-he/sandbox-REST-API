<?php

namespace Sandbox\ClientApiBundle\Controller\ThirdParty;

use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sandbox\ClientApiBundle\Data\ThirdParty\ThirdPartyOAuthLoginData;
use Sandbox\ClientApiBundle\Form\ThirdParty\ThirdPartyOAuthLoginType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sandbox\ClientApiBundle\Data\ThirdParty\ThirdPartyOAuthWeChatData;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

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
    public function postOAuthLoginAction(
        Request $request
    ) {
        $login = new ThirdPartyOAuthLoginData();

        $form = $this->createForm(new ThirdPartyOAuthLoginType(), $login);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        // get third party resources
        $weChat = $login->getWeChat();

        // for third party oauth login,
        // currently, we are supporting WeChat
        if (!$this->isResourceAuthenticated($weChat)) {
            throw new UnauthorizedHttpException(self::UNAUTHED_API_CALL);
        }

        // TODO $this->handleClientUserLogin ...

        return new View();
    }

    /**
     * @param ThirdPartyOAuthWeChatData $weChat
     *
     * @return bool
     */
    private function isResourceAuthenticated(
        $weChat
    ) {
        if (!is_null($weChat)) {
            return $this->doOAuthWithWeChat($weChat);
        }

        return false;
    }

    /**
     * @param ThirdPartyOAuthWeChatData $weChat
     *
     * @return bool
     */
    private function doOAuthWithWeChat(
        $weChat
    ) {
        $code = $weChat->getCode();
        if (is_null($code) || empty($code)) {
            return false;
        }

        // TODO do oauth with wechat api

        // TODO save wechat auth info from response

        return true;
    }
}
