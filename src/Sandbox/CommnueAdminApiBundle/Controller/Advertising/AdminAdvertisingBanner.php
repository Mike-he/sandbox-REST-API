<?php

namespace Sandbox\CommnueAdminApiBundle\Controller\Advertising;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\AdminApiBundle\Data\Banner\BannerPosition;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Banner\Banner;
use Sandbox\ApiBundle\Entity\Banner\BannerTag;
use Sandbox\ApiBundle\Form\Banner\BannerType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use Knp\Component\Pager\Paginator;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;

class AdminAdvertisingBanner extends SandboxRestController
{
    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
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
     * @Route("/advertising/banners")
     * @Method({"GET"})
     * @return View
     */
    public function getBannersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    )
    {
        $pageIndex = $paramFetcher->get('pageIndex');
        $pageLimit = $paramFetcher->get('pageLimit');
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
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/advertising/banner/{id}/position")
     * @Method({"POST"})
     * @return View
     */
    public function postBannerAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    )
    {
        $banner = new Banner();
        $form = $this->createForm( new BannerType(),$banner);
        $form->handleRequest($request);

        if(!$form->isValid()){
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $url = $form['url']->getData();

        return $this->handleBannerPost(
            $banner,
            $url
        );
    }

    /**
     * @param Banner $banner
     * @param $url
     * @return View
     */
    private function handleBannerPost(
        $banner,
        $url
    ) {
        $em = $this->getDoctrine()->getManager();

        $source = $banner->getSource();
        $sourceId = $banner->getSourceId();

        $banner->setTag(BannerTag::ADVERTISEMENT);

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
     * @param Request $request
     * @param $id
     *
     * @Route("/advertising/banners/{id}/position")
     * @Method({"POST"})
     * @return mixed
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