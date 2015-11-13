<?php

namespace Sandbox\AdminApiBundle\Controller\Verify;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\Feed\FeedController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;
use Sandbox\ApiBundle\Entity\Admin\AdminType;
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
     */
    public function getVerifyFeedsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminVerifyPermission(AdminPermissionMap::OP_LEVEL_VIEW);

        // filters
        $query = $paramFetcher->get('query');

        // check if query is null or empty
        if (is_null($query) || empty($query)) {
            return new View(array());
        }

        $feeds = $this->getRepo('Feed\FeedView')->getVerifyFeeds($query);

        return $this->handleGetFeeds($feeds, null);
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
     * @throws \Exception
     *
     * @return View
     */
    public function deleteFeedAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminVerifyPermission(AdminPermissionMap::OP_LEVEL_EDIT);

        $feed = $this->getRepo('Feed\Feed')->find($id);
        $this->throwNotFoundIfNull($feed, self::NOT_FOUND_MESSAGE);

        $em = $this->getDoctrine()->getManager();
        $em->remove($feed);
        $em->flush();
    }

    /**
     * Check user permission.
     *
     * @param int $opLevel
     */
    private function checkAdminVerifyPermission(
        $opLevel
    ) {
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_VERIFY,
            $opLevel
        );
    }
}
