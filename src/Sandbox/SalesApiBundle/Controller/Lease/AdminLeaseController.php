<?php

namespace Sandbox\SalesApiBundle\Controller\Lease;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Constants\ProductOrderMessage;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Entity\Log\Log;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\Product\ProductAppointment;
use Sandbox\ApiBundle\Traits\GenerateSerialNumberTrait;
use Sandbox\ApiBundle\Traits\HasAccessToEntityRepositoryTrait;
use Sandbox\ApiBundle\Traits\LeaseNotificationTrait;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use FOS\RestBundle\Controller\Annotations;

class AdminLeaseController extends SalesRestController
{
    use GenerateSerialNumberTrait;
    use HasAccessToEntityRepositoryTrait;
    use LeaseNotificationTrait;

    /**
     * Get Lease Detail.
     *
     * @param $id
     *
     * @Route("/leases/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getLeaseAction(
        $id
    ) {
        // check user permission
        $this->checkAdminLeasePermission(AdminPermission::OP_LEVEL_VIEW);

        $lease = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')->find($id);

        $this->throwNotFoundIfNull($lease, self::NOT_FOUND_MESSAGE);

        $bills = $this->getLeaseBillRepo()->findBy(array(
            'lease' => $lease,
            'type' => LeaseBill::TYPE_LEASE,
        ));
        $lease->setBills($bills);

        $totalLeaseBills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->countBills(
                $lease,
                LeaseBill::TYPE_LEASE
            );
        $lease->setTotalLeaseBillsAmount($totalLeaseBills);

        $paidLeaseBills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->countBills(
                $lease,
                LeaseBill::TYPE_LEASE,
                LeaseBill::STATUS_PAID
            );
        $lease->setPaidLeaseBillsAmount($paidLeaseBills);

        $otherBills = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->countBills(
                $lease,
                LeaseBill::TYPE_OTHER
            );
        $lease->setOtherBillsAmount($otherBills);

        $pendingLeaseBill = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->sumBillsFees(
                $lease,
                LeaseBill::STATUS_PENDING
            );
        $pendingLeaseBill = is_null($pendingLeaseBill) ? 0 : $pendingLeaseBill;
        $lease->setPushedLeaseBillsFees($pendingLeaseBill);

        $view = new View();
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(['main'])
        );
        $view->setData($lease);

        return $view;
    }

    /**
     * Get List of Lease.
     *
     * @Route("/leases")
     * @Method({"GET"})
     *
     * @Annotations\QueryParam(
     *    name="status",
     *    array=false,
     *    default="all",
     *    nullable=true,
     *    strict=true,
     *    description="status of lease"
     * )
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
     * @Annotations\QueryParam(
     *    name="keyword",
     *    default=null,
     *    nullable=true,
     *    description="applicant, room, number"
     * )
     *
     * @Annotations\QueryParam(
     *    name="keyword_search",
     *    default=null,
     *    nullable=true,
     *    description="search query"
     * )
     *
     * @Annotations\QueryParam(
     *    name="create_date_range",
     *    default=null,
     *    nullable=true,
     *    description="last_week, last_month"
     * )
     *
     * @Annotations\QueryParam(
     *    name="create_start",
     *    default=null,
     *    nullable=true,
     *    description="create start date"
     * )
     *
     * @Annotations\QueryParam(
     *    name="create_end",
     *    default=null,
     *    nullable=true,
     *    description="create end date"
     * )
     *
     * @Annotations\QueryParam(
     *    name="start_date",
     *    default=null,
     *    nullable=true,
     *    description="appointment start date"
     * )
     *
     * @Annotations\QueryParam(
     *    name="end_date",
     *    default=null,
     *    nullable=true,
     *    description="appointment end date"
     * )
     *
     * @return View
     */
    public function getLeasesAction(
        ParamFetcherInterface $paramFetcher,
        Request $request
    ) {
        // check user permission
        $this->checkAdminLeasePermission(AdminPermission::OP_LEVEL_VIEW);

        // filters
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $offset = ($pageIndex - 1) * $pageLimit;
        $limit = $pageLimit;

        $status = $paramFetcher->get('status');

        // search keyword and query
        $keyword = $paramFetcher->get('keyword');
        $keywordSearch = $paramFetcher->get('keyword_search');

        // creation date filter
        $createRange = $paramFetcher->get('create_date_range');
        $createStart = $paramFetcher->get('create_start');
        $createEnd = $paramFetcher->get('create_end');

        // appointment date filter
        $startDate = $paramFetcher->get('start_date');
        $endDate = $paramFetcher->get('end_date');

        //get my buildings list
        $myBuildingIds = $this->getMySalesBuildingIds(
            $this->getAdminId(),
            array(
                AdminPermission::KEY_SALES_BUILDING_LONG_TERM_LEASE,
            )
        );

        $leases = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')
            ->findLeases(
                $myBuildingIds,
                $status,
                $keyword,
                $keywordSearch,
                $createRange,
                $createStart,
                $createEnd,
                $startDate,
                $endDate,
                $limit,
                $offset
            );

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease')
            ->countLeasesAmount(
                $myBuildingIds,
                $status,
                $keyword,
                $keywordSearch,
                $createRange,
                $createStart,
                $createEnd,
                $startDate,
                $endDate
            );

        foreach ($leases as $lease) {
            $totalLeaseBills = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Lease\LeaseBill')
                ->countBills(
                    $lease,
                    LeaseBill::TYPE_LEASE
                );
            $lease->setTotalLeaseBillsAmount($totalLeaseBills);

            $paidLeaseBills = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Lease\LeaseBill')
                ->countBills(
                    $lease,
                    LeaseBill::TYPE_LEASE,
                    LeaseBill::STATUS_PAID
                );
            $lease->setPaidLeaseBillsAmount($paidLeaseBills);

            $otherBills = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Lease\LeaseBill')
                ->countBills(
                    $lease,
                    LeaseBill::TYPE_OTHER
                );
            $lease->setOtherBillsAmount($otherBills);

            $pendingLeaseBill = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Lease\LeaseBill')
                ->sumBillsFees(
                    $lease,
                    LeaseBill::STATUS_PENDING
                );
            $pendingLeaseBill = is_null($pendingLeaseBill) ? 0 : $pendingLeaseBill;
            $lease->setPushedLeaseBillsFees($pendingLeaseBill);
        }

        $view = new View();
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(['lease_list'])
        );

        $view->setData(
            array(
            'current_page_number' => (int) $pageIndex,
            'num_items_per_page' => (int) $pageLimit,
            'items' => $leases,
            'total_count' => (int) $count,
        ));

        return $view;
    }

    /**
     * Create a new lease.
     *
     * @param $request
     *
     * @Route("/leases")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postLeaseAction(
        Request $request
    ) {
        // check user permission
        $this->checkAdminLeasePermission(AdminPermission::OP_LEVEL_EDIT);

        $payload = json_decode($request->getContent(), true);

        return $this->handleLeasePost($payload);
    }

    /**
     * Edit a lease.
     *
     * @param $request
     * @param $id
     *
     * @Route("/leases/{id}")
     * @Method({"PUT"})
     *
     * @return View
     */
    public function putLeaseAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminLeasePermission(AdminPermission::OP_LEVEL_EDIT);

        $payload = json_decode($request->getContent(), true);

        return $this->handleLeasePut($payload, $id);
    }

    /**
     * Patch Lease Status.
     *
     * @param $request
     * @param $id
     *
     * @Route("/leases/{id}/status")
     * @Method({"PATCH"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function patchLeaseStatusAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminLeasePermission(AdminPermission::OP_LEVEL_EDIT);

        $payload = json_decode($request->getContent(), true);

        if (
            !key_exists('status', $payload) ||
            !filter_var($payload['status'], FILTER_DEFAULT)
        ) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $lease = $this->getLeaseRepo()->find($id);
        $this->throwNotFoundIfNull($lease, self::NOT_FOUND_MESSAGE);

        $status = $lease->getStatus();
        switch ($payload['status']) {
            case Lease::LEASE_STATUS_CONFIRMING:
                if ($status != Lease::LEASE_STATUS_DRAFTING) {
                    throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
                }
                break;
            case Lease::LEASE_STATUS_PERFORMING:
                if ($status != Lease::LEASE_STATUS_CONFIRMED) {
                    throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
                }
                break;
            case Lease::LEASE_STATUS_CLOSED:
                if (
                    $status != Lease::LEASE_STATUS_DRAFTING ||
                    $status != Lease::LEASE_STATUS_CONFIRMING ||
                    $status != Lease::LEASE_STATUS_CONFIRMED
                ) {
                    throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
                }
                break;
            case Lease::LEASE_STATUS_TERMINATED:
                if (
                    $status != Lease::LEASE_STATUS_PERFORMING ||
                    $status != Lease::LEASE_STATUS_RECONFIRMING ||
                    $lease->getEndDate() < new \DateTime('now')
                ) {
                    throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
                }

                $this->setAccessActionToDelete($lease->getAccessNo());

                // remove door access
                $this->callRepealRoomOrderCommand(
                    $lease->getBuilding()->getServer(),
                    $lease->getAccessNo()
                );

                // send notification to removed users
                $removeUsers = $lease->getInvitedPeopleIds();
                array_push($removeUsers, $lease->getSupervisorId());
                if (!empty($removeUsers)) {
                    $this->sendXmppLeaseNotification(
                        $lease,
                        $removeUsers,
                        ProductOrder::ACTION_INVITE_REMOVE,
                        $lease->getSupervisorId(),
                        [],
                        ProductOrderMessage::CANCEL_ORDER_MESSAGE_PART1,
                        ProductOrderMessage::CANCEL_ORDER_MESSAGE_PART2
                    );
                }

                break;
            case Lease::LEASE_STATUS_END:
                if ($status != Lease::LEASE_STATUS_PERFORMING) {
                    throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
                }
                break;
            default:
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $lease->setStatus($payload['status']);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        // generate log
        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_LEASE,
            'logAction' => Log::ACTION_EDIT,
            'logObjectKey' => Log::OBJECT_LEASE,
            'logObjectId' => $lease->getId(),
        ));

        return new View();
    }

    /**
     * Delete Draft of Lease.
     *
     * @Route("/leases/{id}")
     * @Method({"DELETE"})
     *
     * @param int $id
     *
     * @throws \Exception
     */
    public function deleteLeaseAction(
        $id
    ) {
        // check user permission
        $this->checkAdminLeasePermission(AdminPermission::OP_LEVEL_EDIT);

        $em = $this->getDoctrine()->getManager();

        $lease = $this->getLeaseRepo()->findOneBy(
                array(
                    'id' => $id,
                    'status' => Lease::LEASE_STATUS_DRAFTING,
                )
            );

        $this->throwNotFoundIfNull($lease, self::NOT_FOUND_MESSAGE);

        $em->remove($lease);
        $em->flush();

        // generate log
        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_LEASE,
            'logAction' => Log::ACTION_DELETE,
            'logObjectKey' => Log::OBJECT_LEASE,
            'logObjectId' => $lease->getId(),
        ));
    }

    /**
     * @param $payload
     *
     * @return View
     */
    private function handleLeasePost(
        $payload
    ) {
        $this->checkLeaseAttributesIsValid($payload);

        $em = $this->getDoctrine()->getManager();
        $lease = new Lease();

        if (!empty($payload['drawee'])) {
            $drawee = $this->getUserRepo()->find($payload['drawee']);
            $this->throwNotFoundIfNull($drawee, self::NOT_FOUND_MESSAGE);
            $lease->setDrawee($drawee);
        }

        if (!empty($payload['supervisor'])) {
            $supervisor = $this->getUserRepo()->find($payload['drawee']);
            $this->throwNotFoundIfNull($supervisor, self::NOT_FOUND_MESSAGE);
            $lease->setDrawee($supervisor);
        }

        $product = $this->getProductRepo()->find($payload['product']);
        $this->throwNotFoundIfNull($product, self::NOT_FOUND_MESSAGE);
        $lease->setProduct($product);

        $startDate = new \DateTime($payload['start_date']);
        $endDate = new \DateTime($payload['end_date']);

        $lease->setDeposit($payload['deposit']);
        $lease->setEndDate($endDate);
        $lease->setLesseeAddress($payload['lessee_address']);
        $lease->setLesseeContact($payload['lessee_contact']);
        $lease->setLesseeEmail($payload['lessee_email']);
        $lease->setLesseeName($payload['lessee_name']);
        $lease->setLesseePhone($payload['lessee_phone']);
        $lease->setLessorAddress($payload['lessor_address']);
        $lease->setLessorName($payload['lessor_name']);
        $lease->setLessorPhone($payload['lessor_phone']);
        $lease->setLessorEmail($payload['lessor_email']);
        $lease->setLessorContact($payload['lessor_contact']);
        $lease->setMonthlyRent($payload['monthly_rent']);
        $lease->setPurpose($payload['purpose']);
        $lease->setStatus($payload['status']);
        $lease->setStartDate($startDate);
        $lease->setSerialNumber($this->generateLeaseSerialNumber());
        $lease->setTotalRent($payload['total_rent']);

        if (
            isset($payload['other_expenses']) &&
            gettype($payload['other_expenses'] == 'string')
        ) {
            $lease->setOtherExpenses($payload['other_expenses']);
        }

        if (
            isset($payload['supplementary_terms']) &&
            gettype($payload['supplementary_terms'] == 'string')
        ) {
            $lease->setSupplementaryTerms($payload['supplementary_terms']);
        }

        // If lease create from product appointment
        if (
            isset($payload['product_appointment']) &&
            gettype($payload['product_appointment'] == 'doulbe')
        ) {
            $productAppointment = $this->getProductAppointmentRepo()
                ->find($payload['product_appointment']);

            $this->throwNotFoundIfNull($productAppointment, self::NOT_FOUND_MESSAGE);

            if ($productAppointment->getStatus() != ProductAppointment::STATUS_ACCEPTED) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }

            $lease->setProductAppointment($productAppointment);
        }

        $this->handleLeaseRentTypesPost($payload['lease_rent_types'], $lease);
        $this->handleLeaseBillPost($payload['bills'], $lease, $em);

        $em->persist($lease);
        $em->flush();

        $response = array(
            'id' => $lease->getId(),
        );

        // generate log
        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_LEASE,
            'logAction' => Log::ACTION_CREATE,
            'logObjectKey' => Log::OBJECT_LEASE,
            'logObjectId' => $lease->getId(),
        ));

        return new View($response, 201);
    }

    private function checkLeaseAttributesIsValid($payload)
    {
        if (
            !key_exists('lessee_address', $payload) ||
            !key_exists('lessee_contact', $payload) ||
            !key_exists('lessee_email', $payload) ||
            !key_exists('lessee_name', $payload) ||
            !key_exists('lessee_phone', $payload) ||
            !key_exists('lessor_address', $payload) ||
            !key_exists('lessor_name', $payload) ||
            !key_exists('lessor_phone', $payload) ||
            !key_exists('lessor_email', $payload) ||
            !key_exists('lessor_contact', $payload) ||
            !key_exists('deposit', $payload) ||
            !key_exists('monthly_rent', $payload) ||
            !key_exists('total_rent', $payload) ||
            !key_exists('status', $payload) ||
            !key_exists('purpose', $payload) ||
            !key_exists('start_date', $payload) ||
            !key_exists('end_date', $payload) ||
            !key_exists('drawee', $payload) ||
            !key_exists('supervisor', $payload) ||
            !key_exists('product', $payload) ||
            !key_exists('product', $payload) ||
            !key_exists('product', $payload) ||
            !key_exists('lease_rent_types', $payload) ||
            !key_exists('bills', $payload) ||
            gettype($payload['lease_rent_types']) != 'array' ||
            gettype($payload['bills']) != 'array'
        ) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        if (
            $payload['status'] != Lease::LEASE_STATUS_DRAFTING
        ) {
            if (
                (gettype($payload['deposit']) != 'double' && gettype($payload['deposit']) != 'integer') ||
                (gettype($payload['monthly_rent']) != 'double' && gettype($payload['deposit']) != 'integer') ||
                (gettype($payload['total_rent']) != 'double' && gettype($payload['deposit']) != 'integer') ||
                empty($payload['deposit']) ||
                empty($payload['monthly_rent']) ||
                empty($payload['total_rent']) ||
                empty($payload['lease_rent_types']) ||
                empty($payload['bills']) ||
                !preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $payload['start_date']) ||
                !preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $payload['end_date']) ||
                !filter_var($payload['lessee_address'], FILTER_DEFAULT) ||
                !filter_var($payload['lessee_contact'], FILTER_DEFAULT) ||
                !filter_var($payload['lessee_name'], FILTER_DEFAULT) ||
                !filter_var($payload['lessee_phone'], FILTER_DEFAULT) ||
                !filter_var($payload['lessee_email'], FILTER_VALIDATE_EMAIL) ||
                !filter_var($payload['lessor_email'], FILTER_VALIDATE_EMAIL) ||
                !filter_var($payload['lessor_address'], FILTER_DEFAULT) ||
                !filter_var($payload['lessor_name'], FILTER_DEFAULT) ||
                !filter_var($payload['lessor_phone'], FILTER_DEFAULT) ||
                !filter_var($payload['lessor_contact'], FILTER_DEFAULT) ||
                !filter_var($payload['purpose'], FILTER_DEFAULT) ||
                !filter_var($payload['status'], FILTER_DEFAULT) ||
                !filter_var($payload['drawee'], FILTER_VALIDATE_INT) ||
                !filter_var($payload['supervisor'], FILTER_VALIDATE_INT) ||
                !filter_var($payload['product'], FILTER_VALIDATE_INT)
            ) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }

            foreach ($payload['bills'] as $billAttributes) {
                if (
                    !key_exists('name', $billAttributes) ||
                    !key_exists('amount', $billAttributes) ||
                    !key_exists('description', $billAttributes) ||
                    !key_exists('start_date', $billAttributes) ||
                    !key_exists('end_date', $billAttributes) ||
                    (gettype($billAttributes['amount']) != 'double' && gettype($billAttributes['amount']) != 'integer') ||
                    empty($billAttributes['amount']) ||
                    !filter_var($billAttributes['name'], FILTER_DEFAULT) ||
                    !filter_var($billAttributes['description'], FILTER_DEFAULT) ||
                    !preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $billAttributes['start_date']) ||
                    !preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $billAttributes['end_date'])
                ) {
                    throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
                }
            }
        }

        return;
    }

    private function handleLeaseRentTypesPost(
        $leaseRentTypeIds,
        $lease
    ) {
        foreach ($leaseRentTypeIds as $leaseRentTypeId) {
            $leaseRentType = $this->getLeaseRentTypesRepo()->find($leaseRentTypeId);
            if (is_null($leaseRentType)) {
                throw new NotFoundHttpException(self::NOT_FOUND_MESSAGE);
            }
            $lease->addLeaseRentTypes($leaseRentType);
        }
    }

    private function handleLeaseBillPost(
        $payloadBills,
        $lease,
        $em
    ) {
        foreach ($payloadBills as $billAttributes) {
            $bill = new LeaseBill();

            $startDate = new \DateTime($billAttributes['start_date']);
            $endDate = new \DateTime($billAttributes['end_date']);

            $bill->setName($billAttributes['name']);
            $bill->setAmount($billAttributes['amount']);
            $bill->setDescription($billAttributes['description']);
            $bill->setSerialNumber($this->generateSerialNumber(LeaseBill::LEASE_BILL_LETTER_HEAD));
            $bill->setStartDate($startDate);
            $bill->setEndDate($endDate);
            $bill->setType(LeaseBill::TYPE_LEASE);
            $bill->setStatus(LeaseBill::STATUS_PENDING);
            $bill->setLease($lease);

            $em->persist($bill);
        }
    }

    /**
     * @param $payload
     * @param $leaseId
     *
     * @return View
     */
    private function handleLeasePut($payload, $leaseId)
    {
        $this->checkLeaseAttributesIsValid($payload);

        $em = $this->getDoctrine()->getManager();
        $lease = $this->getLeaseRepo()->find($leaseId);

        if (!empty($payload['drawee'])) {
            $drawee = $this->getUserRepo()->find($payload['drawee']);
            $this->throwNotFoundIfNull($drawee, self::NOT_FOUND_MESSAGE);
            $lease->setDrawee($drawee);
        }

        if (!empty($payload['supervisor'])) {
            $supervisor = $this->getUserRepo()->find($payload['supervisor']);
            $this->throwNotFoundIfNull($supervisor, self::NOT_FOUND_MESSAGE);
            $lease->setSupervisor($supervisor);
        }

        $product = $this->getProductRepo()->find($payload['product']);
        $this->throwNotFoundIfNull($product, self::NOT_FOUND_MESSAGE);
        $lease->setProduct($product);

        $lease->setDeposit($payload['deposit']);
        $lease->setLesseeAddress($payload['lessee_address']);
        $lease->setLesseeContact($payload['lessee_contact']);
        $lease->setLesseeEmail($payload['lessee_email']);
        $lease->setLesseeName($payload['lessee_name']);
        $lease->setLesseePhone($payload['lessee_phone']);
        $lease->setLessorAddress($payload['lessor_address']);
        $lease->setLessorName($payload['lessor_name']);
        $lease->setLessorPhone($payload['lessor_phone']);
        $lease->setLessorEmail($payload['lessor_email']);
        $lease->setLessorContact($payload['lessor_contact']);
        $lease->setMonthlyRent($payload['monthly_rent']);
        $lease->setPurpose($payload['purpose']);
        $lease->setSerialNumber($this->generateLeaseSerialNumber());
        $lease->setTotalRent($payload['total_rent']);
        $lease->setModificationDate(new \DateTime('now'));

        switch ($lease->getStatus()) {
            case Lease::LEASE_STATUS_DRAFTING:
                $lease->setStatus($payload['status']);
                break;
            case Lease::LEASE_STATUS_CONFIRMING:
                break;
            case Lease::LEASE_STATUS_CONFIRMED:
                if ($payload['status'] != Lease::LEASE_STATUS_RECONFIRMING) {
                    throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
                }
                $lease->setStatus(Lease::LEASE_STATUS_RECONFIRMING);
                break;
            case Lease::LEASE_STATUS_RECONFIRMING:
                if ($payload['status'] != Lease::LEASE_STATUS_RECONFIRMING) {
                    throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
                }
                $lease->setStatus(Lease::LEASE_STATUS_RECONFIRMING);
                break;
            case Lease::LEASE_STATUS_PERFORMING:
                if ($payload['status'] != Lease::LEASE_STATUS_RECONFIRMING) {
                    throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
                }
                $lease->setStatus(Lease::LEASE_STATUS_RECONFIRMING);
                break;
            default:
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        if (
            isset($payload['other_expenses']) &&
            gettype($payload['other_expenses'] == 'string')
        ) {
            $lease->setOtherExpenses($payload['other_expenses']);
        }

        if (
            isset($payload['supplementary_terms']) &&
            gettype($payload['supplementary_terms'] == 'string')
        ) {
            $lease->setSupplementaryTerms($payload['supplementary_terms']);
        }

        // If lease from product appointment
        if (
            isset($payload['product_appointment']) &&
            gettype($payload['product_appointment'] == 'doulbe')
        ) {
            $productAppointment = $this->getProductAppointmentRepo()
                ->find($payload['product_appointment']);

            $this->throwNotFoundIfNull($productAppointment, self::NOT_FOUND_MESSAGE);

            if ($productAppointment->getStatus() != ProductAppointment::STATUS_ACCEPTED) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }

            $lease->setProductAppointment($productAppointment);
        }

        $this->handleLeaseRentTypesPut($payload['lease_rent_types'], $lease);
        $this->handleLeaseBillPut($payload['bills'], $lease, $em);

        $startDate = new \DateTime($payload['start_date']);
        $endDate = new \DateTime($payload['end_date']);

        if (
            $startDate != $lease->getStartDate() ||
            $endDate != $lease->getEndDate()
        ) {
            $this->setAccessActionToDelete($lease->getAccessNo());
            $this->callRepealRoomOrderCommand(
                $lease->getBuilding()->getServer(),
                $lease->getAccessNo()
            );

            $lease->setAccessNo($this->generateAccessNumber());
            $lease->setStartDate($startDate);
            $lease->setEndDate($endDate);

            $users = $lease->getInvitedPeopleIds();
            array_push($users, $lease->getSupervisorId());
            $this->addPeople(
                $users,
                $lease,
                $lease->getBuilding()->getServer()
            );
        }

        $em->persist($lease);
        $em->flush();

        // generate log
        $this->generateAdminLogs(array(
            'logModule' => Log::MODULE_LEASE,
            'logAction' => Log::ACTION_EDIT,
            'logObjectKey' => Log::OBJECT_LEASE,
            'logObjectId' => $lease->getId(),
        ));

        return new View();
    }

    private function handleLeaseRentTypesPut(
        $leaseRentTypeIds,
        $lease
    ) {
        $leaseRentTypes = $lease->getLeaseRentTypes();
        foreach ($leaseRentTypes as $leaseRentType) {
            $lease->removeLeaseRentTypes($leaseRentType);
        }

        foreach ($leaseRentTypeIds as $leaseRentTypeId) {
            $leaseRentType = $this->getLeaseRentTypesRepo()->find($leaseRentTypeId);
            if (is_null($leaseRentType)) {
                throw new NotFoundHttpException(self::NOT_FOUND_MESSAGE);
            }
            $lease->addLeaseRentTypes($leaseRentType);
        }
    }

    private function handleLeaseBillPut(
        $payloadBills,
        $lease,
        $em
    ) {
        $bills = $this->getLeaseBillRepo()->findBy(array(
            'lease' => $lease->getId(),
            'status' => LeaseBill::STATUS_PENDING,
            'type' => LeaseBill::TYPE_LEASE,
        ));

        foreach ($bills as $bill) {
            $em->remove($bill);
        }

        foreach ($payloadBills as $billAttributes) {
            $bill = new LeaseBill();

            $startDate = new \DateTime($billAttributes['start_date']);
            $endDate = new \DateTime($billAttributes['end_date']);

            $bill->setName($billAttributes['name']);
            $bill->setAmount($billAttributes['amount']);
            $bill->setDescription($billAttributes['description']);
            $bill->setSerialNumber($this->generateSerialNumber(LeaseBill::LEASE_BILL_LETTER_HEAD));
            $bill->setStartDate($startDate);
            $bill->setEndDate($endDate);
            $bill->setType(LeaseBill::TYPE_LEASE);
            $bill->setStatus(LeaseBill::STATUS_PENDING);
            $bill->setLease($lease);

            $em->persist($bill);
        }
    }

    /**
     * @param $opLevel
     */
    private function checkAdminLeasePermission(
        $opLevel
    ) {
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_SALES_BUILDING_LONG_TERM_LEASE],
            ],
            $opLevel
        );
    }

    /**
     * @param $users
     * @param $lease
     * @param $base
     *
     * @return array|mixed
     */
    private function addPeople(
        $users,
        $lease,
        $base
    ) {
        $em = $this->getDoctrine()->getManager();
        $roomDoors = $lease->getRoom()->getDoorControl();

        if (is_null($base) || empty($base) || empty($roomDoors)) {
            return;
        }

        $userArray = [];
        foreach ($users as $userId) {
            $this->storeDoorAccess(
                $em,
                $lease->getAccessNo(),
                $userId,
                $lease->getBuildingId(),
                $lease->getRoomId(),
                $lease->getStartDate(),
                $lease->getEndDate()
            );

            $userArray = $this->getUserArrayIfAuthed(
                $base,
                $userId,
                $userArray
            );
        }

        $em->flush();

        // set room access
        if (!empty($userArray)) {
            $this->callSetRoomOrderCommand(
                $base,
                $userArray,
                $roomDoors,
                $lease->getAccessNo()
            );
        }

        return;
    }
}
