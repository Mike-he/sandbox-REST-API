<?php

namespace Sandbox\ClientApiBundle\Controller\Member;

use Sandbox\ApiBundle\Controller\Member\MemberController;
use Sandbox\ApiBundle\Entity\Random\ClientRandomRecord;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\User\UserProfile;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Entity\Buddy\Buddy;
use Sandbox\ApiBundle\Entity\Buddy\BuddyRequest;

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
    const ERROR_BUILDING_NOT_SET_MESSAGE = '您还未设置办公楼！';

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
        // get params
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        // get max limit
        $limit = $this->getLoadMoreLimit($limit);

        if (!$this->isAuthProvided()) {
            // open to public
            $users = $this->getRepo('User\User')->findBy(
                array('banned' => false),
                array('id' => 'DESC'),
                $limit,
                $offset
            );
        } else {
            $userId = $this->getUserId();
            $clientId = $this->getUser()->getClientId();

            $em = $this->getDoctrine()->getManager();

            // get user's retrieved member IDs if any
            $myRecords = $this->getRepo('Random\ClientRandomRecord')
                ->findBy(array(
                    'userId' => $userId,
                    'clientId' => $clientId,
                    'entityName' => 'member',
                ));

            $recordIds = array();

            foreach ($myRecords as $myRecord) {
                // if offset is not provided, means user is trying to reload the page
                // then we should remove user's retrieval records
                // otherwise, we should exclude these records for the next page
                if (is_null($offset) || $offset <= 0) {
                    $em->remove($myRecord);
                } else {
                    array_push($recordIds, $myRecord->getEntityId());
                }
            }

            // find random members
            $users = $this->getRepo('User\User')->findRandomMembers(
                $userId,
                $recordIds,
                $limit
            );
            if (is_null($users) || empty($users)) {
                return new View(array());
            }

            foreach ($users as $user) {
                // add user's retrieval record
                $randomRecord = new ClientRandomRecord();
                $randomRecord->setUserId($userId);
                $randomRecord->setClientId($clientId);
                $randomRecord->setEntityId($user->getId());
                $randomRecord->setEntityName('member');
                $em->persist($randomRecord);
            }

            $em->flush();
        }

        // members for response
        $members = array();

        foreach ($users as $user) {
            // set profile
            $profile = $this->getRepo('User\UserProfile')->findOneByUser($user);

            $member = array(
                'id' => $user->getId(),
                'profile' => $profile,
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

        // get globals
        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        // get max limit
        $limit = $this->getLoadMoreLimit($limit);

        // get my profile
        $myProfile = $this->getRepo('User\UserProfile')->findOneByUserId($userId);
        $this->throwNotFoundIfNull($myProfile, self::NOT_FOUND_MESSAGE);

        // TODO change to $myProfile->getBuilding()
        // for some reason, now this is not working in my local environment
        //var_dump($myProfile->getBuilding());

        // get my building
        $buildingId = $myProfile->getBuildingId();
        if (is_null($buildingId)) {
            return new View(array());
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
            $offset,
            $globals['nearby_range_km']
        );
        if (is_null($users) || empty($users)) {
            return new View(array());
        }

        // members for response
        $members = array();

        foreach ($users as $user) {
            $memberId = $user->getId();

            $profile = $this->getRepo('User\UserProfile')->findOneByUserId($memberId);

            $member = array(
                'id' => $memberId,
                'profile' => $profile,
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
        $userId = $this->getUserId();

        // get params
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');
        $lastId = $paramFetcher->get('last_id');

        // get max limit
        $limit = $this->getLoadMoreLimit($limit);

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

            $member = array(
                'id' => $visitor->getId(),
                'profile' => $profile,
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
        $userId = $this->getUserId();
        $myUser = $this->getRepo('User\User')->find($userId);

        $query = $paramFetcher->get('query');
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        // get max limit
        $limit = $this->getLoadMoreLimit($limit);

        $membersResult = $this->getRepo('User\UserView')->searchMember(
            $query,
            $limit,
            $offset
        );

        $members = array();
        foreach ($membersResult as $profile) {
            $memberId = $profile->getUserId();
            $buddy = $this->getRepo('User\User')->find($memberId);

            $myBuddy = $this->getRepo('Buddy\Buddy')->findOneBy(array(
               'user' => $myUser,
                'buddy' => $buddy,
            ));

            if (!is_null($myBuddy)) {
                $profile->setStatus(BuddyRequest::STATUS_ACCEPTED);
            } else {
                $myBuddyRequest = $this->getRepo('Buddy\BuddyRequest')->findOneBy(array(
                    'askUser' => $myUser,
                    'recvUser' => $buddy,
                    'status' => BuddyRequest::STATUS_PENDING,
                ));

                if (!is_null($myBuddyRequest)) {
                    $profile->setStatus(BuddyRequest::STATUS_PENDING);
                }
            }

            // check buddy status
            $member = array(
                'id' => $memberId,
                'profile' => $profile,
            );

            array_push($members, $member);
        }

        $view = new View($members);
        $view->setSerializationContext(SerializationContext::create()->setGroups(['member']));

        return $view;
    }
    // elastica search members
//    public function getMembersSearchAction(
//        Request $request,
//        ParamFetcherInterface $paramFetcher
//    ) {
//        $userId = $this->getUserId();
//
//        // if user is not authorized, respond empty list
//        if (!$this->checkUserAuthorized($userId)) {
//            return new View(array());
//        }
//
//        $query = $paramFetcher->get('query');
//        $limit = $paramFetcher->get('limit');
//        $offset = $paramFetcher->get('offset');
//
//        // get max limit
//        $limit = $this->getLoadMoreLimit($limit);
//
//        // find all members who have the query in any of their mapped fields
//        $finder = $this->container->get('fos_elastica.finder.search.member');
//
//        $multiMatchQuery = new \Elastica\Query\MultiMatch();
//
//        $multiMatchQuery->setQuery($query);
//        $multiMatchQuery->setType('phrase_prefix');
//        $multiMatchQuery->setFields(array('name'));
//
//        $results = $finder->find($multiMatchQuery);
//        if (is_null($results) || empty($results)) {
//            return new View(array());
//        }
//
//        // members for response
//        $members = array();
//
//        for ($i = $offset; $i < count($results); ++$i) {
//            if (count($members) >= $limit) {
//                break;
//            }
//
//            $profile = $results[$i];
//            $userId = $profile->getUserId();
//
//            $user = $this->getRepo('User\User')->find($userId);
//            if (is_null($user)
//                || $user->isBanned()
//                || !$user->isAuthorized()) {
//                continue;
//            }
//
//            $member = array(
//                'id' => $userId,
//                'profile' => $profile,
//            );
//
//            array_push($members, $member);
//        }
//
//        // set view
//        $view = new View($members);
//        $view->setSerializationContext(
//            SerializationContext::create()->setGroups(array('member'))
//        );
//
//        return $view;
//    }
}
