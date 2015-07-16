<?php

namespace Sandbox\ClientApiBundle\Controller\Feed;

use Doctrine\ORM\EntityManager;
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
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Annotations\QueryParam(
     *    name="limit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many announcements to return "
     * )
     *
     * @Annotations\QueryParam(
     *    name="offset",
     *    array=false,
     *    default="0",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="Offset from which to start listing announcements"
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
        $offset = $paramFetcher->get('offset');

        $feed = $this->getRepo('Feed\Feed')->findBy(
            [],
            ['creationDate' => 'DESC'],
            $limit,
            $offset
        );

        return new View($feed);
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
        $feed = $this->getRepo('Feed\Feed')->find($id);

        return new View($feed);
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
        $this->addAttachments(
            $em,
            $feed,
            $attachments
        );

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
}
