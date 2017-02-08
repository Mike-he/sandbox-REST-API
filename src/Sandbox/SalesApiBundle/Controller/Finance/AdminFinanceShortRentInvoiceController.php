<?php

namespace Sandbox\SalesApiBundle\Controller\Finance;

use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;

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
     * @Method({"POST"})
     * @Route("/finance/short/rent/invoices")
     *
     * @return View
     */
    public function getOfficialInvoiceProfileAction(
        Request $request
    ) {
        $this->checkAdminSalesInvoicePermission($this->getAdminId(), AdminPermission::OP_LEVEL_EDIT);

        $profile = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceOfficialInvoiceProfile')
            ->find(1);

        return new View($profile);
    }

    /**
     * @param $adminId
     * @param $level
     */
    private function checkAdminSalesInvoicePermission(
        $adminId,
        $level
    ) {
        $this->throwAccessDeniedIfAdminNotAllowed(
            $adminId,
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_PLATFORM_INVOICE,
                ),
            ),
            $level
        );
    }
}
