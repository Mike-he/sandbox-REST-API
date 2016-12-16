<?php

namespace Sandbox\ClientApiBundle\Controller\Lease;

use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Controller\Payment\PaymentController;
use Sandbox\ApiBundle\Entity\Lease\LeaseBill;
use Sandbox\ApiBundle\Entity\Lease\LeaseBillOfflineTransfer;
use Sandbox\ApiBundle\Entity\Lease\LeaseBillTransferAttachment;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Form\Lease\LeaseBillOfflineTransferPost;
use Sandbox\ClientApiBundle\Data\ThirdParty\ThirdPartyOAuthWeChatData;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations\Get;

class ClientLeaseBillController extends PaymentController
{
    const BILL_NOT_FOUND_CODE = 400001;
    const BILL_NOT_FOUND_MESSAGE = 'Can not find bill';
    const BILL_NOT_SUPPORT_BALANCE_PAYMENT_CODE = 400002;
    const BILL_NOT_SUPPORT_BALANCE_PAYMENT__MESSAGE = 'Does not support the balance payment';

    /**
     * Get all bills for current user.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     *
     * @Annotations\QueryParam(
     *    name="lease_id",
     *    default=null,
     *    nullable=true,
     *    description="lease id"
     * )
     *
     *
     * @Annotations\QueryParam(
     *    name="limit",
     *    array=false,
     *    default="10",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="limit for page"
     * )
     *
     * @Annotations\QueryParam(
     *    name="offset",
     *    array=false,
     *    default="0",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Offset of page"
     * )
     *
     * @Route("/leases/bills/my")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getUserBillsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();
        $leaseId = $paramFetcher->get('lease_id');
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        if (!is_null($leaseId)) {
            $lease = $this->getDoctrine()->getRepository("SandboxApiBundle:Lease\Lease")->find($leaseId);
            if (!$lease) {
                return new View();
            }
        }

        $bills = $this->getDoctrine()
            ->getRepository("SandboxApiBundle:Lease\LeaseBill")
            ->findMyBills(
                $userId,
                $leaseId,
                $limit,
                $offset
            );

        $data = $this->handleBillsData($bills);

        return new View($data);
    }

    /**
     * Get Bill Info.
     *
     * @param Request $request
     *
     * @Route("/leases/bills/{id}")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getBillByIdAction(
        Request $request,
        $id
    ) {
        $userId = $this->getUserId();

        $bill = $this->getDoctrine()
            ->getRepository("SandboxApiBundle:Lease\LeaseBill")
            ->find($id);

        if (is_null($bill)) {
            return $this->customErrorView(
                400,
                self::BILL_NOT_FOUND_CODE,
                self::BILL_NOT_FOUND_MESSAGE
            );
        }

        $data = array();
        if ($bill->getDrawee() == $userId ||
            $bill->getLease()->getDrawee()->getId() == $userId ||
            $bill->getLease()->getSupervisor()->getId() == $userId
        ) {
            $data = $this->handleBillInfo($bill);
        }

        return new View($data);
    }

    /**
     * Pay Bill.
     *
     * @param Request $request
     *
     * @Route("/leases/bills/{id}/pay")
     * @Method({"POST"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function payAction(
        Request $request,
        $id
    ) {
        $bill = $this->getDoctrine()
            ->getRepository("SandboxApiBundle:Lease\LeaseBill")
            ->find($id);

        if (is_null($bill)) {
            return $this->customErrorView(
                400,
                self::BILL_NOT_FOUND_CODE,
                self::BILL_NOT_FOUND_MESSAGE
            );
        }

        // check if request user is the same as drawee
        $this->throwAccessDeniedIfNotSameUser($bill->getLease()->getDrawee()->getId());

        if ($bill->getStatus() !== 'unpaid') {
            return $this->customErrorView(
                400,
                self::WRONG_PAYMENT_STATUS_CODE,
                self::WRONG_PAYMENT_STATUS_MESSAGE
            );
        }

        $requestContent = json_decode($request->getContent(), true);
        $channel = $requestContent['channel'];

        if (is_null($channel)) {
            return $this->customErrorView(
                400,
                self::CHANNEL_IS_EMPTY_CODE,
                self::CHANNEL_IS_EMPTY_MESSAGE
            );
        }

        $token = '';
        $smsId = '';
        $smsCode = '';
        $openId = null;

        switch ($channel) {
            case LeaseBill::CHANNEL_ACCOUNT:
                return $this->customErrorView(
                    400,
                    self::BILL_NOT_SUPPORT_BALANCE_PAYMENT_CODE,
                    self::BILL_NOT_SUPPORT_BALANCE_PAYMENT__MESSAGE
                );
                break;

            case LeaseBill::CHANNEL_OFFLINE:
                return $this->payByOffline(
                    $bill,
                    $channel
                );
                break;
            case ProductOrder::CHANNEL_WECHAT_PUB:
                $wechat = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:ThirdParty\WeChat')
                    ->findOneBy(
                        [
                            'userId' => $this->getUserId(),
                            'loginFrom' => ThirdPartyOAuthWeChatData::DATA_FROM_WEBSITE,
                        ]
                    );
                $this->throwNotFoundIfNull($wechat, self::NOT_FOUND_MESSAGE);

                $openId = $wechat->getOpenId();
                break;
            default:
        }

        $billNumber = $bill->getSerialNumber();
        $amount = $bill->getRevisedAmount() ? $bill->getRevisedAmount() : $bill->getAmount();
        $charge = $this->payForOrder(
            $token,
            $smsId,
            $smsCode,
            $billNumber,
            $amount,
            $channel,
            LeaseBill::PAYMENT_SUBJECT,
            LeaseBill::PAYMENT_BODY,
            $openId
        );
        $charge = json_decode($charge, true);

        return new View($charge);
    }

    /**
     * Update Transfe.
     *
     * @param Request $request
     * @param $id
     *
     * @Route("/leases/bills/{id}/transfer")
     * @Method({"PUT"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function updateTransferAction(
        Request $request,
        $id
    ) {
        $userId = $this->getUserId();

        $bill = $this->getDoctrine()
            ->getRepository("SandboxApiBundle:Lease\LeaseBill")
            ->findOneBy(
                array(
                    'id' => $id,
                    'status' => ProductOrder::STATUS_UNPAID,
                    'drawee' => $userId,
                    'payChannel' => ProductOrder::CHANNEL_OFFLINE,
                )
            );

        if (is_null($bill)) {
            return $this->customErrorView(
                400,
                self::BILL_NOT_FOUND_CODE,
                self::BILL_NOT_FOUND_MESSAGE
            );
        }

        $transfer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBillOfflineTransfer')
            ->findOneBy(array('bill' => $id));

        if (is_null($transfer)) {
            return new View();
        }

        $transferStatus = $transfer->getTransferStatus();
        if ($transferStatus != LeaseBillOfflineTransfer::STATUS_UNPAID &&
            $transferStatus != LeaseBillOfflineTransfer::STATUS_RETURNED
        ) {
            return $this->customErrorView(
                400,
                self::WRONG_ORDER_STATUS_CODE,
                self::WRONG_ORDER_STATUS_MESSAGE
            );
        }

        $form = $this->createForm(new LeaseBillOfflineTransferPost(), $transfer);
        $form->submit(json_decode($request->getContent(), true));

        if (!$form->isValid()) {
            return $this->customErrorView(
                400,
                self::INVALID_FORM_CODE,
                self::INVALID_FORM_MESSAGE
            );
        }

        $attachmentArray = $transfer->getAttachments();
        if (empty($attachmentArray)) {
            return new View();
        }

        $em = $this->getDoctrine()->getManager();

        $transferAttachments = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBillTransferAttachment')
            ->findBy(array('transfer' => $transfer));

        foreach ($transferAttachments as $transferAttachment) {
            $em->remove($transferAttachment);
        }

        $attachment = new LeaseBillTransferAttachment();
        $attachment->setContent($attachmentArray[0]['content']);
        $attachment->setAttachmentType($attachmentArray[0]['attachmentType']);
        $attachment->setFilename($attachmentArray[0]['filename']);
        $attachment->setPreview($attachmentArray[0]['preview']);
        $attachment->setSize($attachmentArray[0]['size']);
        $attachment->setTransfer($transfer);
        $em->persist($attachment);

        $bill->setStatus(LeaseBill::STATUS_VERIFY);

        $transfer->setTransferStatus(LeaseBillOfflineTransfer::STATUS_PENDING);

        $em->flush();

        return new View();
    }

    /**
     * @param $bills
     *
     * @return array
     */
    private function handleBillsData(
        $bills
    ) {
        $result = array();
        foreach ($bills as $bill) {
            $room = $bill->getLease()->getProduct()->getRoom();
            $building = $room->getBuilding();

            $attachment = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomAttachmentBinding')
                ->findAttachmentsByRoom($room, 1);

            $result[] = array(
                'id' => $bill->getId(),
                'serial_number' => $bill->getserialNumber(),
                'name' => $bill->getName(),
                'creation_date' => $bill->getCreationDate(),
                'status' => $bill->getStatus(),
                'start_date' => $bill->getStartDate(),
                'end_date' => $bill->getEndDate(),
                'amount' => (float) $bill->getRevisedAmount(),
                'room_type' => $this->get('translator')->trans(ProductOrderExport::TRANS_ROOM_TYPE.$room->getType()),
                'address' => $building->getCity()->getName().$building->getAddress(),
                'content' => $attachment ? $attachment[0]['content'] : '',
                'preview' => $attachment ? $attachment[0]['preview'] : '',
            );
        }

        return $result;
    }

    /**
     * @param $bill
     *
     * @return array
     */
    private function handleBillInfo(
        $bill
    ) {
        $room = $bill->getLease()->getProduct()->getRoom();
        $building = $room->getBuilding();

        $drawee = $bill->getDrawee() ? $bill->getDrawee() : $bill->getLease()->getDrawee()->getId();

        $attachment = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomAttachmentBinding')
            ->findAttachmentsByRoom($room);

        $result = array(
            'id' => $bill->getId(),
            'serial_number' => $bill->getserialNumber(),
            'name' => $bill->getName(),
            'description' => $bill->getDescription(),
            'creation_date' => $bill->getCreationDate(),
            'payment_date' => $bill->getPaymentDate(),
            'status' => $bill->getStatus(),
            'start_date' => $bill->getStartDate(),
            'end_date' => $bill->getEndDate(),
            'amount' => (float) $bill->getAmount(),
            'revised_amount' => (float) $bill->getRevisedAmount(),
            'revision_note' => $bill->getRevisionNote(),
            'lease' => array(
                    'id' => $bill->getLease()->getId(),
                    'serial_number' => $bill->getLease()->getserialNumber(),
                    ),
            'product' => array(
                        'name' => $room->getName(),
                        'type' => $this->get('translator')->trans(ProductOrderExport::TRANS_ROOM_TYPE.$room->getType()),
                        'address' => $building->getCity()->getName().$building->getAddress(),
                    ),
            'drawee' => $drawee,
            'attachment' => $attachment,
            'can_pay' => $this->getUserId() == $drawee ? true : false,
            'pay_channel' => $bill->getPayChannel(),
        );

        return $result;
    }

    /**
     * @param $bill
     * @param $channel
     *
     * @return View
     */
    private function payByOffline(
        $bill,
        $channel
    ) {
        $bill->setPayChannel($channel);
        $bill->setDrawee($this->getUserId());
        $bill->setPaymentDate(new \DateTime());

        $transfer = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBillOfflineTransfer')
            ->findOneBy(array('bill' => $bill));

        if (!is_null($transfer)) {
            return new View();
        }

        $transfer = new LeaseBillOfflineTransfer();
        $transfer->setBill($bill);

        $em = $this->getDoctrine()->getManager();
        $em->persist($transfer);
        $em->flush();

        return new View();
    }
}
