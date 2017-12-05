<?php

namespace Sandbox\CommnueAdminApiBundle\Controller\Advertising;

use Sandbox\ApiBundle\Controller\Advertising\AdvertisingController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Entity\Advertising\CommnueAdvertisingMiddle;
use Sandbox\ApiBundle\Entity\Material\CommnueMaterial;
use Sandbox\ApiBundle\Form\Advertising\AdvertisingPositionType;
use Sandbox\ApiBundle\Form\Advertising\CommnueAdvertisingMiddlePatchType;
use Sandbox\ApiBundle\Form\Advertising\CommnueAdvertisingMiddleType;
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

class AdminAdvertisingMiddleController extends AdvertisingController
{
    /**
     * Get Advertising Middle List
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
     *    description="How many advertising middles to return per page"
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
     * @Route("/commercial/middles")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getAdvertisingMiddlesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminAdvertisementPermission(AdminPermission::OP_LEVEL_VIEW);

        $pageIndex = $paramFetcher->get('pageIndex');
        $pageLimit = $paramFetcher->get('pageLimit');
        $search = $paramFetcher->get('search');

        $middles = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Advertising\CommnueAdvertisingMiddle')
            ->getMiddleList($search);

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $middles,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * Get Advertising Middle By Id
     *
     * @param $id
     *
     * @Route("/commercial/middles/{id}")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdvertisingMiddleByIdAction(
        $id
    ) {
        // check user permission
        $this->checkAdminAdvertisementPermission(AdminPermission::OP_LEVEL_VIEW);

        $middle = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Advertising\CommnueAdvertisingMiddle')
            ->find($id);

        $this->throwNotFoundIfNull($middle,self::NOT_FOUND_MESSAGE);

        return new View($middle);
    }

    /**
     * Create Advertising Middle
     *
     * @param Request $request
     *
     * @Route("/commercial/middles")
     * @Method({"POST"})
     *
     * @return View
     * @throws \Exception
     */
    public function postAdvertisingMiddleAction(
        Request $request
    ) {
        // check user permission
        $this->checkAdminAdvertisementPermission(AdminPermission::OP_LEVEL_EDIT);

        $middle = new CommnueAdvertisingMiddle();
        $form = $this->createForm(new CommnueAdvertisingMiddleType(), $middle);
        $form->handleRequest($request);

        if(!$form->isValid()){
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $url = $form['url']->getData();

        return $this->handleMiddlePost(
            $middle,
            $url
        );
    }

    /**
     * Update Advertising Middle
     *
     * @param Request $request
     * @param $id
     *
     * @Route("/commercial/middles/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     */
    public function patchAdvertisingMiddleAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminAdvertisementPermission(AdminPermission::OP_LEVEL_EDIT);

       $middle = $this->getDoctrine()
           ->getRepository('SandboxApiBundle:Advertising\CommnueAdvertisingMiddle')
           ->find($id);

        $this->throwNotFoundIfNull($middle, self::NOT_FOUND_MESSAGE);

        $middleJson = $this->container->get('serializer')->serialize($middle, 'json');

        $patch = new Patch($middleJson, $request->getContent());
        $middleJson = $patch->apply();

        $form = $this->createForm(new CommnueAdvertisingMiddlePatchType(), $middle);
        $form->submit(json_decode($middleJson, true));

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * Delete Advertising Middle
     *
     * @param $id
     *
     * @Route("/commercial/middles/{id}")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function deleteAdvertisingMiddleAction(
        $id
    ) {
        // check user permission
        $this->checkAdminAdvertisementPermission(AdminPermission::OP_LEVEL_EDIT);

        $middle = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Advertising\CommnueAdvertisingMiddle')
            ->find($id);

        $this->throwNotFoundIfNull($middle, self::NOT_FOUND_MESSAGE);

        $em = $this->getDoctrine()->getManager();
        $em->remove($middle);

        $em->flush();

        return new View();
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Route("/commercial/middles/{id}/position")
     * @Method({"POST"})
     *
     * @return View
     */
    public function changeMiddlePositionAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminAdvertisementPermission(AdminPermission::OP_LEVEL_EDIT);

        $middle = $this->getRepo('Advertising\CommnueAdvertisingMiddle')->find($id);
        $this->throwNotFoundIfNull($middle, self::NOT_FOUND_MESSAGE);
        $position = new AdvertisingPosition();
        $form = $this->createForm(new AdvertisingPositionType(), $position);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        return $this->updateAdvertisingMiddlePosition(
            $middle,
            $position
        );
    }

    /**
     * @param CommnueAdvertisingMiddle $middle
     * @param $url
     * @return View
     */
    private function handleMiddlePost(
        $middle,
        $url
    ) {
        $em = $this->getDoctrine()->getManager();

        $source = $middle->getSource();
        $sourceId = $middle->getSourceId();
        $sourceCat = $middle->getSourceCat();

        switch ($source) {
            case CommnueAdvertisingMiddle::SOURCE_URL:
                if (is_null($url) || empty($url)) {
                    return $this->customErrorView(
                        400,
                        self::URL_NULL_CODE,
                        self::URL_NULL_MESSAGE
                    );
                }
                $middle->setContent($url);

                break;
            case CommnueAdvertisingMiddle::SOURCE_BLANK_BLOCK:
                break;
            case 'material':
                $this->handleMaterial($middle, $sourceCat, $sourceId);

                break;
            default:
                return $this->customErrorView(
                    400,
                    self::WRONG_SOURCE_CODE,
                    self::WRONG_SOURCE_MESSAGE
                );

                break;
        }

        // check if advertising middle already exists
        if ($source != CommnueAdvertisingMiddle::SOURCE_BLANK_BLOCK) {
            $existMiddle = $this->getExistingMiddle(
                $source,
                $sourceId,
                $url
            );

            if (!is_null($existMiddle)) {
                return $this->customErrorView(
                    400,
                    self::ADVERTISEMENT_ALREADY_EXIST_CODE,
                    self::ADVERTISEMENT_ALREADY_EXIST_MESSAGE
                );
            }
        }

        $em->persist($middle);
        $em->flush();

        return new View(array(
            'id' => $middle->getId(),
        ));
    }

    /**
     * Set Advertising Middle For Event
     *
     * @param CommnueAdvertisingMiddle $middle
     * @param int    $sourceId
     */
    private function setMiddleContentForEvent(
        $middle,
        $sourceId
    ) {
        $event = $this->getRepo('Event\Event')->find($sourceId);
        $this->throwNotFoundIfNull($event, self::NOT_FOUND_MESSAGE);

        $middle->setContent($event->getName());
    }

    /**
     * Set Advertising Middle For Material.
     *
     * @param CommnueAdvertisingMiddle $middle
     * @param int    $sourceId
     */
    private function setMiddleContentForMaterial(
        $middle,
        $sourceId
    ) {
        $material = $this->getRepo('Material\CommnueMaterial')->find($sourceId);
        $this->throwNotFoundIfNull($material, self::NOT_FOUND_MESSAGE);

        $middle->setContent($material->getTitle());
    }

    /**
     * @param $source
     * @param $sourceId
     * @param $url
     * @return object
     */
    private function getExistingMiddle(
        $source,
        $sourceId,
        $url
    ) {
        if (!is_null($url)) {
            $existMiddle = $this->getRepo('Advertising\CommnueAdvertisingMiddle')->findOneBy(
                [
                    'source' => $source,
                    'content' => $url,
                ]
            );
        } else {
            $existMiddle = $this->getRepo('Advertising\CommnueAdvertisingMiddle')->findOneBy(
                [
                    'source' => $source,
                    'sourceId' => $sourceId,
                ]
            );
        }

        return $existMiddle;
    }

    /**
     * @param CommnueAdvertisingMiddle         $middle
     * @param AdvertisingPosition $position
     *
     * @return View
     */
    private function updateAdvertisingMiddlePosition(
        $middle,
        $position
    ) {
        $action = $position->getAction();

        if ($action == AdvertisingPosition::ACTION_TOP) {
            $middle->setSortTime(round(microtime(true) * 1000));
        } elseif (
            $action == AdvertisingPosition::ACTION_UP ||
            $action == AdvertisingPosition::ACTION_DOWN
        ) {
            $this->swapAdvertisingMiddlePosition(
                $middle,
                $action
            );
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * @param CommnueAdvertisingMiddle         $middle
     * @param string $action
     */
    private function swapAdvertisingMiddlePosition(
        $middle,
        $action
    ) {
        $sortTime = $middle->getSortTime();
        $swapMiddle = $this->getRepo('Advertising\CommnueAdvertisingMiddle')->findSwapMiddle(
            $sortTime,
            $action
        );

        if (!is_null($swapMiddle)) {
            $swapSortTime = $swapMiddle->getSortTime();
            $middle->setSortTime($swapSortTime);
            $swapMiddle->setSortTime($sortTime);
        }
    }

    /**
     * @param CommnueAdvertisingMiddle         $middle
     * @param $sourceCat
     * @param $sourceId
     *
     * @return View
     */
    private function handleMaterial(
        $middle,
        $sourceCat,
        $sourceId
    ) {
        $sourceArray = [
            CommnueMaterial::SOURCE_NEWS,
            CommnueMaterial::SOURCE_ANNOUNCEMENT,
            CommnueMaterial::SOURCE_INSTRUCTION,
            CommnueMaterial::SOURCE_ADVERTISING
        ];

        $middle->setSource($sourceCat);

        switch($sourceCat){
            case CommnueAdvertisingMiddle::SOURCE_EVENT:
                $this->setMiddleContentForEvent(
                    $middle,
                    $sourceId
                );
                break;
            case in_array($sourceCat, $sourceArray):
                $this->setMiddleContentForMaterial(
                    $middle,
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
     * Check user permission.
     *
     * @param int $opLevel
     */
    private function checkAdminAdvertisementPermission($opLevel)
    {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_COMMNUE_PLATFORM_ADVERTISEMENT],
            ],
            $opLevel
        );
    }
}