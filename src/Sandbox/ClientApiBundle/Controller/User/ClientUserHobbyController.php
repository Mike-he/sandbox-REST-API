<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\User\UserProfileController;
use Sandbox\ApiBundle\Entity\User\UserHobbyMap;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\Serializer\SerializationContext;

/**
 * Rest controller for UserHobbyMap.
 *
 * @category Sandbox
 *
 * @author   Josh Yang
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @see     http://www.Sandbox.cn/
 * @Route("/profile")
 */
class ClientUserHobbyController extends UserProfileController
{
    /**
     * Get user's hobbies.
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
     * @Route("/hobbies")
     * @Method({"GET"})
     *
     * @return array
     */
    public function getUserHobbyAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $paramFetcher->get('user_id');
        if (is_null($userId)) {
            $userId = $this->getUserId();
        }

        $hobbies = $this->getRepo('User\UserHobbyMap')->findByUserId($userId);

        $hobbiesResults = $this->generateHobbyMapResult($hobbies);

        $view = new View($hobbiesResults);
        $view->setSerializationContext(SerializationContext::create()->setGroups(array('profile')));

        return $view;
    }

    /**
     * @param Request $request
     *
     * @Route("/hobbies")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postUserHobbyAction(
        Request $request
    ) {
        $userId = $this->getUserId();
        $user = $this->getRepo('User\User')->find($userId);

        $em = $this->getDoctrine()->getManager();

        $hobbyIds = json_decode($request->getContent(), true);
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

        $em->flush();

        return new View();
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="id",
     *    array=true,
     *    strict=true,
     *    description=""
     * )
     *
     * @Route("/hobbies")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function deleteUserHobbyAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $this->getRepo('User\UserHobbyMap')->deleteUserHobbies(
            $paramFetcher->get('id'),
            $this->getUserId()
        );

        return new View();
    }
}
