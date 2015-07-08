<?php

namespace Sandbox\ApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\User\UserHobby;
use Sandbox\ApiBundle\Entity\User\UserHobbyMap;
use Sandbox\ApiBundle\Entity\User\UserEducation;
use Sandbox\ApiBundle\Entity\User\UserExperience;
use Sandbox\ApiBundle\Entity\User\UserPortfolio;
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
