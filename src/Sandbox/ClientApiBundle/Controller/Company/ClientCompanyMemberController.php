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
        $members = $this->getRepo('Company\CompanyMember')->findByCompanyId($id);
        $this->throwNotFoundIfNull($members, self::NOT_FOUND_MESSAGE);
//        $members = $company->getMembers();

        foreach ($members as &$member) {
            $profile = $this->getRepo('User\UserProfile')->findOneByUserId($member->getUserId());
            $member->setProfile($profile);
        }

        $view = new View($members);

        $view->setSerializationContext(SerializationContext::create()->setGroups(array('company_member_basic')));

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
        $em = $this->getDoctrine()->getManager();

        $company = $this->getRepo('Company\Company')->find($id);

        $memberIds = json_decode($request->getContent(), true);
        foreach ($memberIds as $memberId) {
            $companyMember = $this->getRepo('Company\CompanyMember')->findOneBy(array(
                'company' => $company,
                'userId' => $memberId,
            ));
            if (!is_null($companyMember)) {
                continue;
            }

            $companyMember = $this->generateCompanyMember($company, $memberId);
            $em->persist($companyMember);
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
        //TODO check user‘s auth
        $this->getRepo('Company\CompanyMember')->deleteCompanyMembers(
            $paramFetcher->get('id'),
            $id
        );

        return new View();
    }
}
