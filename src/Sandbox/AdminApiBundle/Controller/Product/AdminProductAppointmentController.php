<?php

namespace Sandbox\AdminApiBundle\Controller\Product;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Knp\Component\Pager\Paginator;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

class AdminProductAppointmentController extends SandboxRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param int                   $id
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many products to return "
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageIndex",
     *    array=false,
     *    default="1",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="page number "
     * )
     *
     * @Route("/products/appointments/list")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getProductAppointmentsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $this->checkAdminProductAppointmentPermission(AdminPermission::OP_LEVEL_VIEW);

        // filters
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $appointments = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductAppointment')
            ->findBy(array(
                'productId' => $id,
            ));

        // set extra
        foreach ($appointments as $appointment) {
            $user = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\UserView')
                ->findOneBy(array(
                    'id' => $appointment->getUserId(),
                ));

            if (is_null($user)) {
                continue;
            }

            $appointment->setUser($user);
        }

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $appointments,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    private function checkAdminProductAppointmentPermission(
        $opLevel
    ) {
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_LONG_TERM_APPOINTMENT],
            ],
            $opLevel
        );
    }
}
