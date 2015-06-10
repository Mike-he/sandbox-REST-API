<?php
/**
 * API for Feeditems
 *
 * PHP version 5.3
 *
 * @category Sandbox
 * @package  ApiBundle
 * @author   Allan Simon <simona@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 * @link     http://www.Sandbox.cn/
 *
 */
namespace Sandbox\ApiBundle\Controller;

use Sandbox\ApiBundle\Entity\Feed;
use Sandbox\ApiBundle\Entity\FeedAttachment;
use Sandbox\ApiBundle\Form\FeedType;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Rest controller for Feed
 *
 * @category Sandbox
 * @package  Sandbox\ApiBundle\Controller
 * @author   Josh Yang
 * @license  http://www.Sandbox.cn/ Proprietary
 * @link     http://www.Sandbox.cn/
 */
class FeedController extends CommonTaskApprovalController
{
    const NOT_FOUND_MESSAGE = "This resource does not exist";

    const FEED_TYPE = "Feed";

    const BAD_PARAM_MESSAGE = "Bad parameters";

    const NOT_ALLOWED_MESSAGE = "You are not allowed to perform this action";

    /**
     * List all feed.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     *
     * @Annotations\QueryParam(
     *    name="last",
     *    default="",
     *    description="
     *        last task retrieved by client
     *        to know where to start pagination
     *    "
     * )
     * @Annotations\QueryParam(
     *    name="limit",
     *    requirements="\d\d?",
     *    nullable=true,
     *    default=20,
     *    strict=true,
     *    description="How many task to return. between 0 and 99, default 5"
     * )
     * @Annotations\QueryParam(
     *    name="companies",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    description=""
     * )
     *
     * @Annotations\View()
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @return array
     */
    public function getFeedsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userID = $this->getUsername();
        $limit = $paramFetcher->get('limit');
        $last = $paramFetcher->get('last');

        $companies = $paramFetcher->get('companies');

        $repo = $this->getRepo('FeedView');

        $feeds = $repo->findAllFeedsByUsersAllCompany($userID, $companies, $limit, $last);

        foreach ($feeds as $feed) {
            $like = $this->getRepo('FeedLike')->findOneBy(array(
                'fid' => $feed->getId(),
                'authorid' => $userID,
            ));

            if (!is_null($like)) {
                $likeID = $like->getId();
                $feed->setMyLikeId($likeID);
            }
        }

        return new View($feeds);
    }

    /**
     * Get a single feed.
     *
     * @ApiDoc(
     *   output = "Feed",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the note is not found"
     *   }
     * )
     *
     *
     * @param Request $request the request object
     * @param String  $id      the feed Id
     *
     * @return array
     */
    public function getFeedAction(
        Request $request,
        $id
    ) {
        $userID = $this->getUsername();

        // check user's permission
        $feed = $this->getRepo('FeedView')->findOneById($id);
        $this->throwNotFoundIfNull($feed, self::NOT_FOUND_MESSAGE);

        $this->throwAccessDeniedIfNotCompanyMember($feed->getParentid(), $userID);

        $like = $this->getRepo('FeedLike')->findOneBy(array(
            'fid' => $feed->getId(),
            'authorid' => $userID,
        ));

        if (!is_null($like)) {
            $likeID = $like->getId();
            $feed->setMyLikeId($likeID);
        }

        return new View($feed);
    }

    /**
     * @param  Request                 $request
     * @return View
     * @throws BadRequestHttpException
     */
    public function postFeedAction(
        Request $request
    ) {
        $feed = new Feed();
        $form = $this->createForm(new FeedType(), $feed);
        $form->submit(json_decode($request->getContent(), true));

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $userID = $this->getUsername();

        $this->throwAccessDeniedIfNotCompanyMember($feed->getParentid(), $userID);

        $em = $this->getDoctrine()->getManager();

        $feed->setOwnerid($userID);

        $em->persist($feed);
        $em->flush();

        $attachments = $feed->getAttachments();
        foreach ($attachments as $attachment) {
            $content = $attachment['content'];
            $attachmentType = $attachment['attachmenttype'];
            $filename = $attachment['filename'];
            $preview = $attachment['preview'];
            $size = $attachment['size'];

            if (is_null($content) ||
                $content === '' ||
                is_null($attachmentType) ||
                $attachmentType === '' ||
                is_null($size) ||
                $size === '') {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }

            $feedAttachment = new FeedAttachment();
            $feedAttachment->setFid($feed->getId());
            $feedAttachment->setContent($content);
            $feedAttachment->setAttachmenttype($attachmentType);
            $feedAttachment->setFilename($filename);
            $feedAttachment->setPreview($preview);
            $feedAttachment->setSize($size);

            $em->persist($feedAttachment);
            $em->flush();
        }

        $view = $this->routeRedirectView('get_feed', array('id' => $feed->getId()));
        $view->setData(array('id' => $feed->getId()));

        return $view;
    }

    /**
     * @param  Request                 $request
     * @param $id
     * @throws BadRequestHttpException
     */
    public function deleteFeedAction(
        Request $request,
        $id
    ) {
        $userID = $this->getUsername();

        // check user's permission
        $feed = $this->getRepo('Feed')->findOneById($id);
        $this->throwNotFoundIfNull($feed, self::NOT_FOUND_MESSAGE);

        $this->throwAccessDeniedIfNotCompanyMember($feed->getParentid(), $userID);

        // only owner can delete feed
        if ($userID != $feed->getOwnerid()) {
            throw new BadRequestHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($feed);
        $em->flush();
    }
}
