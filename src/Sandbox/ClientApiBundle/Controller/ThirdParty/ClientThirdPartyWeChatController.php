<?php

namespace Sandbox\ClientApiBundle\Controller\ThirdParty;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sandbox\ApiBundle\Entity\ThirdParty\WeChat;
use Sandbox\ApiBundle\Traits\WeChatApi;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

/**
 * Client Third Party WeChat controller.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang <yimo.zhang@Sandbox.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientThirdPartyWeChatController extends ClientThirdPartyController
{
    use WeChatApi;

    /**
     * Retrieve WeChat user info.
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
     * @Route("/sns/userinfo")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getWeChatSnsUserInfoAction(
        Request $request
    ) {
        $userId = $this->getUserId();
        $user = $this->getRepo('User\User')->find($userId);

        // get WeChat bind to me
        $weChat = $this->getRepo('ThirdParty\WeChat')->findOneByUser($user);
        if (is_null($weChat)) {
            $this->throwNotFoundIfNull($weChat, self::NOT_FOUND_MESSAGE);
        }

        $result = $this->getWeChatSnsUserInfo($weChat);

        return new View($result);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/info")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function deleteWeChatInfoAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();

        // get WeChat bind to me
        $weChat = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:ThirdParty\WeChat')
            ->findBy(array(
                'userId' => $userId,
            ));

        $em = $this->getDoctrine()->getManager();
        foreach ($weChat as $item) {
            $em->remove($item);
        }

        $em->flush();

        return new View();
    }
}
