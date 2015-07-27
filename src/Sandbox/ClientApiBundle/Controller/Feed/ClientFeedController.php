<?php

namespace Sandbox\ClientApiBundle\Controller\Feed;

use Doctrine\Common\Collections\ArrayCollection;
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
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Rest controller for Feed.
 *
 * @category Sandbox
 *
 * @author   Sergi Uceda <sergiu@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
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
     *    default="20",
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
        $limit = $paramFetcher->get('limit');
        $lastId = $paramFetcher->get('last_id');

        $feeds = $this->getRepo('Feed\FeedView')->getFeeds(
            $limit,
            $lastId
        );

        return $this->handleGetFeeds($feeds);
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
     *    default="20",
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
        $limit = $paramFetcher->get('limit');
        $lastId = $paramFetcher->get('last_id');

        $userId = $this->getUserId();

        $feeds = $this->getRepo('Feed\FeedView')->getFeedsByBuddies(
            $limit,
            $lastId,
            $userId
        );

        return $this->handleGetFeeds($feeds);
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
     *    default="20",
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
        $limit = $paramFetcher->get('limit');
        $lastId = $paramFetcher->get('last_id');

        $userId = $this->getUserId();
        $profile = $this->getRepo('User\UserProfile')->findOneByUserId($userId);
        $buildingId = $profile->getBuildingId();

        $feeds = $this->getRepo('Feed\FeedView')->getFeedsByBuilding(
            $limit,
            $lastId,
            $buildingId
        );

        return $this->handleGetFeeds($feeds);
    }

    /**
     * Get feed by id.
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
        $feed = $this->getRepo('Feed\FeedView')->find($id);
        $this->throwNotFoundIfNull($feed, self::NOT_FOUND_MESSAGE);

        $feedOwnerId = $feed->getOwnerId();

        $profile = $this->getRepo('User\UserProfile')->findOneByUserId($feedOwnerId);
        $this->throwNotFoundIfNull($profile, self::NOT_FOUND_MESSAGE);
        $feed->setOwner($profile);

        $like = $this->getRepo('Feed\FeedLike')->findOneBy(array(
            'feedId' => $feed->getId(),
            'authorId' => $feedOwnerId,
        ));

        if (!is_null($like)) {
            $feed->setMyLikeId($like->getId());
        }

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
        $feed = new Feed();

        $form = $this->createForm(new FeedType(), $feed);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $feed->setCreationDate(new \DateTime('now'));
        $feed->setOwnerid($this->getUserId());

        $em = $this->getDoctrine()->getManager();
        $em->persist($feed);
        $em->flush();

        //add attachments
        $attachments = $form['feed_attachments']->getData();

        if (!is_null($attachments)) {
            $this->addAttachments(
                $em,
                $feed,
                $attachments
            );
        }

        $response = array(
            'id' => $feed->getId(),
        );

        return new View($response);
    }

    /**
     * delete feed by id.
     *
     * @param Request $request
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
            throw new BadRequestHttpException(self::NOT_ALLOWED_MESSAGE);
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
        $em->flush();
    }

    /**
     * @param ArrayCollection $feeds
     *
     * @return View
     */
    private function handleGetFeeds(
        $feeds
    ) {
        foreach ($feeds as $feed) {
            $feedOwnerId = $feed->getOwnerId();

            $profile = $this->getRepo('User\UserProfile')->findOneByUserId($feedOwnerId);
            $this->throwNotFoundIfNull($profile, self::NOT_FOUND_MESSAGE);
            $feed->setOwner($profile);

            $like = $this->getRepo('Feed\FeedLike')->findOneBy(array(
                'feedId' => $feed->getId(),
                'authorId' => $feedOwnerId,
            ));

            if (!is_null($like)) {
                $feed->setMyLikeId($like->getId());
            }
        }

        $view = new View($feeds);
        $view->setSerializationContext(SerializationContext::create()->setGroups(['feed']));

        return $view;
    }
}
