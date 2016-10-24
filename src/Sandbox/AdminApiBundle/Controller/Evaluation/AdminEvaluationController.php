<?php

namespace Sandbox\AdminApiBundle\Controller\Evaluation;

use Rs\Json\Patch;
use Sandbox\ApiBundle\Controller\Evaluation\EvaluationController;
use Sandbox\ApiBundle\Entity\Evaluation\Evaluation;
use Sandbox\ApiBundle\Entity\Evaluation\EvaluationAttachment;
use Sandbox\ApiBundle\Form\Evaluation\EvaluationPatchType;
use Sandbox\ApiBundle\Form\Evaluation\EvaluationPostType;
use Sandbox\ApiBundle\Form\Evaluation\EvaluationPutType;
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
class AdminEvaluationController extends EvaluationController
{
    const ERROR_BUILDING_HAS_COMMENTS_CODE = 400001;
    const ERROR_BUILDING_HAS_COMMENTS_MESSAGE = 'This building has been comments';

    const ERROR_BUILDING_NOT_MATCH_CODE = 400002;
    const ERROR_BUILDING_NOT_MATCH__MESSAGE = 'This building is not match';

    /**
     * Create A Evaluation.
     *
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
     * Update Evaluation.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("/evaluation/{id}")
     * @Method({"PUT"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function putEvaluationAction(
        Request $request,
        $id
    ) {
        $evaluation = $this->getDoctrine()->getRepository('SandboxApiBundle:Evaluation\Evaluation')->find($id);
        $this->throwNotFoundIfNull($evaluation, self::NOT_FOUND_MESSAGE);
        $buildingId = $evaluation->getBuildingId();

        $form = $this->createForm(
            new EvaluationPutType(),
            $evaluation,
            array(
                'method' => 'PUT',
            )
        );
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        if ($buildingId != $evaluation->getBuildingId()) {
            return $this->customErrorView(
                400,
                self::ERROR_BUILDING_NOT_MATCH_CODE,
                self::ERROR_BUILDING_NOT_MATCH__MESSAGE
            );
        }

        return $this->handleEvaluationPut(
            $evaluation
        );
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Route("/evaluation/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function patchEvaluationAction(
        Request $request,
        $id
    ) {
        $evaluation = $this->getDoctrine()->getRepository('SandboxApiBundle:Evaluation\Evaluation')->find($id);
        $this->throwNotFoundIfNull($evaluation, self::NOT_FOUND_MESSAGE);

        $evaluationJson = $this->container->get('serializer')->serialize($evaluation, 'json');
        $patch = new Patch($evaluationJson, $request->getContent());
        $evaluationJson = $patch->apply();

        $form = $this->createForm(new EvaluationPatchType(), $evaluation);
        $form->submit(json_decode($evaluationJson, true));

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
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

        $building = $em->getRepository('SandboxApiBundle:Room\RoomBuilding')->find($buildingId);
        $this->throwNotFoundIfNull($building, self::NOT_FOUND_MESSAGE);

        if ($type == Evaluation::TYPE_OFFICIAL) {
            $lastEvaluation = $em->getRepository('SandboxApiBundle:Evaluation\Evaluation')
                ->findOneBy(
                    array(
                        'buildingId' => $buildingId,
                        'type' => Evaluation::TYPE_OFFICIAL,
                    )
                );

            if ($lastEvaluation) {
                return $this->customErrorView(
                    400,
                    self::ERROR_BUILDING_HAS_COMMENTS_CODE,
                    self::ERROR_BUILDING_HAS_COMMENTS_MESSAGE
                );
            }
        } else {
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
     * @param $evaluation
     *
     * @return View
     */
    private function handleEvaluationPut(
        $evaluation
    ) {
        $em = $this->getDoctrine()->getManager();
        $attachments = $evaluation->getAttachments();

        $this->modifyEvaluationAttachments(
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
    private function modifyEvaluationAttachments(
        $evaluation,
        $attachments
    ) {
        $em = $this->getDoctrine()->getManager();

        $attachs = $this->getDoctrine()->getRepository('SandboxApiBundle:Evaluation\EvaluationAttachment')
            ->findBy(array('evaluationId' => $evaluation));
        foreach ($attachs as $attach) {
            $em->remove($attach);
        }

        $this->addEvaluationAttachments(
            $evaluation,
            $attachments
        );
    }
}
