<?php

namespace Sandbox\CommnueAdminApiBundle\Controller\Advertising;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\AdminApiBundle\Data\Banner\BannerPosition;
use Sandbox\ApiBundle\Controller\Advertising\AdvertisingController;
use Sandbox\ApiBundle\Entity\Banner\Banner;
use Sandbox\ApiBundle\Entity\Banner\BannerTag;
use Sandbox\ApiBundle\Entity\Material\CommnueMaterial;
use Sandbox\ApiBundle\Form\Advertising\AdvertisingPositionType;
use Sandbox\ApiBundle\Form\Banner\BannerPatchType;
use Sandbox\ApiBundle\Form\Banner\BannerType;
use Sandbox\CommnueAdminApiBundle\Data\Advertising\AdvertisingPosition;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use Knp\Component\Pager\Paginator;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Rs\Json\Patch;

class AdminAdvertisingBannerController extends AdvertisingController
{
    /**
     * Get Banner List
     *
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
     *
     * @return View
     */
    public function getBannersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    )
    {
        // check user permission
        $this->checkAdminBannerPermission(AdminPermission::OP_LEVEL_VIEW);

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
     * Get Banner By Id
     *
     * @param $id
     *
     * @Route("/advertising/banners/{id}")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getBannerByIdAction(
        $id
    ) {
        // check user permission
        $this->checkAdminBannerPermission(AdminPermission::OP_LEVEL_VIEW);

        $banner = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Banner\Banner')
            ->find($id);

        $this->throwNotFoundIfNull($banner,self::NOT_FOUND_MESSAGE);

        // translate tag name
        $tag = $banner->getTag();
        $trans = $this->container->get('translator')->trans($tag->getKey());
        $tag->setName($trans);

        return new View($banner);
    }

    /**
     * Create Banner
     *
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/advertising/banners")
     * @Method({"POST"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postBannerAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    )
    {
        // check user permission
        $this->checkAdminBannerPermission(AdminPermission::OP_LEVEL_EDIT);

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
     * Update Banner.
     *
     * @param Request $request
     * @param int     $id
     *
     * @Route("/advertising/banners/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function patchBannerAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminBannerPermission(AdminPermission::OP_LEVEL_EDIT);

        // get banner
        $banner = $this->getRepo('Banner\Banner')->find($id);
        $this->throwNotFoundIfNull($banner, self::NOT_FOUND_MESSAGE);

        $bannerJson = $this->container->get('serializer')->serialize($banner, 'json');

        $patch = new Patch($bannerJson, $request->getContent());
        $bannerJson = $patch->apply();

        $form = $this->createForm(new BannerPatchType(), $banner);
        $form->submit(json_decode($bannerJson, true));

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * Delete Banner
     *
     * @param $id
     *
     * @Route("/advertising/banners/{id}")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function deleteBannerAction(
        $id
    ) {
        // check user permission
        $this->checkAdminBannerPermission(AdminPermission::OP_LEVEL_EDIT);

        $banner = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Banner\Banner')
            ->find($id);

        $this->throwNotFoundIfNull($banner, self::NOT_FOUND_MESSAGE);

        $em = $this->getDoctrine()->getManager();
        $em->remove($banner);
        $em->flush();

        return new View();
    }

    /**
     * Change Banner Position
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

        $position = new AdvertisingPosition();
        $form = $this->createForm(new AdvertisingPositionType(), $position);
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

        $tag = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Banner\BannerTag')
            ->findOneBy(array('key'=>BannerTag::ADVERTISEMENT));

        $banner->setTag($tag);

        $sourceArray = [
            CommnueMaterial::SOURCE_NEWS,
            CommnueMaterial::SOURCE_ANNOUNCEMENT,
            CommnueMaterial::SOURCE_INSTRUCTION
        ];

        switch ($source) {
            case Banner::SOURCE_EVENT:
                $this->setBannerContentForEvent(
                    $banner,
                    $sourceId
                );
                break;
            case in_array($source, $sourceArray):
                $this->setBannerContentForMaterial(
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

        return new View(array(
            'id' => $banner->getId(),
        ));
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
    private function setBannerContentForMaterial(
        $banner,
        $sourceId
    ) {
        $material = $this->getRepo('Material\CommnueMaterial')->find($sourceId);
        $this->throwNotFoundIfNull($material, self::NOT_FOUND_MESSAGE);

        $banner->setContent($material->getTitle());
    }

    /**
     * @param $source
     * @param $sourceId
     * @param $url
     * @return object
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
                ['key' => AdminPermission::KEY_COMMNUE_PLATFORM_BANNER],
            ],
            $opLevel
        );
    }
}