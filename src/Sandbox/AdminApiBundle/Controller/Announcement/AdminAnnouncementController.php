<?php

namespace Sandbox\AdminApiBundle\Controller\Announcement;

use Knp\Component\Pager\Paginator;
use Sandbox\ApiBundle\Controller\Announcement\AnnouncementController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;
use Sandbox\ApiBundle\Entity\Admin\AdminType;
use Sandbox\ApiBundle\Entity\Announcement\Announcement;
use Sandbox\ApiBundle\Form\Announcement\AnnouncementType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;

/**
 * Announcement controller.
 *
 * @category Sandbox
 *
 * @author   Sergi Uceda <sergiu@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminAnnouncementController extends AnnouncementController
{
    /**
     * Get announcements.
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
     *    name="pageIndex",
     *    array=false,
     *    default="1",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="page number "
     * )
     *
     *
     * @Annotations\QueryParam(
     *    name="sortBy",
     *    array=false,
     *    default="id",
     *    nullable=true,
     *    requirements="(creation_date|modification_date)",
     *    strict=true,
     *    description="Sort by date"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many announcements to return "
     * )
     *
     * @Annotations\QueryParam(
     *    name="direction",
     *    array=false,
     *    default="ASC",
     *    nullable=true,
     *    requirements="(ASC|DESC)",
     *    strict=true,
     *    description="sort direction"
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
        // check user permission
        $this->checkAdminAnnouncementPermission(AdminPermissionMap::OP_LEVEL_VIEW);

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $direction = $paramFetcher->get('direction');
        $sortBy = $paramFetcher->get('sortBy');

        if ($sortBy === 'modification_date') {
            $sortBy = 'modificationDate';
        } elseif ($sortBy === 'creation_date') {
            $sortBy = 'creationDate';
        }

        // get announcement repository
        $repo = $this->getRepo('Announcement\Announcement');

        $query = $repo->createQueryBuilder('a')
            ->orderBy('a.'.$sortBy, $direction)
            ->getQuery();

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $query,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * Get announcement by id.
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
        // check user permission
        $this->checkAdminAnnouncementPermission(AdminPermissionMap::OP_LEVEL_VIEW);

        // get announcement
        $announcement = $this->getRepo('Announcement\Announcement')->find($id);

        return new View($announcement);
    }

    /**
     * Post announcement.
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
     * @Route("/announcements")
     * @Method({"POST"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function postAnnouncementAction(
        Request $request
    ) {
        // check user permission
        $this->checkAdminAnnouncementPermission(AdminPermissionMap::OP_LEVEL_EDIT);

        $announcement = new Announcement();

        $form = $this->createForm(new AnnouncementType(), $announcement);

        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $now = new \DateTime('now');
        $announcement->setCreationDate($now);
        $announcement->setModificationDate($now);

        $em = $this->getDoctrine()->getManager();
        $em->persist($announcement);
        $em->flush();

        $response = array(
            'id' => $announcement->getId(),
        );

        return new View($response);
    }

    /**
     * Put announcement.
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
     * @Method({"PUT"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function putAnnouncementAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminAnnouncementPermission(AdminPermissionMap::OP_LEVEL_EDIT);

        $announcement = $this->getRepo('Announcement\Announcement')->find($id);

        $form = $this->createForm(
            new AnnouncementType(),
            $announcement,
            array('method' => 'PUT')
        );

        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $content = json_decode($request->getContent(), true);

        $announcement->setTitle($content['title']);
        $announcement->setDescription($content['description']);
        $announcement->setModificationDate(new \DateTime('now'));

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $response = array(
            'id' => $announcement->getId(),
        );

        return new View($response);
    }

    /**
     * Delete an announcement.
     *
     * @param Request $request the request object
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     204 = "OK"
     *  }
     * )
     *
     * @Route("/announcements/{id}")
     * @Method({"DELETE"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function deleteAnnouncementAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminAnnouncementPermission(AdminPermissionMap::OP_LEVEL_EDIT);

        // get announcement
        $room = $this->getRepo('Announcement\Announcement')->find($id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($room);
        $em->flush();
    }

    /**
     * Check user permission.
     *
     * @param Integer $OpLevel
     */
    private function checkAdminAnnouncementPermission(
        $OpLevel
    ) {
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_ANNOUNCEMENT,
            $OpLevel
        );
    }
}
