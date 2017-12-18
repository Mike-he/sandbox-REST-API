<?php

namespace Sandbox\CommnueAdminApiBundle\Controller\User;

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Commnue\CommnueUser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AdminUserController extends SandboxRestController
{
    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Annotations\QueryParam(
     *     name="banned",
     *     array=false,
     *     default=null,
     *     nullable=true,
     *     strict=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="authenticate",
     *    array=false,
     *    default=null,
     *    nullable=true,
     *    strict=true,
     *    description="user authenticate"
     * )
     *
     * @Annotations\QueryParam(
     *     name="startDate",
     *     array=false,
     *     default=null,
     *     nullable=true,
     *     strict=true
     * )
     *
     * @Annotations\QueryParam(
     *     name="endDate",
     *     array=false,
     *     default=null,
     *     nullable=true,
     *     strict=true
     * )
     *
     * @Annotations\QueryParam(
     *    name="name",
     *    default=null,
     *    description="search query"
     * )
     *
     * @Annotations\QueryParam(
     *    name="phone",
     *    default=null
     * )
     *
     * @Annotations\QueryParam(
     *    name="email",
     *    default=null
     * )
     *
     * @Annotations\QueryParam(
     *    name="id",
     *    default=null
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageLimit",
     *    array=false,
     *    default="20",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="How many rooms to return "
     * )
     *
     * @Annotations\QueryParam(
     *    name="pageIndex",
     *    array=false,
     *    default="1",
     *    nullable=true,
     *    requirements="\d+",
     *    strict=true,
     *    description="page number "
     * )
     *
     * @Route("/users")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getUsersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');
        $offset = ($pageIndex - 1) * $pageLimit;

        $banned = $paramFetcher->get('banned');
        $authenticated = $paramFetcher->get('authenticate');
        $startDate = $paramFetcher->get('startDate');
        $endDate = $paramFetcher->get('endDate');
        $name = $paramFetcher->get('name');
        $phone = $paramFetcher->get('phone');
        $email = $paramFetcher->get('email');
        $id = $paramFetcher->get('id');

        $userIds = null;
        if (!is_null($banned) || !is_null($authenticated)) {
            $commnueUserIds = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Commnue\CommnueUser')
                ->getAdminCommnueUserIds(
                    $banned,
                    $authenticated
                );

            $userIds = $commnueUserIds;
        }

        $users = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserView')
            ->getAdminCommnueUsers(
                $startDate,
                $endDate,
                $name,
                $phone,
                $email,
                $id,
                $userIds,
                $pageLimit,
                $offset
            );

        $usersCount = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Commnue\CommnueUser')
            ->countAdminCommnueUsers(
                $banned,
                $authenticated
            );

        $response = [];
        foreach ($users as $user) {
            $commnueUser = $this->getDoctrine()
                ->getRepository('SandboxApiBundle:Commnue\CommnueUser')
                ->findOneBy(['userId' => $user['id']]);

            if (!is_null($commnueUser)) {
                $commnueUserAuthTagId = $commnueUser->getAuthTagId();

                if (!is_null($commnueUserAuthTagId)) {
                    $commnueUserAuthTag = $this->getDoctrine()
                        ->getRepository('SandboxApiBundle:Commnue\CommnueUserAuthenticationTags')
                        ->find($commnueUserAuthTagId);

                    $user['authenticated'] = true;
                    $user['authentication_tag']['id'] = $commnueUserAuthTag->getId();
                    $user['authentication_tag']['icon_url'] = $commnueUserAuthTag->getIconUrl();
                    $user['authentication_tag']['name'] = $commnueUserAuthTag->getName();
                } else {
                    $user['authenticated'] = false;
                }

                $user['is_banned'] = $commnueUser->isBanned();

                array_push($response, $user);

                continue;
            }

            $user['authenticated'] = false;
            $user['is_banned'] = false;

            array_push($response, $user);
        }

        return new View([
            'current_page_number' => (int) $pageIndex,
            'num_items_per_page' => (int) $pageLimit,
            'items' => $response,
            'total_count' => $usersCount,
        ]);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param int                   $id
     *
     * @Route("/users/{id}")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getOneUserAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $user = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User')
            ->find($id);

        $userProfile = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\UserProfile')
            ->findOneBy(['userId' => $id]);

        return new View([
            'name' => $userProfile->getName(),
            'phone' => $user->getPhone(),
            'email' => $user->getEmail(),
            'gender' => $userProfile->getGender(),
            'dat_of_birth' => $userProfile->getDateOfBirth(),
        ]);
    }

    /**
     * @param Request $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/users")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postAdminCommnueUsersAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $content = json_decode($request->getContent(), true);

        if (!isset($content['user_id']) || is_null($content['user_id'])
            || !isset($content['is_banned']) || is_null($content['is_banned']))
        {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $commnueUser = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Commnue\CommnueUser')
            ->findOneBy(['userId' => $content['user_id']]);

        $em = $this->getDoctrine()->getManager();

        if (is_null($commnueUser)) {
            $commnueUser = new CommnueUser();
            $commnueUser->setUserId($content['user_id']);

            $em->persist($commnueUser);
        }

        $commnueUser->setIsBanned($content['is_banned']);
        $commnueUser->setAuthTagId($content['auth_tag_id']);

        $em->flush();

        return new View();
    }
}