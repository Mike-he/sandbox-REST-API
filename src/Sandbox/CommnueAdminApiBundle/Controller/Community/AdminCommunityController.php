<?php

namespace Sandbox\CommnueAdminApiBundle\Controller\Community;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Controller\Location\LocationController;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\Annotations;
use Knp\Component\Pager\Paginator;
use Symfony\Component\HttpFoundation\Request;

class AdminCommunityController extends LocationController
{
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
     *    name="category",
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
     * @Route("/community/communities")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getCommunitiesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $pageIndex = $paramFetcher->get('pageIndex');
        $pageLimit = $paramFetcher->get('pageLimit');
        $city = $paramFetcher->get('city');
        $category = $paramFetcher->get('category');
        $search = $paramFetcher->get('search');

        $communitise = $this->getDoctrine()->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->getAllRoomBuildings(
                $category,
                $search
            );

        $results = array();
        foreach($communitise as $community){
            $results[] = $this->getCommunitiyInfo($community);
        }

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $communitise,
            $pageIndex,
            $pageLimit
        );

        return new View( $pagination);
    }

    /**
     * @param RoomBuilding $community
     */
    private function getCommunitiyInfo(
        $community
    ) {
        $data = [];
        $buildingId = $community->getId();
        $buildingNum = $this->getRepo('SandboxApiBundle:Room\Room')
            ->countsRoomByBuilding($community);

        $isFreezon = $community->isFreezon();
        $isAuthentication = $community->isAuthentication();

        $data['status'] = '未认证';
        if($isAuthentication){
            $data['status'] = '已认证';
        }
        if($isFreezon){
            $data['status'] = '已冻结';
        }
    }

}