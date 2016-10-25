<?php

namespace Sandbox\AdminApiBundle\Controller\Evaluation;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Knp\Component\Pager\Paginator;
use FOS\RestBundle\Controller\Annotations;
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
     * @param Request $request
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
     * @Route("/evaluation/scores")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getAdminEvaluationScoresAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $buildingId = $paramFetcher->get('building');

        // get official star
        $evaluation = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Evaluation\Evaluation')
            ->findOneBy(array(
                'type' => Evaluation::TYPE_OFFICIAL,
                'buildingId' => $buildingId,
            ));

        // get total evaluation star
        $building = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->find($buildingId);

        if (is_null($building)) {
            throw new BadRequestHttpException(self::NOT_FOUND_MESSAGE);
        }

        if (is_null($evaluation)) {
            $officialStar = null;
        } else {
            $officialStar = $evaluation->getTotalStar();
        }

        $orderEvaluationCount = $building->getOrderEvaluationNumber();
        $buildingEvaluationCount = $building->getBuildingEvaluationNumber();

        return new View(array(
            'total_evaluation_count' => $orderEvaluationCount + $buildingEvaluationCount + 1,
            'total_evaluation_star' => $building->getEvaluationStar(),
            'official_evaluation_star' => $officialStar,
            'order_evaluation_count' => $orderEvaluationCount,
            'order_evaluation_star' => $building->getOrderStar(),
            'building_evaluation_count' => $buildingEvaluationCount,
            'building_evaluation_star' => $building->getBuildingStar(),
        ));
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="10",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="limit for page"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageIndex",
     *    array=false,
     *    default="1",
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
     *    requirements="\d+",
     *    strict=true,
     *    description="min star"
     * )
     *
     * @Annotations\QueryParam(
     *    name="max_star",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
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
     * @Annotations\QueryParam(
     *    name="with_comment",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="with comment"
     * )
     *
     * @Annotations\QueryParam(
     *    name="type",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="evaluation type"
     * )
     *
     * @Annotations\QueryParam(
     *    name="visible",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="evaluation visible"
     * )
     *
     * @Annotations\QueryParam(
     *    name="user_profile_name",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="user profile name"
     * )
     *
     * @Annotations\QueryParam(
     *    name="username",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="username"
     * )
     *
     * @Annotations\QueryParam(
     *    name="sort_by",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="username"
     * )
     *
     * @Annotations\QueryParam(
     *    name="sort_direction",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="username"
     * )
     *
     * @Route("/evaluations")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getEvaluationsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userProfileName = $paramFetcher->get('user_profile_name');
        $username = $paramFetcher->get('username');
        $minStar = $paramFetcher->get('min_star');
        $maxStar = $paramFetcher->get('max_star');
        $buildingId = $paramFetcher->get('building');
        $isWithPic = $paramFetcher->get('with_pic');
        $isWithComment = $paramFetcher->get('with_comment');
        $type = $paramFetcher->get('type');
        $visible = $paramFetcher->get('visible');
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $sortBy = $paramFetcher->get('sort_by');
        $sortDirection = $paramFetcher->get('sort_direction');

        $evaluationsQuery = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Evaluation\Evaluation')
            ->getAdminEvaluations(
                $userProfileName,
                $username,
                $buildingId,
                $minStar,
                $maxStar,
                $isWithPic,
                $isWithComment,
                $type,
                $visible,
                $sortBy,
                $sortDirection
            );

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $evaluationsQuery,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

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
