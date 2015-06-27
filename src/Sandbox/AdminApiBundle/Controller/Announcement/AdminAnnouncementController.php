<?php

namespace Sandbox\AdminApiBundle\Controller\Announcement;

use Sandbox\ApiBundle\Controller\Announcement\AnnouncementController;
use Sandbox\ApiBundle\Entity\Announcement\Announcement;
use Sandbox\ApiBundle\Form\Announcement\AnnouncementType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Acl\Exception\Exception;
use Sandbox\ApiBundle\Entity\Room\Room;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;

/**
 * Announcement controller
 *
 * @category Sandbox
 * @package  Sandbox\AdminApiBundle\Controller
 * @author   Sergi Uceda <sergiu@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 * @link     http://www.Sandbox.cn/
 */
class AdminAnnouncementController extends AnnouncementController
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

    /**
     * Post announcement
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
        $announcement = new Announcement();

        $form = $this->createForm(new AnnouncementType(), $announcement);

        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $now = new \DateTime("now");
        $announcement->setCreationDate($now);
        $announcement->setModificationDate($now);

        $em = $this->getDoctrine()->getManager();
        $em->persist($announcement);
        $em->flush();

        $response = array(
            "id" => $announcement->getId(),
        );

        return new View($response);
    }

    /**
     * Put announcement
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
        $announcement->setModificationDate(new \DateTime("now"));

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $response = array(
            "id" => $announcement->getId(),
        );

        return new View($response);
    }

    /**
     * Delete an announcement
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
     * @throws \Exception
     */
    public function deleteAnnouncementAction(
        Request $request,
        $id
    ) {
        // get announcement
        $room = $this->getRepo('Announcement\Announcement')->find($id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($room);
        $em->flush();
    }

    /**
     * Get order by array
     *
     * @param $paramFetcher
     * @return null|array
     */
    private function getSortBy(
        $paramFetcher
    ) {
        $sortBy = $paramFetcher->get('sort');

        switch ($sortBy) {
            case 'creation_date':
                return ['creationDate' => 'ASC'];
                break;
            case '-creation_date':
                return ['creationDate' => 'DESC'];
                break;
            case 'modification_date':
                return ['modificationDate' => 'ASC'];
                break;
            case '-modification_date':
                return ['modificationDate' => 'DESC'];
                break;
            default:
                return;
                break;
        }
    }
}
