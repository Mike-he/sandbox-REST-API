<?php
/**
 * API for Vcards
 *
 * PHP version 5.3
 *
 * @category Sandbox
 * @package  ApiBundle
 * @author   Josh Yang
 * @license  http://www.Sandbox.cn/ Proprietary
 * @link     http://www.Sandbox.cn/
 *
 */
namespace Sandbox\ClientApiBundle\Controller;

use Sandbox\ApiBundle\Controller\VcardController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Rs\Json\Patch;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Rest controller for VCards
 *
 * @category Sandbox
 * @package  Sandbox\ApiBundle\Controller
 * @author   Josh Yang
 * @license  http://www.Sandbox.cn/ Proprietary
 * @link     http://www.Sandbox.cn/
 */
class ClientVcardController extends VcardController
{
    const NOT_FOUND_MESSAGE = "This resource does not exist";

    const BAD_PARAM_MESSAGE = "Bad parameters";

    /**
     * Get a single VCard.
     *
     * @param Request               $request      the request object
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @ApiDoc(
     *   output = "VCard",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the note is not found"
     *   }
     * )
     * @Annotations\QueryParam(
     *    name="companyid",
     *    default=null,
     *    description="
     *      if precised give the vcard of this user for this company,
     *      if not precised give the user's own vcard
     *    "
     * )
     *
     * @Annotations\QueryParam(
     *    name="userid",
     *    default=null,
     *    description="userid"
     * )
     * @return array
     *
     * @throws NotFoundHttpException when resource does not exist
     */
    public function getVcardsAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $userid = $paramFetcher->get('userid');
        $companyid = $paramFetcher->get('companyid');

        $vcardRepo = $this->getRepo('JtVCard');

        $vcard = null;

        $vcard = $vcardRepo->findOneBy(
            array(
                'userid' => $userid,
                'companyid' => null,
            )
        );

        if (!is_null($userid) &&
            !is_null($companyid)) {
            $vcard = $vcardRepo->getVCardFromCompany($userid, $companyid);
        }

        $this->throwNotFoundIfNull($vcard, self::NOT_FOUND_MESSAGE);

        return new View($vcard);
    }

    /**
     * @param  Request $request
     * @param $id
     * @return View
     */
    public function getVcardAction(
        Request $request,
        $id
    ) {
        $vcardRepo = $this->getRepo('JtVCard');
        $vcard = $vcardRepo->findOneById($id);

        $this->throwNotFoundIfNull($vcard, self::NOT_FOUND_MESSAGE);

        return new View($vcard);
    }

    /**
     * @param  Request                   $request
     * @param $id
     * @return View
     * @throws Patch\FailedTestException
     */
    public function patchVcardAction(
        Request $request,
        $id
    ) {
        $vcard = $this->getRepo('JtVCard')->find($id);
        $this->throwNotFoundIfNull($vcard, 'vcard_patch '.self::NOT_FOUND_MESSAGE);

        $vcardJSON = $this->container->get('serializer')->serialize($vcard, 'json');
        $patch = new Patch($vcardJSON, $request->getContent());
        $vcardAfterPatchedJSON = $patch->apply();

        $form = $this->createForm(new JtVCardType(), $vcard);
        $form->submit(json_decode($vcardAfterPatchedJSON, true));

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $view = new View();
        $view->setData(json_decode($vcardAfterPatchedJSON, true));

        return $view;
    }

    /**
     * @param  Request               $request
     * @param  ParamFetcherInterface $paramFetcher
     * @return View
     */
    public function postVcardAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        $vcard = new JtVCard();
        $form = $this->createForm(new JtVCardType(), $vcard);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            throw new BadRequestHttpException(self::BAD_PARAM_MESSAGE);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($vcard);
        $em->flush();

        $view = $this->routeRedirectView('get_vcard', array('id' => $vcard->getId()));
        $view->setData(array('id' => $vcard->getId()));

        return $view;
    }
}
