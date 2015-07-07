<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\User\UserProfileController;
use Sandbox\ApiBundle\Form\User\UserProfileBasicType;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Rs\Json\Patch;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Rest controller for user`s basic profile.
 *
 * @category Sandbox
 *
 * @author   Josh Yang
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 * @Route("/profile")
 */
class ClientUserBasicProfileController extends UserProfileController
{
    /**
     * Get user's education.
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
     * @Route("/basic")
     * @Method({"GET"})
     *
     * @return array
     */
    public function getUserBasicProfileAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $paramFetcher->get('user_id');
        if (is_null($userId)) {
            $userId = $this->getUserId();
        }

        $userBasic = $this->getRepo('User\UserProfile')->findOneByUserId($userId);

        $view = new View($userBasic);
        $view->setSerializationContext(SerializationContext::create()->setGroups(array('profile_basic')));

        return $view;
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param int                   $id
     *
     * @Route("/basic/{id}")
     * @Method({"PATCH"})
     *
     * @return View
     */
    public function patchUserBasicProfileAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $profile = $this->getRepo('User\UserProfile')->find($id);
        $this->throwNotFoundIfNull($profile, self::NOT_FOUND_MESSAGE);

        if ($this->getUserId() != $profile->getUserId()) {
            throw new AccessDeniedHttpException(self::NOT_ALLOWED_MESSAGE);
        }

        $profileJson = $this->container->get('serializer')->serialize($profile, 'json');
        $patch = new Patch($profileJson, $request->getContent());
        $profileJson = $patch->apply();

        $form = $this->createForm(new UserProfileBasicType(), $profile);
        $form->submit(json_decode($profileJson, true));

        $profile->setModificationDate(new \DateTime('now'));
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return new View();
    }
}
