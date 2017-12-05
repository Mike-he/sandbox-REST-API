<?php

namespace Sandbox\CommnueAdminApiBundle\Controller\Advertising;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\AdminApiBundle\Data\Banner\BannerPosition;
use Sandbox\ApiBundle\Controller\Advertising\AdvertisingController;
use Sandbox\ApiBundle\Entity\Banner\Banner;
use Sandbox\ApiBundle\Entity\Banner\CommnueBanner;
use Sandbox\ApiBundle\Entity\Material\CommnueMaterial;
use Sandbox\ApiBundle\Form\Advertising\AdvertisingPositionType;
use Sandbox\ApiBundle\Form\Banner\CommnueBannerPatchType;
use Sandbox\ApiBundle\Form\Banner\CommnueBannerType;
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
use Sandbox\ApiBundle\Form\Banner\BannerPositionType;

class AdminAdvertisingBannerController extends AdvertisingController
{
    /**
     * Get Commnue Banner List
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
            ->getRepository('SandboxApiBundle:Banner\CommnueBanner')
            ->getAdminBannerList(
                $search
            );

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $banners,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * Get Commnue Banner By Id
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
            ->getRepository('SandboxApiBundle:Banner\CommnueBanner')
            ->find($id);

        $this->throwNotFoundIfNull($banner,self::NOT_FOUND_MESSAGE);

        return new View($banner);
    }

    /**
     * Create Commnue Banner
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

        $banner = new CommnueBanner();
        $form = $this->createForm( new CommnueBannerType(),$banner);
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
     * Update Commnue Banner.
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
        $banner = $this->getRepo('Banner\CommnueBanner')->find($id);
        $this->throwNotFoundIfNull($banner, self::NOT_FOUND_MESSAGE);

        $bannerJson = $this->container->get('serializer')->serialize($banner, 'json');

        $patch = new Patch($bannerJson, $request->getContent());
        $bannerJson = $patch->apply();

        $form = $this->createForm(new CommnueBannerPatchType(), $banner);
        $form->submit(json_decode($bannerJson, true));

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * Delete Commnue Banner
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
       // $this->checkAdminBannerPermission(AdminPermission::OP_LEVEL_EDIT);

        $banner = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Banner\CommnueBanner')
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
        $banner = $this->getRepo('Banner\CommnueBanner')->find($id);
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
        $sourceCat = $banner->getSourceCat();

       switch ($source) {
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
           case 'material':
               $this->handleMaterial($banner, $sourceCat, $sourceId);

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
                $sourceCat,
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
     * @param Banner $banner
     * @param $sourceCat
     * @param $sourceId
     *
     * @return View
     */
    private function handleMaterial(
        $banner,
        $sourceCat,
        $sourceId
    ) {
        $sourceArray = [
            CommnueMaterial::SOURCE_NEWS,
            CommnueMaterial::SOURCE_ANNOUNCEMENT,
            CommnueMaterial::SOURCE_INSTRUCTION
        ];

        $banner->setSource($sourceCat);

        switch($sourceCat){
            case Banner::SOURCE_EVENT:
                $this->setBannerContentForEvent(
                    $banner,
                    $sourceId
                );
                break;
            case in_array($sourceCat, $sourceArray):
                $this->setBannerContentForMaterial(
                    $banner,
                    $sourceId
                );
                break;
            default:
                return $this->customErrorView(
                    400,
                    self::WRONG_SOURCE_CODE,
                    self::WRONG_SOURCE_MESSAGE
                );
                break;
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
            $existBanner = $this->getRepo('Banner\CommnueBanner')->findOneBy(
                [
                    'source' => 'url',
                    'content' => $url,
                ]
            );
        } else {
            $existBanner = $this->getRepo('Banner\CommnueBanner')->findOneBy(
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
     * @param CommnueBanner $banner
     * @param string $action
     */
    private function swapBannerPosition(
        $banner,
        $action
    ) {
        $sortTime = $banner->getSortTime();
        $swapBanner = $this->getRepo('Banner\CommnueBanner')->findSwapBanner(
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