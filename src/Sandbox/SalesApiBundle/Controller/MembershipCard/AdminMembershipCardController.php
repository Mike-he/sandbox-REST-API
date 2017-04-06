<?php

namespace Sandbox\SalesApiBundle\Controller\MembershipCard;

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

        $adminPlatform = $this->get('sandbox_api.admin_platform')->getAdminPlatform();
        $salesCompanyId = $adminPlatform['sales_company_id'];

        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $membershipCards = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:MembershipCard\MembershipCard')
            ->findBy(array('companyId' => $salesCompanyId));

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

        $userGroup = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserGroup')
            ->findOneBy(array('card' => $id));

        $this->handleDoorsControl(
            $em,
            $membershipCard,
            $userGroup
        );

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
        $exitsDoorsControls = $em->getRepository('SandboxApiBundle:User\UserGroupDoors')
            ->findBy(array('card' => $membershipCard));

        if ($exitsDoorsControls) {
            foreach ($exitsDoorsControls as $exitsDoorsControl) {
                $em->remove($exitsDoorsControl);
            }

            //Todo: Remove Door Access
        }

        $doorsControls = $membershipCard->getDoorsControl();

        foreach ($doorsControls as $doorsControl) {
            $building = $doorsControl['buidling_id'];
            $controls = $doorsControl['controls'];
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

        //Todo: Add Door Access
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
//                ['key' => AdminPermission::KEY_SALES_BUILDING_LONG_TERM_LEASE],
            ],
            $OpLevel
        );
    }
}
