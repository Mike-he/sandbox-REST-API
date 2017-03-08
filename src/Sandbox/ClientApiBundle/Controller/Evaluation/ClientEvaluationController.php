<?php

namespace Sandbox\ClientApiBundle\Controller\Evaluation;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Controller\Evaluation\EvaluationController;
use Sandbox\ApiBundle\Entity\Evaluation\Evaluation;
use Sandbox\ApiBundle\Entity\Evaluation\EvaluationAttachment;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Form\Evaluation\EvaluationPostType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use FOS\RestBundle\Controller\Annotations;

/**
 *  Client Evaluation Controller.
 *
 * @category Sandbox
 *
 * @author   Feng Li <feng.li@sandbox3.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientEvaluationController extends EvaluationController
{
    const ERROR_ORDER_HAS_COMMENTS_CODE = 400001;
    const ERROR_ORDER_HAS_COMMENTS_MESSAGE = 'This order has been comments';

    const ERROR_EVALUATION_REPEAT_COMMENT_CODE = 400002;
    const ERROR_EVALUATION_REPEAT_COMMENT_MESSAGE = "A month can't repeat comment";

    const ERROR_ORDER_NOT_COMPLETED_CODE = 400003;
    const ERROR_ORDER_NOT_COMPLETED_MESSAGE = 'The order has not been completed';

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="building",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    requirements="\d+",
     *    strict=true,
     *    description="Building id"
     * )
     *
     * @Route("/evaluations/check_my")
     * @Method({"GET"})
     *
     * @return View
     */
    public function checkMyBuildingEvaluationAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        if (!$this->isAuthProvided()) {
            return new View(array(
                'able_to_create_building_evaluation' => false,
            ));
        }

        $buildingId = $paramFetcher->get('building');

        $em = $this->getDoctrine()->getManager();

        $building = $em->getRepository('SandboxApiBundle:Room\RoomBuilding')->find($buildingId);
        $this->throwNotFoundIfNull($building, self::NOT_FOUND_MESSAGE);
        $lastEvaluation = $em->getRepository('SandboxApiBundle:Evaluation\Evaluation')
            ->findOneBy(
                array(
                    'userId' => $this->getUserId(),
                    'buildingId' => $buildingId,
                    'type' => Evaluation::TYPE_BUILDING,
                ),
                array('creationDate' => 'DESC')
            );

        $ableToCreateBuildingEvaluation = true;

        if ($lastEvaluation) {
            $diff = date_diff(new \DateTime('now'), $lastEvaluation->getCreationDate());
            if ($diff->format('%m') < 1) {
                $ableToCreateBuildingEvaluation = false;
            }
        }

        return new View(array(
            'able_to_create_building_evaluation' => $ableToCreateBuildingEvaluation,
            'evaluation' => $this->buildDataConstruct($lastEvaluation),
        ));
    }

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
     * @Annotations\QueryParam(
     *    name="building",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    requirements="\d+",
     *    strict=true,
     *    description="Building id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="min_star",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="min star"
     * )
     *
     * @Annotations\QueryParam(
     *    name="max_star",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="max star"
     * )
     *
     * @Annotations\QueryParam(
     *    name="with_pic",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="with picture"
     * )
     *
     * @Route("/evaluations")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getEvaluationAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $minStar = $paramFetcher->get('min_star');
        $maxStar = $paramFetcher->get('max_star');
        $buildingId = $paramFetcher->get('building');
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');
        $isWithPic = $paramFetcher->get('with_pic');

        $evaluations = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Evaluation\Evaluation')
            ->getClientEvaluations(
                $limit,
                $offset,
                $buildingId,
                null,
                $minStar,
                $maxStar,
                $isWithPic
            );

        $response = array();
        foreach ($evaluations as $evaluation) {
            $data = $this->buildDataConstruct($evaluation);

            array_push($response, $data);
        }

        return new View($response);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="order_id",
     *     array=false,
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
     * @Route("/evaluations/my")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMyEvaluationsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();
        $orderId = $paramFetcher->get('order_id');

        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $evaluations = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Evaluation\Evaluation')
            ->getClientEvaluations(
                $limit,
                $offset,
                null,
                $userId,
                null,
                null,
                null,
                $orderId
            );

        $response = array();
        foreach ($evaluations as $evaluation) {
            $data = $this->buildDataConstruct($evaluation);

            array_push($response, $data);
        }

        return new View($response);
    }

    /**
     * @param Request $request
     *
     * @Route("/evaluation")
     * @Method({"POST"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function postEvaluationAction(
        Request $request
    ) {
        $evaluation = new Evaluation();

        $form = $this->createForm(new EvaluationPostType(), $evaluation);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return $this->handleEvaluationPost(
            $evaluation
        );
    }

    /**
     * @param $evaluation
     *
     * @return View
     */
    private function handleEvaluationPost(
        $evaluation
    ) {
        $em = $this->getDoctrine()->getManager();
        $now = new \DateTime('now');

        $type = $evaluation->getType();
        $attachments = $evaluation->getAttachments();
        $buildingId = $evaluation->getBuildingId();
        $productOrderId = $evaluation->getProductOrderId();

        switch ($type) {
            case Evaluation::TYPE_BUILDING:
                $building = $em->getRepository('SandboxApiBundle:Room\RoomBuilding')->find($buildingId);
                $this->throwNotFoundIfNull($building, self::NOT_FOUND_MESSAGE);
                $lastEvaluation = $em->getRepository('SandboxApiBundle:Evaluation\Evaluation')
                    ->findOneBy(
                        array(
                            'userId' => $this->getUserId(),
                            'buildingId' => $buildingId,
                            'type' => Evaluation::TYPE_BUILDING,
                        ),
                        array('creationDate' => 'DESC')
                    );

                if ($lastEvaluation) {
                    $diff = date_diff(new \DateTime('now'), $lastEvaluation->getCreationDate());
                    if ($diff->format('%m') < 1) {
                        return $this->customErrorView(
                            400,
                            self::ERROR_EVALUATION_REPEAT_COMMENT_CODE,
                            self::ERROR_EVALUATION_REPEAT_COMMENT_MESSAGE
                        );
                    }
                }

                $building->setBuildingEvaluationNumber($building->getBuildingEvaluationNumber() + 1);
                break;
            case Evaluation::TYPE_ORDER:
                if (is_null($productOrderId)) {
                    throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
                }

                $productOrder = $em->getRepository('SandboxApiBundle:Order\ProductOrder')->find($productOrderId);
                $this->throwNotFoundIfNull($productOrder, self::NOT_FOUND_MESSAGE);
                $building = $productOrder->getProduct()->getRoom()->getBuilding();

                if ($productOrder->getStatus() != ProductOrder::STATUS_COMPLETED) {
                    return $this->customErrorView(
                        400,
                        self::ERROR_ORDER_NOT_COMPLETED_CODE,
                        self::ERROR_ORDER_NOT_COMPLETED_MESSAGE
                    );
                }

                $checkEvaluation = $em->getRepository('SandboxApiBundle:Evaluation\Evaluation')
                    ->checkEvaluation(
                        $this->getUserId(),
                        Evaluation::TYPE_ORDER,
                        $building,
                        $productOrderId
                    );

                if (!empty($checkEvaluation)) {
                    return $this->customErrorView(
                        400,
                        self::ERROR_ORDER_HAS_COMMENTS_CODE,
                        self::ERROR_ORDER_HAS_COMMENTS_MESSAGE
                    );
                }

                $evaluation->setProductOrder($productOrder);
                $building->setOrderEvaluationNumber($building->getOrderEvaluationNumber() + 1);
                break;
            default:
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $user = $em->getRepository('SandboxApiBundle:User\User')->find($this->getUserId());
        $evaluation->setBuilding($building);
        $evaluation->setUser($user);
        $evaluation->setCreationDate($now);
        $em->persist($evaluation);

        $this->addEvaluationAttachments(
            $evaluation,
            $attachments
        );

        $em->flush();

        $response = array(
            'id' => $evaluation->getId(),
        );

        return new View($response);
    }

    /**
     * @param $evaluation
     * @param $attachments
     */
    private function addEvaluationAttachments(
        $evaluation,
        $attachments
    ) {
        $em = $this->getDoctrine()->getManager();

        if (!is_null($attachments) && !empty($attachments)) {
            foreach ($attachments as $attachment) {
                $evaluationAttachemnt = new EvaluationAttachment();
                $evaluationAttachemnt->setEvaluation($evaluation);
                $evaluationAttachemnt->setContent($attachment['content']);
                $evaluationAttachemnt->setAttachmentType($attachment['attachment_type']);
                $evaluationAttachemnt->setFilename($attachment['filename']);
                $evaluationAttachemnt->setPreview($attachment['preview']);
                $evaluationAttachemnt->setSize($attachment['size']);
                $em->persist($evaluationAttachemnt);
            }
        }
    }

    /**
     * @param Evaluation $evaluation
     *
     * @return array
     */
    private function buildDataConstruct(
        $evaluation
    ) {
        if (is_null($evaluation)) {
            return null;
        }

        $userProfile = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserProfile')
            ->findOneBy(array(
                'user' => $evaluation->getUser(),
            ));
        $userName = !is_null($userProfile) ? $userProfile->getName() : null;

        $building = $evaluation->getBuilding();
        $buildingCity = $building->getCity()->getName();
        $buildingDistrict = $building->getDistrict() ? $building->getDistrict()->getName() : null;

        $attachments = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuildingAttachment')
            ->findOneBy(array('building' => $building->getId()));

        $productOrder = $evaluation->getProductOrder();

        $orderId = null;
        $productOrderRoomName = null;
        $roomType = null;
        $roomAttachment = null;
        if ($productOrder) {
            $orderId = $productOrder->getId();
            $roomId = $productOrder->getProduct()->getRoom()->getId();
            $productOrderRoomName = $productOrder->getProduct()->getRoom()->getName();

            $type = $productOrder->getProduct()->getRoom()->getType();
            $roomType = $this->get('translator')->trans(ProductOrderExport::TRANS_ROOM_TYPE.$type);

            $roomAttachmentBinding = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomAttachmentBinding')
                ->findOneBy(array('room' => $roomId));

            $roomAttachment = $roomAttachmentBinding ? $roomAttachmentBinding->getAttachmentId()->getContent() : null;
        }

        $data = [
            'id' => $evaluation->getId(),
            'type' => $evaluation->getType(),
            'total_star' => $evaluation->getTotalStar(),
            'comment' => $evaluation->getComment(),
            'user' => [
                'id' => $evaluation->getUser()->getId(),
                'name' => $userName,
            ],
            'creation_date' => $evaluation->getCreationDate(),
            'building_id' => $building->getId(),
            'building_name' => $building->getName(),
            'building_city' => $buildingCity.' '.$buildingDistrict,
            'building_attachment' => $attachments ? $attachments->getContent() : null,
            'order_id' => $orderId,
            'room_name' => $productOrderRoomName,
            'room_type' => $roomType,
            'room_attachment' => $roomAttachment,

        ];

        $attachments = $evaluation->getEvaluationAttachments();
        $attachmentsArray = array();
        foreach ($attachments as $attachment) {
            array_push($attachmentsArray, array(
                'content' => $attachment->getContent(),
                'attachment_type' => $attachment->getAttachmentType(),
                'filename' => $attachment->getFilename(),
                'size' => $attachment->getSize(),
            ));
        }

        $data = array_merge($data, array('evaluation_attachments' => $attachmentsArray));

        return $data;
    }
}
