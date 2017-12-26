<?php

namespace Sandbox\ApiBundle\Controller\Log;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminStatusLog;
use Sandbox\ApiBundle\Entity\Log\Log;
use Sandbox\ApiBundle\Form\Log\LogType;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class LogController.
 */
class LogController extends SandboxRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/logs")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postLogAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $adminId = $this->getAdminId();

        $log = new Log();

        $form = $this->createForm(new LogType(), $log);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];
        $log->setPlatform($adminPlatform['platform']);
        $log->setAdminUsername($adminId);

        $salesCompany = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->find($salesCompanyId);
        $log->setSalesCompany($salesCompany);

        $em = $this->getDoctrine()->getManager();
        $em->persist($log);
        $em->flush();

        if ($log->getLogObjectKey() == 'invoice' &&
            $log->getLogAction() == 'create'
        ) {
            $this->get('sandbox_api.admin_status_log')
                ->autoLog(
                    $adminId,
                    'completed',
                    '确认开票',
                    $log->getLogObjectKey(),
                    $log->getLogObjectId(),
                    AdminStatusLog::TYPE_SALES_ADMIN,
                    $salesCompanyId
                );
        }

        return new View(array(
            'id' => $log->getId(),
        ));
    }
}
