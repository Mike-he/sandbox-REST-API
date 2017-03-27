<?php

namespace Sandbox\ApiBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Class AdminPlatformService.
 */
class AdminPlatformService
{
    const PRECONDITION_NOT_SET = 'The precondition not set';

    private $container;
    private $user;

    public function __construct(
        ContainerInterface $container,
        TokenStorage $tokenStorage
    ) {
        $this->container = $container;
        $this->user = $tokenStorage->getToken()->getUser();
    }

    /**
     * @return array
     */
    public function getAdminPlatform()
    {
        $userId = $this->user->getUserId();
        $clientId = $this->user->getClientId();

        $adminPlatform = $this->container->get('doctrine')
            ->getRepository('SandboxApiBundle:Admin\AdminPlatform')
            ->findOneBy(array(
                'userId' => $userId,
                'clientId' => $clientId,
            ));

        if (is_null($adminPlatform)) {
            throw new PreconditionFailedHttpException(self::PRECONDITION_NOT_SET);
        }

        return array(
            'platform' => $adminPlatform->getPlatform(),
            'sales_company_id' => $adminPlatform->getSalesCompanyId(),
        );
    }
}
