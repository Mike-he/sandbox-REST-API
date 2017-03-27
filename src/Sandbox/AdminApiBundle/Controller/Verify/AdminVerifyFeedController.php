<?php

namespace Sandbox\AdminApiBundle\Controller\Verify;

use Knp\Component\Pager\Paginator;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\Feed\FeedController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;

/**
 * Class AdminVerifyFeedController.
 *
 * @category Sandbox
 *
 * @author   Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminVerifyFeedController extends FeedController
{
    /**
     * Get admin verify feeds.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
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
     *    name="query",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    description="query key word"
     * )
     *
     * @Route("/feeds")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getVerifyFeedsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminVerifyPermission(AdminPermission::OP_LEVEL_VIEW);

        // filters
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $query = $paramFetcher->get('query');

        // check if query is null or empty
        if (is_null($query) || empty($query)) {
            return new View(array());
        }

        $feeds = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Feed\FeedView')
            ->getVerifyFeeds($query);

        $feedsArray = $this->handleGetVerifyFeeds($feeds, null);

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $feedsArray,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * Delete feed by id.
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
     * @return View
     *
     * @throws \Exception
     */
    public function deleteFeedAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminVerifyPermission(AdminPermission::OP_LEVEL_EDIT);

        $feed = $this->getRepo('Feed\Feed')->find($id);
        $this->throwNotFoundIfNull($feed, self::NOT_FOUND_MESSAGE);

        // set feed visible false
        $feed->setIsDeleted(true);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     */
    private function checkAdminVerifyPermission(
        $opLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_VERIFY],
            ],
            $opLevel
        );
    }
}
