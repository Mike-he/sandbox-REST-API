<?php

namespace Sandbox\ClientApiBundle\Controller\Company;

use Sandbox\ApiBundle\Entity\Company\Company;
use Sandbox\ApiBundle\Entity\Company\CompanyInvitation;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Form\Company\CompanyInvitationPatchType;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use JMS\Serializer\SerializationContext;
use Rs\Json\Patch;

/**
 * Rest controller for Company Invitation.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientCompanyInvitationController extends ClientCompanyMemberController
{
    /**
     * Get my company invitations.
     *
     * @param Request $request the request object
     *
     * @Route("/invitations")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getCompanyInvitationsAction(
        Request $request
    ) {
        // get my user
        $myUserId = $this->getUserId();
        $myUser = $this->getRepo('User\User')->find($myUserId);

        // get my company invitations
        $invitations = $this->getRepo('Company\CompanyInvitation')->getCompanyInvitations($myUser);

        // set view
        $view = new View($invitations);
        $view->setSerializationContext(SerializationContext::create()->setGroups(
            array('company_invitation')
        ));

        return $view;
    }

    /**
     * @param Request $request
     * @param int     $id
     *
     * @Route("/invitations/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     */
    public function patchCompanyInvitationAction(
        Request $request,
        $id
    ) {
        // get my user
        $myUserId = $this->getUserId();
        $myUser = $this->getRepo('User\User')->find($myUserId);

        // get company invitation
        $companyInvitation = $this->getRepo('Company\CompanyInvitation')->find($id);
        $this->throwNotFoundIfNull($companyInvitation, self::NOT_FOUND_MESSAGE);

        // check user is allowed to modify
        if ($myUserId != $companyInvitation->getRecvUserId()) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        // check status is pending
        if ($companyInvitation->getStatus() === CompanyInvitation::STATUS_PENDING) {
            // bind data
            $invitationJson = $this->container->get('serializer')->serialize($companyInvitation, 'json');
            $patch = new Patch($invitationJson, $request->getContent());
            $invitationJson = $patch->apply();

            $form = $this->createForm(new CompanyInvitationPatchType(), $companyInvitation);
            $form->submit(json_decode($invitationJson, true));

            // set modification date
            $companyInvitation->setModificationDate(new \DateTime('now'));

            // update to db
            $em = $this->getDoctrine()->getManager();

            if ($companyInvitation->getStatus() === CompanyInvitation::STATUS_ACCEPTED) {
                $company = $companyInvitation->getCompany();

                // save company member
                $this->saveCompanyMember($em, $company, $myUser);

                // update user profile's company
                $this->setUserProfileCompany($myUserId, $company);

                // send notification
                $this->sendXmppCompanyNotification(
                    $company,
                    $myUser,
                    $myUser,
                    'member_add',
                    true
                );
            }

            $em->flush();
        }

        return new View();
    }
}
