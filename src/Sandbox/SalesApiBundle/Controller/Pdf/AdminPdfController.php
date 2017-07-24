<?php

namespace Sandbox\SalesApiBundle\Controller\Pdf;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations;

class AdminPdfController extends SalesRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param int                   $id
     *
     * @Annotations\QueryParam(
     *    name="company",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    strict=true,
     *    description="company id"
     * )
     *
     * @Route("/pdf/leases/{id}")
     * @Method({"GET"})
     *
     * @return Response
     */
    public function exportLeaseToPdfAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        //authenticate with web browser cookie
        $admin = $this->authenticateAdminCookie();
        $adminId = $admin->getId();
        $companyId = $paramFetcher->get('company');

        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $adminId,
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_BUILDING_LONG_TERM_LEASE,
                ),
            ),
            AdminPermission::OP_LEVEL_VIEW,
            AdminPermission::PERMISSION_PLATFORM_SALES,
            $companyId
        );

        $lease = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')->find($id);

        $this->throwNotFoundIfNull($lease, self::NOT_FOUND_MESSAGE);

        $bills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->findBy(array(
                'lease' => $lease,
                'type' => LeaseBill::TYPE_LEASE,
            ));

        $customer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomer')
            ->find($lease->getLesseeCustomer());

        $enterprise = null;
        if ($lease->getLesseeEnterprise()) {
            $enterprise = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\EnterpriseCustomer')
                ->find($lease->getLesseeEnterprise());
        }

        $building = null;
        if ($lease->getBuildingId()) {
            $building = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                ->find($lease->getBuildingId());
        }

        $product = null;
        if ($lease->getProductId()) {
            $product = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\Product')
                ->find($lease->getProductId());
        }

        $html = $this->renderView(':Leases:leases_print.html.twig', array(
            'lease' => $lease,
            'building' => $building,
            'product' => $product,
            'customer' => $customer,
            'enterprise' => $enterprise,
            'bills' => $bills,
        ));

        $fileName = $lease->getSerialNumber().'.pdf';

        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            200,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "attachment; filename='$fileName'",
            )
        );
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param int                   $id
     *
     * @Annotations\QueryParam(
     *    name="company",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    strict=true,
     *    description="company id"
     * )
     *
     * @Route("/pdf/lease/clues/{id}")
     * @Method({"GET"})
     *
     * @return Response
     */
    public function exportLeaseClueToPdfAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        //authenticate with web browser cookie
        $admin = $this->authenticateAdminCookie();
        $adminId = $admin->getId();
        $companyId = $paramFetcher->get('company');

        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $adminId,
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_PLATFORM_LEASE_CLUE,
                ),
            ),
            AdminPermission::OP_LEVEL_VIEW,
            AdminPermission::PERMISSION_PLATFORM_SALES,
            $companyId
        );

        $clue = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseClue')->find($id);

        $this->throwNotFoundIfNull($clue, self::NOT_FOUND_MESSAGE);

        $customer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomer')
            ->find($clue->getLesseeCustomer());

        $building = null;
        if ($clue->getBuildingId()) {
            $building = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                ->find($clue->getBuildingId());
        }

        $product = null;
        if ($clue->getProductId()) {
            $product = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\Product')
                ->find($clue->getProductId());
        }

        $html = $this->renderView(':Leases:leases_clue_print.html.twig', array(
            'clue' => $clue,
            'building' => $building,
            'product' => $product,
            'customer' => $customer,
        ));

        $fileName = $clue->getSerialNumber().'.pdf';

        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            200,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "attachment; filename='$fileName'",
            )
        );
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param int                   $id
     *
     * @Annotations\QueryParam(
     *    name="company",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    strict=true,
     *    description="company id"
     * )
     *
     * @Route("/pdf/lease/offers/{id}")
     * @Method({"GET"})
     *
     * @return Response
     */
    public function exportLeaseOfferToPdfAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        //authenticate with web browser cookie
        $admin = $this->authenticateAdminCookie();
        $adminId = $admin->getId();
        $companyId = $paramFetcher->get('company');

        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $adminId,
            array(
                array(
                    'key' => AdminPermission::KEY_SALES_PLATFORM_LEASE_OFFER,
                ),
            ),
            AdminPermission::OP_LEVEL_VIEW,
            AdminPermission::PERMISSION_PLATFORM_SALES,
            $companyId
        );

        $offer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseOffer')->find($id);

        $this->throwNotFoundIfNull($offer, self::NOT_FOUND_MESSAGE);

        $customer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserCustomer')
            ->find($offer->getLesseeCustomer());

        $enterprise = null;
        if ($offer->getLesseeEnterprise()) {
            $enterprise = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:User\EnterpriseCustomer')
                ->find($offer->getLesseeEnterprise());
        }

        $building = null;
        if ($offer->getBuildingId()) {
            $building = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                ->find($offer->getBuildingId());
        }

        $product = null;
        if ($offer->getProductId()) {
            $product = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\Product')
                ->find($offer->getProductId());
        }

        $html = $this->renderView(':Leases:leases_offer_print.html.twig', array(
            'offer' => $offer,
            'building' => $building,
            'product' => $product,
            'customer' => $customer,
            'enterprise' => $enterprise,
        ));

        $fileName = $offer->getSerialNumber().'.pdf';

        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            200,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "attachment; filename='$fileName'",
            )
        );
    }
}
