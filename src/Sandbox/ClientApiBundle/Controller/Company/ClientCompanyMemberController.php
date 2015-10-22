<?php

namespace Sandbox\ClientApiBundle\Controller\Company;

use Sandbox\ApiBundle\Controller\Company\CompanyMemberController;
use Sandbox\ApiBundle\Entity\Company\Company;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Delete;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;

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
        $user = $this->getRepo('User\User')->find($id);

        // get company
        $company = $this->getRepo('Company\Company')->find($id);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        // check user is allowed to modify
        $this->throwAccessDeniedIfNotCompanyCreator($company, $userId);

        //add member
        $em = $this->getDoctrine()->getManager();

        $memberIds = json_decode($request->getContent(), true);

        foreach ($memberIds as $memberId) {
            $member = $this->getRepo('User\User')->find($memberId);
            if (is_null($member)) {
                continue;
            }

            //check member is buddy
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
        $memberIds = $paramFetcher->get('id');
        // check param is empty
        if (empty($memberIds)) {
            // quit company
            return $this->quitMyCompany($id);
        } else {
            // delete members
           return $this->deleteMembers($memberIds, $id);
        }
    }

    /**
     * @param $companyId
     *
     * @return View
     */
    public function quitMyCompany($companyId)
    {
        $userId = $this->getUserId();
        if ($this->isCompanyCreator($userId, $companyId)) {
            return $this->customErrorView(
                400,
                self::ERROR_IS_CREATOR_SET_CODE,
                self::ERROR_IS_CREATOR_SET_MESSAGE
            );
        }

        // quit my company
        $companyMember = $this->getRepo('Company\CompanyMember')->findOneBy(array(
            'userId' => $userId,
            'companyId' => $companyId,
        ));
        $this->throwNotFoundIfNull($companyMember, self::NOT_FOUND_MESSAGE);

        $em = $this->getDoctrine()->getManager();
        $em->remove($companyMember);
        $em->flush();

        return new View();
    }

    /**
     * @param $memberIds
     * @param $companyId
     *
     * @return View
     */
    public function deleteMembers(
        $memberIds,
        $companyId
    ) {
        // check user is allowed to modify
        $company = $this->getRepo('Company\Company')->find($companyId);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);
        $userId = $this->getUserId();
        $this->throwAccessDeniedIfNotCompanyCreator($company, $userId);

        // removed creator id
        $memIds = array();
        foreach ($memberIds as $memberId) {
            if ($this->isCompanyCreator($companyId, $memberId)) {
                continue;
            }
            $memIds[] = $memberId;
        }

        //delete members
        $this->getRepo('Company\CompanyMember')->deleteCompanyMembers(
            $memIds,
            $companyId
        );

        return new View();
    }
}
