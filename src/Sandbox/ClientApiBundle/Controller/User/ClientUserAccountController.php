<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyUserCard;
use Sandbox\ApiBundle\Entity\User\User;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use JMS\Serializer\SerializationContext;

/**
 * User Account controller.
 *
 * @category Sandbox
 *
 * @author   Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @see     http://www.Sandbox.cn/
 */
class ClientUserAccountController extends SandboxRestController
{
    /**
     * Get my user account.
     *
     * @param Request $request the request object
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Method({"GET"})
     * @Route("/account")
     *
     * @return View
     *
     * @throws \Exception
     */
    public function getUserAccountAction(
        Request $request
    ) {
        $userId = $this->getUserId();
        $userView = $this->getRepo('User\UserView')->find($userId);

        $view = new View($userView);
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(array('account'))
        );

        return $view;
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/card")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getUserCardInformationAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();

        // get default user card info
        $defaultCard = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyUserCard')
            ->findOneBy(array(
                'type' => SalesCompanyUserCard::TYPE_OFFICIAL,
                'companyId' => null,
            ));

        $salesUser = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesUser')
            ->findOneBy(array(
                'userId' => $userId,
                'isAuthorized' => true,
            ));

        $userCard = null;
        if (!is_null($salesUser)) {
            $building = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Room\RoomBuilding')
                ->find($salesUser->getBuildingId());

            if (!is_null($building)) {
                $companyId = $building->getCompany()->getId();

                // get card info according to sales company
                $userCard = $this->getDoctrine()
                    ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyUserCard')
                    ->findOneBy(array(
                        'companyId' => $companyId,
                    ));
            }
        }

        if (is_null($userCard)) {
            $userCard = $defaultCard;
        }

        $view = new View($userCard);
        $view->setSerializationContext(SerializationContext::create()->setGroups(array('client')));

        return $view;
    }
}
