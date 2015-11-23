<?php

namespace Sandbox\ApiBundle\Traits;

use Symfony\Component\Security\Acl\Exception\Exception;

/**
 * Company Notification Trait.
 *
 * @category Sandbox
 *
 * @author   Leo Xu <leox@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
trait CompanyNotification
{
    use SendNotification;

    /**
     * @param Company $company
     * @param User    $fromUser
     * @param User    $recvUser
     * @param string  $action
     * @param bool    $memberSync
     */
    protected function sendXmppCompanyNotification(
        $company,
        $fromUser,
        $recvUser,
        $action,
        $memberSync
    ) {
        try {
            // get event message data
            $jsonData = $this->getCompanyNotificationJsonData(
                $company, $action, $fromUser, $recvUser, $memberSync
            );

            // send xmpp notification
            $this->sendXmppNotification($jsonData, false);
        } catch (Exception $e) {
            error_log('Send company notification went wrong!');
        }
    }

    /**
     * @param Company $company
     * @param string  $action
     * @param User    $fromUser
     * @param User    $recvUser
     * @param bool    $memberSync
     *
     * @return string | object
     */
    private function getCompanyNotificationJsonData(
        $company,
        $action,
        $fromUser,
        $recvUser,
        $memberSync
    ) {
        // get globals
        $globals = $this->getGlobals();

        $domainURL = $globals['xmpp_domain'];

        // get receivers
        $receivers = array();

        if ($memberSync) {
            $members = $this->getRepo('Company\CompanyMember')->findBy(array(
                'company' => $company,
            ));

            foreach ($members as $member) {
                $user = $this->getRepo('User\User')->find($member->getUserId());
                if (is_null($user)) {
                    continue;
                }
                $jid = $user->getXmppUsername().'@'.$domainURL;
                $receivers[] = array('jid' => $jid);
            }
        } else {
            $jid = $recvUser->getXmppUsername().'@'.$domainURL;
            $receivers[] = array('jid' => $jid);
        }

        // get content array
        $contentArray = $this->getDefaultContentArray(
            'company', $action, $fromUser
        );

        $contentArray['company'] = array(
            'id' => $company->getId(),
            'name' => $company->getName(),
        );

        $data = $this->getNotificationJsonData($receivers, $contentArray);

        return json_encode(array($data));
    }
}
