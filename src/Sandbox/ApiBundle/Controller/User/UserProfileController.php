<?php

namespace Sandbox\ApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Buddy\BuddyRequest;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\User\UserProfile;
use Sandbox\ApiBundle\Entity\User\UserHobby;
use Sandbox\ApiBundle\Entity\User\UserHobbyMap;
use Sandbox\ApiBundle\Entity\User\UserEducation;
use Sandbox\ApiBundle\Entity\User\UserExperience;
use Sandbox\ApiBundle\Entity\User\UserPortfolio;
use Sandbox\ApiBundle\Entity\User\UserProfileVisitor;
use Sandbox\ApiBundle\Form\User\UserEducationType;
use Sandbox\ApiBundle\Form\User\UserExperienceType;
use Sandbox\ApiBundle\Form\User\UserPortfolioType;

/**
 * User Profile Controller.
 *
 * @category Sandbox
 *
 * @author   Josh Yang <josh.yang@Sandbox.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class UserProfileController extends SandboxRestController
{
    /**
     * @param int         $myUserId
     * @param User        $requestUser
     * @param UserProfile $profile
     * @param string      $viewGroup
     *
     * @return string
     */
    protected function setProfileWithViewGroup(
        $myUserId,
        $requestUser,
        $profile,
        $viewGroup
    ) {
        // get globals
        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        // save visitor record
        $this->saveUserProfileVisitor(
            $myUserId,
            $requestUser->getId()
        );

        // if user is not my buddy, then do not show email, phone or birthday
        $myBuddy = $this->getRepo('Buddy\Buddy')->findOneBy(array(
            'userId' => $myUserId,
            'buddyId' => $requestUser->getId(),
        ));

        if (!is_null($myBuddy)) {
            $profile->setStatus(BuddyRequest::BUDDY_REQUEST_STATUS_ACCEPTED);

            // if both user is buddy with each other
            // then show user jid
            $otherBuddy = $this->getRepo('Buddy\Buddy')->findOneBy(array(
                'userId' => $requestUser->getId(),
                'buddyId' => $myUserId,
            ));

            if (!is_null($otherBuddy)) {
                $jid = $requestUser->getXmppUsername().'@'.$globals['xmpp_domain'];
                $profile->setJid($jid);
            }
        } else {
            $viewGroup = $viewGroup.'_stranger';

            $myBuddyRequest = $this->getRepo('Buddy\BuddyRequest')->findOneBy(array(
                'askUserId' => $requestUser->getId(),
                'recvUserId' => $myUserId,
                'status' => BuddyRequest::BUDDY_REQUEST_STATUS_PENDING,
            ));

            if (!is_null($myBuddyRequest)) {
                $profile->setStatus(BuddyRequest::BUDDY_REQUEST_STATUS_PENDING);
            }
        }

        return $viewGroup;
    }

    /**
     * @param $myUserId
     * @param $requestUserId
     */
    protected function saveUserProfileVisitor(
        $myUserId,
        $requestUserId
    ) {
        $visitor = new UserProfileVisitor();
        $visitor->setUserId($myUserId);
        $visitor->setVisitorId($requestUserId);

        $em = $this->getDoctrine()->getManager();
        $em->persist($visitor);
        $em->flush();
    }

    /**
     * @param User      $user
     * @param UserHobby $hobby
     *
     * @return UserHobbyMap
     */
    protected function generateUserHobbyMap(
        $user,
        $hobby
    ) {
        $userHobbyMap = new UserHobbyMap();

        $userHobbyMap->setUser($user);
        $userHobbyMap->setHobby($hobby);
        $userHobbyMap->setCreationDate(new \DateTime('now'));

        return $userHobbyMap;
    }

    /**
     * @param User  $user
     * @param array $education
     *
     * @return UserEducation
     */
    protected function generateUserEducation(
        $user,
        $education
    ) {
        $userEducation = new UserEducation();

        $form = $this->createForm(new UserEducationType(), $userEducation);
        $form->submit($education);

        $userEducation->setUser($user);

        return $userEducation;
    }

    /**
     * @param User  $user
     * @param array $experience
     *
     * @return UserExperience
     */
    protected function generateUserExperience(
        $user,
        $experience
    ) {
        $userExperience = new UserExperience();

        $form = $this->createForm(new UserExperienceType(), $userExperience);
        $form->submit($experience);

        $userExperience->setUser($user);

        return $userExperience;
    }

    /**
     * @param User  $user
     * @param array $portfolio
     *
     * @return UserPortfolio
     */
    protected function generateUserPortfolio(
        $user,
        $portfolio
    ) {
        $userPortfolio = new UserPortfolio();

        $form = $this->createForm(new UserPortfolioType(), $userPortfolio);
        $form->submit($portfolio);

        $userPortfolio->setUser($user);

        return $userPortfolio;
    }
}
