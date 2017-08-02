<?php

namespace Sandbox\ClientApiBundle\Controller\Product;

use Rs\Json\Patch;
use Sandbox\ApiBundle\Controller\Product\ProductController;
use Sandbox\ApiBundle\Entity\Admin\AdminRemark;
use Sandbox\ApiBundle\Entity\Admin\AdminStatusLog;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Sandbox\ApiBundle\Entity\Lease\LeaseClue;
use Sandbox\ApiBundle\Entity\Product\Product;
use Sandbox\ApiBundle\Entity\Product\ProductAppointment;
use Sandbox\ApiBundle\Form\Product\ProductAppointmentPatchType;
use Sandbox\ApiBundle\Form\Product\ProductAppointmentPostType;
use Sandbox\ApiBundle\Traits\GenerateSerialNumberTrait;
use Sandbox\ApiBundle\Traits\HasAccessToEntityRepositoryTrait;
use Sandbox\ApiBundle\Traits\StringUtil;
use Sandbox\ApiBundle\Traits\YunPianSms;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Rest controller for Client Product Appointment.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientProductAppointmentController extends ProductController
{
    use HasAccessToEntityRepositoryTrait;
    use YunPianSms;
    use StringUtil;
    use GenerateSerialNumberTrait;

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="limit",
     *    array=false,
     *    default="10",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="limit for the page"
     * )
     *
     * @Annotations\QueryParam(
     *    name="offset",
     *    array=false,
     *    default="0",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="start of the page"
     * )
     *
     * @Route("/products/appointments/list")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getUserProductAppointmentsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $user = $this->checkIfBannedUser();

        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $appointments = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductAppointment')
            ->findBy(
                ['userId' => $user->getId()],
                ['modificationDate' => 'DESC'],
                $limit,
                $offset
            );

        foreach ($appointments as $appointment) {
            $product = $appointment->getProduct();
            $productRentSet = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Product\ProductRentSet')
                ->findOneBy(array(
                    'product' => $product,
                    'status' => true,
                ));
            $product->setRentSet($productRentSet);
        }

        $view = new View($appointments);
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client_appointment_list']));

        return $view;
    }

    /**
     * @param Request $request
     * @param int     $id
     *
     * @Route("/products/appointments/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getUserProductAppointmentByIdAction(
        Request $request,
        $id
    ) {
        $user = $this->checkIfBannedUser();

        $appointment = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductAppointment')
            ->findOneBy([
                'id' => $id,
                'userId' => $user->getId(),
            ]);

        $product = $appointment->getProduct();
        $productRentSet = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductRentSet')
            ->findOneBy(array(
                'product' => $product,
                'status' => true,
            ));
        $product->setRentSet($productRentSet);

        $view = new View($appointment);
        $view->setSerializationContext(
            SerializationContext::create()->setGroups([
                'client_appointment_list',
                'client_appointment_detail',
            ]));

        return $view;
    }

    /**
     * @Route("/products/appointments")
     * @Method({"POST"})
     *
     * @param Request $request
     *
     * @return View
     */
    public function postProductAppointmentsAction(
        Request $request
    ) {
        $user = $this->checkIfBannedUser();

        return $this->handleAppointmentPost(
            $request,
            $user
        );
    }

    /**
     * @Route("/products/appointments/{id}")
     * @Method({"PATCH"})
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function recallProductAppointmentsAction(
        Request $request,
        $id
    ) {
        $user = $this->checkIfBannedUser();

        $appointment = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductAppointment')
            ->findOneBy([
                'userId' => $user->getId(),
                'id' => $id,
                'status' => ProductAppointment::STATUS_PENDING,
            ]);

        $this->throwNotFoundIfNull($appointment, self::NOT_FOUND_MESSAGE);

        return $this->handleProductAppointmentRecall(
            $request,
            $appointment
        );
    }

    /**
     * @param Request            $request
     * @param ProductAppointment $appointment
     */
    private function handleProductAppointmentRecall(
        $request,
        $appointment
    ) {
        $appointmentJson = $this->container->get('serializer')->serialize($appointment, 'json');
        $patch = new Patch($appointmentJson, $request->getContent());
        $appointmentJson = $patch->apply();

        $form = $this->createForm(new ProductAppointmentPatchType(), $appointment);
        $form->submit(json_decode($appointmentJson, true));

        if ($appointment->getStatus() !== ProductAppointment::STATUS_WITHDRAWN) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $em = $this->getDoctrine()->getManager();

        $lease = $this->getLeaseRepo()->findOneBy(['productAppointment' => $appointment]);
        if (!is_null($lease)) {
            if ($lease->getStatus() == Lease::LEASE_STATUS_DRAFTING) {
                $em->remove($lease);
            }
        }

        $em->flush();
    }

    /**
     * @return \Sandbox\ApiBundle\Entity\User\User
     */
    private function checkIfBannedUser()
    {
        $userId = $this->getUserId();

        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->findOneBy([
                'id' => $userId,
                'banned' => false,
            ]);
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        return $user;
    }

    /**
     * @param Request $request
     * @param $user
     *
     * @return View
     */
    private function handleAppointmentPost(
        $request,
        $user
    ) {
        $appointment = new ProductAppointment();

        $form = $this->createForm(new ProductAppointmentPostType(), $appointment);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $product = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\Product')
            ->getLongTermProductById($appointment->getProductId());
        $this->throwNotFoundIfNull($product, self::NOT_FOUND_MESSAGE);

        $profileId = $appointment->getProfileId();
        $profile = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserAppointmentProfile')
            ->findOneBy([
                'id' => $profileId,
                'user' => $user,
            ]);
        $this->throwNotFoundIfNull($profile, self::NOT_FOUND_MESSAGE);

        $period = $appointment->getRentTimeLength();

        $rentSet = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductRentSet')
            ->findOneBy(array(
                'product' => $product,
                'status' => true,
            ));
        if (is_null($rentSet)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }
        $unit = $rentSet->getUnitPrice();

        $startRentDate = new \DateTime($appointment->getStartRentDate());
        $startRentDate->setTime(00, 00, 00);
        $endRentDate = clone $startRentDate;
        $endRentDate->modify("+$period $unit");
        $endRentDate->setTime(23, 59, 59);

        // set fields
        $appointment->setUserId($user->getId());
        $appointment->setProduct($product);
        $appointment->setApplicantName($profile->getContact());
        $appointment->setApplicantCompany($profile->getName());
        $appointment->setApplicantPhone($profile->getPhone());
        $appointment->setApplicantEmail($profile->getEmail());
        $appointment->setStartRentDate($startRentDate);
        $appointment->setEndRentDate($endRentDate);
        $appointment->setAddress($profile->getAddress());
        $appointment->setAppointmentNumber($this->getAppointmentNumberForPost($profileId));
        $appointment->setRentTimeUnit($unit);

        $em = $this->getDoctrine()->getManager();
        $em->persist($appointment);
        $em->flush();

        /** @var Product $product */
        $building = $product->getRoom()->getBuilding();

        $productRentSet = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductRentSet')
            ->findOneBy(array('product'=> $product));
        
        $customerId = $this->get('sandbox_api.sales_customer')->createCustomer(
            $this->getUserId(),
            $building->getCompanyId()
        );

        $serialNumber = $this->generateSerialNumber(LeaseClue::LEASE_CLUE_LETTER_HEAD);

        $leaseClue = new LeaseClue();
        $leaseClue->setSerialNumber($serialNumber);
        $leaseClue->setCompanyId($building->getCompanyId());
        $leaseClue->setBuildingId($building->getId());
        $leaseClue->setProductId($product->getId());
        $leaseClue->setLesseeName($appointment->getApplicantName());
        $leaseClue->setLesseeAddress($appointment->getAddress());
        $leaseClue->setLesseeEmail($appointment->getApplicantEmail());
        $leaseClue->setLesseePhone($appointment->getApplicantPhone());
        $leaseClue->setLesseeCustomer($customerId);
        $leaseClue->setStartDate($appointment->getStartRentDate());
        $leaseClue->setEndDate($appointment->getEndRentDate());
        $leaseClue->setCycle($appointment->getRentTimeLength());
        $leaseClue->setProductAppointmentId($appointment->getId());
        $leaseClue->setMonthlyRent($productRentSet->getBasePrice());
        $leaseClue->setStatus(LeaseClue::LEASE_CLUE_STATUS_CLUE);
        $em->persist($leaseClue);
        $em->flush();

        $message = '创建申请，自动创建线索';
        $this->get('sandbox_api.admin_remark')->autoRemark(
            $this->getUserId(),
            'sales',
            $building->getCompanyId(),
            $message,
            AdminRemark::OBJECT_LEASE_CLUE,
            $leaseClue->getId()
        );

        $logMessage = '申请线索';
        $this->get('sandbox_api.admin_status_log')->autoLog(
            $this->getUserId(),
            LeaseClue::LEASE_CLUE_STATUS_CLUE,
            $logMessage,
            AdminStatusLog::OBJECT_LEASE_CLUE,
            $leaseClue->getId()
        );

        $this->sendNotification($product);

        return new View(
            ['id' => $appointment->getId()],
            self::HTTP_STATUS_CREATE_SUCCESS
        );
    }

    /**
     * @param Product $product
     */
    private function sendNotification(
        Product $product
    ) {
        $building = $product->getRoom()->getBuilding();
        $buildingName = $building->getName();
        $email = $building->getEmail();
        $phones = $building->getOrderRemindPhones();

        if (!is_null($phones)) {
            // send sms
            $phones = explode(',', $phones);
            foreach ($phones as $phone) {
                $this->send_sms($phone, self::ZH_SMS_APPOINTMENT_BEFORE.$buildingName.self::ZH_SMS_APPOINTMENT_AFTER);
            }
        }

        if (!is_null($email)) {
            // send email
            $subject = '【创合秒租】'.$this->before('@', $email).'，办公室申请';
            $this->sendEmail($subject, $email, $this->before('@', $email),
                'Emails/product_appointment_email.html.twig',
                array(
                    'building' => $buildingName,
                ));
        }
    }

    /**
     * @param $profileId
     *
     * @return string
     */
    private function getAppointmentNumberForPost(
        $profileId
    ) {
        $date = round(microtime(true) * 1000);

        $orderNumber = ProductAppointment::APPOINTMENT_NUMBER_LETTER."$date"."$profileId";

        return $orderNumber;
    }
}
