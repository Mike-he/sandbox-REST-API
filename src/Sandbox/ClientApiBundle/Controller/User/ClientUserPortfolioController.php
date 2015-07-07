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
 * Rest controller for UserPortfolio.
 *
 * @category Sandbox
 *
 * @author   Josh Yang
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
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
        $userId = (int) $paramFetcher->get('user_id');
        if ($userId === 0) {
            $userId = $this->getUserId();
        }

        $userPortfolios = $this->getRepo('User\UserPortfolio')->findByUserId($userId);

        return new View($userPortfolios);
    }

    /**
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     *
     * @Route("/portfolios")
     * @Method({"POST"})
     *
     * @return View
     */
    public function postUserPortfolioAction(
        Request $request,
        ParamFetcherInterface $paramFetcher

    ) {
        $userId = $this->getUserId();

        $portfolioResponseArray = array();

        $em = $this->getDoctrine()->getManager();

        $portfoliosArray = json_decode($request->getContent(), true);
        foreach ($portfoliosArray as $portfolio) {
            $userPortfolio = new UserPortfolio();
            $form = $this->createForm(new UserPortfolioType(), $userPortfolio);
            $form->submit($portfolio);
            if (!$form->isValid()) {
                throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
            }
            $userPortfolio->setUserId($userId);
            $em->persist($userPortfolio);
            $em->flush();

            $insidePortfolioArray = array(
                'id' => $userPortfolio->getId(),
                'content' => $userPortfolio->getContent(),
                'attachment_type' => $userPortfolio->getAttachmentType(),
                'file_name' => $userPortfolio->getFileName(),
                'preview' => $userPortfolio->getPreview(),
                'size' => $userPortfolio->getSize(),
            );
            array_push($portfolioResponseArray, $insidePortfolioArray);
        }

        return new View($portfolioResponseArray);
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
        $userPortfolioIds = $paramFetcher->get('id');
        $this->getRepo('User\UserPortfolio')->deleteUserPortfoliosByIds($userPortfolioIds);

        return new View();
    }
}
