<?php

namespace Sandbox\ClientApiBundle\Controller\Evaluation;

use Sandbox\ApiBundle\Controller\Evaluation\EvaluationController;
use Sandbox\ApiBundle\Entity\Evaluation\Evaluation;
use Sandbox\ApiBundle\Entity\Evaluation\EvaluationAttachment;
use Sandbox\ApiBundle\Form\Evaluation\EvaluationPostType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
    const ERROR_ORDER_HAS_COMMENTS_MESSAGE = 'The order has comments';

    const ERROR_EVALUATION_REPEAT_COMMENT_CODE = 400002;
    const ERROR_EVALUATION_REPEAT_COMMENT_MESSAGE = "A month can't repeat comment";

    /**
     * @param Request $request
     *
     * @Route("evaluation")
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

        $building = $em->getRepository('SandboxApiBundle:Room\RoomBuilding')->find($buildingId);
        $this->throwNotFoundIfNull($building, self::NOT_FOUND_MESSAGE);

        switch ($type) {
            case Evaluation::TYPE_BUILDING :
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

                break;
            case Evaluation::TYPE_ORDER :
                if (is_null($productOrderId)) {
                    throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
                }

                $checkEvaluation = $em->getRepository('SandboxApiBundle:Evaluation\Evaluation')
                    ->checkEvaluation(
                        $this->getUserId(),
                        Evaluation::TYPE_ORDER,
                        $buildingId,
                        $productOrderId
                    );

                if (!is_null($checkEvaluation)) {
                    return $this->customErrorView(
                        400,
                        self::ERROR_ORDER_HAS_COMMENTS_CODE,
                        self::ERROR_ORDER_HAS_COMMENTS_MESSAGE
                    );
                }
                $productOrder = $em->getRepository('SandboxApiBundle:Order\ProductOrder')->find($productOrderId);
                $this->throwNotFoundIfNull($productOrder, self::NOT_FOUND_MESSAGE);
                $evaluation->setProductOrder($productOrder);
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
}
