<?php

namespace Sandbox\ClientApiBundle\Controller\News;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use JMS\Serializer\SerializationContext;

/**
 * Class ClientNewsController.
 *
 * @category Sandbox
 *
 * @author   Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientNewsController extends SandboxRestController
{
    /**
     * Get all client news.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
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
     *    description="offset of page"
     * )
     *
     * @Route("/news")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throw \Exception
     */
    public function getAllClientNewsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // filters
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        // get max limit
        $limit = $this->getLoadMoreLimit($limit);

        $newsArray = array();
        $allNews = $this->getRepo('News\News')->getAllClientNews(
            $limit,
            $offset
        );
        foreach ($allNews as $news) {
            $attachments = $this->getRepo('News\NewsAttachment')->findByNews($news);
            $news->setAttachments($attachments);

            array_push($newsArray, $news);
        }

        $view = new View($newsArray);
        $view->setSerializationContext(SerializationContext::create()->setGroups(['main']));

        return $view;
    }

    /**
     * Get definite id of news.
     *
     * @param Request $request
     * @param int     $id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Route("/news/{id}")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getClientNewsAction(
        Request $request,
        $id
    ) {
        // get a news
        $news = $this->getRepo('News\News')->findOneBy(array(
            'id' => $id,
            'isDeleted' => false,
        ));
        $this->throwNotFoundIfNull($news, self::NOT_FOUND_MESSAGE);

        // set news attachments
        $attachments = $this->getRepo('News\NewsAttachment')->findByNews($news);
        $news->setAttachments($attachments);

        $view = new View($news);
        $view->setSerializationContext(SerializationContext::create()->setGroups(['main']));

        return $view;
    }
}
