<?php

namespace Sandbox\ClientApiBundle\Controller\Member;

use Sandbox\ApiBundle\Controller\Member\MemberController;
use Sandbox\ApiBundle\Entity\Member\ClientMemberRecommendRandomRecord;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\User\UserProfile;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\Serializer\SerializationContext;

/**
 * Rest controller for UserProfile.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientMemberController extends MemberController
{
    /**
     * Get recommend members.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
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
     * @Route("/members/recommend")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMembersRecommendAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();

        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $em = $this->getDoctrine()->getManager();

        $myRecords = $this->getRepo('Member\ClientMemberRecommendRandomRecord')
            ->findByUserId($userId);

        $recordMemberIds = array();

        foreach ($myRecords as $myRecord) {
            // if offset is not provided, means user is trying to reload the page
            // then we should remove user's retrieval records
            if (is_null($offset) || $offset <= 0) {
                $em->remove($myRecord);
            } else {
                array_push($recordMemberIds, $myRecord->getMemberId());
            }
        }

        $users = $this->getRepo('User\User')->findRandomMembers(
            $userId,
            $recordMemberIds,
            $limit
        );

        $members = array();

        foreach ($users as $user) {
            $memberId = $user->getId();

            // add user's retrieval record
            $randomRecord = new ClientMemberRecommendRandomRecord();
            $randomRecord->setUserId($userId);
            $randomRecord->setMemberId($memberId);
            $em->persist($randomRecord);

            // set profile
            $profile = $this->getRepo('User\UserProfile')->findOneByUserId($memberId);

            // TODO set company info

            $member = array(
                'id' => $memberId,
                'profile' => $profile,
                'company' => '',
            );

            array_push($members, $member);
        }

        $em->flush();

        // set view
        $view = new View($members);
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(array('member'))
        );

        return $view;
    }
    /**
     * Get nearby members.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
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
     * @Route("/members/nearby")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMembersNearbyAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();

        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        // get my profile
        $myProfile = $this->getRepo('User\UserProfile')->findOneByUserId($userId);
        $this->throwNotFoundIfNull($myProfile, self::NOT_FOUND_MESSAGE);

        // TODO change to $myProfile->getBuilding()
        // for some reason, now the mapping is not working in my local environment
        //var_dump($myProfile->getBuilding());

        // get my building
        $buildingId = $myProfile->getBuildingId();
        if (is_null($buildingId)) {
            // TODO return custom error: building not set
        }

        // get my profile)
        $myBuilding = $this->getRepo('Room\RoomBuilding')->findOneById($buildingId);
        $this->throwNotFoundIfNull($myBuilding, self::NOT_FOUND_MESSAGE);

        // find nearby members
        $users = $this->getRepo('User\User')->findNearbyMembers(
            $userId,
            $myBuilding->getLat(),
            $myBuilding->getLng(),
            $limit,
            $offset
        );

        $members = array();

        foreach ($users as $user) {
            $memberId = $user['id'];

            $profile = $this->getRepo('User\UserProfile')->findOneByUserId($memberId);

            // TODO set company info

            $member = array(
                'id' => $memberId,
                'profile' => $profile,
                'company' => '',
            );

            array_push($members, $member);
        }

        // set view
        $view = new View($members);
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(array('member'))
        );

        return $view;
    }

    /**
     * Get member who visited my profile.
     *
     * @param Request $request the request object
     *
     * @Route("/members/visitor")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMembersVisitorAction(
        Request $request
    ) {
        $visitors = $this->getRepo('User\UserProfileVisitor')->findAllMyVisitors(
            $this->getUserId()
        );

        $members = array();

        foreach ($visitors as $visitor) {
            $visitorId = $visitor->getVisitorId();

            $profile = $this->getRepo('User\UserProfile')->findOneByUserId($visitorId);

            // TODO set company info

            $member = array(
                'id' => $visitorId,
                'profile' => $profile,
                'company' => '',
            );

            array_push($members, $member);
        }

        // set view
        $view = new View($members);
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(array('member'))
        );

        return $view;
    }

    /**
     * Search members.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @Annotations\QueryParam(
     *    name="query",
     *    default=null,
     *    description="search query"
     * )
     *
     * @Route("/members/search")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getBuddiesSearchAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
    }
}
