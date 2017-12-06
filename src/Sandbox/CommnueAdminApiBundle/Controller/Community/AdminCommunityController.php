<?php

namespace Sandbox\CommnueAdminApiBundle\Controller\Community;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Controller\Location\LocationController;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Parameter\Parameter;
use Sandbox\ApiBundle\Entity\Room\CommnueBuildingHot;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\Annotations;
use Knp\Component\Pager\Paginator;
use Symfony\Component\HttpFoundation\Request;

class AdminCommunityController extends LocationController
{
    const ERROR_NOT_ALLOWED_ADD_CODE = 400001;
    const ERROR_NOT_ALLOWED_ADD_MESSAGE = 'More than the allowed number of hits';

    /**
     * GET Communties List
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many communities to return"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageIndex",
     *    array=false,
     *    default="1",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="page number"
     * )
     *
     * @Annotations\QueryParam(
     *    name="city",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="city id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="commnue_status",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="community category"
     * )
     *
     * @Annotations\QueryParam(
     *    name="search",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="query key word"
     * )
     *
     * @Route("/community")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getCommunitiesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminCommunityPermission(AdminPermission::OP_LEVEL_VIEW);

        $pageIndex = $paramFetcher->get('pageIndex');
        $pageLimit = $paramFetcher->get('pageLimit');
        $city = $paramFetcher->get('city');
        $commnueStatus = $paramFetcher->get('commnue_status');
        $search = $paramFetcher->get('search');

        $communitise = $this->getDoctrine()->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->getAllCommnueRoomBuildings(
                $commnueStatus,
                $search
            );

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $communitise,
            $pageIndex,
            $pageLimit
        );

        return new View( $pagination);
    }

    /**
     * Get Community By Id
     *
     * @param $id
     *
     * @Route("/community/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getCommnuitiesByIdAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminCommunityPermission(AdminPermission::OP_LEVEL_VIEW);

        $community = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->find($id);

        $this->throwNotFoundIfNull($community, self::NOT_FOUND_MESSAGE);

        $buildingCompany = $this->getRepo('Room\RoomBuildingCompany')->findOneByBuilding($community);
        $phone = $buildingCompany->getPhone();

        $company = $community->getCompany();
        $contactPhone = $company->getContacterPhone();

        $result = [];
        $result['name'] = $community->getName();
        $result['address'] = $community->getAddress();
        $result['phone'] = $phone;
        $result['contacter'] = $community->getCommunityManagerName();
        $result['contacterPhone'] = $contactPhone;
        $result['contacterEmail'] = $community->getEmail();

        return new View($result);
    }

    /**
     * Certify Community
     *
     * @param Request $request
     * @param $id
     *
     * @Route("/community/{id}/certify")
     * @Method({"PATCH"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function certifyCommunitiesAction(
        Request $request,
        $id
    ) {
        // check user permission
        //$this->checkAdminCommunityPermission(AdminPermission::OP_LEVEL_EDIT);

        $community = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->find($id);

        $this->throwNotFoundIfNull($community, self::NOT_FOUND_MESSAGE);

        $commnueStatus = $community->getCommnueStatus();
        if($commnueStatus == RoomBuilding::FREEZON){
            $this->customErrorView(
                400,
                self::WRONG_CERTIFY_CODE,
                self::WRONG_CERTIFY_MESSAGE
            );
        }
        $community->setCommnueStatus(RoomBuilding::CERTIFIED);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * Freezon Community
     *
     * @param Request $request
     * @param $id
     *
     * @Route("/community/{id}/freezon")
     * @Method({"PATCH"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function freezonCommunityAction(
        Request $request,
        $id
    ) {
        // check user permission
       // $this->checkAdminCommunityPermission(AdminPermission::OP_LEVEL_EDIT);

        $community = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->find($id);

        $this->throwNotFoundIfNull($community, self::NOT_FOUND_MESSAGE);

        $community->setCommnueStatus(RoomBuilding::FREEZON);

        $em = $this->getDoctrine()->getManager();
        $hot = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\CommnueBuildingHot')
            ->findOneBy(array(
                'buildingId'=>$id
            ));
        if(!is_null($hot)){
            $em->remove($hot);
        }

        $em->flush();

        return new View();
    }

    /**
     * Set Hot Community
     *
     * @param Request $request
     * @param $id
     *
     * @Route("/community/hot/{id}")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postHotCommunityAction(
        Request $request,
        $id
    )
    {
        // check user permission
        $this->checkAdminCommunityPermission(AdminPermission::OP_LEVEL_EDIT);

        $em = $this->getDoctrine()->getManager();

        $count = $em->getRepository('SandboxApiBundle:Room\CommnueBuildingHot')->countHots();

        $parameter = $em->getRepository('SandboxApiBundle:Parameter\Parameter')
            ->findOneBy(array('key' => Parameter::KEY_COMMNUE_BUILDING_HOT));

        $allowNumber = $parameter ? (int)$parameter->getValue() : 3;

        if ($count >= $allowNumber) {
            return $this->customErrorView(
                400,
                self::ERROR_NOT_ALLOWED_ADD_CODE,
                self::ERROR_NOT_ALLOWED_ADD_MESSAGE
            );
        }

        $hot = new CommnueBuildingHot();
        $hot->setBuildingId($id);
        $em->persist($hot);

        $em->flush();

        return new View(null, 201);
    }

    /**
     * Get Hot Communities Counts
     *
     * @Route("/community/hot/counts")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getHotCommunityCountAction()
    {
        // check user permission
        $this->checkAdminCommunityPermission(AdminPermission::OP_LEVEL_VIEW);

        $count = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\CommnueBuildingHot')
            ->countHots();

        $parameter = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Parameter\Parameter')
            ->findOneBy(array('key' => Parameter::KEY_COMMNUE_BUILDING_HOT));

        $allowNumber = $parameter ? (int) $parameter->getValue() : 3;

        $result = [
            'max_allow_number' => $allowNumber,
            'count' => $count,
        ];

        return new View($result);
    }

    private function setBuildingInfo(
        $building,
        $request
    ) {
        $phones = $this->getRepo('Room\RoomBuildingPhones')->findByBuilding($building);
        $building->setPhones($phones);



        // set room counts
        $roomCounts = $this->getRepo('Room\Room')->countsRoomByBuilding($building);
        $building->setRoomCounts((int) $roomCounts);

        return $building;
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     */
    private function checkAdminCommunityPermission(
        $opLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_COMMNUE_PLATFORM_COMMUNITY],
            ],
            $opLevel
        );
    }

}