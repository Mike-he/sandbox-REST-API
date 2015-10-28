<?php

namespace Sandbox\ApiBundle\Controller\Company;

use Sandbox\ApiBundle\Entity\Company\Company;
use Sandbox\ApiBundle\Entity\Company\CompanyMember;
use Sandbox\ApiBundle\Entity\Company\CompanyInvitation;
use Sandbox\ApiBundle\Entity\User\User;

/**
 * Company Member Controller.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang <yimo.zhang@Sandbox.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class CompanyMemberController extends CompanyController
{
    /**
     * @param int $userId
     * @param int $companyId
     *
     * @return bool
     */
    public function isCompanyMember(
        $userId,
        $companyId
    ) {
        $member = $this->getRepo('Company\CompanyMember')
            ->findOneBy(array(
                'companyId' => $companyId,
                'userId' => $userId,
            ));
        if (is_null($member)) {
            return false;
        }

        return true;
    }

    /**
     * @param object  $em
     * @param Company $company
     * @param User    $user
     */
    protected function saveCompanyMember(
        $em,
        $company,
        $user
    ) {
        $companyMember = $this->getRepo('Company\CompanyMember')->findOneBy(array(
            'company' => $company,
            'user' => $user,
        ));

        if (is_null($companyMember)) {
            $companyMember = new CompanyMember();
            $companyMember->setCompany($company);
            $companyMember->setUser($user);

            $em->persist($companyMember);
        }
    }

    /**
     * @param object  $em
     * @param Company $company
     * @param User    $askUser
     * @param User    $recvUser
     */
    protected function saveCompanyInvitation(
        $em,
        $company,
        $askUser,
        $recvUser
    ) {
        $companyInvitation = $this->getRepo('Company\CompanyInvitation')->findOneBy(array(
            'company' => $company,
            'recvUser' => $recvUser,
        ));

        if (is_null($companyInvitation)) {
            // new invitation
            $companyInvitation = new CompanyInvitation();
            $companyInvitation->setCompany($company);
            $companyInvitation->setRecvUser($recvUser);
            $companyInvitation->setCreationDate(new \DateTime('now'));
        }

        // update invitation
        $companyInvitation->setAskUser($askUser);
        $companyInvitation->setStatus(CompanyInvitation::STATUS_PENDING);
        $companyInvitation->setModificationDate(new \DateTime('now'));

        if (is_null($companyInvitation->getId())) {
            // save
            $em->persist($companyInvitation);
        }
    }
}
