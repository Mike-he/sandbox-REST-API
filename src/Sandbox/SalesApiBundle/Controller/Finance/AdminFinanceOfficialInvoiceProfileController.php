<?php

namespace Sandbox\SalesApiBundle\Controller\Finance;

use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;

/**
 * Admin Finance Official Invoice Profile Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leo.xu@sandbox3.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @see     http://www.Sandbox.cn/
 */
class AdminFinanceOfficialInvoiceProfileController extends PaymentController
{
    /**
     * @param Request $request
     *
     * @Method({"GET"})
     * @Route("/finance/official/invoice/profile")
     *
     * @return View
     */
    public function getOfficialInvoiceProfileAction(
        Request $request
    ) {
        $profile = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Finance\FinanceOfficialInvoiceProfile')
            ->findOneBy(['current' => true]);

        return new View($profile);
    }
}
