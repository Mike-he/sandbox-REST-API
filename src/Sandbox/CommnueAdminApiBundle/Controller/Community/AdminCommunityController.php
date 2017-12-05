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
     * @Route("/communities")
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
     * @Route("/communities/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getCommnuitiesByIdAction(
        $id
    ) {
        $community = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->getCommnueRoomBuildingsById($id);

        $this->throwNotFoundIfNull($community, self::NOT_FOUND_MESSAGE);

        return new View($community);
    }

    /**
     * Certify Community
     *
     * @param Request $request
     * @param $id
     *
     * @Route("/communities/{id}/certify")
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
     * @Route("/communities/{id}/freezon")
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
        $community = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Room\RoomBuilding')
            ->find($id);

        $this->throwNotFoundIfNull($community, self::NOT_FOUND_MESSAGE);

        $community->setCommnueStatus(RoomBuilding::FREEZON);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }
}