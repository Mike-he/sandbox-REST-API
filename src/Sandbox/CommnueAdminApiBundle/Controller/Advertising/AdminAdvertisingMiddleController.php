<?php

namespace Sandbox\CommnueAdminApiBundle\Controller\Advertising;

use Sandbox\ApiBundle\Controller\Banner\BannerController;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Entity\Advertising\CommnueAdvertisingMiddle;
use Sandbox\ApiBundle\Form\Advertising\CommnueAdvertisingMiddlePatchType;
use Sandbox\ApiBundle\Form\Advertising\CommnueAdvertisingMiddleType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use Knp\Component\Pager\Paginator;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Rs\Json\Patch;

class AdminAdvertisingMiddleController extends BannerController
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
     * @Route("/advertising/middles")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getAdvertisingMiddlesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $pageIndex = $paramFetcher->get('pageIndex');
        $pageLimit = $paramFetcher->get('pageLimit');

        $middles = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Advertising\CommnueAdvertisingMiddle')
            ->findAll();

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
     * @Route("/advertising/middles/{id}")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdvertisingMiddleByIdAction(
        $id
    ) {
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
     * @Route("/advertising/middles")
     * @Method({"POST"})
     *
     * @return View
     * @throws \Exception
     */
    public function postAdvertisingMiddleAction(
        Request $request
    ) {
        $middle = new CommnueAdvertisingMiddle();
        $form = $this->createForm(new CommnueAdvertisingMiddleType(), $middle);
        $form->handleRequest($request);

        if(!$form->isValid()){
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $url = $form['url']->getData();

        return $this->handleBannerPost(
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
     * @Route("/advertising/middles/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     */
    public function patchAdvertisingMiddleAction(
        Request $request,
        $id
    ) {
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
     * @Route("/advertising/middle/{id}")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function deleteAdvertisingMiddleAction(
        $id
    ) {
        $middle = $this->getDoctrine()
            ->getRepository('Advertising\CommnueAdvertisingMiddle')
            ->find($id);

        $this->throwNotFoundIfNull($middle, self::NOT_FOUND_MESSAGE);

        $em = $this->getDoctrine()->getManager();
        $em->remove($middle);

        $em->flush();

        return new View();
    }

    /**
     * @param CommnueAdvertisingMiddle $middle
     * @param $url
     * @return View
     */
    private function handleBannerPost(
        $middle,
        $url
    ) {
        $em = $this->getDoctrine()->getManager();

        $source = $middle->getSource();
        $sourceId = $middle->getSourceId();

        $sourceArray = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Material\CommnueMaterial')
            ->getCategory();

        switch ($source) {
            case CommnueAdvertisingMiddle::SOURCE_EVENT:
                $this->setMiddleContentForEvent(
                    $middle,
                    $sourceId
                );
                break;
            case in_array($source, $sourceArray):
                $this->setMiddleContentForMaterial(
                    $middle,
                    $sourceId
                );

                break;
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
                    self::BANNER_ALREADY_EXIST_CODE,
                    self::BANNER_ALREADY_EXIST_MESSAGE
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

}