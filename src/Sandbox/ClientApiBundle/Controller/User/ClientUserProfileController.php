<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\User\UserProfileController;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\User\UserEducation;
use Sandbox\ApiBundle\Entity\User\UserExperience;
use Sandbox\ApiBundle\Entity\User\UserHobby;
use Sandbox\ApiBundle\Entity\User\UserHobbyMap;
use Sandbox\ApiBundle\Entity\User\UserPortfolio;
use Sandbox\ApiBundle\Entity\User\UserProfile;
use Sandbox\ApiBundle\Form\User\UserEducationType;
use Sandbox\ApiBundle\Form\User\UserExperienceType;
use Sandbox\ApiBundle\Form\User\UserPortfolioType;
use Sandbox\ApiBundle\Form\User\UserProfileType;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

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

        // profile
        $userProfile = $this->getRepo('User\UserProfile')->findOneByUserId($userId);
        $this->throwNotFoundIfNull($userProfile, self::NOT_FOUND_MESSAGE);

        // education
        $userEducation = $this->getRepo('User\UserEducation')->findByUserId($userId);
        if (!empty($userEducation)) {
            $userProfile->setEducations($userEducation);
        }

        // experience
        $userExperience = $this->getRepo('User\UserExperience')->findByUserId($userId);
        if (!empty($userExperience)) {
            $userProfile->setExperiences($userExperience);
        }

        // portfolio
        $userPortfolio = $this->getRepo('User\UserPortfolio')->findByUserId($userId);
        if (!empty($userPortfolio)) {
            $userProfile->setPortfolios($userPortfolio);
        }

        $userHobbyMap = $this->getRepo('User\UserHobbyMap')->findByUserId($userId);
        $userHobbyArray = array();
        if (!empty($userHobbyMap)) {
            foreach ($userHobbyMap as $userHobby) {
                if (is_null($userHobby)) {
                    continue;
                }

                $id = $userHobby->getId();
                $hobbyId = $userHobby->getHobbyId();
                $hobby = $this->getRepo('User\Hobby')->findOneById($hobbyId);
                $insideHobbyArray = array(
                    'name' => $hobby->getName(),
                    'id' => $id,
                    'hobby_id' => $hobbyId,
                );

                array_push($userHobbyArray, $insideHobbyArray);
            }
        }

        if (!empty($userHobbyArray)) {
            $userProfile->setHobbies($userHobbyArray);
        }

        return new View($userProfile);
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

        $userProfile = $this->getRepo('User\UserProfile')->findOneByUserId($userId);
        if (!is_null($userProfile)) {
            throw new ConflictHttpException(self::CONFLICT_MESSAGE);
        }

        $userProfile = new UserProfile();

        $form = $this->createForm(new UserProfileType(), $userProfile);
        $form->handleRequest($request);

        if ($form->isValid()) {
            return $this->handlePostUserProfile($userProfile);
        }

        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
    }

    /**
     * @param UserProfile $userProfile
     *
     * @return View
     */
    private function handlePostUserProfile(
        $userProfile
    ) {
        $em = $this->getDoctrine()->getManager();

        // set request user
        $userId = $this->getUserId();
        $user = $this->getRepo('User\User')->find($userId);
        if (is_null($user)) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }
        $userProfile->setUser($user);

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
                'userId' => $userId,
                'hobbyId' => $hobbyId,
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

    /**
     * @param User  $user
     * @param array $education
     *
     * @return UserEducation
     */
    private function generateUserEducation(
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
    private function generateUserExperience(
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
    private function generateUserPortfolio(
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
     * @param User      $user
     * @param UserHobby $hobby
     *
     * @return UserHobbyMap
     */
    private function generateUserHobbyMap(
        $user,
        $hobby
    ) {
        $userHobbyMap = new UserHobbyMap();

        $userHobbyMap->setUser($user);
        $userHobbyMap->setHobby($hobby);
        $userHobbyMap->setCreationDate(new \DateTime('now'));

        return $userHobbyMap;
    }
}
