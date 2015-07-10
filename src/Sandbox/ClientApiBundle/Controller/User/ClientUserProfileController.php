<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\User\UserProfileController;
use Sandbox\ApiBundle\Entity\Buddy\BuddyRequest;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\User\UserProfile;
use Sandbox\ApiBundle\Form\User\UserProfileType;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use JMS\Serializer\SerializationContext;

/**
 * Rest controller for UserProfile.
 *
 * @category Sandbox
 *
 * @author   Josh Yang
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class ClientUserProfileController extends UserProfileController
{
    /**
     * Get a single Profile.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @Annotations\QueryParam(
     *    name="user_id",
     *    default=null,
     *    description="userId"
     * )
     *
     * @Route("/profile")
     * @Method({"GET"})
     *
     * @return array
     */
    public function getProfileAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $paramFetcher->get('user_id');
        if (is_null($userId)) {
            $userId = $this->getUserId();
        }

        // get user
        $user = $this->getRepo('User\User')->find($userId);
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        // get profile
        $profile = $this->getRepo('User\UserProfile')->findOneByUser($user);
        $this->throwNotFoundIfNull($profile, self::NOT_FOUND_MESSAGE);

        $viewGroup = 'profile';

        // if user is not my buddy, then do not show email, phone or birthday
        if ($this->getUserId() != $userId) {
            $myBuddy = $this->getRepo('Buddy\Buddy')->findOneBy(array(
                'userId' => $this->getUserId(),
                'buddyId' => $userId,
            ));

            if (!is_null($myBuddy)) {
                $profile->setStatus(BuddyRequest::BUDDY_REQUEST_STATUS_ACCEPTED);
            } else {
                $viewGroup = $viewGroup.'_stranger';

                $myBuddyRequest = $this->getRepo('Buddy\BuddyRequest')->findOneBy(array(
                    'askUserId' => $userId,
                    'recvUserId' => $this->getUserId(),
                    'status' => BuddyRequest::BUDDY_REQUEST_STATUS_PENDING,
                ));

                if (!is_null($myBuddyRequest)) {
                    $profile->setStatus(BuddyRequest::BUDDY_REQUEST_STATUS_PENDING);
                }
            }
        }

        // set profile extra fields
        $profile->setHobbies($user->getHobbies());
        $profile->setEducations($user->getEducations());
        $profile->setExperiences($user->getExperiences());
        $profile->setPortfolios($user->getPortfolios());

        // set view
        $view = new View($profile);
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(array($viewGroup))
        );

        return $view;
    }

    /**
     * @param Request $request
     *
     * @Route("/profile")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postProfileAction(
        Request $request
    ) {
        $userId = $this->getUserId();
        $user = $this->getRepo('User\User')->find($userId);
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        $userProfile = $this->getRepo('User\UserProfile')->findOneByUser($user);
        if (!is_null($userProfile)) {
            throw new ConflictHttpException(self::CONFLICT_MESSAGE);
        }

        $userProfile = new UserProfile();

        $form = $this->createForm(new UserProfileType(), $userProfile);
        $form->handleRequest($request);

        if ($form->isValid()) {
            return $this->handlePostUserProfile($userProfile, $user);
        }

        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
    }

    /**
     * @param UserProfile $userProfile
     * @param User        $user
     *
     * @return View
     */
    private function handlePostUserProfile(
        $userProfile,
        $user
    ) {
        $em = $this->getDoctrine()->getManager();

        // set user
        $userProfile->setUser($user);

        // set building
        $buildingId = $userProfile->getBuildingId();
        if (!is_null($buildingId)) {
            $building = $this->getRepo('Room\RoomBuilding')->find($buildingId);
            if (is_null($building)) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }
            $userProfile->setBuilding($building);
        }

        // hobby
        $hobbyIds = $userProfile->getHobbyIds();
        if (!is_null($hobbyIds) && !empty($hobbyIds)) {
            foreach ($hobbyIds as $hobbyId) {
                $hobby = $this->getRepo('User\UserHobby')->find($hobbyId);
                if (is_null($hobby)) {
                    continue;
                }

                $hobbyMap = $this->getRepo('User\UserHobbyMap')->findOneBy(array(
                    'user' => $user,
                    'hobby' => $hobby,
                ));
                if (!is_null($hobbyMap)) {
                    continue;
                }

                $userHobbyMap = $this->generateUserHobbyMap($user, $hobby);
                $em->persist($userHobbyMap);
            }
        }

        // education
        $educations = $userProfile->getEducations();
        if (!is_null($educations) && !empty($educations)) {
            foreach ($educations as $education) {
                $userEducation = $this->generateUserEducation($user, $education);
                $em->persist($userEducation);
            }
        }

        // experience
        $experiences = $userProfile->getExperiences();
        if (!is_null($experiences) && !empty($experiences)) {
            foreach ($experiences as $experience) {
                $userExperience = $this->generateUserExperience($user, $experience);
                $em->persist($userExperience);
            }
        }

        // portfolio
        $portfolios = $userProfile->getPortfolios();
        if (!is_null($portfolios) && !empty($portfolios)) {
            foreach ($portfolios as $portfolio) {
                $userPortfolio = $this->generateUserPortfolio($user, $portfolio);
                $em->persist($userPortfolio);
            }
        }

        // save to db
        $em->persist($userProfile);
        $em->flush();

        // set view
        $view = new View();
        $view->setData(
            array('id' => $userProfile->getId())
        );

        return $view;
    }
}
