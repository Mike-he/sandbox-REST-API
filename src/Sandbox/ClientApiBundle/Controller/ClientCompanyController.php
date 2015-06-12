<?php
/**
 * API for Companies
 *
 * PHP version 5.3
 *
 * @category Sandbox
 * @package  ApiBundle
 * @author   Allan Simon <simona@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 * @link     http://www.Sandbox.cn/
 *
 */
namespace Sandbox\ClientApiBundle\Controller;

use Sandbox\ApiBundle\Controller\CompanyController;
use Sandbox\ApiBundle\Entity\Company;
use Sandbox\ApiBundle\Entity\CompanyAdmin;
use Sandbox\ApiBundle\Entity\Companymember;
use Sandbox\ApiBundle\Entity\GuestTag;
use Sandbox\ApiBundle\Entity\JtVCard;
use Sandbox\ApiBundle\Form\CompanyType;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Rs\Json\Patch;

/**
 * Rest controller for Companies
 *
 * @category Sandbox
 * @package  Sandbox\ApiBundle\Controller
 * @author   Allan SIMON <simona@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 * @link     http://www.Sandbox.cn/
 */
class ClientCompanyController extends CompanyController
{
    const INTERNAL_SERVER_ERROR = "Internal server error";

    const MEMBER_IS_NOT_DELETE = 0;

    /**
     * Get companies.
     *
     * @param Request $request the request object
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Annotations\View()
     *
     * @return array
     */
    public function getCompaniesAction(
        Request $request
    ) {
        $username = $this->getUsername();

        $companies = $this->getRepo('Company')->findAllWithUserId(
            $username
        );

        return new View($companies);
    }

    /**
     * Get a single Company.
     *
     * @param Request $request the request object
     * @param String  $id      the company Id
     *
     * @ApiDoc(
     *   output = "Company",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the note is not found"
     *   }
     * )
     *
     * @return array
     *
     * @throws NotFoundHttpException when resource does not exist
     */
    public function getCompanyAction(
        Request $request,
        $id
    ) {
        $company = $this->getRepo('Company')->find($id);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        return new View($company);
    }

    /**
     * @param Request $request the request object
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @return string
     * @throws BadRequestHttpException
     */
    public function postCompanyAction(
        Request $request
    ) {
        $userId = $this->getUsername();

        $company = new Company();

        $form = $this->createForm(new CompanyType(), $company);
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isValid()) {
            $company->setCreatorid($userId);
            $time = time();
            $company->setCreationdate($time);
            $company->setModificationdate($time);

            // save company to db
            $em = $this->getDoctrine()->getManager();
            $em->persist($company);
            $em->flush();

            // get auto generated new company ID
            $companyId = $company->getId();

            // get vcard
            $vcard = $this->getRepo('JtVCard')->findOneBy(array(
                'userid' => $userId,
                'companyid' => null,
            ));

            $this->throwNotFoundIfNull($vcard, 'vcard '.self::NOT_FOUND_MESSAGE);

            // get user fullname
            $fullName = $vcard->getName();
            $gender = $vcard->getGender();

            // save creator to company member
            $companyMember = $this->setCompanyMember(
                $userId,
                $companyId
            );
            $em->persist($companyMember);
            $em->flush();

            // save creator's vcard in company
            $jtVCard = $this->setVCard(
                $companyId,
                $userId,
                $fullName,
                $gender
            );
            $em->persist($jtVCard);
            $em->flush();

            // add some default guest tags for newly created company
            // Customers, Partners, Suppliers
            $guestTag = $this->setGuestTag($companyId, 'Customers');
            $em->persist($guestTag);

            $guestTag = $this->setGuestTag($companyId, 'Partners');
            $em->persist($guestTag);

            $guestTag = $this->setGuestTag($companyId, 'Suppliers');
            $em->persist($guestTag);

            $companyAdmin = $this->setCompanyAdmin($companyId);
            $em->persist($companyAdmin);

            // flush
            $em->flush();

            // reply result
            $view = $this->routeRedirectView('get_company', array(
                'id' => $company->getId(),
            ));
            $view->setData(array(
                'id' => $company->getId(),
            ));

            return $view;
        }

        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @return View
     * @throws BadRequestHttpException
     */
    public function patchCompanyAction(
        Request $request,
        $id
    ) {
        $userId = $this->getUsername();

        try {
            // get company entity
            $company = $this->getRepo('Company')->find($id);
            $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

            // get company creator ID
            $creatorId = $company->getCreatorid();
            if ($creatorId != $userId) {
                // if user is not the creator of this company
                // return error
                throw new BadRequestHttpException(self::NOT_ALLOWED_MESSAGE);
            }

            // do json patch
            $companyJSON = $this->container->get('serializer')->serialize($company, 'json');
            $patch = new Patch($companyJSON, $request->getContent());
            $companyPatchJSON = $patch->apply();
            $companyArray = json_decode($companyPatchJSON, true);

            $form = $this->createForm(new CompanyType(), $company);
            $form->submit($companyArray);

            // update company modification date
            $company->setModificationdate(time());

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $view = new View();
            $view->setData($companyArray);

            return $view;
        } catch (\Exception $e) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }
    }

    /**
     * @param Request $request
     * @param $companyId
     */
    public function deleteCompanyAction(
        Request $request,
        $companyId
    ) {
        $company = $this->getRepo('Company')->find($companyId);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        // only company creator can delete company
        $userId = $this->getUsername();
        if ($userId != $company->getCreatorid()) {
            throw new BadRequestHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        // call openfire api
        $groupChatJIDs = $this->getGroupChatJIDsFromCompany($companyId);
        if (!is_null($groupChatJIDs) && !empty($groupChatJIDs)) {
            $httpResponseCode = $this->callApiDeleteAllGroupChats($request, json_encode($groupChatJIDs));
            $this->throwBadRequestIfCallApiFailed($httpResponseCode);
        }

        $this->deleteCompanyProcess($company);
    }

    /**
     * @param $companyId
     * @param $userId
     * @param $name
     * @param $gender
     *
     * @return JtVCard
     */
    private function setVCard(
        $companyId,
        $userId,
        $name,
        $gender
    ) {
        $vcard = new JtVCard();

        $vcard->setCompanyid($companyId);
        $vcard->setUserid($userId);
        $vcard->setName($name);
        $vcard->setGender($gender);

        return $vcard;
    }

    /**
     * @param $companyId
     * @param $value
     *
     * @return GuestTag
     */
    private function setGuestTag(
        $companyId,
        $value
    ) {
        $guestTag = new GuestTag();

        $guestTag->setCompanyid($companyId);
        $guestTag->setValue($value);

        return $guestTag;
    }

    /**
     * @param $companyId
     * @return CompanyAdmin
     */
    private function setCompanyAdmin(
        $companyId
    ) {
        $username = 'c'.$companyId.'admin'.
            $this->getCompanyAdminCounts($companyId);

        $password = $this->get('string_util')->randomKeys(8);

        $companyAdmin = new CompanyAdmin();
        $companyAdmin->setCompanyid($companyId);
        $companyAdmin->setUsername($username);
        $companyAdmin->setPassword($password);
        $companyAdmin->setType('root');
        $companyAdmin->setCreationdate(time());
        $companyAdmin->setModificationdate(time());

        return $companyAdmin;
    }

    /**
     * @param $companyId
     * @return array
     */
    private function getCompanyAdminCounts(
        $companyId
    ) {
        $admins = $this->getRepo('CompanyAdmin')->findBy(array(
            'companyid' => $companyId,
        ));

        return count($admins) + 1;
    }

    /**
     * @param $companyId
     * @return array
     */
    private function getGroupIdsFromCompany($companyId)
    {
        $groupIDArray = array();
        $groups = $this->getRepo('Group')->findBy(array('companyid' => $companyId));
        foreach ($groups as $group) {
            $id = (string) $group->getId();
            array_push($groupIDArray, $id);
        }

        return $groupIDArray;
    }

    /**
     * @param $companyId
     * @return array
     */
    private function getGroupChatJIDsFromCompany($companyId)
    {
        $groupChatJIDArray = array();

        $groupChats = $this->getRepo('Groupchat')->findBy(array(
            'parentid' => $companyId,
            'parenttype' => 'company',
        ));

        if (is_null($groupChats) || empty($groupChats)) {
            return $groupChatJIDArray;
        }

        foreach ($groupChats as $groupChat) {
            $jid = (string) $groupChat->getJid();
            array_push($groupChatJIDArray, $jid);
        }

        return array('rooms' => $groupChatJIDArray);
    }

    /**
     * @param  Request $request
     * @param $JSONData
     * @return mixed
     */
    private function callApiDeleteAllGroupChats(
        Request $request,
        $JSONData
    ) {
        // the request auth from header
        $auth = $request->headers->get(self::HTTP_HEADER_AUTH);

        $globals = $this->container->get('twig')->getGlobals();

        $apiUrl = $globals['openfire_innet_protocol'].
            $globals['openfire_innet_address'].
            $globals['openfire_innet_port'].
            $globals['openfire_plugin_groupchat'].
            $globals['openfire_plugin_groupchat_rooms'];

        // init curl
        $ch = curl_init($apiUrl);
        $this->get('curl_util')->callAPI($ch, $JSONData, $auth, 'DELETE');

        return curl_getinfo($ch, CURLINFO_HTTP_CODE);
    }

    /**
     * @param $company
     * @throws InternalErrorException
     */
    private function deleteCompanyProcess($company)
    {
        $companyId = $company->getId();

        $em = $this->getDoctrine()->getManager();
        try {
            //begin transaction
            $em->getConnection()->beginTransaction();

            // delete groupchats extras from comapny
            $this->getRepo('GroupChatExtra')->deleteAllGroupChatExtrasByCompany($companyId);

            // delete messages from company
            $this->getRepo('MessageArchive')->deleteAllMessagesByCompany($companyId);

            // delete chatconfigs from company
            $this->getRepo('ChatConfig')->deleteAllChatConfigsByCompany($companyId);

            // delete vcards from company
            $this->getRepo('JtVCard')->deleteAllVCardsByCompany($companyId);

            // delete feeds from company
            $this->getRepo('Feed')->deleteAllFeedsByCompany($companyId);

            // delete tasks from company
            $groupIds = $this->getGroupIdsFromCompany($companyId);
            $this->getRepo('Task')->deleteAllTasksByCompany($companyId, $groupIds);

            // delete approvals from company
            $this->getRepo('Approval')->deleteAllApprovalsByCompany($companyId);

            // delete groups from company
            $this->getRepo('Group')->deleteAllGroupsByCompany($companyId);

            // delete company
            $em->remove($company);
            $em->flush();

            // commit all action
            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollback();
            throw new InternalErrorException(self::INTERNAL_SERVER_ERROR);
        }
    }
}
