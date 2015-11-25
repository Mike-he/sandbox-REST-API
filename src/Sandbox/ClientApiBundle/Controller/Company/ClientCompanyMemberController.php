<?php

namespace Sandbox\ClientApiBundle\Controller\Company;

use Sandbox\ApiBundle\Controller\Company\CompanyMemberController;
use Sandbox\ApiBundle\Entity\Company\Company;
use Sandbox\ApiBundle\Entity\User\User;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Delete;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Traits\CompanyNotification;

/**
 * Rest controller for CompanyMemberMap.
 *
 * @category Sandbox
 *
 * @author   Josh Yang
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientCompanyMemberController extends CompanyMemberController
{
    use CompanyNotification;
    const ERROR_IS_CREATOR_SET_CODE = 400001;
    const ERROR_IS_CREATOR_SET_MESSAGE = 'You are company creator, cannot quit!';

    /**
     * Get Company's members.
     *
     * @param Request $request contains request info
     * @param int     $id      id of the company
     *
     * @Get("/companies/{id}/members")
     *
     * @return array
     */
    public function getMembersAction(
        Request $request,
        $id
    ) {
        // get not banned and authorized members
        $company = $this->getRepo('Company\Company')->findOneById($id);
        $members = $this->getRepo('Company\CompanyMember')
                        ->getCompanyMembers($company);
        $this->throwNotFoundIfNull($members, self::NOT_FOUND_MESSAGE);

        foreach ($members as &$member) {
            $profile = $this->getRepo('User\UserProfile')
                            ->findOneByUserId($member->getUserId());
            $member->setProfile($profile);
        }

        $view = new View($members);

        $view->setSerializationContext(SerializationContext::create()
             ->setGroups(array('company_member_basic')));

        return $view;
    }

    /**
     * add members.
     *
     * @param Request $request
     * @param int     $id
     *
     *
     * @POST("/companies/{id}/members")
     *
     * @return View
     */
    public function postCompanyMemberAction(
        Request $request,
        $id
    ) {
        $userId = $this->getUserId();
        $user = $this->getRepo('User\User')->find($userId);

        // get company
        $company = $this->getRepo('Company\Company')->find($id);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        // check user is allowed to modify
        $this->throwAccessDeniedIfNotCompanyCreator($company, $userId);

        // add member
        $em = $this->getDoctrine()->getManager();

        $memberIds = json_decode($request->getContent(), true);

        foreach ($memberIds as $memberId) {
            $member = $this->getRepo('User\User')->find($memberId);
            if (is_null($member)) {
                continue;
            }

            // check member is buddy
            $buddy = $this->getRepo('Buddy\Buddy')->findOneBy(array(
                'userId' => $userId,
                'buddyId' => $memberId,
            ));
            if (is_null($buddy)) {
                continue;
            }

            // check user is member
            if ($this->isCompanyMember($memberId, $id)) {
                continue;
            }

            $this->saveCompanyInvitation($em, $company, $user, $member);

            // send notification
            $this->sendXmppCompanyNotification(
                $company,
                $user,
                $member,
                'invite',
                false
            );
        }

        $em->flush();

        return new view();
    }

    /**
     * delete members.
     *
     * @param $id
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="id",
     *    array=true,
     *    strict=true,
     *    description=""
     * )
     *
     * @Delete("/companies/{id}/members")
     *
     * @return View
     */
    public function deleteCompanyMembersAction(
        $id,
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();
        $user = $this->getRepo('User\User')->find($userId);

        // get company
        $company = $this->getRepo('Company\Company')->find($id);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        // get member id array
        $memberIds = $paramFetcher->get('id');

        // check param is empty
        if (empty($memberIds)) {
            // quit company
            return $this->quitMyCompany($company, $user);
        } else {
            // delete members
           return $this->deleteMembers($company, $user, $memberIds);
        }
    }

    /**
     * @param Company $company
     * @param User    $user
     *
     * @return View
     */
    public function quitMyCompany(
        $company,
        $user
    ) {
        // creator cannot quit
        if ($company->getCreator() == $user) {
            return $this->customErrorView(
                400,
                self::ERROR_IS_CREATOR_SET_CODE,
                self::ERROR_IS_CREATOR_SET_MESSAGE
            );
        }

        // quit my company
        $companyMember = $this->getRepo('Company\CompanyMember')->findOneBy(array(
            'user' => $user,
            'company' => $company,
        ));

        if (!is_null($companyMember)) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($companyMember);
            $em->flush();
        }

        return new View();
    }

    /**
     * @param Company $company
     * @param User    $user
     * @param array   $memberIds
     *
     * @return View
     */
    public function deleteMembers(
        $company,
        $user,
        $memberIds
    ) {
        // check user is allowed to modify
        $this->throwAccessDeniedIfNotCompanyCreator($company, $user->getId());

        if (is_null($memberIds) || empty($memberIds)) {
            return new View();
        }

        // delete members
        $em = $this->getDoctrine()->getManager();

        foreach ($memberIds as $memberId) {
            $member = $this->getRepo('Company\CompanyMember')->find($memberId);
            if (is_null($member)) {
                continue;
            }

            $em->remove($member);
        }

        $em->flush();

        return new View();
    }
}
