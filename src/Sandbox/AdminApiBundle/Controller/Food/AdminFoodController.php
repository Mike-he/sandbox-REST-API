<?php

namespace Sandbox\AdminApiBundle\Controller\Food;

use Doctrine\ORM\EntityManager;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;
use Sandbox\ApiBundle\Entity\Food\Food;
use Sandbox\ApiBundle\Entity\Food\FoodAttachment;
use Sandbox\ApiBundle\Entity\Food\FoodForm;
use Sandbox\ApiBundle\Entity\Food\FoodFormOption;
use Sandbox\ApiBundle\Form\Food\FoodAttachmentType;
use Sandbox\ApiBundle\Form\Food\FoodType;
use Sandbox\ApiBundle\Form\Food\FoodPutType;
use Sandbox\ApiBundle\Form\Food\FoodFormType;
use Sandbox\ApiBundle\Form\Food\FoodFormOptionType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sandbox\ApiBundle\Controller\Food\FoodController;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Knp\Component\Pager\Paginator;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;

/**
 * Admin Food Controller.
 *
 * @category Sandbox
 *
 * @author   Leo Xue <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminFoodController extends FoodController
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
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many food to return per page"
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
     *    name="direction",
     *    array=false,
     *    default="DESC",
     *    nullable=true,
     *    requirements="(ASC|DESC)",
     *    strict=true,
     *    description="sort direction"
     * )
     *
     * @Annotations\QueryParam(
     *    name="search",
     *    default=null,
     *    nullable=true,
     *    description="search query"
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
        // check user permission
        $this->checkAdminFoodPermission(AdminPermissionMap::OP_LEVEL_VIEW);

        //filters
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $category = $paramFetcher->get('category');
        $buildingId = $paramFetcher->get('building');

        // get building
        if (is_null($buildingId) || empty($buildingId)) {
            $this->throwNotFoundIfNull($buildingId, self::NOT_FOUND_MESSAGE);
        }

        // sort direction
        $direction = $paramFetcher->get('direction');

        //search by name and number
        $search = $paramFetcher->get('search');

        $query = $this->getRepo('Food\Food')->getFoodList(
            $category,
            $buildingId,
            $direction,
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
        // check user permission
        $this->checkAdminFoodPermission(AdminPermissionMap::OP_LEVEL_VIEW);

        // get food
        $food = $this->getRepo('Food\Food')->find($id);
        $this->throwNotFoundIfNull($food, self::NOT_FOUND_MESSAGE);

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['admin_detail']));
        $view->setData($food);

        return $view;
    }

    /**
     * Post Food.
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
     * @Route("/food")
     * @Method({"POST"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postFoodAction(
        Request $request
    ) {
        // check user permission
        $this->checkAdminFoodPermission(AdminPermissionMap::OP_LEVEL_EDIT);

        $food = new Food();

        $form = $this->createForm(new FoodType(), $food);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $attachments = $form['food_attachments']->getData();
        $forms = $form['food_forms']->getData();

        return $this->handleFoodPost(
            $food,
            $attachments,
            $forms
        );
    }

    /**
     * Update Food.
     *
     * @param Request $request
     * @param int     $id
     *
     *
     * @Route("/food/{id}")
     * @Method({"PUT"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function putFoodAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminFoodPermission(AdminPermissionMap::OP_LEVEL_EDIT);

        // get food
        $food = $this->getRepo('Food\Food')->find($id);
        $this->throwNotFoundIfNull($food, self::NOT_FOUND_MESSAGE);

        $form = $this->createForm(
            new FoodPutType(),
            $food,
            array(
                'method' => 'PUT',
            )
        );
        $form->handleRequest($request);

        $attachments = $form['food_attachments']->getData();
        $forms = $form['food_forms']->getData();

        return $this->handleFoodPut(
            $food,
            $attachments,
            $forms
        );
    }

    /**
     * @param Food  $food
     * @param array $attachments
     * @param array $forms
     *
     * @return View
     */
    private function handleFoodPost(
        $food,
        $attachments,
        $forms
    ) {
        $roomCity = $this->getRepo('Room\RoomCity')->find($food->getCityId());
        $roomBuilding = $this->getRepo('Room\RoomBuilding')->find($food->getBuildingId());

        if (is_null($roomCity) || is_null($roomBuilding)) {
            throw new BadRequestHttpException(self::LOCATION_CANNOT_NULL);
        }

        $food->setCity($roomCity);
        $food->setBuilding($roomBuilding);

        $em = $this->getDoctrine()->getManager();
        $em->persist($food);

        if (is_null($attachments) || empty($attachments)) {
            throw new BadRequestHttpException(self::ATTACHMENT_CANNOT_NULL);
        }
        //add attachments
        $this->addFoodAttachment(
            $em,
            $food,
            $attachments
        );

        //add forms
        if (!is_null($forms) && !empty($forms)) {
            $this->addFoodForms(
                $em,
                $food,
                $forms
            );
        }

        $em->flush();
        $response = array(
            'id' => $food->getId(),
        );

        return new View($response);
    }

    /**
     * @param Food  $food
     * @param array $attachments
     * @param array $forms
     *
     * @return View
     */
    private function handleFoodPut(
        $food,
        $attachments,
        $forms
    ) {
        $em = $this->getDoctrine()->getManager();
        $foodId = $food->getId();

        if (!is_null($attachments) && !empty($attachments)) {
            //remove attachments
            $this->removeFoodProperties(
                $em,
                $foodId,
                self::ENTITY_FOOD_ATTACHMENT
            );
            //add attachments
            $this->addFoodAttachment(
                $em,
                $food,
                $attachments
            );
        }

        if (!is_null($forms) && !empty($forms)) {
            //remove forms
            $this->removeFoodProperties(
                $em,
                $foodId,
                self::ENTITY_FOOD_FORM
            );
            //add forms
            $this->addFoodForms(
                $em,
                $food,
                $forms
            );
        }

        $food->setModificationDate(new \DateTime());
        $em->flush();

        return new View();
    }

    /**
     * remove attachment.
     *
     * @param EntityManager $em
     * @param Food          $food
     * @param Array         $attachments
     */
    private function removeFoodProperties(
        $em,
        $foodId,
        $entityName
    ) {
        $properties = $this->getRepo($entityName)->findBy(['foodId' => $foodId]);
        if (!is_null($properties) && !empty($properties)) {
            foreach ($properties as $property) {
                $em->remove($property);
            }
        }
    }

    /**
     * Save attachment to db.
     *
     * @param EntityManager $em
     * @param Food          $food
     * @param Array         $attachments
     */
    private function addFoodAttachment(
        $em,
        $food,
        $attachments
    ) {
        foreach ($attachments as $attachment) {
            $foodAttachment = new FoodAttachment();
            $form = $this->createForm(new FoodAttachmentType(), $foodAttachment);
            $form->submit($attachment, true);

            $foodAttachment->setFood($food);
            $em->persist($foodAttachment);
        }
    }

    /**
     * Save food forms to db.
     *
     * @param EntityManager $em
     * @param Food          $food
     * @param Array         $forms
     */
    private function addFoodForms(
        $em,
        $food,
        $formData
    ) {
        foreach ($formData as $data) {
            $foodForm = new FoodForm();
            $form = $this->createForm(new FoodFormType(), $foodForm);
            $form->submit($data, true);

            $foodForm->setFood($food);
            $em->persist($foodForm);

            $options = $form['form_options']->getData();
            if (!is_null($options) && !empty($options)) {
                $this->addFoodFormOptions(
                    $em,
                    $foodForm,
                    $options
                );
            }
        }
    }

    /**
     * set food form options.
     *
     * @param EntityManager $em
     * @param FoodForm      $foodForm
     * @param array         $options
     */
    private function addFoodFormOptions(
        $em,
        $foodForm,
        $options
    ) {
        foreach ($options as $option) {
            $formOption = new FoodFormOption();
            $form = $this->createForm(new FoodFormOptionType(), $formOption);
            $form->submit($option, true);

            $formOption->setForm($foodForm);
            $em->persist($formOption);
        }
    }

    /**
     * Delete Food.
     *
     * @param Request $request the request object
     * @param int     $id
     *
     * @Route("/food/{id}")
     * @Method({"DELETE"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function deleteFoodAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkAdminFoodPermission(AdminPermissionMap::OP_LEVEL_EDIT);

        // get food
        $food = $this->getRepo('Food\Food')->find($id);
        $this->throwNotFoundIfNull($food, self::NOT_FOUND_MESSAGE);

        //delete food
        $em = $this->getDoctrine()->getManager();
        $em->remove($food);
        $em->flush();

        return new View();
    }

    /**
     * Check user permission.
     *
     * @param Integer $OpLevel
     */
    private function checkAdminFoodPermission(
        $OpLevel
    ) {
    }
}
