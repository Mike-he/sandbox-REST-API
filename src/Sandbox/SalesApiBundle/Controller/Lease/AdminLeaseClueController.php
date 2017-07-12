<?php

namespace Sandbox\SalesApiBundle\Controller\Lease;

use FOS\RestBundle\View\View;
use Knp\Component\Pager\Paginator;
use Sandbox\ApiBundle\Entity\Lease\LeaseClue;
use Sandbox\ApiBundle\Form\Lease\LeaseCluePostType;
use Sandbox\ApiBundle\Traits\GenerateSerialNumberTrait;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;

class AdminLeaseClueController extends SalesRestController
{
    use GenerateSerialNumberTrait;

    /**
     * Get Lease Clues.
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
     * @Route("/lease/clues")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getCluesListsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $clues = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseClue')
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
     * Get clue info.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("/lease/clues/{id}")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getClueByIdAction(
        Request $request,
        $id
    ) {
        $clue = $this->getDoctrine()->getRepository('SandboxApiBundle:Lease\LeaseClue')->find($id);
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
     * @Route("/lease/clues")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postLeaseCluesAction(
        Request $request
    ) {
        // check user permission

        $clue = new LeaseClue();
        $form = $this->createForm(new LeaseCluePostType(), $clue);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return $this->saveLeaseClue(
            $clue,
            'POST'
        );
    }

    /**
     * Update a lease clue.
     *
     * @param $request
     *
     * @Route("/lease/clues/{id}")
     * @Method({"PUT"})
     *
     * @return View
     */
    public function putLeaseCluesAction(
        Request $request,
        $id
    ) {
        $clue = $this->getDoctrine()->getRepository('SandboxApiBundle:Lease\LeaseClue')->find($id);
        $this->throwNotFoundIfNull($clue, self::NOT_FOUND_MESSAGE);

        $form = $this->createForm(
            new LeaseCluePostType(),
            $clue,
            array('method' => 'PUT')
        );
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return $this->saveLeaseClue(
            $clue,
            'PUT'
        );
    }

    /**
     * @param LeaseClue $clue
     * @param $method
     *
     * @return View
     */
    private function saveLeaseClue(
        $clue,
        $method
    ) {
        $em = $this->getDoctrine()->getManager();

        $customerId = $clue->getLesseeCustomer();
        if (is_null($customerId)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        } else {
            $customer = $em->getRepository('SandboxApiBundle:User\UserCustomer')->find($customerId);
            $this->throwNotFoundIfNull($customer, self::NOT_FOUND_MESSAGE);
        }

        $productId = $clue->getProductId();
        if ($productId) {
            $product = $em->getRepository('SandboxApiBundle:Product\Product')->find($productId);
            $this->throwNotFoundIfNull($product, self::NOT_FOUND_MESSAGE);
        }

        $productAppointmentId = $clue->getProductAppointmentId();
        if ($productAppointmentId) {
            $productAppointment = $em->getRepository('SandboxApiBundle:Product\ProductAppointment')->find($productAppointmentId);
            $this->throwNotFoundIfNull($productAppointment, self::NOT_FOUND_MESSAGE);
        }

        $startDate = $clue->getStartDate();
        if ($startDate) {
            $clue->setStartDate(new \DateTime($startDate));
        }

        $endDate = $clue->getEndDate();
        if ($endDate) {
            $clue->setEndDate(new \DateTime($endDate));
        }

        if ($method == 'POST') {
            $serialNumber = $this->generateSerialNumber(LeaseClue::LEASE_CLUE_LETTER_HEAD);
            $clue->setSerialNumber($serialNumber);
        }

        $em->persist($clue);
        $em->flush();

        if ($method == 'POST') {
            $response = array(
                'id' => $clue->getId(),
            );

            return new View($response, 201);
        }
    }
}
