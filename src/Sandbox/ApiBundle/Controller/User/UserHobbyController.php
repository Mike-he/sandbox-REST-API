<?php

namespace Sandbox\ApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use JMS\Serializer\SerializationContext;

/**
 * User Hobby Controller.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang <yimozh@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class UserHobbyController extends SandboxRestController
{
    /**
     * Get all hobbies.
     *
     * @param Request $request the request object
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @return View
     */
    public function getHobbiesAction(
        Request $request
    ) {
        $hobbies = $this->getRepo('User\UserHobby')->findAll();

        $view = new View($hobbies);
        $view->setSerializationContext(
            SerializationContext::create()->setGroups(array('hobbies'))
        );

        return $view;
    }

    /**
     * Get a single hobby.
     *
     * @param Request $request the request object
     * @param string  $id      the hobby Id
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @return View
     */
    public function getHobbyAction(
        Request $request,
        $id
    ) {
        $hobby = $this->getRepo('User\UserHobby')->find($id);

        return new View($hobby);
    }
}
