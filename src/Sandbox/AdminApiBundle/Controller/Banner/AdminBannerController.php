<?php

namespace Sandbox\AdminApiBundle\Controller\Banner;

use Doctrine\ORM\EntityManager;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;
use Sandbox\ApiBundle\Entity\Admin\AdminType;
use Sandbox\ApiBundle\Entity\Banner\Banner;
use Sandbox\ApiBundle\Entity\Banner\BannerAttachment;
use Sandbox\ApiBundle\Form\Banner\BannerType;
use Sandbox\ApiBundle\Form\Banner\BannerPutType;
use Sandbox\ApiBundle\Form\Banner\BannerAttachmentType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sandbox\ApiBundle\Controller\Banner\BannerController;
use FOS\RestBundle\View\View;
use Knp\Component\Pager\Paginator;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;

/**
 * Admin Banner Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xue <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminBannerController extends BannerController
{
    /**
     * Get Banner List.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many food to return per page"
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
     *    name="search",
     *    default=null,
     *    nullable=true,
     *    description="search query"
     * )
     *
     * @Route("/banners")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getBannersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminBannerPermission(AdminPermissionMap::OP_LEVEL_EDIT);

        // filters
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $search = $paramFetcher->get('search');

        $query = $this->getRepo('Banner\Banner')->getBannerList(
            $search
        );

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $query,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * Post Banner.
     *
     * @param Request $request
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     201 = "Returned when successful created"
     *  }
     * )
     *
     * @Route("/banners")
     * @Method({"POST"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postBannerAction(
        Request $request
    ) {
        // check user permission
        $this->checkAdminBannerPermission(AdminPermissionMap::OP_LEVEL_EDIT);

        $banner = new Banner();

        $form = $this->createForm(new BannerType(), $banner);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $attachments = $form['banner_attachments']->getData();
        $url = $form['url']->getData();

        return $this->handleBannerPost(
            $banner,
            $attachments,
            $url
        );
    }

    /**
     * Update Banner.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("/banners/{id}")
     * @Method({"PUT"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function putBannerAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminBannerPermission(AdminPermissionMap::OP_LEVEL_EDIT);

        // get food
        $banner = $this->getRepo('Banner\Banner')->find($id);
        $this->throwNotFoundIfNull($banner, self::NOT_FOUND_MESSAGE);

        $form = $this->createForm(
            new BannerPutType(),
            $banner,
            array(
                'method' => 'PUT',
            )
        );
        $form->handleRequest($request);

        $attachments = $form['banner_attachments']->getData();

        return $this->handleBannerPut(
            $banner,
            $attachments
        );
    }

    /**
     * @param Banner $banner
     * @param array  $attachments
     * @param string $url
     *
     * @return View
     */
    private function handleBannerPost(
        $banner,
        $attachments,
        $url
    ) {
        $em = $this->getDoctrine()->getManager();
        if (is_null($attachments) || empty($attachments)) {
            throw new BadRequestHttpException(self::ATTACHMENT_NULL);
        }
        // add attachments
        $this->addBannerAttachment(
            $em,
            $banner,
            $attachments
        );

        $source = $banner->getSource();
        $sourceId = $banner->getSourceId();
        switch ($source) {
            case Banner::SOURCE_EVENT:
                $this->setBannerContentForEvent(
                    $banner,
                    $sourceId
                );

                break;
            case Banner::SOURCE_NEWS:
                $this->setBannerContentForNews(
                    $banner,
                    $sourceId
                );

                break;
            case Banner::SOURCE_URL:
                if (is_null($url) || empty($url)) {
                    throw new BadRequestHttpException(self::URL_NULL);
                }
                $banner->setContent($url);

                break;
            default:
                throw new BadRequestHttpException(self::WRONG_SOURCE);

                break;
        }

        // check if banner already exists
        $existBanner = $this->getRepo('Banner\Banner')->findOneBy(
            [
                'source' => $source,
                'sourceId' => $sourceId,
            ]
        );
        if (!is_null($existBanner)) {
            throw new BadRequestHttpException(self::BANNER_ALREADY_EXIST);
        }

        $em->persist($banner);
        $em->flush();
        $response = array(
            'id' => $banner->getId(),
        );

        return new View($response);
    }

    /**
     * @param Banner $banner
     * @param array  $attachments
     *
     * @return View
     */
    private function handleBannerPut(
        $banner,
        $attachments
    ) {
        $em = $this->getDoctrine()->getManager();
        if (!is_null($attachments) && !empty($attachments)) {
            // remove attachments
            $this->removeAttachment(
                $em,
                $banner
            );

            // add attachments
            $this->addBannerAttachment(
                $em,
                $banner,
                $attachments
            );
        }

        $em->flush();

        return new View();
    }

    /**
     * @param $em
     * @param $banner
     */
    private function removeAttachment(
        $em,
        $banner
    ) {
        $attachments = $this->getRepo('Banner\BannerAttachment')->findBy(['banner' => $banner]);
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                $em->remove($attachment);
            }
        }
    }

    /**
     * set banner content for event.
     *
     * @param Banner $banner
     * @param int    $sourceId
     */
    private function setBannerContentForEvent(
        $banner,
        $sourceId
    ) {
        $event = $this->getRepo('Event\Event')->find($sourceId);
        $this->throwNotFoundIfNull($event, self::NOT_FOUND_MESSAGE);

        $banner->setContent($event->getName());
    }

    /**
     * set banner content for news.
     *
     * @param Banner $banner
     * @param int    $sourceId
     */
    private function setBannerContentForNews(
        $banner,
        $sourceId
    ) {
        $news = $this->getRepo('News\News')->find($sourceId);
        $this->throwNotFoundIfNull($news, self::NOT_FOUND_MESSAGE);

        $banner->setContent($news->getTitle());
    }

    /**
     * Save attachment to db.
     *
     * @param EntityManager $em
     * @param Banner        $banner
     * @param Array         $attachments
     */
    private function addBannerAttachment(
        $em,
        $banner,
        $attachments
    ) {
        foreach ($attachments as $attachment) {
            $bannerAttachment = new BannerAttachment();
            $form = $this->createForm(new BannerAttachmentType(), $bannerAttachment);
            $form->submit($attachment, true);

            $bannerAttachment->setBanner($banner);
            $em->persist($bannerAttachment);
        }
    }

    /**
     * Delete Banner.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @Route("/banners/{id}")
     * @Method({"DELETE"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function deleteBannerAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminBannerPermission(AdminPermissionMap::OP_LEVEL_EDIT);

        // get food
        $banner = $this->getRepo('Banner\Banner')->find($id);
        $this->throwNotFoundIfNull($banner, self::NOT_FOUND_MESSAGE);

        // delete food
        $em = $this->getDoctrine()->getManager();
        $em->remove($banner);
        $em->flush();

        return new View();
    }

    /**
     * Check user permission.
     *
     * @param Integer $OpLevel
     */
    private function checkAdminBannerPermission(
        $OpLevel
    ) {
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_ROOM,
            $OpLevel
        );
    }
}
