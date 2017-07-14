<?php

namespace Sandbox\SalesApiBundle\Controller\Lease;

use FOS\RestBundle\View\View;
use Knp\Component\Pager\Paginator;
use Sandbox\ApiBundle\Entity\Lease\LeaseOffer;
use Sandbox\ApiBundle\Form\Lease\LeaseOfferType;
use Sandbox\ApiBundle\Traits\GenerateSerialNumberTrait;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;

class AdminLeaseOfferController extends SalesRestController
{
    use GenerateSerialNumberTrait;

    /**
     * Get Lease Offers.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many products to return"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageIndex",
     *    array=false,
     *    default="1",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="page number"
     * )
     *
     * @Route("/lease/offers")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function geOfferListsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $clues = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseOffer')
            ->findAll();

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $clues,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * Get offer info.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("/lease/offers/{id}")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getOfferByIdAction(
        Request $request,
        $id
    ) {
        $clue = $this->getDoctrine()->getRepository('SandboxApiBundle:Lease\LeaseOffer')->find($id);
        $this->throwNotFoundIfNull($clue, self::NOT_FOUND_MESSAGE);

        $view = new View();
        $view->setData($clue);

        return $view;
    }

    /**
     * Create a new lease clue.
     *
     * @param $request
     *
     * @Route("/lease/offers")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postLeaseOffersAction(
        Request $request
    ) {
        // check user permission

        $offer = new LeaseOffer();
        $form = $this->createForm(new LeaseOfferType(), $offer);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $leaseRentTypeIds = $form['rent_type_ids']->getData();

        return $this->saveLeaseOffer(
            $offer,
            $leaseRentTypeIds,
            'POST'
        );
    }

    /**
     * Update a lease clue.
     *
     * @param $request
     *
     * @Route("/lease/offers/{id}")
     * @Method({"PUT"})
     *
     * @return View
     */
    public function putLeaseOffersAction(
        Request $request,
        $id
    ) {
        $offer = $this->getDoctrine()->getRepository('SandboxApiBundle:Lease\LeaseOffer')->find($id);
        $this->throwNotFoundIfNull($offer, self::NOT_FOUND_MESSAGE);

        $form = $this->createForm(
            new LeaseOfferType(),
            $offer,
            array('method' => 'PUT')
        );
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $leaseRentTypeIds = $form['rent_type_ids']->getData();

        return $this->saveLeaseOffer(
            $offer,
            $leaseRentTypeIds,
            'PUT'
        );
    }

    /**
     * @param LeaseOffer $offer
     * @param $leaseRentTypeIds
     * @param $method
     *
     * @return View
     */
    private function saveLeaseOffer(
        $offer,
        $leaseRentTypeIds,
        $method
    ) {
        $em = $this->getDoctrine()->getManager();

        $customerId = $offer->getLesseeCustomer();
        if (is_null($customerId)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        } else {
            $customer = $em->getRepository('SandboxApiBundle:User\UserCustomer')->find($customerId);
            $this->throwNotFoundIfNull($customer, self::NOT_FOUND_MESSAGE);
        }

        if ($offer->getLesseeType() == LeaseOffer::LEASE_OFFER_LESSEE_TYPE_ENTERPRISE) {
            $enterpriseId = $offer->getLesseeEnterprise();
            if (is_null($enterpriseId)) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            } else {
                // todo: check salse enterprise
            }
        }

        $buildingId = $offer->getBuildingId();
        if ($buildingId) {
            $building = $em->getRepository('SandboxApiBundle:Room\RoomBuilding')->find($buildingId);
            $this->throwNotFoundIfNull($building, self::NOT_FOUND_MESSAGE);
        }

        $productId = $offer->getProductId();
        if ($productId) {
            $product = $em->getRepository('SandboxApiBundle:Product\Product')->find($productId);
            $this->throwNotFoundIfNull($product, self::NOT_FOUND_MESSAGE);
        }

        $leaseClueId = $offer->getLeaseClueId();
        if ($leaseClueId) {
            $leaseClue = $em->getRepository('SandboxApiBundle:Lease\LeaseClue')->find($leaseClueId);
            $this->throwNotFoundIfNull($leaseClue, self::NOT_FOUND_MESSAGE);
        }

        $startDate = $offer->getStartDate();
        if ($startDate) {
            $offer->setStartDate(new \DateTime($startDate));
        }

        $endDate = $offer->getEndDate();
        if ($endDate) {
            $endDate = new \DateTime($endDate);
            $endDate->setTime(23, 59, 59);
            $offer->setEndDate($endDate);
        }

        if ($method == 'POST') {
            $serialNumber = $this->generateSerialNumber(LeaseOffer::LEASE_OFFER_LETTER_HEAD);
            $offer->setSerialNumber($serialNumber);
        }

        $leaseRentTypes = $offer->getLeaseRentTypes();
        foreach ($leaseRentTypes as $leaseRentType) {
            $offer->removeLeaseRentTypes($leaseRentType);
        }

        foreach ($leaseRentTypeIds as $leaseRentTypeId) {
            $leaseRentType = $em->getRepository('SandboxApiBundle:Lease\LeaseRentTypes')->find($leaseRentTypeId);
            if ($leaseRentType) {
                $offer->addLeaseRentTypes($leaseRentType);
            }
        }

        $em->persist($offer);
        $em->flush();

        if ($method == 'POST') {
            $response = array(
                'id' => $offer->getId(),
            );

            return new View($response, 201);
        }
    }
}
