<?php

namespace Sandbox\AdminApiBundle\Controller\Bulletin;

use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Knp\Component\Pager\Paginator;
use Sandbox\AdminApiBundle\Data\Position\Position;
use Sandbox\ApiBundle\Controller\Bulletin\BulletinController;
use Sandbox\ApiBundle\Entity\Bulletin\BulletinPost;
use Sandbox\ApiBundle\Entity\Bulletin\BulletinPostAttachment;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;
use Sandbox\ApiBundle\Form\Bulletin\BulletinPostForm;
use Sandbox\ApiBundle\Form\Bulletin\BulletinPostAttachmentForm;
use Sandbox\ApiBundle\Form\Position\PositionType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class AdminBulletinPostController.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminBulletinPostController extends BulletinController
{
    /**
     * Create admin bulletin post.
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
     * @Route("/bulletin/post")
     * @Method({"POST"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postAdminBulletinPostAction(
        Request $request
    ) {
        // check user permission
        $this->checkAdminBulletinPermission(AdminPermissionMap::OP_LEVEL_EDIT);

        $post = new BulletinPost();

        $form = $this->createForm(new BulletinPostForm(), $post);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $type = $this->getRepo('Bulletin\BulletinType')->findOneBy(
            [
                'id' => $post->getTypeId(),
                'deleted' => false,
            ]
        );
        $this->throwNotFoundIfNull($type, self::NOT_FOUND_MESSAGE);

        $post->setType($type);

        return $this->handleBulletinPost(
            $post
        );
    }

    /**
     * Modify bulletin post.
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
     * @Route("/bulletin/posts/{id}")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function putAdminBulletinPostAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminBulletinPermission(AdminPermissionMap::OP_LEVEL_EDIT);

        $post = $this->getRepo('Bulletin\BulletinPost')->findOneBy(
            [
                'id' => $id,
                'deleted' => false,
            ]
        );
        $this->throwNotFoundIfNull($post, self::NOT_FOUND_MESSAGE);

        $form = $this->createForm(
            new BulletinPostForm(),
            $post,
            array('method' => 'PUT')
        );
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $type = $this->getRepo('Bulletin\BulletinType')->findOneBy(
            [
                'id' => $post->getTypeId(),
                'deleted' => false,
            ]
        );
        $this->throwNotFoundIfNull($type, self::NOT_FOUND_MESSAGE);

        $post->setType($type);

        return $this->handleBulletinPut(
            $post
        );
    }

    /**
     * Get admin bulletin posts.
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
     * @Annotations\QueryParam(
     *    name="type",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="type"
     * )
     *
     * @Annotations\QueryParam(
     *    name="search",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="search"
     * )
     *
     * @Route("/bulletin/posts")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdminBulletinPostsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminBulletinPermission(AdminPermissionMap::OP_LEVEL_VIEW);

        // filters
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $type = $paramFetcher->get('type');
        $search = $paramFetcher->get('search');

        $posts = $this->getRepo('Bulletin\BulletinPost')->getAdminBulletinPosts(
            $type,
            $search
        );

        $posts = $this->get('serializer')->serialize(
            $posts,
            'json',
            SerializationContext::create()->setGroups(['admin'])
        );
        $posts = json_decode($posts, true);

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $posts,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * Get bulletin post by Id.
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
     * @Route("/bulletin/posts/{id}")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdminBulletinPostByIdAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminBulletinPermission(AdminPermissionMap::OP_LEVEL_VIEW);

        $post = $this->getRepo('Bulletin\BulletinPost')->findOneBy(
            [
                'id' => $id,
                'deleted' => false,
            ]
        );
        $this->throwNotFoundIfNull($post, self::NOT_FOUND_MESSAGE);

        // set view
        $view = new View();
        $view->setData($post);
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin']));

        return $view;
    }

    /**
     * Delete bulletin post.
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
     * @Route("/bulletin/posts/{id}")
     * @Method({"DELETE"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function deleteBulletinPostAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminBulletinPermission(AdminPermissionMap::OP_LEVEL_EDIT);

        $post = $this->getRepo('Bulletin\BulletinPost')->findOneBy(
            [
                'id' => $id,
                'deleted' => false,
            ]
        );
        $this->throwNotFoundIfNull($post, self::NOT_FOUND_MESSAGE);

        $post->setDeleted(true);
        $post->setModificationDate(new \DateTime());

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Method({"POST"})
     * @Route("/bulletin/posts/{id}/position")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function changePostPositionAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminBulletinPermission(AdminPermissionMap::OP_LEVEL_EDIT);

        $post = $this->getRepo('Bulletin\BulletinPost')->findOneBy(
            [
                'id' => $id,
                'deleted' => false,
            ]
        );
        $this->throwNotFoundIfNull($post, self::NOT_FOUND_MESSAGE);

        $position = new Position();

        $form = $this->createForm(new PositionType(), $position);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $action = $position->getAction();

        if (empty($action) || is_null($action)) {
            return new View();
        }

        $this->setPosition(
            $post,
            $action
        );

        return new View();
    }

    /**
     * @param $post
     * @param $action
     */
    private function setPosition(
        $post,
        $action
    ) {
        if ($action == Position::ACTION_TOP) {
            $post->setSortTime(round(microtime(true) * 1000));
        } elseif ($action == Position::ACTION_UP || $action == Position::ACTION_DOWN) {
            $swapItem = $this->getRepo('Bulletin\BulletinPost')->findSwapBulletinPost(
                $post,
                $action
            );

            if (empty($swapItem)) {
                return;
            }

            // swap
            $itemSortTime = $post->getSortTime();
            $post->setSortTime($swapItem->getSortTime());
            $swapItem->setSortTime($itemSortTime);
        }

        // save
        $em = $this->getDoctrine()->getManager();
        $em->flush();
    }

    /**
     * @param $post
     *
     * @return View
     */
    private function handleBulletinPut(
        $post
    ) {
        $em = $this->getDoctrine()->getManager();

        $this->modifyAttachments($em, $post);

        $em->flush();

        return new View();
    }

    /**
     * @param $em
     * @param $post
     */
    private function modifyAttachments(
        $em,
        $post
    ) {
        $attachments = $post->getAttachments();

        if (is_null($attachments) || empty($attachments)) {
            return;
        }

        $oldAttachments = $this->getRepo('Bulletin\BulletinPostAttachment')->findByPost($post);

        foreach ($oldAttachments as $oldAttachment) {
            $em->remove($oldAttachment);
        }

        $this->addPostAttachments($em, $post);
    }

    /**
     * @param $post
     *
     * @return View
     */
    private function handleBulletinPost(
        $post
    ) {
        $em = $this->getDoctrine()->getManager();
        $em->persist($post);

        $this->addPostAttachments($em, $post);

        $em->flush();

        return new View(['id' => $post->getId()]);
    }

    /**
     * @param $em
     * @param $post
     */
    private function addPostAttachments(
        $em,
        $post
    ) {
        $attachments = $post->getAttachments();

        if (is_null($attachments)) {
            return;
        }

        foreach ($attachments as $attachment) {
            $postAttachment = new BulletinPostAttachment();

            $form = $this->createForm(new BulletinPostAttachmentForm(), $postAttachment);
            $form->submit($attachment, true);

            $postAttachment->setPost($post);

            $em->persist($postAttachment);
        }
    }
}
