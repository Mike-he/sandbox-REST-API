<?php

namespace Sandbox\CommnueAdminApiBundle\Controller\User;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Knp\Component\Pager\Paginator;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Commnue\CommnueUserAuthenticationTags;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AdminUserAuthenticationTagsController extends SandboxRestController
{
    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
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
     * @Route("/users_authentication_tags")
     * @Method({"GET"})
     *
     * @return View
     */
    public function getAdminUserAuthTagsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $pageLimit = $paramFetcher->get('pageLimit');
        $pageIndex = $paramFetcher->get('pageIndex');

        $tags = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Commnue\CommnueUserAuthenticationTags')
            ->findBy(
                [],
                ['creationDate' => 'DESC']
            );

        $paginator = new Paginator();
        $pagination = $paginator->paginate(
            $tags,
            $pageIndex,
            $pageLimit
        );

        return new View($pagination);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @Route("/users_authentication_tags")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postAdminUserAuthTagsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $content = json_decode($request->getContent(), true);

        if (!isset($content['name']) || is_null($content['name'])
            || !isset($content['icon_url']) || is_null($content['icon_url'])
            || !isset($content['description']) || is_null($content['description'])) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $tag = new CommnueUserAuthenticationTags();
        $tag->setIconUrl($content['icon_url']);
        $tag->setName($content['name']);
        $tag->setDescription($content['description']);

        $em = $this->getDoctrine()->getManager();
        $em->persist($tag);
        $em->flush();

        return new View(['id' => $tag->getId()], '201');
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     * @param $id
     *
     * @Route("/users_authentication_tags/{id}")
     * @Method({"PUT"})
     *
     * @return View
     */
    public function putAdminUserAuthTagsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher,
        $id
    ) {
        $tag = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Commnue\CommnueUserAuthenticationTags')
            ->find($id);
        $this->throwNotFoundIfNull($tag, self::NOT_FOUND_MESSAGE);

        $content = json_decode($request->getContent(), true);

        if (!isset($content['name']) || is_null($content['name'])
            || !isset($content['icon_url']) || is_null($content['icon_url'])
            || !isset($content['description']) || is_null($content['description'])) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $em = $this->getDoctrine()->getManager();
        $tag->setIconUrl($content['icon_url']);
        $tag->setName($content['name']);
        $tag->setDescription($content['description']);

        $em->flush();

        return new View();
    }
}
