<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\User\UserProfileController;
use Sandbox\ApiBundle\Entity\User\UserPortfolio;
use Sandbox\ApiBundle\Form\User\UserPortfolioType;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Rest controller for UserPortfolio
 *
 * @category Sandbox
 * @package  Sandbox\ApiBundle\Controller
 * @author   Josh Yang
 * @license  http://www.Sandbox.cn/ Proprietary
 * @link     http://www.Sandbox.cn/
 * @Route("/profile")
 */
class ClientUserPortfolioController extends UserProfileController
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
     * @Route("/portfolio")
     * @Method({"GET"})
     *
     * @return array
     */
    public function getUserPortfolioAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = (int) $paramFetcher->get('user_id');
        if (is_null($userId)) {
            $userId = $this->getUserid();
        }

        $userPortfolios = $this->getRepo('User\UserPortfolio')->findByUserId($userId);

        return new View($userPortfolios);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     *
     * @Route("/portfolio")
     * @Method({"POST"})
     * @return View
     */
    public function postUserPortfolioAction(
        Request $request,
        ParamFetcherInterface $paramFetcher

    ) {
        $userId = $this->getUserid();
        $userPortfolio = new UserPortfolio();

        $form = $this->createForm(new UserPortfolioType(), $userPortfolio);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $userPortfolio->setUserId($userId);
            $em = $this->getDoctrine()->getManager();
            $em->persist($userPortfolio);
            $em->flush();

            return new View($userPortfolio->getId());
        }

        throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
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
     * @Route("/portfolio")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function deleteUserPortfolioAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userPortfolioIds = $paramFetcher->get('id');
        $this->getRepo('User\UserPortfolio')->deleteUserPortfoliosByIds($userPortfolioIds);

        return new View();
    }
}
