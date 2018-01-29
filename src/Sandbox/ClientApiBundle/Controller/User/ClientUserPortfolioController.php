<?php

namespace Sandbox\ClientApiBundle\Controller\User;

use Sandbox\ApiBundle\Controller\User\UserProfileController;
use Sandbox\ApiBundle\Entity\User\UserPortfolio;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\Serializer\SerializationContext;

/**
 * Rest controller for UserPortfolio.
 *
 * @category Sandbox
 *
 * @author   Josh Yang
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @see     http://www.Sandbox.cn/
 * @Route("/profile")
 */
class ClientUserPortfolioController extends UserProfileController
{
    /**
     * Get user's portfolio.
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
     * @Route("/portfolios")
     * @Method({"GET"})
     *
     * @return array
     */
    public function getUserPortfolioAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userId = $paramFetcher->get('user_id');
        if (is_null($userId)) {
            $userId = $this->getUserId();
        }

        $portfolios = $this->getRepo('User\UserPortfolio')->findByUserId($userId);

        $view = new View($portfolios);
        $view->setSerializationContext(SerializationContext::create()->setGroups(array('profile')));

        return $view;
    }

    /**
     * @param Request $request
     *
     * @Route("/portfolios")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postUserPortfolioAction(
        Request $request
    ) {
        $userId = $this->getUserId();
        $user = $this->getRepo('User\User')->find($userId);

        $em = $this->getDoctrine()->getManager();

        $portfolios = json_decode($request->getContent(), true);
        foreach ($portfolios as $portfolio) {
            $userPortfolio = $this->generateUserPortfolio($user, $portfolio);
            $em->persist($userPortfolio);
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
     * @Route("/portfolios")
     * @Method({"DELETE"})
     *
     * @return View
     */
    public function deleteUserPortfolioAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $this->getRepo('User\UserPortfolio')->deleteUserPortfolios(
            $paramFetcher->get('id'),
            $this->getUserId()
        );

        return new View();
    }
}
