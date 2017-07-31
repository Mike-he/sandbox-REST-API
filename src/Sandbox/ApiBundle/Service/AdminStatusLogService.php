<?php

namespace Sandbox\ApiBundle\Service;

use Sandbox\ApiBundle\Entity\Admin\AdminStatusLog;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AdminStatusLogService.
 */
class AdminStatusLogService
{
    private $container;
    private $doctrine;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->doctrine = $this->container->get('doctrine');
    }

    public function autoLog(
        $adminId,
        $status,
        $message,
        $object,
        $objectId
    ) {
        $em = $this->doctrine->getManager();

        $profile = $this->doctrine
            ->getRepository('SandboxApiBundle:User\UserProfile')
            ->findOneBy(['userId' => $adminId]);

        $adminStatusLog = new AdminStatusLog();
        $adminStatusLog->setUserId($adminId);
        $adminStatusLog->setUsername($profile ? $profile->getName() : '');
        $adminStatusLog->setObject($object);
        $adminStatusLog->setObjectId($objectId);
        $adminStatusLog->setStatus($status);
        $adminStatusLog->setRemarks($message);

        $em->persist($adminStatusLog);
        $em->flush();
    }
}
