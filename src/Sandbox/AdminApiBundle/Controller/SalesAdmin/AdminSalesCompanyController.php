<?php

namespace Sandbox\AdminApiBundle\Controller\SalesAdmin;

use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Controller\Location\LocationController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;

/**
 * Class AdminCompanyController.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leo.xu@sandbox3.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminSalesCompanyController extends LocationController
{
    /**
     * @param Request $request
     *
     * @Route("/companies")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getSalesCompaniesAction(
        Request $request
    ) {
        $companies = $this->getRepo('SalesAdmin\SalesCompany')->getSalesCompanies();

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['dropdown']));
        $view->setData($companies);

        return $view;
    }
}
