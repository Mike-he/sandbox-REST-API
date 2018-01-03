<?php

namespace Sandbox\CommnueClientApiBundle\Controller\Expert;

use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Constants\CustomErrorMessagesConstants;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Expert\Expert;
use Sandbox\ApiBundle\Entity\Expert\ExpertOrder;
use Sandbox\ApiBundle\Entity\Service\ViewCounts;
use Sandbox\ApiBundle\Entity\User\UserFavorite;
use Sandbox\ApiBundle\Form\Expert\ExpertPostType;
use Sandbox\ApiBundle\Form\Expert\ExpertPutType;
use Sandbox\ApiBundle\Traits\UserIdCardTraits;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ClientExpertController extends SandboxRestController
{
    use UserIdCardTraits;

    /**
     * Check A Expert.
     *
     * @param $request
     *
     * @Route("/experts/check")
     * @Method({"GET"})
     *
     * @return View
     */
    public function checkExpertAction(
        Request $request
    ) {
        $user = $this->getUser();

        $expert = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\Expert')
            ->findOneBy(array('userId' => $user->getUserId()));

        $response = array();
        if ($expert) {
            $response['status'] = true;
            $response['banned'] = $expert->isBanned();
        } else {
            $response['status'] = false;
        }

        return new View($response);
    }

    /**
     * Get Detail.
     *
     * @param $request
     *
     * @Route("/experts/my")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMyExpertAction(
        Request $request
    ) {
        $user = $this->getUser();

        $expert = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\Expert')
            ->findOneBy(array('userId' => $user->getUserId()));

        $this->throwNotFoundIfNull($expert, self::NOT_FOUND_MESSAGE);

        $view = new View();
        $view->setData($expert);

        return $view;
    }

    /**
     * Create A Expert.
     *
     * @param $request
     *
     * @Route("/experts")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postExpertAction(
        Request $request
    ) {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $expert = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\Expert')
            ->findOneBy(array('userId' => $user->getUserId()));

        if ($expert) {
            return $this->customErrorView(
                400,
                CustomErrorMessagesConstants::ERROR_EXPERT_HAS_CREATED_CODE,
                CustomErrorMessagesConstants::ERROR_EXPERT_HAS_CREATED_MESSAGE
            );
        }

        $expert = new Expert();
        $expert->setUserId($user->getUserId());

        $form = $this->createForm(new ExpertPostType(), $expert);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $requestContent = json_decode($request->getContent(), true);

        $userInfo = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->find($user->getUserId());
        if ($userInfo->getCredentialNo()) {
            $expert->setCredentialNo($userInfo->getCredentialNo());
        } else {
            $check = $this->checkIDCardValidation(
                $expert->getName(),
                $expert->getCredentialNo()
            );

            if (!$check) {
                return $this->customErrorView(
                    400,
                    CustomErrorMessagesConstants::ERROR_ID_CARD_AUTHENTICATION_FAILURE_CODE,
                    CustomErrorMessagesConstants::ERROR_ID_CARD_AUTHENTICATION_FAILURE_MESSAGE
                );
            }
        }

        $fieldIds = $requestContent['field_ids'];

        foreach ($fieldIds as $fieldId) {
            $field = $this->getDoctrine()->getRepository('SandboxApiBundle:Expert\ExpertField')->find($fieldId);
            if ($field) {
                $expert->addExpertFields($field);
            }
        }

        $expert->setUserId($user->getUserId());
        $em->persist($expert);
        $em->flush();

        $types = [ViewCounts::TYPE_VIEW, ViewCounts::TYPE_BOOKING];
        foreach ($types as $type) {
            $viewCount = new ViewCounts();
            $viewCount->setCount(0);
            $viewCount->setObject(ViewCounts::OBJECT_EXPERT);
            $viewCount->setObjectId($expert->getId());
            $viewCount->setType($type);

            $em->persist($viewCount);
        }
        $em->flush();

        $response = array(
            'id' => $expert->getId(),
        );

        return new View($response, 201);
    }

    /**
     * Update Expert Info.
     *
     * @param $request
     *
     * @Route("/experts")
     * @Method({"PUT"})
     *
     * @return View
     */
    public function putExpertAction(
        Request $request
    ) {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $expert = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\Expert')
            ->findOneBy(array('userId' => $user->getUserId()));
        $this->throwNotFoundIfNull($expert, self::NOT_FOUND_MESSAGE);

        $form = $this->createForm(
            new ExpertPutType(),
            $expert,
            array('method' => 'PUT')
        );
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $requestContent = json_decode($request->getContent(), true);

        $expertFields = $expert->getExpertFields();
        foreach ($expertFields as $expertField) {
            $expert->removeExpertFields($expertField);
        }
        $fieldIds = $requestContent['field_ids'];
        foreach ($fieldIds as $fieldId) {
            $field = $this->getDoctrine()->getRepository('SandboxApiBundle:Expert\ExpertField')->find($fieldId);
            if ($field) {
                $expert->addExpertFields($field);
            }
        }

        $em->persist($expert);
        $em->flush();

        return new View();
    }

    /**
     * Get Lists.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="field",
     *     array=false,
     *     default=null,
     *     nullable=true,
     *     strict=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="country",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     * )
     *
     * @Annotations\QueryParam(
     *    name="province",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    description="services typeId"
     * )
     *
     * @Annotations\QueryParam(
     *    name="city",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    description="services typeId"
     * )
     *
     * @Annotations\QueryParam(
     *    name="district",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    requirements="\d+",
     *    description="services typeId"
     * )
     *
     * @Annotations\QueryParam(
     *    name="sort",
     *    default="default",
     *    nullable=true,
     *    description="smart sort"
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
     * @Route("/experts")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getExpertsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $field = $paramFetcher->get('field');
        $sort = $paramFetcher->get('sort');
        $country = $paramFetcher->get('country');
        $province = $paramFetcher->get('province');
        $city = $paramFetcher->get('city');
        $district = $paramFetcher->get('district');

        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $experts = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\Expert')
            ->getExperts(
                $field,
                $country,
                $province,
                $city,
                $district,
                $sort,
                $limit,
                $offset
            );

        $view = new View();
        $view->setData($experts);

        return $view;
    }

    /**
     * Get Detail.
     *
     * @param $id
     *
     * @Route("/experts/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getDetailAction(
        $id
    ) {
        $expert = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\Expert')->find($id);
        $this->throwNotFoundIfNull($expert, self::NOT_FOUND_MESSAGE);

        $cityName = '';
        if ($expert->getCityId()) {
            $city = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomCity')
                ->find($expert->getCityId());
            $cityName = $city ? $city->getName() : '';
        }

        $districtName = '';
        if ($expert->getDistrictId()) {
            $district = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomCity')
                ->find($expert->getDistrictId());

            $districtName = $district ? $district->getName() : '';
        }

        $user = $this->getDoctrine()
            ->getRepository("SandboxApiBundle:User\User")
            ->find($expert->getUserId());

        $viewCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Service\ViewCounts')
            ->findOneBy(array(
                'object' => ViewCounts::OBJECT_EXPERT,
                'objectId' => $id,
                'type' => ViewCounts::TYPE_VIEW,
            ));

        $userId = $this->getUserId();

        $favorite = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserFavorite')
            ->findOneBy(array(
                'userId' => $userId,
                'object' => UserFavorite::OBJECT_EXPERT,
                'objectId' => $id,
            ));

        $order = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\ExpertOrder')
            ->findOneBy(array(
                'expertId' => $id,
                'userId' => $userId,
                'status' => ExpertOrder::STATUS_PENDING,
            ));

        $data = [
            'id' => $expert->getId(),
            'user_id' => $expert->getUserId(),
            'xmpp_username' => $user->getXmppUsername(),
            'baned' => $expert->isBanned(),
            'is_service' => $expert->isService(),
            'photo' => $expert->getPhoto(),
            'name' => $expert->getName(),
            'city_name' => $cityName,
            'district_name' => $districtName,
            'base_price' => (float) $expert->getBasePrice(),
            'identity' => $expert->getIdentity(),
            'introduction' => $expert->getIntroduction(),
            'description' => $expert->getDescription(),
            'view_count' => $viewCount ? $viewCount->getCount() : 0,
            'is_favorite' => $favorite ? true : false,
            'order_id' => $order ? $order->getId() : '',
        ];

        $this->get('sandbox_api.view_count')->autoCounting(
            ViewCounts::OBJECT_EXPERT,
            $id,
            ViewCounts::TYPE_VIEW
        );

        $view = new View();
        $view->setData($data);

        return $view;
    }
}
