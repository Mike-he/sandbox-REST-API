<?php

namespace Sandbox\CommnueAdminApiBundle\Controller\Dashboard;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class AdminDashboardController extends SandboxRestController
{
    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="startDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="startDate"
     * )
     *
     * @Annotations\QueryParam(
     *    name="endDate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="endDate"
     * )
     *
     * @Route("/dashboard/users_data")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getUserDashboardAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $startDate = $paramFetcher->get('startDate');
        $endDate = $paramFetcher->get('endDate');

        $repo = $this->getDoctrine()->getRepository('SandboxApiBundle:User\UserView');

        $month = null;
        if ($startDate && $endDate) {
            $month = $repo->countRegUsers($startDate.' 00:00:00', $endDate.' 23:59:59');
        }

        $result = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->countTotalUsers();

        $crmUrl = $this->container->getParameter('crm_api_url');
        $url = $crmUrl.'/commnue/admin/dashboard/users_auth?endDate='.$endDate.'&startDate='.$startDate;
        $ch = curl_init($url);

        $response = $this->callAPI($ch, 'GET');
        $response = json_decode($response, true);

        return new View([
            'total_users' => (int) $result['total'],
            'register_users' => $month,
            'auth_users' => $response['auth_users'],
        ]);
    }
}