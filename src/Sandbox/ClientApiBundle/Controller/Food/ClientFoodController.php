<?php

namespace Sandbox\ClientApiBundle\Controller\Food;

use Sandbox\ApiBundle\Entity\Food\Food;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sandbox\ApiBundle\Controller\Food\FoodController;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;

/**
 * Client Food Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientFoodController extends FoodController
{
    /**
     * Get Food List.
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
     *    name="category",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="(drink|dessert)",
     *    strict=true,
     *    description="Filter by food category"
     * )
     *
     * @Annotations\QueryParam(
     *    name="building",
     *    array=false,
     *    default=null,
     *    nullable=false,
     *    strict=true,
     *    description="Filter by building id"
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
     * @Route("/food")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getFoodAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // filters
        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');
        $category = $paramFetcher->get('category');
        $buildingId = $paramFetcher->get('building');

        // get building
        if (is_null($buildingId) || empty($buildingId)) {
            $this->throwNotFoundIfNull($buildingId, self::NOT_FOUND_MESSAGE);
        }

        $food = $this->getRepo('Food\Food')->getFoodList(
            $category,
            $buildingId,
            'DESC',
            null,
            $limit,
            $offset
        );

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client_detail']));
        $view->setData($food);

        return $view;
    }

    /**
     * Get food by id.
     *
     * @param Request $request
     * @param int     $id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Route("/food/{id}")
     * @Method({"GET"})
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getFoodByIdAction(
        Request $request,
        $id
    ) {
        // get food
        $food = $this->getRepo('Food\Food')->find($id);
        $this->throwNotFoundIfNull($food, self::NOT_FOUND_MESSAGE);

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client_detail']));
        $view->setData($food);

        return $view;
    }
}
