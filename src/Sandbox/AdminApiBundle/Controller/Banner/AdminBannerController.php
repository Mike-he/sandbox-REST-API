<?php

namespace Sandbox\AdminApiBundle\Controller\Banner;

use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
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
 * @author   Leo Xu <leox@gobeta.com.cn>
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
        $this->checkAdminBannerPermission(AdminPermission::OP_LEVEL_VIEW);

        // filters
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $search = $paramFetcher->get('search');

        $banners = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Banner\Banner')
            ->getAdminBannerList(
                $search
            );

        foreach ($banners as $banner) {
            // translate tag name
            $tagName = $banner->getTag()->getKey();
            $trans = $this->get('translator')->trans($tagName);
            $banner->getTag()->setName($trans);
        }

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $banners,
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
        $this->checkAdminBannerPermission(AdminPermission::OP_LEVEL_EDIT);

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
        $this->checkAdminBannerPermission(AdminPermission::OP_LEVEL_EDIT);

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
        $this->checkAdminBannerPermission(AdminPermission::OP_LEVEL_EDIT);

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

        $tagId = $banner->getTagId();
        $tag = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Banner\BannerTag')
            ->find($tagId);
        if (is_null($tag)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $banner->setTag($tag);
        $banner->setModificationDate(new \DateTime('now'));

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
        $this->checkAdminBannerPermission(AdminPermission::OP_LEVEL_EDIT);

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
     * Get Banner By Id.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @Route("/banners/{id}")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getBannerByIdAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminBannerPermission(AdminPermission::OP_LEVEL_VIEW);

        // get banner
        $banner = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Banner\Banner')
            ->find($id);
        $this->throwNotFoundIfNull($banner, self::NOT_FOUND_MESSAGE);

        // translate tag name
        $tag = $banner->getTag();
        $trans = $this->container->get('translator')->trans($tag->getKey());
        $tag->setName($trans);

        return new View($banner);
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

        $tagId = $banner->getTagId();
        $tag = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Banner\BannerTag')
            ->find($tagId);
        if (is_null($tag)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $banner->setTag($tag);

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
                    return $this->customErrorView(
                        400,
                        self::URL_NULL_CODE,
                        self::URL_NULL_MESSAGE
                    );
                }
                $banner->setContent($url);

                break;
            case Banner::SOURCE_BLANK_BLOCK:
                break;
            default:
                return $this->customErrorView(
                    400,
                    self::WRONG_SOURCE_CODE,
                    self::WRONG_SOURCE_MESSAGE
                );

                break;
        }

        // check if banner already exists
        if ($source != Banner::SOURCE_BLANK_BLOCK) {
            $existBanner = $this->getExistingBanner(
                $source,
                $sourceId,
                $url
            );

            if (!is_null($existBanner)) {
                return $this->customErrorView(
                    400,
                    self::BANNER_ALREADY_EXIST_CODE,
                    self::BANNER_ALREADY_EXIST_MESSAGE
                );
            }
        }

        $em->persist($banner);
        $em->flush();
        $response = array(
            'id' => $banner->getId(),
        );

        return new View($response);
    }

    /**
     * @param string $source
     * @param int    $sourceId
     * @param string $url
     */
    private function getExistingBanner(
        $source,
        $sourceId,
        $url
    ) {
        if (!is_null($url)) {
            $existBanner = $this->getRepo('Banner\Banner')->findOneBy(
                [
                    'source' => $source,
                    'content' => $url,
                ]
            );
        } else {
            $existBanner = $this->getRepo('Banner\Banner')->findOneBy(
                [
                    'source' => $source,
                    'sourceId' => $sourceId,
                ]
            );
        }

        return $existBanner;
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
     * @param int $opLevel
     */
    private function checkAdminBannerPermission($opLevel)
    {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_OFFICIAL_PLATFORM_BANNER],
            ],
            $opLevel
        );
    }
}
