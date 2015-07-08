<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\User\UserProfileController;
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

        // set profile extra fields
        $profile->setHobbies($user->getHobbies());
        $profile->setEducations($user->getEducations());
        $profile->setExperiences($user->getExperiences());
        $profile->setPortfolios($user->getPortfolios());

        // set view
        $view = new View($profile);
        $view->setSerializationContext(SerializationContext::create()->setGroups(array('profile')));

        return $view;
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     *
     * @Route("/profile")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postProfileAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
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
        $building = $this->getRepo('Room\RoomBuilding')->find($buildingId);
        if (is_null($building)) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }
        $userProfile->setBuilding($building);

        // education
        $educations = $userProfile->getEducations();
        foreach ($educations as $education) {
            $userEducation = $this->generateUserEducation($user, $education);
            $em->persist($userEducation);
        }

        // experience
        $experiences = $userProfile->getExperiences();
        foreach ($experiences as $experience) {
            $userExperience = $this->generateUserExperience($user, $experience);
            $em->persist($userExperience);
        }

        // hobby
        $hobbyIds = $userProfile->getHobbyIds();
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

        // portfolio
        $portfolios = $userProfile->getPortfolios();
        foreach ($portfolios as $portfolio) {
            $userPortfolio = $this->generateUserPortfolio($user, $portfolio);
            $em->persist($userPortfolio);
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
