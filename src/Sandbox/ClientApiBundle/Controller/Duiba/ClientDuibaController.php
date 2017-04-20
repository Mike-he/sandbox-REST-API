<?php

namespace Sandbox\ClientApiBundle\Controller\Duiba;

use Sandbox\ApiBundle\Controller\Duiba\DuibaController;
use Sandbox\ApiBundle\Traits\DuibaApi;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcherInterface;

/**
 * Bean Controller.
 *
 * @category Sandbox
 *
 * @author   Feng Li <feng.li@sandbox3.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientDuibaController extends DuibaController
{
    use DuibaApi;

    /**
     * Duiba Auto Login.
     *
     * @param Request $request
     * @Route("/duiba/login")
     * @Method({"GET"})
     *
     * @return View
     */
    public function duibaLoginAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $duibaAppKey = $this->getParameter('duiba_app_key');
        $duibaAppSecret = $this->getParameter('duiba_app_secret');

        $uid = $this->getUserId();

        $user = $this->getDoctrine()->getRepository('SandboxApiBundle:User\User')->find($uid);
        $credits = $user->getBean();

        $autoLogin = $this->buildCreditAutoLoginRequest(
            $duibaAppKey,
            $duibaAppSecret,
            $uid,
            $credits
        );

        $data = array('login_url' => $autoLogin);

        return new View($data);
    }
}
