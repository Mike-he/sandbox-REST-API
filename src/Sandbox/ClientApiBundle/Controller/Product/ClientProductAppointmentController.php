<?php

namespace Sandbox\ClientApiBundle\Controller\Product;

use Rs\Json\Patch;
use Sandbox\ApiBundle\Controller\Product\ProductController;
use Sandbox\ApiBundle\Entity\Product\Product;
use Sandbox\ApiBundle\Entity\Product\ProductAppointment;
use Sandbox\ApiBundle\Form\Product\ProductAppointmentPatchType;
use Sandbox\ApiBundle\Form\Product\ProductAppointmentPostType;
use Sandbox\ApiBundle\Traits\HasAccessToEntityRepositoryTrait;
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

        $lease = $this->getLeaseRepo()->findOneBy(['productAppointment' => $appointment]);
        if (!is_null($lease)) {
            if ($lease->getStatus() == Lease::LEASE_STATUS_DRAFTING) {
                $lease->setStatus(Lease::LEASE_STATUS_CLOSED);
            }
        }

        $em = $this->getDoctrine()->getManager();
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
        $unit = $product->getUnitPrice();

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

        return new View(
            ['id' => $appointment->getId()],
            self::HTTP_STATUS_CREATE_SUCCESS
        );
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
