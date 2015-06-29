<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\User\UserProfileController;
use Sandbox\ApiBundle\Entity\User\UserHobbyMap;
use Sandbox\ApiBundle\Form\User\UserHobbyMapType;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Rest controller for UserHobbyMap
 *
 * @category Sandbox
 * @package  Sandbox\ApiBundle\Controller
 * @author   Josh Yang
 * @license  http://www.Sandbox.cn/ Proprietary
 * @link     http://www.Sandbox.cn/
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
     * @Route("/hobby")
     * @Method({"GET"})
     *
     * @return array
     */
    public function getUserHobbyAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = (int) $paramFetcher->get('user_id');
        if ($userId === 0) {
            $userId = $this->getUserid();
        }

        $userHobbyMap = $this->getRepo('User\UserHobbyMap')->findByUserId($userId);
        $userHobbyArray = array();
        if (!empty($userHobbyMap)) {
            foreach ($userHobbyMap as $userHobby) {
                if (is_null($userHobby)) {
                    continue;
                }

                $hobby = $userHobby->getHobby();
                $name = $hobby->getName();
                $id = $userHobby->getId();
                $hobbyId = $userHobby->getHobbyId();

                $insideHobbyArray = array(
                    'name' => $name,
                    'id' => $id,
                    'hobby_id' => $hobbyId,
                );

                array_push($userHobbyArray, $insideHobbyArray);
            }
        }

        return new View($userHobbyArray);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     *
     * @Route("/hobby")
     * @Method({"POST"})
     * @return View
     */
    public function postUserHobbyAction(
        Request $request,
        ParamFetcherInterface $paramFetcher

    ) {
        $userId = $this->getUserid();

        $hobbyResponseArray = array();
        $em = $this->getDoctrine()->getManager();

        $hobbyIdsArray = json_decode($request->getContent(), true);
        foreach ($hobbyIdsArray as $hobbyId) {
            $userHobbyMap = new UserHobbyMap();
            $form = $this->createForm(new UserHobbyMapType(), $userHobbyMap);
            $form->submit($hobbyId);
            if (!$form->isValid()) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }
            $userHobbyMap->setUserId($userId);
            $em->persist($userHobbyMap);
            $em->flush();

            $insideHobbyIdArray = array('id' => $userHobbyMap->getId());
            array_push($hobbyResponseArray, $insideHobbyIdArray);
        }

        return new View($hobbyResponseArray);
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
     * @Route("/hobby")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function deleteUserHobbyAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userHobbyMaps = $paramFetcher->get('id');
        $this->getRepo('User\UserHobbyMap')->deleteUserHobbyMapsByIds($userHobbyMaps);

        return new View();
    }
}
