<?php

namespace Sandbox\ClientApiBundle\Controller\Company;

use Sandbox\ApiBundle\Controller\Company\DirectoryController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Rest controller for Directories.
 *
 * @category Sandbox
 *
 * @author   Allan SIMON <simona@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientDirectoryController extends DirectoryController
{
    /**
     * List all members in the companies the current user belongs to.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Annotations\View()
     * @Annotations\QueryParam(
     *    name="search",
     *    default=null,
     *    nullable=true,
     *    description="search members matching the search query"
     * )
     *
     * @return array
     */
    public function getDirectoriesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $username = $this->getUsername();

        $search = $paramFetcher->get('search');
        $repo = $this->getRepo('Directory');

        $members = null;
        $members = $this->ifNotNullGetItems(
            is_null($search),
            $members,
            function () use ($username, $repo, $search) {
                return $repo->findVisibleMatchingSearch(
                    $username,
                    $search
                );
            }
        );
        $members = $this->ifNotNullGetItems(
            // will be executed if no other filter has been applied
            // yet
            false,
            $members,
            function () use ($username, $repo, $search) {
                return $repo->findAllVisible($username);
            }
        );

        return new View($members);
    }
}
