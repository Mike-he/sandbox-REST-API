<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\Location\LocationController;
use Sandbox\ApiBundle\Entity\User\UserAppointmentProfile;
use Sandbox\ApiBundle\Form\User\UserAppointmentProfileType;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Rest controller for User appointment profile.
 *
 * @category Sandbox
 *
 * @author   Leo Xu
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @see     http://www.Sandbox.cn/
 */
class ClientUserAppointmentProfileController extends LocationController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *    name="limit",
     *    array=false,
     *    default="10",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="limit for the page"
     * )
     *
     * @Annotations\QueryParam(
     *    name="offset",
     *    array=false,
     *    default="0",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="start of the page"
     * )
     *
     * @Route("/profiles")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getUserAppointmentProfilesAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $this->getUserId();
        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->findOneBy([
                'banned' => false,
                'id' => $userId,
            ]);
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        $limit = $paramFetcher->get('limit');
        $offset = $paramFetcher->get('offset');

        $profiles = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserAppointmentProfile')
            ->findBy(
                ['user' => $user],
                ['modificationDate' => 'DESC'],
                $limit,
                $offset
            );

        $view = new View($profiles);
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client']));

        return $view;
    }

    /**
     * @param Request $request
     * @param int     $id
     *
     * @Route("/profiles/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getUserAppointmentProfileByIdAction(
        Request $request,
        $id
    ) {
        $profile = $this->getProfileById($id);

        $view = new View($profile);
        $view->setSerializationContext(SerializationContext::create()->setGroups(['client']));

        return $view;
    }

    /**
     * @param Request $request
     *
     * @Route("/profiles")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postUserAppointmentProfileAction(
        Request $request
    ) {
        $userId = $this->getUserId();
        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->findOneBy([
                'banned' => false,
                'id' => $userId,
            ]);
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        return $this->handleProfilePost($request, $user);
    }

    /**
     * @param Request $request
     * @param int     $id
     *
     * @Route("/profiles/{id}")
     * @Method({"PUT"})
     *
     * @return View
     */
    public function putUserAppointmentProfileAction(
        Request $request,
        $id
    ) {
        $profile = $this->getProfileById($id);

        $this->handleProfilePut($request, $profile);

        return new View();
    }

    /**
     * @param Request $request
     * @param int     $id
     *
     * @Route("/profiles/{id}")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function deleteUserAppointmentProfileAction(
        Request $request,
        $id
    ) {
        $profile = $this->getProfileById($id);

        $this->handleProfileDelete($profile);

        return new View();
    }

    /********************** PRIVATE FUNCTIONS ******************************************/

    /**
     * @param UserAppointmentProfile $profile
     */
    private function handleProfileDelete(
        $profile
    ) {
        $em = $this->getDoctrine()->getManager();

        $em->remove($profile);
        $em->flush();
    }

    /**
     * @param int $id
     *
     * @return UserAppointmentProfile
     */
    private function getProfileById(
        $id
    ) {
        $userId = $this->getUserId();
        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->findOneBy([
                'banned' => false,
                'id' => $userId,
            ]);
        $this->throwNotFoundIfNull($user, self::NOT_FOUND_MESSAGE);

        $profile = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserAppointmentProfile')
            ->findOneBy([
                'id' => $id,
                'user' => $user,
            ]);
        $this->throwNotFoundIfNull($profile, self::NOT_FOUND_MESSAGE);

        return $profile;
    }

    /**
     * @param Request                $request
     * @param UserAppointmentProfile $profile
     */
    private function handleProfilePut(
        $request,
        $profile
    ) {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(
            new UserAppointmentProfileType(),
            $profile,
            array('method' => 'PUT')
        );
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $em->flush();
    }

    /**
     * @param $request
     * @param $user
     */
    private function handleProfilePost(
        $request,
        $user
    ) {
        $em = $this->getDoctrine()->getManager();

        $profile = new UserAppointmentProfile();

        $form = $this->createForm(new UserAppointmentProfileType(), $profile);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $profile->setUser($user);

        $em->persist($profile);
        $em->flush();

        return new View(
            ['id' => $profile->getId()],
            self::HTTP_STATUS_CREATE_SUCCESS
        );
    }
}
