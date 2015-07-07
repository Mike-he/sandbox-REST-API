<?php

namespace Sandbox\ApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\User\UserHobby;
use Sandbox\ApiBundle\Entity\User\UserHobbyMap;

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
}
