<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\User\UserProfileController;
use Sandbox\ApiBundle\Entity\User\UserEducation;
use Sandbox\ApiBundle\Entity\User\UserExperience;
use Sandbox\ApiBundle\Entity\User\UserHobbyMap;
use Sandbox\ApiBundle\Entity\User\UserPortfolio;
use Sandbox\ApiBundle\Entity\User\UserProfile;
use Sandbox\ApiBundle\Form\User\UserProfileType;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
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
        if ($userId === 0) {
            $userId = $this->getUserid();
        }

        $userProfile = $this->getRepo('User\UserProfile')->findOneByUserId($userId);
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
     * @return View
     */
    public function postProfileAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserid();
        $userProfile = $this->getRepo('User\UserProfile')->findOneByUserId($userId);
        if (!is_null($userProfile)) {
            throw new BadRequestHttpException('can not add profile.');
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
     * @param  UserProfile $userProfile
     * @return View
     */
    private function handlePostUserProfile(
        $userProfile
    ) {
        $userId = (int) $this->getUserid();
        $em = $this->getDoctrine()->getManager();

        $userEducations = $userProfile->getEducations();
        if (!empty($userEducations)) {
            foreach ($userEducations as $userEducation) {
                $userEducationEntity = $this->generateUserEducationEntity($userId, $userEducation);
                $em->persist($userEducationEntity);
            }
        }

        $userExperiences = $userProfile->getExperiences();
        if (!empty($userExperiences)) {
            foreach ($userExperiences as $userExperience) {
                $userExperienceEntity = $this->generateUserExperienceEntity($userId, $userExperience);
                $em->persist($userExperienceEntity);
            }
        }

        $userHobbies = $userProfile->getHobbies();
        if (!empty($userHobbies)) {
            foreach ($userHobbies as $userHobby) {
                $userHobbyMapEntity = $this->generateUserHobbyMapEntity($userId, $userHobby);
                $em->persist($userHobbyMapEntity);
            }
        }

        $userPortfolios = $userProfile->getPortfolios();
        if (!empty($userPortfolios)) {
            foreach ($userPortfolios as $userPortfolio) {
                $userPortfolioEntity = $this->generateUserPortfolioEntity($userId, $userPortfolio);
                $em->persist($userPortfolioEntity);
            }
        }

        $userProfile->setUserId($userId);
        $em->persist($userProfile);
        $em->flush();

        $view = new View();
        $view->setData(
            array('id' => $userProfile->getId())
        );

        return $view;
    }

    /**
     * @param int   $userId
     * @param array $userEducation
     *
     * @return UserEducation
     */
    private function generateUserEducationEntity(
        $userId,
        $userEducation
    ) {
        $userEducationEntity = new UserEducation();
        $userEducationEntity->setUserId($userId);

        $startDate = $userEducation['start_date'];
        if (!is_null($startDate)) {
            $userEducationEntity->setStartDate($startDate);
        }

        $endDate = $userEducation['end_date'];
        if (!is_null($endDate)) {
            $userEducationEntity->setEndDate($endDate);
        }

        $detail = $userEducation['detail'];
        if (!is_null($detail)) {
            $userEducationEntity->setDetail($detail);
        }

        return $userEducationEntity;
    }

    /**
     * @param  int            $userId
     * @param  array          $userExperience
     * @return UserExperience
     */
    private function generateUserExperienceEntity(
        $userId,
        $userExperience
    ) {
        $userExperienceEntity = new UserExperience();
        $userExperienceEntity->setUserId($userId);

        $startDate = $userExperience['start_date'];
        if (!is_null($startDate)) {
            $userExperienceEntity->setStartDate($startDate);
        }

        $endDate = $userExperience['end_date'];
        if (!is_null($endDate)) {
            $userExperienceEntity->setEndDate($endDate);
        }

        $detail = $userExperience['detail'];
        if (!is_null($detail)) {
            $userExperience->setDetail($detail);
        }

        return $userExperienceEntity;
    }

    /**
     * @param  int           $userId
     * @param  array         $userPortfolio
     * @return UserPortfolio
     */
    private function generateUserPortfolioEntity(
        $userId,
        $userPortfolio
    ) {
        $userPortfolioEntity = new UserPortfolio();
        $userPortfolioEntity->setUserId($userId);

        $attachmentType = $userPortfolio['attachment_type'];
        if (!is_null($attachmentType)) {
            $userPortfolioEntity->setAttachmentType($attachmentType);
        }

        $content = $userPortfolio['content'];
        if (!is_null($content)) {
            $userPortfolioEntity->setContent($content);
        }

        $fileName = $userPortfolio['file_name'];
        if (!is_null($fileName)) {
            $userPortfolioEntity->setFileName($fileName);
        }

        $preview = $userPortfolio['preview'];
        if (!is_null($preview)) {
            $userPortfolioEntity->setPreview($preview);
        }

        $size = $userPortfolio['size'];
        if (!is_null($size)) {
            $userPortfolioEntity->setSize($size);
        }

        return $userPortfolioEntity;
    }

    /**
     * @param  int          $userId
     * @param  array        $userHobby
     * @return UserHobbyMap
     */
    private function generateUserHobbyMapEntity(
        $userId,
        $userHobby
    ) {
        $userHobbyMapEntity = new UserHobbyMap();
        $userHobbyMapEntity->setUserId($userId);

        $hobbyId = $userHobby['id'];
        if (!is_null($hobbyId)) {
            $userHobbyMapEntity->setHobbyId($hobbyId);
        }

        return $userHobbyMapEntity;
    }
}
