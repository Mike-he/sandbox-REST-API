<?php

namespace Sandbox\ApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use JMS\Serializer\SerializationContext;

/**
 * Company Industry Controller.
 *
 * @category Sandbox
 *
 * @author   Albert Feng <albert.feng@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class CompanyIndustryController extends SandboxRestController
{
    /**
     * Get all industries.
     *
     * @param Request $request the request object
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @return View
     */
    public function getIndustriesAction(
        Request $request
    ) {
        $industries = $this->getRepo('Company\CompanyIndustry')->findAll();

        $view = new View($industries);
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(array('industries'))
        );

        return $view;
    }

    /**
     * Get a single industry.
     *
     * @param Request $request the request object
     * @param String  $id      the industry Id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @return View
     */
    public function getIndustryAction(
        Request $request,
        $id
    ) {
        $industry = $this->getRepo('Company\CompanyIndustry')->find($id);

        return new View($industry);
    }
}
