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
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use FOS\RestBundle\Request\ParamFetcherInterface;

/**
 * User Profile Controller.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang <yimo.zhang@Sandbox.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class UserProfileController extends SandboxRestController
{
    const USER_HOBBY_PREFIX = 'user.hobby.';

    /**
     * Get background attachments.
     *
     * @param Request $request
     *
     * @Route("/user/background/attachments")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getBackgroundAttachmentsAction(
        Request $request
    ) {
        $attachments = $this->getRepo('User\UserBackground')->findAll();

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['background_list']));
        $view->setData($attachments);

        return $view;
    }

    /**
     * Get avatar attachments.
     *
     * @param Request $request
     *
     * @Route("/user/avatar/attachments")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getAvatarAttachmentsAction(
        Request $request
    ) {
        $attachments = $this->getRepo('User\UserAvatar')->findAll();

        $view = new View();
        $view->setSerializationContext(SerializationContext::create()->setGroups(['avatar_list']));
        $view->setData($attachments);

        return $view;
    }

    /**
     * @param ParamFetcherInterface $paramFetcher
     * @param bool                  $getAll
     *
     * @return View
     *
     * @throws BadRequestHttpException
     */
    protected function handleGetUserProfile(
        $paramFetcher,
        $getAll
    ) {
        // get params
        $userId = $paramFetcher->get('user_id');
        $xmppUsername = $paramFetcher->get('xmpp_username');

        $myUser = null;
        $user = null;

        if (!$this->isAuthProvided()) {
            if (is_null($userId) && is_null($xmppUsername)) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }
        } else {
            // my user
            $myUserId = $this->getUserId();
            $myUser = $this->getRepo('User\User')->find($myUserId);

            // get user id
            if (is_null($userId)) {
                $userId = $myUserId;
            }
        }

        // get request user
        if (is_null($xmppUsername)) {
            $user = $this->getRepo('User\User')->find($userId);
        } else {
            $user = $this->getRepo('User\User')->findOneByXmppUsername($xmppUsername);
        }
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        // check the user is banned
        if ($user->isBanned()) {
            return new View();
        }

        // get profile
        $profile = $this->getRepo('User\UserProfile')->findOneByUser($user);
        $this->throwNotFoundIfNull($profile, self::NOT_FOUND_MESSAGE);

        $viewGroup = 'profile';

        // set profile with view group
        $viewGroup = $this->setProfileWithViewGroup(
            $myUser,
            $user,
            $profile,
            $viewGroup
        );

        if ($getAll) {
            // set user hobbies
            $hobbies = $this->getRepo('User\UserHobbyMap')->findByUser($user);
            if (!is_null($hobbies) && !empty($hobbies)) {
                // translate hobby key
                $hobbiesArray = $this->generateHobbyMapResult(
                    $hobbies
                );

                $profile->setHobbies($hobbiesArray);
            }

            // set user educations
            $educations = $this->getRepo('User\UserEducation')->findByUser($user);
            if (!is_null($educations) && !empty($educations)) {
                $profile->setEducations($educations);
            }

            // set user experiences
            $experiences = $this->getRepo('User\UserExperience')->findByUser($user);
            if (!is_null($experiences) && !empty($experiences)) {
                $profile->setExperiences($experiences);
            }

            // set user portfolios
            $portfolios = $this->getRepo('User\UserPortfolio')->findByUser($user);
            if (!is_null($portfolios) && !empty($portfolios)) {
                $profile->setPortfolios($portfolios);
            }
        }

        $profile->setXmppUsername($user->getXmppUsername());

        // set view
        $view = new View($profile);
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(array($viewGroup))
        );

        return $view;
    }

    /**
     * @param User        $myUser
     * @param User        $requestUser
     * @param UserProfile $profile
     * @param string      $viewGroup
     *
     * @return string
     */
    protected function setProfileWithViewGroup(
        $myUser,
        $requestUser,
        $profile,
        $viewGroup
    ) {
        if (is_null($myUser) || is_null($requestUser)) {
            return $viewGroup.'_stranger';
        }

        if ($myUser == $requestUser) {
            return $viewGroup;
        }

        // get globals
        $twig = $this->container->get('twig');
        $globals = $twig->getGlobals();

        // save visitor record
        $this->saveUserProfileVisitor(
            $myUser,
            $requestUser
        );

        // if user is not my buddy, then do not show email, phone or birthday
        $myBuddy = $this->getRepo('Buddy\Buddy')->findOneBy(array(
            'user' => $myUser,
            'buddy' => $requestUser,
        ));

        if (!is_null($myBuddy)) {
            $profile->setStatus(BuddyRequest::STATUS_ACCEPTED);
            $profile->setBuddyId($myBuddy->getId());

            // if both user is buddy with each other
            // then show user jid
            $otherBuddy = $this->getRepo('Buddy\Buddy')->findOneBy(array(
                'user' => $requestUser,
                'buddy' => $myUser,
            ));

            if (!is_null($otherBuddy)) {
                $jid = $requestUser->getXmppUsername().'@'.$globals['xmpp_domain'];
                $profile->setJid($jid);
            }
        } else {
            $viewGroup = $viewGroup.'_stranger';

            $myBuddyRequest = $this->getRepo('Buddy\BuddyRequest')->findOneBy(array(
                'askUser' => $requestUser,
                'recvUser' => $myUser,
                'status' => BuddyRequest::STATUS_PENDING,
            ));

            if (!is_null($myBuddyRequest)) {
                $profile->setStatus(BuddyRequest::STATUS_PENDING);
            }
        }

        return $viewGroup;
    }

    /**
     * @param User $myVisitor
     * @param User $requestUser
     */
    protected function saveUserProfileVisitor(
        $myVisitor,
        $requestUser
    ) {
        $visitor = new UserProfileVisitor();
        $visitor->setUser($requestUser);
        $visitor->setVisitor($myVisitor);

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

    /**
     * @param $hobbies
     *
     * @return array
     */
    protected function generateHobbyMapResult(
        $hobbies
    ) {
        if (empty($hobbies)) {
            return;
        }

        foreach ($hobbies as $hobbyMap) {
            if (is_null($hobbyMap)) {
                continue;
            }

            // translate hobby key
            $hobby = $hobbyMap->getHobby();
            $hobbyKey = $hobby->getKey();
            $trans = $this->get('translator')->trans(self::USER_HOBBY_PREFIX.$hobbyKey);
            $hobby->setName($trans);
        }

        return $hobbies;
    }
}
