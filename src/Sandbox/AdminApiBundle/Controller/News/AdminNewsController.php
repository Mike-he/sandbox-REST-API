<?php

namespace Sandbox\AdminApiBundle\Controller\News;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Knp\Component\Pager\Paginator;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\News\News;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;
use Sandbox\ApiBundle\Entity\Admin\AdminType;
use Sandbox\ApiBundle\Entity\News\NewsAttachment;
use Sandbox\ApiBundle\Form\News\NewsPostType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class AdminNewsController.
 *
 * @category Sandbox
 *
 * @author   Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminNewsController extends SandboxRestController
{
    /**
     * Create admin news.
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
     * @Route("/news")
     * @Method({"POST"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postAdminNewsAction(
        Request $request
    ) {
        // check user permission
        $this->checkAdminNewsPermission(AdminPermissionMap::OP_LEVEL_EDIT);

        $news = new News();

        $form = $this->createForm(new NewsPostType(), $news);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return $this->handleNewsPost(
            $news,
            $request
        );
    }

    /**
     * Get admin news.
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
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many products to return"
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
     * @Route("/news")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdminNews(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminNewsPermission(AdminPermissionMap::OP_LEVEL_VIEW);

        // filters
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $news = $this->getRepo('News\News')->findByVisible(true);

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $news,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * Save news to db.
     *
     * @param News    $news
     * @param Request $request
     *
     * @return View
     */
    private function handleNewsPost(
        $news,
        $request
    ) {
        $requestContent = $request->getContent();
        $eventArray = json_decode($requestContent, true);

        $attachments = null;
        if (array_key_exists('news_attachments', $eventArray)) {
            $attachments = $eventArray['news_attachments'];
        }

        // add news
        $this->addNews(
            $news
        );

        // add news attachments
        $this->addNewsAttachments(
            $news,
            $attachments
        );

        $response = array(
            'id' => $news->getId(),
        );

        return new View($response);
    }

    /**
     * Save news entity to db.
     *
     * @param News $news
     */
    private function addNews(
        $news
    ) {
        $em = $this->getDoctrine()->getManager();

        $now = new \DateTime('now');

        $news->setCreationDate($now);
        $news->setModificationDate($now);

        $em->persist($news);
    }

    /**
     * Save news attachments.
     *
     * @param News  $news
     * @param Array $attachments
     */
    private function addNewsAttachments(
        $news,
        $attachments
    ) {
        $em = $this->getDoctrine()->getManager();

        if (!is_null($attachments) && !empty($attachments)) {
            foreach ($attachments as $attachment) {
                $newsAttachment = new NewsAttachment();
                $newsAttachment->setNews($news);
                $newsAttachment->setContent($attachment['content']);
                $newsAttachment->setAttachmentType($attachment['attachment_type']);
                $newsAttachment->setFilename($attachment['filename']);
                $newsAttachment->setPreview($attachment['preview']);
                $newsAttachment->setSize($attachment['size']);
                $em->persist($newsAttachment);
            }
            $em->flush();
        }
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     */
    private function checkAdminNewsPermission(
        $opLevel
    ) {
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_NEWS,
            $opLevel
        );
    }
}
