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

/**
 * Rest controller for UserProfile.
 *
 * @category Sandbox
 *
 * @author   Yimo Zhang <yimo.zhang@Sandbox.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @see     http://www.Sandbox.cn/
 */
class ClientUserProfileController extends UserProfileController
{
    /**
     * Get user's rofile.
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
     * @Annotations\QueryParam(
     *    name="xmpp_username",
     *    default=null,
     *    description="xmppUsername"
     * )
     *
     * @Route("/profile")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getProfileAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        return $this->handleGetUserProfile($paramFetcher, true);
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
