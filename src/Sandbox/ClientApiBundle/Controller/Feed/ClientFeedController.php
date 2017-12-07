<?php

namespace Sandbox\ClientApiBundle\Controller\Feed;

use Doctrine\ORM\EntityManager;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Controller\Feed\FeedController;
use Sandbox\ApiBundle\Entity\Feed\Feed;
use Sandbox\ApiBundle\Entity\Feed\FeedAttachment;
use Sandbox\ApiBundle\Form\Feed\FeedType;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Rest controller for Feed.
 *
 * @category Sandbox
 *
 * @author   Sergi Uceda <sergiu@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @see     http://www.Sandbox.cn/
 */
class ClientFeedController extends FeedController
{
    /**
     * List all feed.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @Annotations\QueryParam(
     *    name="limit",
     *    array=false,
     *    default="10",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many feeds to return "
     * )
     *
     * @Annotations\QueryParam(
     *    name="last_id",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="last id"
     * )
     *
     * @Annotations\QueryParam(
     *     name="platform",
     *     array=false,
     *     nullable=true,
     *     strict=true
     * )
     *
     * @Route("feeds/all")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getFeedsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = null;
        if ($this->isAuthProvided()) {
            $userId = $this->getUserId();
        }

        // get params
        $limit = $paramFetcher->get('limit');
        $lastId = $paramFetcher->get('last_id');
        $platform = $paramFetcher->get('platform');

        $feeds = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Feed\FeedView')
            ->getFeeds(
                $limit,
                $lastId,
                $platform
            );

        return $this->handleGetFeeds($feeds, $userId);
    }

    /**
     * List all feed by buddies.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @Annotations\QueryParam(
     *    name="limit",
     *    array=false,
     *    default="10",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many feeds to return "
     * )
     *
     * @Annotations\QueryParam(
     *    name="last_id",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="last id"
     * )
     *
     * @Route("feeds/buddy")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getFeedsByBuddyAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();

        $limit = $paramFetcher->get('limit');
        $lastId = $paramFetcher->get('last_id');

        $feeds = $this->getRepo('Feed\FeedView')->getFeedsByBuddies(
            $limit,
            $lastId,
            $userId
        );

        return $this->handleGetFeeds($feeds, $userId);
    }

    /**
     * List all feed by my building.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @Annotations\QueryParam(
     *    name="limit",
     *    array=false,
     *    default="10",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many feeds to return "
     * )
     *
     * @Annotations\QueryParam(
     *    name="last_id",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="last id"
     * )
     *
     * @Route("feeds/building")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getFeedsByBuildingAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();

        $limit = $paramFetcher->get('limit');
        $lastId = $paramFetcher->get('last_id');

        $profile = $this->getRepo('User\UserProfile')->findOneByUserId($userId);

        $buildingId = $profile->getBuildingId();
        if (is_null($buildingId)) {
            return new View(array());
        }

        $feeds = $this->getRepo('Feed\FeedView')->getFeedsByBuilding(
            $limit,
            $lastId,
            $buildingId
        );

        return $this->handleGetFeeds($feeds, $userId);
    }

    /**
     * List all feed by my colleagues.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @Annotations\QueryParam(
     *    name="limit",
     *    array=false,
     *    default="10",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many feeds to return "
     * )
     *
     * @Annotations\QueryParam(
     *    name="last_id",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="last id"
     * )
     *
     * @Route("feeds/company")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getFeedsByColleaguesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();

        $limit = $paramFetcher->get('limit');
        $lastId = $paramFetcher->get('last_id');

        // get all my company members' feeds
        $feeds = $this->getRepo('Feed\FeedView')->getFeedsByColleagues(
            $limit,
            $lastId,
            $userId
        );

        return $this->handleGetFeeds($feeds, $userId);
    }

    /**
     * List all my feeds.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="limit",
     *    array=false,
     *    default="10",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many feeds to return "
     * )
     *
     * @Annotations\QueryParam(
     *    name="last_id",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="last id"
     * )
     *
     * @Annotations\QueryParam(
     *    name="user_id",
     *    default=null,
     *    description="userId"
     * )
     *
     * @Route("/feeds/my")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getMyFeedsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = null;
        if ($this->isAuthProvided()) {
            $userId = $this->getUserId();
        }

        // request user
        $assignUserId = $paramFetcher->get('user_id');
        if (is_null($assignUserId)) {
            $assignUserId = $userId;
        }

        // get request user
        $assignUser = $this->getRepo('User\User')->find($assignUserId);
        $this->throwNotFoundIfNull($assignUser, self::NOT_FOUND_MESSAGE);

        // check the other user is banned
        if ($assignUser->isBanned()) {
            return new View();
        }

        $limit = $paramFetcher->get('limit');
        $lastId = $paramFetcher->get('last_id');

        // get all my feeds
        $feeds = $this->getRepo('Feed\FeedView')->getMyFeeds(
            $assignUserId,
            $limit,
            $lastId
        );

        return $this->handleGetFeeds($feeds, $userId);
    }

    /**
     * Get feed by id.
     *
     * @param Request $request
     * @param int     $id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Route("feeds/{id}")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getFeedAction(
        Request $request,
        $id
    ) {
        $userId = null;
        if ($this->isAuthProvided()) {
            $userId = $this->getUserId();
        }

        $feed = $this->getRepo('Feed\FeedView')->findOneBy(array(
            'id' => $id,
            'isDeleted' => false,
        ));
        $this->throwNotFoundIfNull($feed, self::NOT_FOUND_MESSAGE);

        $this->setFeed($feed, $userId);

        $view = new View($feed);
        $view->setSerializationContext(SerializationContext::create()->setGroups(['feed']));

        return $view;
    }

    /**
     * Add new feed.
     *
     * @param Request $request
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Route("/feeds")
     * @Method({"POST"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function postFeedAction(
        Request $request
    ) {
        $myUserId = $this->getUserId();
        $myUser = $this->getRepo('User\User')->find($myUserId);

        $feed = new Feed();

        $form = $this->createForm(new FeedType(), $feed);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $feed->setCreationDate(new \DateTime('now'));
        $feed->setOwner($myUser);

        $em = $this->getDoctrine()->getManager();
        $em->persist($feed);

        //add attachments
        $attachments = $form['feed_attachments']->getData();

        if (!is_null($attachments)) {
            $this->addAttachments(
                $em,
                $feed,
                $attachments
            );
        }

        $em->flush();

        $response = array(
            'id' => $feed->getId(),
        );

        return new View($response);
    }

    /**
     * delete feed by id.
     *
     * @param Request $request
     * @param int     $id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     204 = "No content"
     *  }
     * )
     *
     * @Route("feeds/{id}")
     * @Method({"DELETE"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function deleteFeedAction(
        Request $request,
        $id
    ) {
        $feed = $this->getRepo('Feed\Feed')->find($id);
        $this->throwNotFoundIfNull($feed, self::NOT_FOUND_MESSAGE);

        // only owner can delete the feed
        $userId = $this->getUserId();
        if ($userId != $feed->getOwnerId()) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($feed);
        $em->flush();
    }

    /**
     * Add attachments.
     *
     * @param EntityManager  $em
     * @param Feed           $feed
     * @param FeedAttachment $attachments
     */
    private function addAttachments(
        $em,
        $feed,
        $attachments
    ) {
        foreach ($attachments as $attachment) {
            $feedAttachment = new FeedAttachment();

            $feedAttachment->setFeed($feed);
            $feedAttachment->setContent($attachment['content']);
            $feedAttachment->setAttachmentType($attachment['attachment_type']);
            $feedAttachment->setFilename($attachment['filename']);
            $feedAttachment->setPreview($attachment['preview']);
            $feedAttachment->setSize($attachment['size']);

            $em->persist($feedAttachment);
        }
    }
}
