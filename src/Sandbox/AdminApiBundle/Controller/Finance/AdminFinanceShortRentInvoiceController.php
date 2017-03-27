<?php

namespace Sandbox\AdminApiBundle\Controller\Finance;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;

/**
 * Admin Finance Short Rent Invoice Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leo.xu@sandbox3.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminFinanceShortRentInvoiceController extends PaymentController
{
    /**
     * @param Request $request
     *
     * @Annotations\QueryParam(
     *    name="id",
     *    array=true,
     *    default="",
     *    nullable=false,
     *    strict=true,
     *    description="array of id"
     * )
     *
     * @Method({"GET"})
     * @Route("/finance/short/rent/invoices/ids")
     *
     * @return View
     */
    public function getShortRentInvoicesByIdsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $this->checkAdminInvoicePermission($this->getAdminId(), AdminPermission::OP_LEVEL_VIEW);

        $ids = $paramFetcher->get('id');

        $invoices = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceShortRentInvoice')
            ->getShortRentInvoicesByIds($ids);

        return new View($invoices);
    }

    /**
     * @param $adminId
     * @param $level
     */
    private function checkAdminInvoicePermission(
        $adminId,
        $level
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $adminId,
            array(
                array(
                    'key' => AdminPermission::KEY_OFFICIAL_PLATFORM_INVOICE,
                ),
            ),
            $level
        );
    }
}
