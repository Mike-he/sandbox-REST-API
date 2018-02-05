<?php

namespace Sandbox\CommnueClientApiBundle\Controller\Expert;

use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Constants\CustomErrorMessagesConstants;
use Sandbox\ApiBundle\Constants\PlatformConstants;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminRemark;
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
        $userId = $this->getUserId();

        $expert = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\Expert')
            ->findOneBy(array('userId' => $userId));

        $response = array();
        if ($expert) {
            $response['status'] = $expert->getStatus();
            $response['banned'] = $expert->isBanned();
            if (Expert::STATUS_FAILURE == $expert->getStatus()) {
                //get latest failure remark
                $adminRemark = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:Admin\AdminRemark')
                    ->findOneBy(
                        array(
                            'platform' => PlatformConstants::PLATFORM_COMMNUE,
                            'object' => AdminRemark::OBJECT_EXPERT,
                            'objectId' => $expert->getId(),
                        ),
                        array('id' => 'DESC')
                    );

                $response['failure_remark'] = $adminRemark ? $adminRemark->getRemarks() : '';
            }
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
        $userId = $this->getUserId();

        $expert = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\Expert')
            ->findOneBy(array('userId' => $userId));

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
        $userId = $this->getUserId();

        $expert = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\Expert')
            ->findOneBy(array('userId' => $userId));

        if ($expert) {
            return $this->customErrorView(
                400,
                CustomErrorMessagesConstants::ERROR_EXPERT_HAS_CREATED_CODE,
                CustomErrorMessagesConstants::ERROR_EXPERT_HAS_CREATED_MESSAGE
            );
        }

        $expert = new Expert();

        $form = $this->createForm(new ExpertPostType(), $expert);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $requestContent = json_decode($request->getContent(), true);

        $fieldIds = $requestContent['field_ids'];

        if (count($fieldIds) > 3) {
            return $this->customErrorView(
                400,
                CustomErrorMessagesConstants::ERROR_MORE_THAN_QUANTITY_CODE,
                CustomErrorMessagesConstants::ERROR_MORE_THAN_QUANTITY_MESSAGE
            );
        }

        foreach ($fieldIds as $fieldId) {
            $field = $this->getDoctrine()->getRepository('SandboxApiBundle:Expert\ExpertField')->find($fieldId);
            if ($field) {
                $expert->addExpertFields($field);
            }
        }

        $userInfo = $this->getDoctrine()->getRepository('SandboxApiBundle:User\User')->find($userId);
        if ($userInfo->getCredentialNo() != $expert->getCredentialNo()) {
            $expertCheck = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Expert\Expert')
                ->findOneBy(array('credentialNo' => $expert->getCredentialNo()));

            if ($expertCheck) {
                return $this->customErrorView(
                    400,
                    CustomErrorMessagesConstants::ERROR_ID_CARD_HAS_CERTIFIED_CODE,
                    CustomErrorMessagesConstants::ERROR_ID_CARD_HAS_CERTIFIED_MESSAGE
                );
            }

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

        $expert->setUserId($userId);
        $expert->setStatus(Expert::STATUS_PENDING);
        $expert->setTop(true);
        $em->persist($expert);

        if (is_null($userInfo->getCredentialNo())) {
            $userInfo->setCredentialNo($expert->getCredentialNo());
            $userInfo->setAuthorized(true);
        }

        $em->flush();

        $this->syncUserAuth(
            $userId,
            $expert->getName(),
            $expert->getCredentialNo()
        );

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
     * Create A Expert.
     *
     * @param $request
     *
     * @Route("/experts/resubmit")
     * @Method({"POST"})
     *
     * @return View
     */
    public function resubmitExpertAction(
        Request $request
    ) {
        $em = $this->getDoctrine()->getManager();
        $userId = $this->getUserId();

        $expert = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\Expert')
            ->findOneBy(array('userId' => $userId));
        $this->throwNotFoundIfNull($expert, self::NOT_FOUND_MESSAGE);

        if (Expert::STATUS_FAILURE != $expert->getStatus()) {
            return $this->customErrorView(
                400,
                CustomErrorMessagesConstants::ERROR_EXPERT_STATUS_ERROR_CODE,
                CustomErrorMessagesConstants::ERROR_EXPERT_STATUS_ERROR_MESSAGE
            );
        }

        $oldname = $expert->getName();
        $oldCredentialNo = $expert->getCredentialNo();

        $form = $this->createForm(new ExpertPostType(), $expert);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $requestContent = json_decode($request->getContent(), true);

        $fieldIds = $requestContent['field_ids'];

        if (count($fieldIds) > 3) {
            return $this->customErrorView(
                400,
                CustomErrorMessagesConstants::ERROR_MORE_THAN_QUANTITY_CODE,
                CustomErrorMessagesConstants::ERROR_MORE_THAN_QUANTITY_MESSAGE
            );
        }

        foreach ($fieldIds as $fieldId) {
            $field = $this->getDoctrine()->getRepository('SandboxApiBundle:Expert\ExpertField')->find($fieldId);
            if ($field) {
                $expert->addExpertFields($field);
            }
        }

        $expert->setName($oldname);
        $expert->setCredentialNo($oldCredentialNo);
        $expert->setStatus(Expert::STATUS_PENDING);
        $expert->setTop(true);
        $em->persist($expert);
        $em->flush();

        return new View();
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
        $userId = $this->getUserId();

        $expert = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Expert\Expert')
            ->findOneBy(array('userId' => $userId));
        $this->throwNotFoundIfNull($expert, self::NOT_FOUND_MESSAGE);

        if (Expert::STATUS_SUCCESS != $expert->getStatus()) {
            return $this->customErrorView(
                400,
                CustomErrorMessagesConstants::ERROR_EXPERT_HAS_NOT_PASSED_CODE,
                CustomErrorMessagesConstants::ERROR_EXPERT_HAS_NOT_PASSED_MESSAGE
            );
        }

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

        if (count($fieldIds) > 3) {
            return $this->customErrorView(
                400,
                CustomErrorMessagesConstants::ERROR_MORE_THAN_QUANTITY_CODE,
                CustomErrorMessagesConstants::ERROR_MORE_THAN_QUANTITY_MESSAGE
            );
        }

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
        $this->get('sandbox_api.view_count')->autoCounting(
            ViewCounts::OBJECT_EXPERT,
            $id,
            ViewCounts::TYPE_VIEW
        );

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

        $favorite = null;
        $order = null;
        if ($this->isAuthProvided()) {
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
        }

        $orderUrl = $this->getParameter('orders_url');
        $wxShareUrl = $orderUrl.'/expert?expertId='.$expert->getId().'&ptype=share&theme=blue';

        $data = [
            'id' => $expert->getId(),
            'user_id' => $expert->getUserId(),
            'xmpp_username' => $user->getXmppUsername(),
            'banned' => $expert->isBanned(),
            'is_service' => $expert->isService(),
            'photo' => $expert->getPhoto(),
            'preview' => $expert->getPreview(),
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
            'wx_share_url' => $wxShareUrl,
            'expert_fields' => $expert->getExpertFields(),
        ];

        $view = new View();
        $view->setData($data);

        return $view;
    }
}
