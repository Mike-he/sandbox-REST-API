<?php

namespace Sandbox\ClientApiBundle\Controller\Company;

use Sandbox\ApiBundle\Controller\Company\CompanyMemberController;
use Sandbox\ApiBundle\Entity\Company\Companymember;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Acl\Exception\Exception;

/**
 * Rest controller for members of companies
 *
 * @category Sandbox
 * @package  Sandbox\ApiBundle\Controller
 * @author   Allan SIMON <simona@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 * @link     http://www.Sandbox.cn/
 */
class ClientCompanyMemberController extends CompanyMemberController
{
    const HTTP_METHOD_DELETE = 'DELETE';

    /**
     * Get all the members of a company
     * to know who is the owner, refers to /companies/{id}
     *
     * @param Request $request contains request info
     * @param string  $id      id of the company
     *
     * @Get("/companies/{id}/members")
     * @return array
     */
    public function getMembersAction(
        Request $request,
        $id
    ) {
        $userId = $this->getUsername();

        $repo = $this->getRepo('CompanymemberView');
        $currentUser = $repo->findOneBy(
            array(
                'userid' => $userId,
                'companyid' => $id,
            )
        );
        $this->throwAccessDeniedIfNull($currentUser);

        $members = $repo->findByCompanyid($id);

        return new View($members);
    }

    /**
     * @param Request $request  the request object
     * @param int     $id       id of the company
     * @param string  $memberId id of the member
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Delete("/companies/{id}/members/{memberId}")
     * @return string
     * @throws BadRequestHttpException
     */
    public function deleteCompanyMemberAction(
        Request $request,
        $id,
        $memberId
    ) {
        $em = $this->getDoctrine()->getManager();

        if (is_null($id) || is_null($memberId)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $userId = $this->getUsername();

        // get company
        $company = $this->getRepo('Company')->find($id);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        // check user's authority
        $creatorId = $company->getCreatorid();
        if ($creatorId != $userId) {
            throw new BadRequestHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        $companyMember = $this->getRepo('Companymember')->findOneById($memberId);
        if (!is_null($companyMember)
            || $id == $companyMember->getCompanyid()) {
            // company member found
            if ($creatorId === $companyMember->getUserid()) {
                // user is the company creator
                throw new BadRequestHttpException(self::NOT_ALLOWED_MESSAGE);
            }

            // do delete member process
            $this->deleteCompanyMemberProcess($request, $id, $companyMember, $em);
        }

        $em->flush();
    }

    /**
     * @param $id
     * @param ParamFetcherInterface $paramFetcher
     * @param Request               $request
     *
     * @Annotations\QueryParam(
     *    name="id",
     *    array=true,
     *    strict=true,
     *    description=""
     * )
     *
     * @Delete("/companies/{id}/members")
     * @return View
     */
    public function deleteMultipleCompanyMembersAction(
        $id,
        ParamFetcherInterface $paramFetcher,
        Request $request
    ) {
        $em = $this->getDoctrine()->getManager();

        $companyMemberIds = $paramFetcher->get('id');

        $userId = $this->getUsername();

        // get company
        $company = $this->getRepo('Company')->find($id);
        $this->throwNotFoundIfNull($company, self::NOT_FOUND_MESSAGE);

        // check user's authority
        $creatorId = $company->getCreatorid();
        if ($creatorId != $userId) {
            throw new BadRequestHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        $companyMemberRepo = $this->getRepo('Companymember');

        foreach ($companyMemberIds as $companyMemberId) {
            try {
                $companyMember = $companyMemberRepo->find($companyMemberId);
                if (is_null($companyMember)
                    || $id != $companyMember->getCompanyid()
                    || $companyMember->getIsdelete()) {
                    // company member not found
                    // user doesn't belong to this company
                    // user already has been deleted
                    continue;
                }

                if ($creatorId === $companyMember->getUserid()) {
                    // user is the company creator
                    throw new BadRequestHttpException(self::NOT_ALLOWED_MESSAGE);
                }

                // do delete member process
                $this->deleteCompanyMemberProcess($request, $id, $companyMember, $em);
            } catch (Exception $e) {
                continue;
            }
        }

        $em->flush();
    }

    /**
     * @param Request       $request
     * @param integer       $companyID
     * @param Companymember $companyMember
     * @param $em
     */
    private function deleteCompanyMemberProcess(
        Request $request,
        $companyID,
        $companyMember,
        $em
    ) {
        // set isDelete
        $companyMember->setIsdelete(true);

        // delete others' favorite of this member in company
        $this->deleteFromOthersFavorites($companyMember->getId(), $em);

        // delete this member's favorites in company
        $this->deleteFromUserFavorites($companyID, $companyMember->getUserid(), $em);

        // delete from group member
        $this->deleteFromGroupMember($companyID, $companyMember->getUserid(), $em);

        // delete from group chat
        $groupChatJIDs = $this->deleteFromGroupChat($companyID, $companyMember->getUserid(), $em);

        // delete from guest service
        $guestServiceJIDs = $this->deleteFromGuestService($companyID, $companyMember->getUserid(), $em);

        // get all group chat room JIDs
        $groupChatRoomJIDs = array_merge($groupChatJIDs, $guestServiceJIDs);
        if (!is_null($groupChatRoomJIDs) && !empty($groupChatRoomJIDs)) {
            $adminJID = $this->getCompanyAdminJID($companyMember->getCompanyid());
            // delete group chats from Openfire
            $this->deleteGroupChatsFromOpenfire($request, $companyMember->getUserid(), $adminJID, $groupChatRoomJIDs);
        }
    }

    /**
     * @param $companyMemberId
     * @param $em
     */
    private function deleteFromOthersFavorites(
        $companyMemberId,
        $em
    ) {
        $favorites = $this->getRepo('Favorite')->findBy(array(
            'companymemberid' => $companyMemberId,
        ));

        foreach ($favorites as $favorite) {
            $em->remove($favorite);
        }
    }

    /**
     * @param $companyID
     * @param $userID
     * @param $em
     */
    private function deleteFromUserFavorites(
        $companyID,
        $userID,
        $em
    ) {
        $favorites = $this->getRepo('Favorite')->findAllFavoriteWithUserIdAndCompanyId($userID, $companyID);

        foreach ($favorites as $favorite) {
            $em->remove($favorite);
        }
    }

    /**
     * @param $companyID
     * @param $userID
     * @param $em
     */
    private function deleteFromGroupMember(
        $companyID,
        $userID,
        $em
    ) {
        $groups = $this->getRepo('Group')->findAllWithUserIdAndCompanyId($userID, $companyID);

        foreach ($groups as $group) {
            $groupMember = $this->getRepo('Groupmember')->findOneBy(array(
                'groupid' => $group->getId(),
                'userid' => $userID,
            ));

            if (is_null($groupMember)) {
                continue;
            }

            $em->remove($groupMember);
        }
    }

    /**
     * @param integer $companyID
     * @param string  $userID
     * @param $em
     *
     * @return array
     */
    private function deleteFromGroupChat(
        $companyID,
        $userID,
        $em
    ) {
        $groupChatJIDs = array();

        // get user JID
        $globals = $this->container->get('twig')->getGlobals();
        $userJID = $userID.'@'.$globals['xmpp_domain'];

        // get group chats
        $groupChatExtraRepo = $this->getRepo('GroupChatExtra');
        $groupChats = $this->getRepo('Groupchat')->findAllByCompanyAndJID($companyID, $userJID);

        foreach ($groupChats as $groupChat) {
            $groupChatJIDs[] = $groupChat->getJid();

            // only groupchat owner can delete group chat extra
            if ($userJID != $groupChat->getOwner()) {
                continue;
            }

            $groupChatExtra = $groupChatExtraRepo->findOneByRoomid($groupChat->getId());
            if (is_null($groupChatExtra)) {
                continue;
            }
            $em->remove($groupChatExtra);
        }

        return $groupChatJIDs;
    }

    /**
     * @param integer $companyID
     * @param string  $userID
     * @param $em
     *
     * @return array
     */
    private function deleteFromGuestService(
        $companyID,
        $userID,
        $em
    ) {
        $guestServiceJIDs = array();

        // get guest services
        $guestMemberRepo =  $this->getRepo('GuestMember');
        $guestServices = $this->getRepo('GuestView')->findAllWithCompanyAndAffiliation($companyID, $userID);

        foreach ($guestServices as $guestService) {
            $guestServiceJIDs[] = $guestService->getGroupchatjid();

            if ($userID === $guestService->getOrganizer()) {
                // delete all guest members
                $guestMembers = $guestMemberRepo->findBy(array(
                    'guestserviceid' => $guestService->getId(),
                ));
                foreach ($guestMembers as $guestMember) {
                    $em->remove($guestMember);
                }

                // delete guest service
                $guest = $this->getRepo('Guest')->findOneById($guestService->getId());
                if (!is_null($guest)) {
                    $em->remove($guest);
                }
            } else {
                // delete user from guest member
                $guestMembers = $guestMemberRepo->findBy(array(
                    'guestserviceid' => $guestService->getId(),
                    'userid' => $userID,
                    'role' => 'member',
                ));
                foreach ($guestMembers as $guestMember) {
                    $em->remove($guestMember);
                }
            }
        }

        return $guestServiceJIDs;
    }

    /**
     * @param Request $request
     * @param string  $userID
     * @param string  $ownerJID
     * @param array   $groupChatJIDs
     */
    private function deleteGroupChatsFromOpenfire(
        Request $request,
        $userID,
        $ownerJID,
        $groupChatJIDs
    ) {
        $globals = $this->container->get('twig')->getGlobals();
        $userJID = $userID.'@'.$globals['xmpp_domain'];

        // the request token from header
        $token = $request->headers->get(self::HTTP_HEADER_AUTH);

        // get url
        $apiUrl = $this->getOpenfireApiUrl($globals);

        $requestArray = array(
            'user' => $userJID,
            'owner' => $ownerJID,
            'rooms' => $groupChatJIDs,
        );

        // init curl
        $ch = curl_init($apiUrl);
        $this->callOpenfireAPI($ch, json_encode($requestArray), $token);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode != self::HTTP_STATUS_OK) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }
    }

    private function getOpenfireApiUrl($globals)
    {
        // Openfire API URL
        return $apiUrl = $globals['openfire_innet_protocol'].
            $globals['openfire_innet_address'].
            $globals['openfire_innet_port'].
            $globals['openfire_plugin_groupchat'].
            $globals['openfire_plugin_groupchat_rooms'].
            $globals['openfire_plugin_groupchat_rooms_action'].
            $globals['openfire_plugin_groupchat_rooms_action_remove'];
    }

    /**
     * @param $data
     * @return mixed
     */
    private function callOpenfireAPI($ch, $data, $token)
    {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, self::HTTP_METHOD_DELETE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization:'.$token));

        return curl_exec($ch);
    }

    /**
     * @param $companyId
     * @return string
     */
    private function getCompanyAdminJID(
        $companyId
    ) {
        $admin = $this->getRepo('CompanyAdmin')->findOneByCompanyid($companyId);
        $this->throwNotFoundIfNull($admin, self::NOT_FOUND_MESSAGE);

        $globals = $this->container->get('twig')->getGlobals();

        return $admin->getUsername().'@'.$globals['xmpp_domain'];
    }
}
