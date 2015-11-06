<?php

namespace Sandbox\AdminApiBundle\Controller\Banner;

use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;
use Sandbox\ApiBundle\Entity\Admin\AdminType;
use Sandbox\ApiBundle\Entity\Banner\Banner;
use Sandbox\ApiBundle\Form\Banner\BannerType;
use Sandbox\ApiBundle\Form\Banner\BannerPutType;
use Sandbox\ApiBundle\Form\Banner\BannerPositionType;
use Sandbox\AdminApiBundle\Data\Banner\BannerPosition;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sandbox\ApiBundle\Controller\Banner\BannerController;
use Knp\Component\Pager\Paginator;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;

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
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many banners to return per page"
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
     * Change position of banner.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("/banners/{id}/position")
     * @Method({"POST"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function changeBannerPositionAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminBannerPermission(AdminPermissionMap::OP_LEVEL_EDIT);

        // get banner
        $banner = $this->getRepo('Banner\Banner')->find($id);
        $this->throwNotFoundIfNull($banner, self::NOT_FOUND_MESSAGE);

        $position = new BannerPosition();
        $form = $this->createForm(new BannerPositionType(), $position);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return $this->updateBannerPosition(
            $banner,
            $position
        );
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

        $url = $form['url']->getData();

        return $this->handleBannerPost(
            $banner,
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

        // get banner
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

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
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

        // get banner
        $banner = $this->getRepo('Banner\Banner')->find($id);
        $this->throwNotFoundIfNull($banner, self::NOT_FOUND_MESSAGE);

        // delete banner
        $em = $this->getDoctrine()->getManager();
        $em->remove($banner);
        $em->flush();

        return new View();
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
        $url
    ) {
        $em = $this->getDoctrine()->getManager();

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
     * @param Banner         $banner
     * @param BannerPosition $position
     *
     * @return View
     */
    private function updateBannerPosition(
        $banner,
        $position
    ) {
        $action = $position->getAction();

        // change banner position
        if ($action == BannerPosition::ACTION_TOP) {
            $banner->setSortTime(round(microtime(true) * 1000));
        } elseif (
            $action == BannerPosition::ACTION_UP ||
            $action == BannerPosition::ACTION_DOWN
        ) {
            $this->swapBannerPosition(
                $banner,
                $action
            );
        }

        // save
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * @param Banner $banner
     * @param string $action
     */
    private function swapBannerPosition(
        $banner,
        $action
    ) {
        $sortTime = $banner->getSortTime();
        $swapBanner = $this->getRepo('Banner\Banner')->findSwapBanner(
            $sortTime,
            $action
        );

        // swap banner sort time
        if (!is_null($swapBanner)) {
            $swapSortTime = $swapBanner->getSortTime();
            $banner->setSortTime($swapSortTime);
            $swapBanner->setSortTime($sortTime);
        }
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
            AdminPermission::KEY_PLATFORM_BANNER,
            $OpLevel
        );
    }
}
