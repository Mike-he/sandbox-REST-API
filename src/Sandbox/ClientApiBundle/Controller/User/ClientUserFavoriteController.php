<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Constants\ProductOrderExport;
use Sandbox\ApiBundle\Controller\Location\LocationController;
use Sandbox\ApiBundle\Entity\Room\Room;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\ApiBundle\Entity\User\UserFavorite;
use Sandbox\ApiBundle\Form\User\UserFavoriteType;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Rest controller for user favorite.
 *
 * @category Sandbox
 *
 * @author   Leo Xu
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientUserFavoriteController extends LocationController
{
    /**
     * Get user's favorite list.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @Annotations\QueryParam(
     *    name="object",
     *    default=null,
     *    nullable=false,
     *    description="favorite object"
     * )
     *
     * @Annotations\QueryParam(
     *    name="lat",
     *    array=false,
     *    default=0,
     *    nullable=true,
     *    requirements="-?\d*(\.\d+)?$",
     *    strict=true,
     *    description="latitude"
     * )
     *
     * @Annotations\QueryParam(
     *    name="lng",
     *    array=false,
     *    default=0,
     *    nullable=true,
     *    requirements="-?\d*(\.\d+)?$",
     *    strict=true,
     *    description="longitude"
     * )
     *
     * @Annotations\QueryParam(
     *    name="limit",
     *    array=false,
     *    default="10",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="limit for the page"
     * )
     *
     * @Annotations\QueryParam(
     *    name="offset",
     *    array=false,
     *    default="0",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="start of the page"
     * )
     *
     * @Route("/favorites")
     * @Method({"GET"})
     *
     * @return array
     */
    public function getUserFavoritesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();
        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->findOneBy(
                [
                    'banned' => false,
                    'id' => $userId,
                ]
            );
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        $object = $paramFetcher->get('object');
        $lng = $paramFetcher->get('lng');
        $lat = $paramFetcher->get('lat');
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $favorites = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserFavorite')
            ->findBy([
                'userId' => $userId,
                'object' => $object,
            ]);

        $view = new View();
        if (empty($favorites)) {
            return $view;
        }

        // get all objectIds
        $objectIds = [];
        foreach ($favorites as $favorite) {
            $objectId = $favorite->getObjectId();
            array_push($objectIds, $objectId);
        }

        switch ($object) {
            case UserFavorite::OBJECT_BUILDING:
                $objects = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                    ->getFavoriteBuildings(
                        $objectIds,
                        $lng,
                        $lat,
                        $limit,
                        $offset,
                        $excludeIds = [9] // the company id of xiehe
                    );

                if ($lat == 0 || $lng == 0) {
                    $objectArray = [];
                    foreach ($objects as $object) {
                        $object['distance'] = 0;
                        array_push($objectArray, $object);
                    }

                    $objects = $this->handleSearchBuildingsData($objectArray);
                } else {
                    $objects = $this->handleSearchBuildingsData($objects);
                }

                break;
            case UserFavorite::OBJECT_PRODUCT:
                $contents = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Product\Product')
                    ->findFavoriteProducts(
                        $lat,
                        $lng,
                        $objectIds,
                        $limit,
                        $offset
                    );

                $objects = [];
                foreach ($contents as $content) {
                    if ($lat == 0 || $lng == 0) {
                        $content['distance'] = 0;
                    }

                    $product = $content['product'];
                    $product->setDistance($content['distance']);

                    $room = $product->getRoom();
                    $roomType = $room->getType();

                    if ($roomType == Room::TYPE_DESK) {
                        $price = $this->getDoctrine()
                            ->getRepository('SandboxApiBundle:Room\RoomFixed')
                            ->getFixedSeats($room);

                        if (!is_null($price)) {
                            $product->setBasePrice($price);
                        }
                    }

                    $type = $this->get('translator')->trans(ProductOrderExport::TRANS_ROOM_TYPE.$roomType);
                    $room->setTypeDescription($type);

                    $productLeasingSets = $this->getDoctrine()
                        ->getRepository('SandboxApiBundle:Product\ProductLeasingSet')
                        ->findBy(array('product' => $product));

                    $basePrice = [];
                    foreach ($productLeasingSets as $productLeasingSet) {
                        $unitPrice = $this->get('translator')
                            ->trans(ProductOrderExport::TRANS_ROOM_UNIT.$productLeasingSet->getUnitPrice());
                        $productLeasingSet->setUnitPrice($unitPrice);

                        $basePrice[$unitPrice] = $productLeasingSet->getBasePrice();
                    }
                    $product->setLeasingSets($productLeasingSets);

                    $pos = array_search(min($basePrice), $basePrice);
                    $product->setBasePrice($basePrice[$pos]);
                    $product->setUnitPrice($pos);

                    $productRentSet = $this->getDoctrine()
                        ->getRepository('SandboxApiBundle:Product\ProductRentSet')
                        ->findOneBy(array('product' => $product));

                    $product->setRentSet($productRentSet);

                    array_push($objects, $product);
                }

                $view->setSerializationContext(SerializationContext::create()->setGroups(['client']));

                break;
            default:
                return $view;

                break;
        }

        $view->setData($objects);

        return $view;
    }

    /**
     * @param Request $request
     *
     * @Route("/favorites")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postUserFavoriteAction(
        Request $request
    ) {
        $userId = $this->getUserId();
        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->findOneBy(
                [
                    'banned' => false,
                    'id' => $userId,
                ]
            );
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        $this->handleFavoritePost($request, $userId);

        return new View();
    }

    /**
     * @param Request $request
     *
     * @Route("/favorites")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function deleteUserFavoriteAction(
        Request $request
    ) {
        $em = $this->getDoctrine()->getManager();
        $content = json_decode($request->getContent(), true);

        $userId = $this->getUserId();
        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->findOneBy(
                [
                    'banned' => false,
                    'id' => $userId,
                ]
            );
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        if (is_null($content) ||
            empty($content) ||
            !array_key_exists('object', $content) ||
            !array_key_exists('objectId', $content)
        ) {
            return new View();
        }

        $object = $content['object'];
        $objectId = $content['objectId'];

        $favorite = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserFavorite')
            ->findOneBy(
                [
                    'userId' => $userId,
                    'object' => $object,
                    'objectId' => $objectId,
                ]
            );
        $this->throwNotFoundIfNull($favorite, self::NOT_FOUND_MESSAGE);

        $em->remove($favorite);
        $em->flush();

        return new View();
    }

    /**
     * Get user's favorite list.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @Annotations\QueryParam(
     *    name="object",
     *    default=null,
     *    nullable=true,
     *    description="favorite object"
     * )
     *
     * @Annotations\QueryParam(
     *    name="id",
     *    array=true,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="ids of object"
     * )
     *
     * @Route("/favorites/list")
     * @Method({"GET"})
     *
     * @return array
     */
    public function getFavoriteListAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();
        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->findOneBy(
                [
                    'banned' => false,
                    'id' => $userId,
                ]
            );
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        $object = $paramFetcher->get('object');
        $ids = $paramFetcher->get('id');

        $favorites = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserFavorite')
            ->getUserFavoriteList(
                $userId,
                $object,
                $ids
            );

        $view = new View($favorites);
        $view->setSerializationContext(SerializationContext::create()->setGroups(['user']));

        return $view;
    }

    /**
     * @param $request
     * @param $userId
     */
    private function handleFavoritePost(
        $request,
        $userId
    ) {
        $em = $this->getDoctrine()->getManager();

        $favorite = new UserFavorite();

        $form = $this->createForm(new UserFavoriteType(), $favorite);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $object = $favorite->getObject();
        $objectId = $favorite->getObjectId();

        $existFavorite = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserFavorite')
            ->findOneBy(
                [
                    'userId' => $userId,
                    'object' => $object,
                    'objectId' => $objectId,
                ]
            );

        if (is_null($existFavorite)) {
            switch ($object) {
                case UserFavorite::OBJECT_BUILDING:
                    $building = $this->getDoctrine()
                        ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                        ->findOneBy([
                            'id' => $objectId,
                            'status' => RoomBuilding::STATUS_ACCEPT,
                            'visible' => true,
                            'isDeleted' => false,
                        ]);
                    $this->throwNotFoundIfNull($building, self::NOT_FOUND_MESSAGE);

                    break;
                case UserFavorite::OBJECT_PRODUCT:
                    $product = $this->getDoctrine()
                        ->getRepository('SandboxApiBundle:Product\Product')
                        ->findOneBy([
                            'id' => $objectId,
                            'visible' => true,
                            'isDeleted' => false,
                        ]);
                    $this->throwNotFoundIfNull($product, self::NOT_FOUND_MESSAGE);

                    break;
                default:
                    throw new NotFoundHttpException();

                    break;
            }

            $favorite->setUserId($userId);

            $em->persist($favorite);
            $em->flush();
        }
    }
}
