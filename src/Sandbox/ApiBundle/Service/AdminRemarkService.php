<?php

namespace Sandbox\ApiBundle\Service;

use Sandbox\ApiBundle\Entity\Admin\AdminRemark;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AdminRemarkService.
 */
class AdminRemarkService
{
    private $container;
    private $doctrine;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->doctrine = $this->container->get('doctrine');
    }

    public function autoRemark(
        $adminId,
        $platform,
        $companyId,
        $message,
        $object,
        $objectId
    ) {
        $em = $this->doctrine->getManager();

        $profile = $this->doctrine
            ->getRepository('SandboxApiBundle:User\UserProfile')
            ->findOneBy(['userId' => $adminId]);

        $remark = new AdminRemark();
        $remark->setUserId($adminId);
        $remark->setUsername($profile->getName());
        $remark->setPlatform($platform);
        $remark->setCompanyId($companyId);
        $remark->setRemarks($message);
        $remark->setObject($object);
        $remark->setObjectId($objectId);

        $em->persist($remark);
        $em->flush();
    }
}
