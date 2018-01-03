<?php

namespace Sandbox\CommnueClientApiBundle\Controller\Expert;

use FOS\RestBundle\View\View;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Constants\CustomErrorMessagesConstants;
use Sandbox\ApiBundle\Constants\LetterHeadConstants;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Expert\Expert;
use Sandbox\ApiBundle\Entity\Expert\ExpertOrder;
use Sandbox\ApiBundle\Entity\Service\ViewCounts;
use Sandbox\ApiBundle\Form\Expert\ExpertOrderPatchType;
use Sandbox\ApiBundle\Traits\GenerateSerialNumberTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpFoundation\Request;

class ClientExpertOrderController extends SandboxRestController
{
    use GenerateSerialNumberTrait;

    /**
     * Create A Expert Order.
     *
     * @param $request
     * @param $id
     *
     * @Route("/experts/{id}/orders")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postExpertAction(
        Request $request,
        $id
    ) {
        $em = $this->getDoctrine()->getManager();
        $userId = $this->getUserId();

        $order = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\ExpertOrder')
            ->findOneBy(array(
                'expertId' => $id,
                'userId' => $userId,
                'status' => ExpertOrder::STATUS_PENDING,
            ));

        if ($order) {
            $response = array(
                'id' => $order->getId(),
            );

            return new View($response);
        }

        $expert = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\Expert')
            ->find($id);
        $this->throwNotFoundIfNull($expert, self::NOT_FOUND_MESSAGE);

        if ($expert->isBanned()) {
            return $this->customErrorView(
                400,
                CustomErrorMessagesConstants::ERROR_EXPERT_HAS_BANNED_CODE,
                CustomErrorMessagesConstants::ERROR_EXPERT_HAS_BANNED_MESSAGE
            );
        }

        if (!$expert->isService()) {
            return $this->customErrorView(
                400,
                CustomErrorMessagesConstants::ERROR_EXPERT_WAS_NOT_IN_SERVICE_CODE,
                CustomErrorMessagesConstants::ERROR_EXPERT_WAS_NOT_IN_SERVICE_MESSAGE
            );
        }

        $orderNumber = $this->generateSerialNumber(LetterHeadConstants::EXPERT_ORDER_LETTER_HEAD);

        $order = new ExpertOrder();
        $order->setUserId($userId);
        $order->setExpertId($id);
        $order->setPrice($expert->getBasePrice());
        $order->setOrderNumber($orderNumber);
        $order->setStatus(ExpertOrder::STATUS_PENDING);

        $em->persist($order);
        $em->flush();

        $this->get('sandbox_api.view_count')->autoCounting(
            ViewCounts::OBJECT_EXPERT,
            $id,
            ViewCounts::TYPE_BOOKING
        );

        $response = array(
            'id' => $order->getId(),
        );

        return new View($response, 201);
    }

    /**
     * Get Lists.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="status",
     *     array=false,
     *     default=null,
     *     nullable=true,
     *     strict=true
     * )
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
     * @Route("/experts/my/orders")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMyOrdersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $status = $paramFetcher->get('status');
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $userId = $this->getUserId();

        $expert = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\Expert')
            ->findOneBy(array('userId' => $userId));

        if (!$expert) {
            return new View();
        }

        $orders = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\ExpertOrder')
            ->getLists(
                $expert->getId(),
                $status,
                $limit,
                $offset
            );

        foreach ($orders as &$order) {
            $user = $this->getDoctrine()->getRepository('SandboxApiBundle:User\UserView')->find($order['user_id']);

            $order['user'] = array(
                'id' => $order['user_id'],
                'name' => $user->getName(),
                'phone' => $user->getPhone(),
            );

            $remarks = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Expert\ExpertOrderRemark')
                ->findBy(array('orderId' => $order['id']));

            $order['remarks'] = $remarks;
        }

        $view = new View();
        $view->setData($orders);

        return $view;
    }

    /**
     * Get Detail.
     *
     * @param $id
     *
     * @Route("/experts/orders/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getDetailAction(
        $id
    ) {
        $order = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\ExpertOrder')
            ->find($id);
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        $expert = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\Expert')
            ->find($order->getExpertId());
        $this->throwNotFoundIfNull($expert, self::NOT_FOUND_MESSAGE);

        $cityName = '';
        if ($expert->getCityId()) {
            $city = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomCity')
                ->find($expert->getCityId());
            $cityName = $city ? $city->getName() : '';
        }

        $districtName = '';
        if ($expert->getDistrictId()) {
            $district = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomCity')
                ->find($expert->getDistrictId());

            $districtName = $district ? $district->getName() : '';
        }

        $data = [
            'id' => $order->getId(),
            'order_number' => $order->getOrderNumber(),
            'status' => $order->getStatus(),
            'price' => $order->getPrice(),
            'creation_date' => $order->getCreationDate(),
            'expert' => array(
                'id' => $expert->getId(),
                'photo' => $expert->getPhoto(),
                'name' => $expert->getName(),
                'phone' => $expert->getPhone(),
                'city_name' => $cityName,
                'district_name' => $districtName,
                'identity' => $expert->getIdentity(),
                'introduction' => $expert->getIntroduction(),
                'fields' => $expert->getExpertFields(),
            ),
        ];

        $view = new View();
        $view->setData($data);

        return $view;
    }

    /**
     * Update Order Status.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @Route("/experts/orders/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function patchBillAction(
        Request $request,
        $id
    ) {
        $order = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\ExpertOrder')
            ->find($id);
        $this->throwNotFoundIfNull($order, self::NOT_FOUND_MESSAGE);

        $oldStatus = $order->getStatus();

        if (ExpertOrder::STATUS_PENDING != $oldStatus) {
            return $this->customErrorView(
                400,
                CustomErrorMessagesConstants::ERROR_STATUS_NOT_CORRECT_CODE,
                CustomErrorMessagesConstants::ERROR_STATUS_NOT_CORRECT_MESSAGE
            );
        }

        $orderJson = $this->container->get('serializer')->serialize($order, 'json');
        $patch = new Patch($orderJson, $request->getContent());
        $billJson = $patch->apply();
        $form = $this->createForm(new ExpertOrderPatchType(), $order);
        $form->submit(json_decode($billJson, true));

        $userId = $this->getUserId();
        $now = new \DateTime();

        switch ($order->getStatus()) {
            case ExpertOrder::STATUS_CANCELLED:
                if ($userId != $order->getUserId()) {
                    return $this->customErrorView(
                        400,
                        CustomErrorMessagesConstants::ERROR_ONLY_MY_OWN_OPERATION_CODE,
                        CustomErrorMessagesConstants::ERROR_ONLY_MY_OWN_OPERATION_MESSAGE
                    );
                }

                $order->setCancelledDate($now);
                break;
            case ExpertOrder::STATUS_COMPLETED:
                $expert = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Expert\Expert')
                    ->find($order->getExpertId());

                if ($userId != $expert->getUserId()) {
                    return $this->customErrorView(
                        400,
                        CustomErrorMessagesConstants::ERROR_ONLY_MY_OWN_OPERATION_CODE,
                        CustomErrorMessagesConstants::ERROR_ONLY_MY_OWN_OPERATION_MESSAGE
                    );
                }

                $order->setCompletedDate($now);
                break;
            default:
                return $this->customErrorView(
                    400,
                    CustomErrorMessagesConstants::ERROR_PAYLOAD_FORMAT_NOT_CORRECT_CODE,
                    CustomErrorMessagesConstants::ERROR_PAYLOAD_FORMAT_NOT_CORRECT_MESSAGE
                );
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($order);
        $em->flush();

        return new View();
    }
}
