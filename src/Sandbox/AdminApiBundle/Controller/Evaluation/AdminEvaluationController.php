<?php

namespace Sandbox\AdminApiBundle\Controller\Evaluation;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Knp\Component\Pager\Paginator;
use FOS\RestBundle\Controller\Annotations;
use Rs\Json\Patch;
use Sandbox\AdminApiBundle\Command\CalculateStarCommand;
use Sandbox\ApiBundle\Controller\Evaluation\EvaluationController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Evaluation\Evaluation;
use Sandbox\ApiBundle\Form\Evaluation\EvaluationPatchType;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
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
     * @Route("/evaluation/scores")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getAdminEvaluationScoresAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminEvaluationPermission(AdminPermission::OP_LEVEL_VIEW);

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
        $officialEvaluationCount = count($evaluation);

        return new View(array(
            'total_evaluation_count' => $orderEvaluationCount + $buildingEvaluationCount + $officialEvaluationCount,
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
        // check user permission
        $this->checkAdminEvaluationPermission(AdminPermission::OP_LEVEL_VIEW);

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
     * Create A Official Evaluation.
     *
     * @param Request $request
     *
     * @Route("/evaluation/official")
     * @Method({"POST"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function postOfficialEvaliation(
        Request $request
    ) {
        // check user permission
        $this->checkAdminEvaluationPermission(AdminPermission::OP_LEVEL_EDIT);

        $em = $this->getDoctrine()->getManager();
        $data = json_decode($request->getContent(), true);

        $evaluation = $em->getRepository('SandboxApiBundle:Evaluation\Evaluation')
            ->findOneBy(
                array(
                    'type' => Evaluation::TYPE_OFFICIAL,
                    'buildingId' => $data['building_id'],
                )
            );
        if (!$evaluation) {
            $now = $now = new \DateTime('now');
            $building = $em->getRepository('SandboxApiBundle:Room\RoomBuilding')->find($data['building_id']);
            $this->throwNotFoundIfNull($building, self::NOT_FOUND_MESSAGE);
            $user = $em->getRepository('SandboxApiBundle:User\User')->find($this->getUserId());

            $evaluation = new Evaluation();
            $evaluation->setUser($user);
            $evaluation->setType(Evaluation::TYPE_OFFICIAL);
            $evaluation->setBuilding($building);
            $evaluation->setEnvironmentStar(0);
            $evaluation->setServiceStar(0);
            $evaluation->setCreationDate($now);
        }
        $evaluation->setTotalStar($data['official_evaluation_star']);
        $em->persist($evaluation);

        $em->flush();

        //execute CalculateStarCommand
        $command = new CalculateStarCommand();
        $command->setContainer($this->container);

        $input = new ArrayInput(array());
        $output = new NullOutput();

        $command->run($input, $output);
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
        // check user permission
        $this->checkAdminEvaluationPermission(AdminPermission::OP_LEVEL_EDIT);

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
     * @param $opLevel
     */
    private function checkAdminEvaluationPermission(
        $opLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_SALES],
            ],
            $opLevel
        );
    }
}
