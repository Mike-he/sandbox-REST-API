<?php

namespace Sandbox\AdminApiBundle\Controller\User;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sandbox\AdminApiBundle\Controller\AdminRestController;
use Sandbox\ApiBundle\Traits\YunPianSms;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class AdminUserSMSController extends AdminRestController
{
    use YunPianSms;

    const YUNPIAN_BATCH_SEND_URL = 'https://sms.yunpian.com/v1/sms/send.json';
    const API_KEY = '21c29536f85636a862306aa6f8ffdf74';

    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/sms")
     * @Method({"POST"})
     *
     * @return View
     */
    public function sendUserSMSAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        var_dump(2);exit;
        $userCounts = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->countTotalUsers();

        $counts = $userCounts['total'];

        $limit = 10;
        $pagesMax = $counts / $limit;
        $pagesMax = (int) $pagesMax;

        $response = [];
        for ($offset = 0; $offset <= $pagesMax; $offset++) {
            $phonesArray = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\User')
                ->getUsersByLimit(
                    $limit,
                    $offset
                );

            $phones = '';
            foreach ($phonesArray as $item) {
                $phones .= $item['phone'].',';
            }

            $phones = substr($phones, 0, -1);

            $re = $this->send_marketing_sms(
                $phones,
                '【创合秒租】创合邀您参与99公益活动，9月7-9日早上9点，您捐1元就能让腾讯捐2.99亿，一起来为公益“抢钱”吧！详细信息请见创合秒租app或微信公众号。t.cn/RpP24bG 回T退订'
            );

            array_push($response, $re);
        }

        return new View($response);
    }
}