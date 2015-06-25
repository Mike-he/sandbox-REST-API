<?php

namespace Sandbox\AdminApiBundle\Controller\Admin;

use Sandbox\ApiBundle\Controller\Admin\AdminLoginController;
use Sandbox\ApiBundle\Entity\Admin\Admin;
use Sandbox\ApiBundle\Entity\Admin\AdminClient;
use Sandbox\ApiBundle\Entity\Admin\AdminToken;
use Sandbox\ApiBundle\Form\Admin\AdminClientType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Security\Acl\Exception\Exception;
use JMS\Serializer\SerializationContext;

/**
 * Login controller
 *
 * @category Sandbox
 * @package  Sandbox\ClientApiBundle\Controller
 * @author   Yimo Zhang <yimo.zhang@Sandbox.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 * @link     http://www.Sandbox.cn/
 */
class AdminUserLoginController extends AdminLoginController
{
    /**
     * Login
     *
     * @param Request $request the request object
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *  }
     * )
     *
     * @Route("/login")
     * @Method({"POST"})
     *
     * @return string
     * @throws \Exception
     */
    public function postAdminUserLoginAction(
        Request $request
    ) {
        // get admin
        $admin = $this->getRepo('Admin\Admin')->find(
            $this->getUser()->getId()
        );

        return $this->handleAdminUserLogin($request, $admin);
    }

    /**
     * @param  Request    $request
     * @param  Admin      $admin
     * @return View
     * @throws \Exception
     */
    private function handleAdminUserLogin(
        Request $request,
        $admin
    ) {
        try {
            $em = $this->getDoctrine()->getManager();

            // save or update admin client
            $adminClient = $this->saveAdminClient($request);
            if (is_null($adminClient->getId())) {
                $em->persist($adminClient);
                $em->flush();
            }

            // save or refresh admin token
            $adminToken = $this->saveAdminToken($admin, $adminClient);
            if (is_null($adminToken->getId())) {
                $em->persist($adminToken);
            }
            $em->flush();

            // admin permission
            $permissions = $this->getAdminPermissions($admin);
            $admin->setPermission($permissions);

            // response
            $view = new View();
            $view->setSerializationContext(SerializationContext::create()->setGroups(array('login')));

            return $view->setData(array(
                'admin' => $admin,
                'token' => $adminToken,
                'client' => $adminClient,
            ));
        } catch (Exception $e) {
            throw new \Exception('Something went wrong!');
        }
    }

    /**
     * @param  Request     $request
     * @return AdminClient
     */
    private function saveAdminClient(
        Request $request
    ) {
        $adminClient = new AdminClient();

        // set creation date for new object
        $now = new \DateTime("now");
        $adminClient->setCreationDate($now);

        // get admin client if exist
        $adminClient = $this->getAdminClientIfExist($request, $adminClient);

        // set ip address
        $adminClient->setIpAddress($request->getClientIp());

        // set modification date
        $adminClient->setModificationDate($now);

        return $adminClient;
    }

    /**
     * @param  Request     $request
     * @param  AdminClient $adminClient
     * @return AdminClient
     */
    private function getAdminClientIfExist(
        Request $request,
        $adminClient
    ) {
        $requestContent = $request->getContent();
        if (is_null($requestContent)) {
            return $adminClient;
        }

        // get client data from request payload
        $payload = json_decode($requestContent, true);
        $clientData = $payload['client'];

        if (is_null($clientData)) {
            return $adminClient;
        }

        if (array_key_exists('id', $clientData)) {
            // get existing admin client
            $adminClientExist = $this->getRepo('Admin\AdminClient')->find($clientData['id']);

            // if exist use the existing object
            // else remove id from client data for further form binding
            if (!is_null($adminClientExist)) {
                $adminClient = $adminClientExist;
            } else {
                unset($clientData['id']);
            }
        }

        // bind client data
        $form = $this->createForm(new AdminClientType(), $adminClient);
        $form->submit($clientData, true);

        return $adminClient;
    }

    /**
     * @param  Admin       $admin
     * @param  AdminClient $adminClient
     * @return AdminToken
     */
    private function saveAdminToken(
        $admin,
        $adminClient
    ) {
        $adminToken = $this->getRepo('Admin\AdminToken')->findOneBy(array(
            'adminId' => $admin->getId(),
            'clientId' => $adminClient->getId(),
        ));

        if (is_null($adminToken)) {
            $adminToken = new AdminToken();
            $adminToken->setAdminId($admin->getId());
            $adminToken->setClientId($adminClient->getId());
            $adminToken->setToken($this->generateRandomToken());
        }

        // refresh creation date
        $adminToken->setCreationDate(new \DateTime("now"));

        return $adminToken;
    }

    /**
     * @param  Admin $admin
     * @return array
     */
    private function getAdminPermissions(
        $admin
    ) {
        $adminPermissions = array();

        foreach ($admin->getPermissionIds() as $myPermission) {
            $permission = $myPermission->getPermission();
            if (is_null($permission)) {
                continue;
            }
            array_push($adminPermissions, $permission);
        }

        return $adminPermissions;
    }
}