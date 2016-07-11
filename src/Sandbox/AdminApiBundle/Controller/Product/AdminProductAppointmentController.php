<?php

namespace Sandbox\AdminApiBundle\Controller\Product;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Knp\Component\Pager\Paginator;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;
use Sandbox\ApiBundle\Entity\Admin\AdminType;
use Sandbox\ApiBundle\Form\Product\ProductAppointmentPatchType;
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
     * @Route("/products/{id}/appointments")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getProductAppointmentsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $this->checkAdminProductAppointmentPermission(AdminPermissionMap::OP_LEVEL_VIEW);

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

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param int                   $id
     *
     * @Route("/product/appointments/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     */
    public function patchProductAppointmentAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        // check user permission
        $this->checkAdminProductAppointmentPermission(AdminPermissionMap::OP_LEVEL_EDIT);

        // get appointment
        $appointment = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductAppointment')
            ->find($id);
        $this->throwNotFoundIfNull($appointment, self::NOT_FOUND_MESSAGE);

        // bind data
        $appointmentJson = $this->get('serializer')->serialize($appointment, 'json');
        $patch = new Patch($appointmentJson, $request->getContent());
        $appointmentJson = $patch->apply();

        $form = $this->createForm(new ProductAppointmentPatchType(), $appointment);
        $form->submit(json_decode($appointmentJson, true));

        $appointment->setModificationDate(new \DateTime('now'));

        // save to db
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    private function checkAdminProductAppointmentPermission(
        $opLevel
    ) {
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_PRODUCT_APPOINTMENT_VERIFY,
            $opLevel
        );
    }
}
