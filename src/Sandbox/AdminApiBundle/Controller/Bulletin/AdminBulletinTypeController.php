<?php

namespace Sandbox\AdminApiBundle\Controller\Bulletin;

use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Knp\Component\Pager\Paginator;
use Sandbox\AdminApiBundle\Data\Position\Position;
use FOS\RestBundle\Controller\Annotations;
use Sandbox\ApiBundle\Controller\Bulletin\BulletinController;
use Sandbox\ApiBundle\Entity\Bulletin\BulletinType;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Form\Bulletin\BulletinTypePost;
use Sandbox\ApiBundle\Form\Position\PositionType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class AdminBulletinTypeController.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminBulletinTypeController extends BulletinController
{
    /**
     * Create admin bulletin type.
     *
     * @param Request $request
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Route("/bulletin/type")
     * @Method({"POST"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postAdminBulletinTypeAction(
        Request $request
    ) {
        // check user permission
        $this->checkAdminBulletinPermission(AdminPermission::OP_LEVEL_EDIT);

        $type = new BulletinType();

        $form = $this->createForm(new BulletinTypePost(), $type);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $existType = $this->getRepo('Bulletin\BulletinType')->findOneBy(
            [
                'name' => $type->getName(),
                'deleted' => false,
            ]
        );

        if (!is_null($existType)) {
            return $this->customErrorView(
                400,
                BulletinType::TYPE_CONFLICT_CODE,
                BulletinType::TYPE_CONFLICT_MESSAGE
            );
        }

        return $this->handleTypePost(
            $type
        );
    }

    /**
     * Modify bulletin type.
     *
     * @param Request $request
     * @param int     $id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Method({"PUT"})
     * @Route("/bulletin/types/{id}")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function putAdminBulletinTypeAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminBulletinPermission(AdminPermission::OP_LEVEL_EDIT);

        $type = $this->getRepo('Bulletin\BulletinType')->findOneBy(
            [
                'id' => $id,
                'deleted' => false,
            ]
        );
        $this->throwNotFoundIfNull($type, self::NOT_FOUND_MESSAGE);

        $oldName = $type->getName();

        $form = $this->createForm(
            new BulletinTypePost(),
            $type,
            array('method' => 'PUT')
        );
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $newName = $type->getName();

        if ($oldName == $newName) {
            return new View();
        }

        $existType = $this->getRepo('Bulletin\BulletinType')->findOneBy(
            [
                'name' => $type->getName(),
                'deleted' => false,
            ]
        );

        if (!is_null($existType)) {
            return $this->customErrorView(
                400,
                BulletinType::TYPE_CONFLICT_CODE,
                BulletinType::TYPE_CONFLICT_MESSAGE
            );
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * Get admin bulletin types.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many products to return"
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageIndex",
     *    array=false,
     *    default="1",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="page number"
     * )
     *
     * @Route("/bulletin/types")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdminBulletinTypesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminBulletinPermission(AdminPermission::OP_LEVEL_VIEW);

        $types = $this->getRepo('Bulletin\BulletinType')->findBy(
            ['deleted' => false],
            ['sortTime' => 'DESC']
        );

        $types = $this->get('serializer')->serialize(
            $types,
            'json',
            SerializationContext::create()->setGroups(['admin'])
        );
        $types = json_decode($types, true);

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $types,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * Get bulletin type by Id.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @param Request $request
     * @param $id
     *
     * @Route("/bulletin/types/{id}")
     * @Method({"GET"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getAdminBulletinTypeByIdAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminBulletinPermission(AdminPermission::OP_LEVEL_VIEW);

        $type = $this->getRepo('Bulletin\BulletinType')->findOneBy(
            [
                'id' => $id,
                'deleted' => false,
            ]
        );
        $this->throwNotFoundIfNull($type, self::NOT_FOUND_MESSAGE);

        // set view
        $view = new View();
        $view->setData($type);
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin']));

        return $view;
    }

    /**
     * Delete bulletin type.
     *
     * @param Request $request
     * @param int     $id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     204 = "OK"
     *  }
     * )
     *
     * @Route("/bulletin/types/{id}")
     * @Method({"DELETE"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function deleteBulletinTypeAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminBulletinPermission(AdminPermission::OP_LEVEL_EDIT);

        $type = $this->getRepo('Bulletin\BulletinType')->findOneBy(
            [
                'id' => $id,
                'deleted' => false,
            ]
        );
        $this->throwNotFoundIfNull($type, self::NOT_FOUND_MESSAGE);

        $type->setDeleted(true);

        $posts = $this->getRepo('Bulletin\BulletinPost')->findBy(
            [
                'typeId' => $id,
                'deleted' => false,
            ]
        );

        foreach ($posts as $post) {
            $post->setDeleted(true);
            $post->setModificationDate(new \DateTime());
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Method({"POST"})
     * @Route("/bulletin/types/{id}/position")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function changePositionAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminBulletinPermission(AdminPermission::OP_LEVEL_EDIT);

        $type = $this->getRepo('Bulletin\BulletinType')->findOneBy(
            [
                'id' => $id,
                'deleted' => false,
            ]
        );
        $this->throwNotFoundIfNull($type, self::NOT_FOUND_MESSAGE);

        $position = new Position();

        $form = $this->createForm(new PositionType(), $position);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $action = $position->getAction();

        if (empty($action) || is_null($action)) {
            return new View();
        }

        $this->setPosition(
            $type,
            $action
        );

        return new View();
    }

    /**
     * @param $type
     * @param $action
     */
    private function setPosition(
        $type,
        $action
    ) {
        if ($action == Position::ACTION_TOP) {
            $type->setSortTime(round(microtime(true) * 1000));
        } elseif ($action == Position::ACTION_UP || $action == Position::ACTION_DOWN) {
            $swapItem = $this->getRepo('Bulletin\BulletinType')->findSwapBulletinType(
                $type,
                $action
            );

            if (empty($swapItem)) {
                return;
            }

            // swap
            $itemSortTime = $type->getSortTime();
            $type->setSortTime($swapItem->getSortTime());
            $swapItem->setSortTime($itemSortTime);
        }

        // save
        $em = $this->getDoctrine()->getManager();
        $em->flush();
    }

    /**
     * @param BulletinType $type
     *
     * @return View
     */
    private function handleTypePost(
        $type
    ) {
        $em = $this->getDoctrine()->getManager();
        $em->persist($type);
        $em->flush();

        return new View(['id' => $type->getId()]);
    }
}
