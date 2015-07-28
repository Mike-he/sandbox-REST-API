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
    const ERROR_BUILDING_NOT_SET_CODE = 400001;
    const ERROR_BUILDING_NOT_SET_MESSAGE = 'Building is not set - 未设置办公楼';

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
        // if user is not authorized, respond empty list
        $cardNo = $this->getCardNoIfUserAuthorized();
        if (is_null($cardNo)) {
            return new View(array());
        }

        $userId = $this->getUserId();
        $clientId = $this->getUser()->getClientId();

        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        // get globals
        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        // set max limit
        if ($limit > $globals['load_more_limit']) {
            $limit = $globals['load_more_limit'];
        }

        $em = $this->getDoctrine()->getManager();

        // get user's retrieved member IDs if any
        $myRecords = $this->getRepo('Member\ClientMemberRecommendRandomRecord')
            ->findBy(array(
                'userId' => $userId,
                'clientId' => $clientId,
            ));

        $recordMemberIds = array();

        foreach ($myRecords as $myRecord) {
            // if offset is not provided, means user is trying to reload the page
            // then we should remove user's retrieval records
            // otherwise, we should exclude these records for the next page
            if (is_null($offset) || $offset <= 0) {
                $em->remove($myRecord);
            } else {
                array_push($recordMemberIds, $myRecord->getMemberId());
            }
        }

        // find random members
        $users = $this->getRepo('User\User')->findRandomMembers(
            $recordMemberIds,
            $limit
        );
        if (is_null($users) || empty($users)) {
            return new View(array());
        }

        // members for response
        $members = array();

        foreach ($users as $user) {
            $memberId = $user->getId();

            // add user's retrieval record
            $randomRecord = new ClientMemberRecommendRandomRecord();
            $randomRecord->setUserId($userId);
            $randomRecord->setClientId($clientId);
            $randomRecord->setMemberId($memberId);
            $em->persist($randomRecord);

            // set profile
            $profile = $this->getRepo('User\UserProfile')->findOneByUserId($memberId);

            // set company info
            $company = $this->getCompanyIfMember($memberId);

            $member = array(
                'id' => $memberId,
                'profile' => $profile,
                'company' => $company,
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
        // if user is not authorized, respond empty list
        $cardNo = $this->getCardNoIfUserAuthorized();
        if (is_null($cardNo)) {
            return new View(array());
        }

        $userId = $this->getUserId();

        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        // get globals
        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        // set max limit
        if ($limit > $globals['load_more_limit']) {
            $limit = $globals['load_more_limit'];
        }

        // get my profile
        $myProfile = $this->getRepo('User\UserProfile')->findOneByUserId($userId);
        $this->throwNotFoundIfNull($myProfile, self::NOT_FOUND_MESSAGE);

        // TODO change to $myProfile->getBuilding()
        // for some reason, now this is not working in my local environment
        //var_dump($myProfile->getBuilding());

        // get my building
        $buildingId = $myProfile->getBuildingId();
        if (is_null($buildingId)) {
            return $this->customErrorView(
                400,
                self::ERROR_BUILDING_NOT_SET_CODE,
                self::ERROR_BUILDING_NOT_SET_MESSAGE
            );
        }

        // get my profile
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
        if (is_null($users) || empty($users)) {
            return new View(array());
        }

        // members for response
        $members = array();

        foreach ($users as $user) {
            $memberId = $user->getId();

            $profile = $this->getRepo('User\UserProfile')->findOneByUserId($memberId);

            // set company info
            $company = $this->getCompanyIfMember($memberId);

            $member = array(
                'id' => $memberId,
                'profile' => $profile,
                'company' => $company,
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
     * @Annotations\QueryParam(
     *    name="last_id",
     *    array=false,
     *    default="0",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="the id to start after"
     * )
     *
     * @Route("/members/visitor")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMembersVisitorAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // if user is not authorized, respond empty list
        $cardNo = $this->getCardNoIfUserAuthorized();
        if (is_null($cardNo)) {
            return new View(array());
        }

        $userId = $this->getUserId();

        // get params
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');
        $lastId = $paramFetcher->get('last_id');

        // get globals
        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        // set max limit
        if ($limit > $globals['load_more_limit']) {
            $limit = $globals['load_more_limit'];
        }

        // find my visitors
        $visitors = $this->getRepo('User\UserProfileVisitor')->findAllMyVisitors(
            $userId,
            $limit,
            $offset,
            $lastId
        );
        if (is_null($visitors) || empty($visitors)) {
            return new View(array());
        }

        // members for response
        $members = array();

        foreach ($visitors as $visitor) {
            $visitorId = $visitor->getVisitorId();

            $profile = $this->getRepo('User\UserProfile')->findOneByUserId($visitorId);

            // set company info
            $company = $this->getCompanyIfMember($visitorId);

            $member = array(
                'id' => $visitor->getId(),
                'profile' => $profile,
                'company' => $company,
                'visit_date' => $visitor->getCreationDate(),
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
    public function getMembersSearchAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // if user is not authorized, respond empty list
        $cardNo = $this->getCardNoIfUserAuthorized();
        if (is_null($cardNo)) {
            return new View(array());
        }

        $query = $paramFetcher->get('query');
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        // find all members who have the query in any of their mapped fields
        $finder = $this->container->get('fos_elastica.finder.search.member');

        $results = $finder->find($query);
        if (is_null($results) || empty($results)) {
            return new View(array());
        }

        $profiles = $output = array_slice($results, $offset, $limit);

        // members for response
        $members = array();

        foreach ($profiles as $profile) {
            $userId = $profile->getUserId();

            // set company info
            $company = $this->getCompanyIfMember($userId);

            $member = array(
                'id' => $userId,
                'profile' => $profile,
                'company' => $company,
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
}
