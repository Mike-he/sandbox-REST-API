<?php

namespace Sandbox\ApiBundle\Controller\Duiba;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Duiba\DuibaOrder;
use Sandbox\ApiBundle\Entity\User\UserBeanFlow;
use Sandbox\ApiBundle\Traits\DuibaApi;
use Sandbox\ApiBundle\Traits\GenerateSerialNumberTrait;
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

        $duibaOrder = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Duiba\DuibaOrder')
            ->findOneBy(array('duibaOrderNum' => $creditConsume['orderNum']));

        if ($duibaOrder) {
            $result = array(
                'status' => 'fail',
                'errorMessage' => '已兑换成功',
                'credits' => $bean,
            );

            return new View($result);
        }

        $now = new \DateTime('now');
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

        $totalUser = $em->getRepository('SandboxApiBundle:User\User')
            ->countTotalUsers();

        $totalBean = $totalUser['bean'];

        $beanFlow = new UserBeanFlow();
        $beanFlow->setUserId($_GET['uid']);
        $beanFlow->setType(UserBeanFlow::TYPE_CONSUME);
        $beanFlow->setChangeAmount('-'.$creditConsume['credits']);
        $beanFlow->setBalance($newBean);
        $beanFlow->setSource(UserBeanFlow::SOURCE_EXCHANGE);
        $beanFlow->setTradeId($creditConsume['orderNum']);
        $beanFlow->setCreationDate($now);
        $beanFlow->setTotal($totalBean - $creditConsume['credits']);
        $em->persist($beanFlow);

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

        $em = $this->getDoctrine()->getManager();

        if ($creditNotify['success'] == 'true') {
            if ($duibaOrder->getCreditsStatus() == 0) {
                $duibaOrder->setCreditsStatus(1);
            } else {
                return new View('error');
            }
        } elseif ($creditNotify['success'] == 'false') {
            if ($duibaOrder->getCreditsStatus() == 0) {
                $duibaOrder->setCreditsStatus(2);

                $userId = $duibaOrder->getUserId();
                $user = $this->getDoctrine()->getRepository('SandboxApiBundle:User\User')->find($userId);

                $newBean = $user->getBean() + $duibaOrder->getCredits();
                $user->setBean($newBean);

                $totalUser = $em->getRepository('SandboxApiBundle:User\User')
                    ->countTotalUsers();

                $totalBean = $totalUser['bean'];

                $beanFlow = new UserBeanFlow();
                $beanFlow->setUserId($userId);
                $beanFlow->setType(UserBeanFlow::TYPE_ADD);
                $beanFlow->setChangeAmount('+'.$duibaOrder->getCredits());
                $beanFlow->setBalance($newBean);
                $beanFlow->setSource(UserBeanFlow::SOURCE_EXCHANGE_FAIL);
                $beanFlow->setTradeId($duibaOrder->getDuibaOrderNum());
                $beanFlow->setCreationDate(new \DateTime('now'));
                $beanFlow->setTotal($totalBean + $duibaOrder->getCredits());
                $em->persist($beanFlow);
            } else {
                return new View('error');
            }
        }

        $em->flush();

        return new View('ok');
    }
}
