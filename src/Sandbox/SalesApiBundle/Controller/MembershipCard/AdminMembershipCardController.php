<?php

namespace Sandbox\SalesApiBundle\Controller\MembershipCard;

use Rs\Json\Patch;
use Sandbox\ApiBundle\Form\MembershipCard\MembershipCardPatchType;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\MembershipCard\MembershipCard;
use Sandbox\ApiBundle\Entity\User\UserGroup;
use Sandbox\ApiBundle\Entity\User\UserGroupDoors;
use Sandbox\ApiBundle\Form\MembershipCard\MembershipCardPostType;
use Sandbox\ApiBundle\Traits\GenerateSerialNumberTrait;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Admin MembershipCard Controller.
 */
class AdminMembershipCardController extends SalesRestController
{
    use GenerateSerialNumberTrait;

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many admins to return "
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
     * @Route("/membership/cards")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMembershipCardsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkMembershipCardPermission(AdminPermission::OP_LEVEL_VIEW);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $membershipCards = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipCard')
            ->findBy(
                array('companyId' => $salesCompanyId),
                array(
                    'visible' => 'DESC',
                    'creationDate' => 'DESC',
                )
            );

        $count = count($membershipCards);

        foreach ($membershipCards as $membershipCard) {
            $this->handleCardMoreInformation($membershipCard);
        }

        $view = new View();
        $view->setData(
            array(
                'current_page_number' => $pageIndex,
                'num_items_per_page' => (int) $pageLimit,
                'items' => $membershipCards,
                'total_count' => (int) $count,
            )
        );

        return $view;
    }

    /**
     * @param Request $request
     *
     * @Route("/membership/cards/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getMembershipCardByIdAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkMembershipCardPermission(AdminPermission::OP_LEVEL_VIEW);

        $membershipCard = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipCard')
            ->find($id);
        $this->throwNotFoundIfNull($membershipCard, self::NOT_FOUND_MESSAGE);

        $this->handleCardMoreInformation($membershipCard);

        return new View($membershipCard);
    }

    /**
     * @param Request $request
     *
     *
     * @Method({"POST"})
     * @Route("/membership/cards")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function postAdminMembershipCardAction(
        Request $request
    ) {
        // check user permission
        $this->checkMembershipCardPermission(AdminPermission::OP_LEVEL_EDIT);

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $membershipCard = new MembershipCard();

        $form = $this->createForm(new MembershipCardPostType(), $membershipCard);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $accessNo = $this->generateSerialNumber(MembershipCard::CARD_LETTER_HEAD);

        $membershipCard->setAccessNo($accessNo);
        $membershipCard->setCompanyId($salesCompanyId);

        $em = $this->getDoctrine()->getManager();
        $em->persist($membershipCard);

        $em->flush();

        $userGroup = $this->handleUserGroup(
            $em,
            $membershipCard,
            $salesCompanyId
        );

        $this->handleDoorsControl(
            $em,
            $membershipCard,
            $userGroup
        );

        $em->flush();

        $this->storeGroupUsers(
            $em,
            $membershipCard,
            $userGroup->getId()
        );

        $em->flush();

        $response = array(
            'id' => $membershipCard->getId(),
        );

        return new View($response, 201);
    }

    /**
     * @param Request $request
     *
     * @Method({"PUT"})
     * @Route("/membership/cards/{id}")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function putAdminMembershipCardAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkMembershipCardPermission(AdminPermission::OP_LEVEL_EDIT);

        $membershipCard = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipCard')
            ->find($id);
        $this->throwNotFoundIfNull($membershipCard, self::NOT_FOUND_MESSAGE);

        $form = $this->createForm(
            new MembershipCardPostType(),
            $membershipCard,
            array('method' => 'PUT')
        );
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($membershipCard);

        $this->removeExitsDoorsControl($em, $membershipCard);

        $userGroup = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserGroup')
            ->findOneBy(array('card' => $id));

        $userGroup->setName($membershipCard->getName());

        $this->handleDoorsControl(
            $em,
            $membershipCard,
            $userGroup
        );

        $em->flush();

        return new View();
    }

    /**
     * @param Request $request the request object
     * @param int     $id
     *
     * @Route("/membership/cards/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function patchAdminMembershipCardAction(
        Request $request,
        $id
    ) {
        // check user permission
        $this->checkMembershipCardPermission(AdminPermission::OP_LEVEL_EDIT);

        $membershipCard = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipCard')
            ->find($id);
        $this->throwNotFoundIfNull($membershipCard, self::NOT_FOUND_MESSAGE);

        // bind data
        $cardJson = $this->container->get('serializer')->serialize($membershipCard, 'json');
        $patch = new Patch($cardJson, $request->getContent());
        $cardJson = $patch->apply();

        $form = $this->createForm(new MembershipCardPatchType(), $membershipCard);
        $form->submit(json_decode($cardJson, true));

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }

    /**
     * @param $em
     * @param $membershipCard
     * @param $salesCompanyId
     *
     * @return UserGroup
     */
    private function handleUserGroup(
        $em,
        $membershipCard,
        $salesCompanyId
    ) {
        $userGroup = new UserGroup();
        $userGroup->setName($membershipCard->getName());
        $userGroup->setCard($membershipCard->getId());
        $userGroup->setType(UserGroup::TYPE_CARD);
        $userGroup->setCompanyId($salesCompanyId);

        $em->persist($userGroup);

        return $userGroup;
    }

    /**
     * @param $em
     * @param $membershipCard
     * @param $userGroup
     */
    private function handleDoorsControl(
        $em,
        $membershipCard,
        $userGroup
    ) {
        $doorsControls = $membershipCard->getDoorsControl();

        foreach ($doorsControls as $doorsControl) {
            $building = $doorsControl['building_id'];
            $controls = $doorsControl['controls'];
            if (empty($controls)) {
                $userGroupDoors = new UserGroupDoors();
                $userGroupDoors->setCard($membershipCard);
                $userGroupDoors->setGroup($userGroup);
                $userGroupDoors->setBuilding($building);

                $em->persist($userGroupDoors);
            } else {
                foreach ($controls as $control) {
                    $userGroupDoors = new UserGroupDoors();
                    $userGroupDoors->setCard($membershipCard);
                    $userGroupDoors->setGroup($userGroup);
                    $userGroupDoors->setBuilding($building);
                    $userGroupDoors->setDoorControlId($control['control_id']);
                    $userGroupDoors->setName($control['control_name']);

                    $em->persist($userGroupDoors);
                }
            }
        }
    }

    /**
     * @param $em
     * @param $membershipCard
     */
    private function removeExitsDoorsControl(
        $em,
        $membershipCard
    ) {
        $exitsDoorsControls = $em->getRepository('SandboxApiBundle:User\UserGroupDoors')
            ->findBy(array('card' => $membershipCard));

        foreach ($exitsDoorsControls as $exitsDoorsControl) {
            $em->remove($exitsDoorsControl);
        }
    }

    /**
     * @param $membershipCard
     */
    private function handleCardMoreInformation(
        $membershipCard
    ) {
        $doorsControls = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserGroupDoors')
            ->findBy(array('card' => $membershipCard));

        $controls = array();
        foreach ($doorsControls as $doorsControl) {
            $building = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                ->find($doorsControl->getBuilding());
            $controls[] = array(
                'name' => $doorsControl->getName(),
                'door_control_id' => $doorsControl->getDoorControlId(),
                'building' => array(
                    'id' => $doorsControl->getBuilding(),
                    'name' => $building ? $building->getName() : null,
                ),
            );
        }

        $membershipCard->setDoorsControl($controls);

        $specification = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipCardSpecification')
            ->findBy(array('card' => $membershipCard));

        $membershipCard->setSpecification($specification);

        $url = $this->getParameter('orders_url').'/member?ptype=productDetail&productId='.$membershipCard->getId();
        $membershipCard->setcardUrl($url);
    }

    /**
     * @param $em
     * @param $card
     * @param $group
     */
    private function storeGroupUsers(
        $em,
        $card,
        $group
    ) {
        $buildings = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserGroupDoors')
            ->getBuildingIdsByGroup(
                null,
                $card
            );

        $allOrders = $this->get('sandbox_api.order')->getAllOrders($buildings);

        foreach ($allOrders as $allOrder) {
            $users = $allOrder['user'];
            foreach ($users as $user) {
                $this->get('sandbox_api.group_user')->storeGroupUser(
                    $em,
                    $group,
                    $user,
                    $allOrder['type'],
                    $allOrder['start'],
                    $allOrder['end'],
                    $allOrder['order_number']
                );
            }
        }
    }

    /**
     * Check user permission.
     */
    private function checkMembershipCardPermission(
        $OpLevel
    ) {
        $this->get('sandbox_api.admin_permission_check_service')->checkPermissions(
            $this->getAdminId(),
            [
                ['key' => AdminPermission::KEY_SALES_PLATFORM_MEMBERSHIP_CARD],
            ],
            $OpLevel
        );
    }
}
