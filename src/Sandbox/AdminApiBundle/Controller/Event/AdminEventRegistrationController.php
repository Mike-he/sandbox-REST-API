<?php

namespace Sandbox\AdminApiBundle\Controller\Event;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Rs\Json\Patch;
use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionMap;
use Sandbox\ApiBundle\Entity\Admin\AdminType;
use Sandbox\ApiBundle\Entity\Event\Event;
use Sandbox\ApiBundle\Form\Event\EventRegistrationPatchType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Class AdminEventRegistrationController.
 *
 * @category Sandbox
 *
 * @author   Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminEventRegistrationController extends SandboxRestController
{
    const ERROR_ACCOUNT_BANNED_CODE = 401001;
    const ERROR_ACCOUNT_BANNED_MESSAGE = 'Registration user is banned or unauthorized. - 报名用户已经被冻结或未认证.';

    /**
     * Patch events registrations status.
     *
     * @param Request               $request
     * @param ParamFetcherInterface $paramFetcher
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Annotations\QueryParam(
     *    name="id",
     *    array=true,
     *    default="1",
     *    nullable=false,
     *    requirements="\d+",
     *    strict=true,
     *    description="event registration id"
     * )
     *
     * @Route("/events/registrations")
     * @Method({"PATCH"})
     *
     * @return View
     *
     * @throws \Exception
     */
    public function patchEventRegistrationAction(
        Request $request,
        ParamFetcherInterface $paramFetcher
    ) {
        // check user permission
        $this->checkAdminEventRegistrationPermission(AdminPermissionMap::OP_LEVEL_EDIT);

        $ids = $paramFetcher->get('id');
        foreach ($ids as $id) {
            $registration = $this->getRepo('Event\EventRegistration')->find($id);

            // check event registration
            if (is_null($registration) || empty($registration)) {
                continue;
            }

            // check registration user's banned and authorized status
            $user = $this->getRepo('User\User')->find($registration->getUser());
            if ($user->isBanned() || !$user->isAuthorized()) {
                return $this->customErrorView(
                    401,
                    self::ERROR_ACCOUNT_BANNED_CODE,
                    self::ERROR_ACCOUNT_BANNED_MESSAGE
                );
            }

            $registrationJson = $this->container->get('serializer')->serialize($registration, 'json');
            $patch = new Patch($registrationJson, $request->getContent());
            $registrationJson = $patch->apply();

            $form = $this->createForm(new EventRegistrationPatchType(), $registration);
            $form->submit(json_decode($registrationJson, true));

            $em = $this->getDoctrine()->getManager();
            $em->flush();
        }

        return new View();
    }

    /**
     * Check user permission.
     *
     * @param $opLevel
     */
    private function checkAdminEventRegistrationPermission(
        $opLevel
    ) {
        $this->throwAccessDeniedIfAdminNotAllowed(
            $this->getAdminId(),
            AdminType::KEY_PLATFORM,
            AdminPermission::KEY_PLATFORM_EVENT,
            $opLevel
        );
    }
}
