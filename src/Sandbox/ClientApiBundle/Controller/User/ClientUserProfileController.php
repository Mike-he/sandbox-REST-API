<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\User\UserProfileController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Rs\Json\Patch;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Rest controller for UserProfile
 *
 * @category Sandbox
 * @package  Sandbox\ApiBundle\Controller
 * @author   Josh Yang
 * @license  http://www.Sandbox.cn/ Proprietary
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
        $userId = (int) $paramFetcher->get('user_id');
        if (is_null($userId)) {
            $userId = $this->getUserid();
        }

        $userProfile = $this->getRepo('User\UserProfile')->findOneById($userId);
        $this->throwNotFoundIfNull($userProfile, self::NOT_FOUND_MESSAGE);

        $userEducation = $this->getRepo('User\UserEducation')->findByUserId($userId);
        if (!empty($userEducation)) {
            $userProfile->setEducations($userEducation);
        }

        $userExperience = $this->getRepo('User\UserExperience')->findByUserId($userId);
        if (!empty($userExperience)) {
            $userProfile->setExperiences($userExperience);
        }

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

        $userProfile->setHobbies($userHobbyArray);

        return new View($userProfile);
    }
//
//    /**
//     * @param  Request $request
//     * @param $id
//     * @return View
//     */
//    public function getVcardAction(
//        Request $request,
//        $id
//    ) {
//        $vcardRepo = $this->getRepo('JtVCard');
//        $vcard = $vcardRepo->findOneById($id);
//
//        $this->throwNotFoundIfNull($vcard, self::NOT_FOUND_MESSAGE);
//
//        return new View($vcard);
//    }
//
//    /**
//     * @param  Request                   $request
//     * @param $id
//     * @return View
//     * @throws Patch\FailedTestException
//     */
//    public function patchVcardAction(
//        Request $request,
//        $id
//    ) {
//        $vcard = $this->getRepo('JtVCard')->find($id);
//        $this->throwNotFoundIfNull($vcard, 'vcard_patch '.self::NOT_FOUND_MESSAGE);
//
//        $vcardJSON = $this->container->get('serializer')->serialize($vcard, 'json');
//        $patch = new Patch($vcardJSON, $request->getContent());
//        $vcardAfterPatchedJSON = $patch->apply();
//
//        $form = $this->createForm(new JtVCardType(), $vcard);
//        $form->submit(json_decode($vcardAfterPatchedJSON, true));
//
//        $em = $this->getDoctrine()->getManager();
//        $em->flush();
//
//        $view = new View();
//        $view->setData(json_decode($vcardAfterPatchedJSON, true));
//
//        return $view;
//    }
//
//    /**
//     * @param  Request               $request
//     * @param  ParamFetcherInterface $paramFetcher
//     * @return View
//     */
//    public function postVcardAction(
//        Request $request,
//        ParamFetcherInterface $paramFetcher
//    ) {
//        $vcard = new JtVCard();
//        $form = $this->createForm(new JtVCardType(), $vcard);
//        $form->handleRequest($request);
//
//        if (!$form->isValid()) {
//            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
//        }
//
//        $em = $this->getDoctrine()->getManager();
//        $em->persist($vcard);
//        $em->flush();
//
//        $view = $this->routeRedirectView('get_vcard', array('id' => $vcard->getId()));
//        $view->setData(array('id' => $vcard->getId()));
//
//        return $view;
//    }
}
