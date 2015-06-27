<?php

namespace Sandbox\ClientApiBundle\Controller\Announcement;

use Sandbox\ApiBundle\Controller\Announcement\AnnouncementController;
use Sandbox\ApiBundle\Entity\Announcement\Announcement;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Security\Acl\Exception\Exception;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;

/**
 * Client Announcement controller
 *
 * @category Sandbox
 * @package  Sandbox\ClientApiBundle\Controller
 * @author   Sergi Uceda <sergiu@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 * @link     http://www.Sandbox.cn/
 */
class ClientAnnouncementController extends AnnouncementController
{
    /**
     * Get announcements
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
     * @Annotations\QueryParam(
     *    name="sort",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="(creation_date|-creation_date|modification_date|-modification_date)",
     *    strict=true,
     *    description="Sort by date"
     * )
     *
     * @Route("/announcements")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getAnnouncementsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // get announcement
        $announcement = $this->getRepo('Announcement\Announcement');

        //sort by
        $sortBy = $this->getSortBy($paramFetcher);

        //find all with or without sort
        $announcement = is_null($sortBy) ?
            $announcement->findAll() :
            $announcement->findBy(
                [],
                $sortBy
            )
        ;

        return new View($announcement);
    }

    /**
     * Get announcement by id
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
     * @Route("/announcements/{id}")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getAnnouncementByIdAction(
        Request $request,
        $id
    ) {
        // get announcement
        $announcement = $this->getRepo('Announcement\Announcement')->find($id);

        return new View($announcement);
    }
}
