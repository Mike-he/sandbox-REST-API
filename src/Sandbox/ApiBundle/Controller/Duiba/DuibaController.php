<?php

namespace Sandbox\ApiBundle\Controller\Duiba;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Duiba\DuibaOrder;
use Sandbox\ApiBundle\Traits\DuibaApi;
use Sandbox\ApiBundle\Traits\GenerateSerialNumberTrait;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;

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
class DuibaController extends SandboxRestController
{
    use DuibaApi;
    use GenerateSerialNumberTrait;

    /**
     * Get DuibaOrders.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/duiba")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getDuibaOrderAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $duibaAppKey = $this->getParameter('duiba_app_key');
        $duibaAppSecret = $this->getParameter('duiba_app_secret');

        $creditConsume = $this->parseCreditConsume(
            $duibaAppKey,
            $duibaAppSecret,
            $_GET
        );

        $user = $this->getDoctrine()->getRepository('SandboxApiBundle:User\User')->find($_GET['uid']);
        if (!$user) {
            $result = array(
                'status' => 'fail',
                'errorMessage' => '没找到用户',
                'credits' => 0,
            );

            return new View($result);
        }

        $bean = $user->getBean();
        $newBean = $bean - $creditConsume['credits'];
        if ($newBean < 0) {
            $result = array(
                'status' => 'fail',
                'errorMessage' => '赤豆不足',
                'credits' => $bean,
            );

            return new View($result);
        }

        $bizId = $this->generateSerialNumber($this->getParameter('duiba_app_env'));

        $em = $this->getDoctrine()->getManager();

        $duibaOrder = new DuibaOrder();
        $duibaOrder->setUserId($_GET['uid']);
        $duibaOrder->setCredits($creditConsume['credits']);
        $duibaOrder->setActualPrice($_GET['actualPrice']);
        $duibaOrder->setAppId(1);
        $duibaOrder->setType($_GET['type']);
        $duibaOrder->setDescription($creditConsume['description']);
        $duibaOrder->setDuibaOrderNum($creditConsume['orderNum']);
        $duibaOrder->setOrderStatus(1);
        $duibaOrder->setCreditsStatus(0);
        $duibaOrder->setBizId($bizId);

        $em->persist($duibaOrder);

        $user->setBean($newBean);

        $em->flush();

        $result = array(
            'status' => 'ok',
            'errorMessage' => '',
            'credits' => $newBean,
            'bizId' => $duibaOrder->getBizId(),
        );

        return new View($result);
    }

    /**
     * Get DuibaOrders.
     *
     * @param Request $request
     *
     * @Route("/duiba/notify")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getDuibaNotifyAction(
        Request $request
    ) {
        $duibaAppKey = $this->getParameter('duiba_app_key');
        $duibaAppSecret = $this->getParameter('duiba_app_secret');

        $creditNotify = $this->parseCreditNotify(
            $duibaAppKey,
            $duibaAppSecret,
            $_GET
        );

        $duibaOrder = $this->getDoctrine()->getRepository('SandboxApiBundle:Duiba\DuibaOrder')
            ->findOneBy(array('duibaOrderNum' => $_GET['orderNum']));

        if (!$duibaOrder) {
            return new view();
        }

        if ($creditNotify['success'] == true) {
            if ($duibaOrder->getCreditsStatus() == 0) {
                $duibaOrder->setOrderStatus(1);
            }
        } else {
            if ($duibaOrder->getCreditsStatus() == 1) {
                $duibaOrder->setCreditsStatus(2);

                $userId = $duibaOrder->getUserId();
                $user = $this->getDoctrine()->getRepository('SandboxApiBundle:User\User')->find($userId);

                $user->setBean($user->getBean() + $duibaOrder->getCredits());
            }
        }

        $em = $this->getDoctrine()->getManager();

        $em->flush();

        return new View('ok');
    }

    /**
     * Get DuibaOrders.
     *
     * @param Request $request
     *
     * @Annotations\QueryParam(
     *    name="uid",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="uid"
     * )
     *
     *
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
