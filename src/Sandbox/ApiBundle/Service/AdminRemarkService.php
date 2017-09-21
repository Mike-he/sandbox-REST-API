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
        $remark->setIsAuto(true);

        $em->persist($remark);
        $em->flush();
    }

    /**
     * @param $inheritObject
     * @param $inheritObjectId
     * @param $object
     * @param $objectId
     */
    public function inheritRemark(
        $inheritObject,
        $inheritObjectId,
        $object,
        $objectId
    ) {
        $em = $this->doctrine->getManager();

        $remarks = $this->doctrine
            ->getRepository('SandboxApiBundle:Admin\AdminRemark')
            ->findBy(array(
                'object' => $inheritObject,
                'objectId' => $inheritObjectId,
            ));

        foreach ($remarks as $remark) {
            $newRemark = new AdminRemark();
            $newRemark->setUserId($remark->getUserId());
            $newRemark->setUsername($remark->getUsername());
            $newRemark->setPlatform($remark->getPlatform());
            $newRemark->setCompanyId($remark->getCompanyId());
            $newRemark->setRemarks($remark->getRemarks());
            $newRemark->setCreationDate($remark->getCreationDate());
            $newRemark->setObject($object);
            $newRemark->setObjectId($objectId);
            $remark->setIsAuto(true);

            $em->persist($newRemark);
        }

        $em->flush();
    }
}
