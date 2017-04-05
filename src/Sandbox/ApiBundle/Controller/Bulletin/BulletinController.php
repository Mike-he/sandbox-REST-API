<?php

namespace Sandbox\ApiBundle\Controller\Bulletin;

use Knp\Component\Pager\Paginator;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use JMS\Serializer\SerializationContext;

/**
 * Bulletin Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class BulletinController extends SalesRestController
{
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
     * @Route("/bulletin/view/posts")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getBulletinPostsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // filters
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $type = $paramFetcher->get('type');

        $posts = $this->getRepo('Bulletin\BulletinPost')->getAdminBulletinPosts(
            $type
        );

        $posts = $this->get('serializer')->serialize(
            $posts,
            'json',
            SerializationContext::create()->setGroups(['client'])
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
     * @Route("/bulletin/view/posts/{id}")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getBulletinPostByIdAction(
        Request $request,
        $id
    ) {
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
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client']));

        return $view;
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     */
    protected function checkAdminBulletinPermission(
        $opLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_BULLETIN],
            ],
            $opLevel
        );
    }
}
