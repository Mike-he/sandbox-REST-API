<?php

namespace Sandbox\AdminApiBundle\Controller\News;

use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Knp\Component\Pager\Paginator;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\News\News;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\News\NewsAttachment;
use Sandbox\ApiBundle\Form\News\NewsPostType;
use Sandbox\ApiBundle\Form\News\NewsPutType;
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
        $this->checkAdminNewsPermission(AdminPermission::OP_LEVEL_EDIT);

        $news = new News();

        $form = $this->createForm(new NewsPostType(), $news);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return $this->handleNewsPost(
            $news
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
    public function getAdminNewsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_NEWS],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_BANNER],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ADVERTISING],
            ],
            AdminPermission::OP_LEVEL_VIEW
        );

        // filters
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $newsArray = array();
        $allNews = $this->getRepo('News\News')->getAllAdminNews();
        foreach ($allNews as $news) {
            $attachments = $this->getRepo('News\NewsAttachment')->findByNews($news);
            $news->setAttachments($attachments);

            array_push($newsArray, $news);
        }

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $newsArray,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * Get definite id of news.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @param Request $request
     * @param $id
     *
     * @Route("/news/{id}")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getOneAdminNewsAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_NEWS],
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_ADVERTISING],
            ],
            AdminPermission::OP_LEVEL_VIEW
        );

        // get an news
        $news = $this->getRepo('News\News')->findOneBy(array(
            'id' => $id,
            'isDeleted' => false,
        ));
        $this->throwNotFoundIfNull($news, self::NOT_FOUND_MESSAGE);

        // set attachments
        $attachments = $this->getRepo('News\NewsAttachment')->findByNews($news);
        $news->setAttachments($attachments);

        // set view
        $view = new View($news);
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(array('main'))
        );

        return $view;
    }

    /**
     * Modify a news.
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
     * @Method({"PUT"})
     * @Route("/news/{id}")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function putAdminNewsAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminNewsPermission(AdminPermission::OP_LEVEL_EDIT);

        $news = $this->getRepo('News\News')->find($id);
        $this->throwNotFoundIfNull($news, self::NOT_FOUND_MESSAGE);

        $form = $this->createForm(
            new NewsPutType(),
            $news,
            array('method' => 'PUT')
        );
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        // handle new form
        return $this->handleNewsPut(
            $news,
            $request
        );
    }

    /**
     * Delete a news.
     *
     * @param Request $request
     * @param int     $id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     204 = "OK"
     *  }
     * )
     *
     * @Route("/news/{id}")
     * @Method({"DELETE"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function deleteNewsAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminNewsPermission(AdminPermission::OP_LEVEL_EDIT);

        $news = $this->getRepo('News\News')->find($id);
        $this->throwNotFoundIfNull($news, self::NOT_FOUND_MESSAGE);

        $news->setIsDeleted(true);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * Save news to db.
     *
     * @param News $news
     *
     * @return View
     */
    private function handleNewsPost(
        $news
    ) {
        $attachments = $news->getAttachments();

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
     * @param News $news
     *
     * @return View
     */
    private function handleNewsPut(
        $news
    ) {
        $attachments = $news->getAttachments();

        // modify news
        $this->modifyNews($news);

        // modify news attachments
        $this->modifyNewsAttachments(
            $news,
            $attachments
        );

        return new View();
    }

    /**
     * Modify news.
     *
     * @param News $news
     */
    private function modifyNews(
        $news
    ) {
        $em = $this->getDoctrine()->getManager();

        $now = new \DateTime('now');
        $news->setModificationDate($now);

        $em->flush();
    }

    /**
     * Modify news attachments.
     *
     * @param News  $news
     * @param array $attachments
     */
    private function modifyNewsAttachments(
        $news,
        $attachments
    ) {
        $em = $this->getDoctrine()->getManager();

        // remove old data from db
        if (!is_null($attachments) || !empty($attachments)) {
            $eventAttachments = $this->getRepo('News\NewsAttachment')->findByNews($news);
            foreach ($eventAttachments as $eventAttachment) {
                $em->remove($eventAttachment);
            }

            $this->addNewsAttachments(
                $news,
                $attachments
            );
        }
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
     * @param array $attachments
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
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_NEWS],
            ],
            $opLevel
        );
    }
}
