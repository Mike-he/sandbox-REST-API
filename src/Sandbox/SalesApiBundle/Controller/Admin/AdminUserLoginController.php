<?php

namespace Sandbox\SalesApiBundle\Controller\Admin;

use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminClient;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdminToken;
use Sandbox\ApiBundle\Form\SalesAdmin\SalesAdminClientType;
use Sandbox\SalesApiBundle\Controller\SalesRestController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesAdmin;
use FOS\RestBundle\View\View;
use Symfony\Component\Security\Acl\Exception\Exception;
use JMS\Serializer\SerializationContext;

/**
 * Login controller.
 *
 * @category Sandbox
 *
 * @author   Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class AdminUserLoginController extends SalesRestController
{
    const ERROR_ACCOUNT_BANNED_CODE = 401001;
    const ERROR_ACCOUNT_BANNED_MESSAGE = '您的账户已经被冻结，如有疑问请联系客服：';

    /**
     * Login.
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
     *
     * @throws \Exception
     */
    public function postAdminUserLoginAction(
        Request $request
    ) {
        // check security & get admin
        $admin = $this->checkSalesAdminLoginSecurity();

        // get globals
        $globals = $this->getGlobals();

        $customerPhone = $globals['customer_service_phone'];

        if ($admin->isBanned()) {
            // user is banned
            return $this->customErrorView(
                401,
                self::ERROR_ACCOUNT_BANNED_CODE,
                self::ERROR_ACCOUNT_BANNED_MESSAGE.$customerPhone);
        }

        return $this->handleAdminUserLogin($request, $admin);
    }

    /**
     * @param Request    $request
     * @param SalesAdmin $admin
     *
     * @return View
     *
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

            // response
            $view = new View();
            $view->setSerializationContext(
                SerializationContext::create()->setGroups(array('login'))
            );

            // set admin cookie
            setrawcookie('sandbox_admin_token', $adminToken->getToken(), null, '/', $request->getHost());

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
     * @param Request $request
     *
     * @return SalesAdminClient
     */
    private function saveAdminClient(
        Request $request
    ) {
        $adminClient = new SalesAdminClient();

        // set creation date for new object
        $now = new \DateTime('now');
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
     * @param Request          $request
     * @param SalesAdminClient $adminClient
     *
     * @return SalesAdminClient
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
            $adminClientExist = $this->getRepo('SalesAdmin\SalesAdminClient')->find($clientData['id']);

            // if exist use the existing object
            // else remove id from client data for further form binding
            if (!is_null($adminClientExist)) {
                $adminClient = $adminClientExist;
            } else {
                unset($clientData['id']);
            }
        }

        // bind client data
        $form = $this->createForm(new SalesAdminClientType(), $adminClient);
        $form->submit($clientData, true);

        return $adminClient;
    }

    /**
     * @param SalesAdmin       $admin
     * @param SalesAdminClient $adminClient
     *
     * @return SalesAdminToken
     */
    private function saveAdminToken(
        $admin,
        $adminClient
    ) {
        $adminToken = $this->getRepo('SalesAdmin\SalesAdminToken')->findOneBy(array(
            'admin' => $admin,
            'client' => $adminClient,
        ));

        if (is_null($adminToken)) {
            $adminToken = new SalesAdminToken();
            $adminToken->setAdmin($admin);
            $adminToken->setAdminId($admin->getId());
            $adminToken->setClient($adminClient);
            $adminToken->setClientId($adminClient->getId());
            $adminToken->setToken($this->generateRandomToken());
        }

        // refresh creation date
        $adminToken->setCreationDate(new \DateTime('now'));

        return $adminToken;
    }
}
