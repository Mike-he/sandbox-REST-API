<?php

namespace Sandbox\AdminApiBundle\Controller\Admin;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\Admin;
use Sandbox\ApiBundle\Entity\Admin\AdminPosition;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use Knp\Component\Pager\Paginator;

/**
 * Admin controller.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang <yimo.zhang@Sandbox.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminAdminsController extends SandboxRestController
{
    /**
     * List all admins.
     *
     * @param Request $request the request object
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Annotations\QueryParam(
     *    name="search",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="name or username"
     * )
     *
     * @Annotations\QueryParam(
     *    name="platform",
     *    array=false,
     *    nullable=false,
     *    strict=true,
     *    description="platform"
     * )
     *
     * @Annotations\QueryParam(
     *    name="isSuperAdmin",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="isSuperAdmin"
     * )
     *
     * @Annotations\QueryParam(
     *    name="company",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="sales admin company"
     * )
     *
     * @Annotations\QueryParam(
     *    name="building",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="building"
     * )
     *
     * @Annotations\QueryParam(
     *    name="shop",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="shop"
     * )
     *
     * @Annotations\QueryParam(
     *    name="position",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="position"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many admins to return "
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
     * @Method({"GET"})
     * @Route("/admins")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdminsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission

        $platform = $paramFetcher->get('platform');
        $isSuperAdmin = $paramFetcher->get('isSuperAdmin');
        $companyId = $paramFetcher->get('company');
        $buildingId = $paramFetcher->get('building');
        $shopId = $paramFetcher->get('shop');
        $position = $paramFetcher->get('position');
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $search = $paramFetcher->get('search');

        $positionIds = is_null($position) ? null : explode(',', $position);

        $positions = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPosition')
            ->getPositions(
                $platform,
                $companyId,
                $isSuperAdmin,
                $positionIds
            );

        $userIds = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
            ->getBindUser(
                $positions,
                $buildingId,
                $shopId,
                $search
            );

        $result = array();
        foreach ($userIds as $userId) {
            $positionBinds = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                ->getBindUserInfo(
                    $userId['userId'],
                    $platform,
                    $companyId
                );

            $positionArr = array();
            foreach ($positionBinds as $positionBind) {
                $positionArr[] = $positionBind->getPosition();
            }

            $buildingArr = array();
            if ($platform == AdminPosition::PLATFORM_SALES || $platform == AdminPosition::PLATFORM_SHOP) {
                $buildingBinds = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                    ->getBindBuilding(
                        $userId['userId'],
                        $platform,
                        $companyId
                    );

                foreach ($buildingBinds as $buildingBind) {
                    $buildingInfo = $this->getDoctrine()->getRepository("SandboxApiBundle:Room\RoomBuilding")
                        ->find($buildingBind['buildingId']);
                    $buildingArr[] = $buildingInfo;
                }
            }

            $user = $this->getDoctrine()->getRepository('SandboxApiBundle:User\UserView')->find($userId['userId']);

            $result[] = array(
                'user_id' => $userId['userId'],
                'user' => $user,
                'position' => $positionArr,
                'building' => $buildingArr,
            );
        }

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $result,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * Get Bilding Position by Company.
     *
     * @param Request $request    the request object
     * @param int     $company_id
     *
     *
     * @Method({"GET"})
     * @Route("/admins/company/{company_id}")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdminBuilding(
        Request $request,
        $company_id
    ) {
        $myBuildings = $this->getDoctrine()->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->getCompanyBuildings($company_id);

        $result = array();
        foreach ($myBuildings as $myBuilding) {
            $userCounts = $this->getDoctrine()->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                ->countBuildingUser($myBuilding);

            $positions = $this->getDoctrine()->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                ->getBuildingPosition($myBuilding);

            $positionArr = array();
            foreach ($positions as $position) {
                $userCount = $this->getDoctrine()->getRepository('SandboxApiBundle:Admin\AdminPositionUserBinding')
                    ->countBuildingUser($myBuilding, $position->getPosition());
                $positionArr[] = array(
                    'position' => $position->getPosition(),
                    'count' => $userCount,
                );
            }

            $result[] = array(
                'count' => $userCounts,
                'building' => $myBuilding,
                'position' => $positionArr,
            );
        }

        return new View($result);
    }
}
