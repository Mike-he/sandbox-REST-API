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

    /**
     * @param $adminId
     * @param $status
     * @param $message
     * @param $object
     * @param $objectId
     * @param string $type
     * @param null   $companyId
     */
    public function autoLog(
        $adminId,
        $status,
        $message,
        $object,
        $objectId,
        $type = AdminStatusLog::TYPE_CLIENT,
        $companyId = null
    ) {
        $em = $this->doctrine->getManager();

        switch ($type) {
            case AdminStatusLog::TYPE_CLIENT:
                $profile = $this->doctrine
                    ->getRepository('SandboxApiBundle:User\UserProfile')
                    ->findOneBy(['userId' => $adminId]);
                $username = $profile ? $profile->getName() : '';
                break;
            case AdminStatusLog::TYPE_OFFICIAL_ADMIN:
                $adminProfile = $this->doctrine
                    ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdminProfiles')
                    ->findOneBy(array(
                        'userId' => $adminId,
                        'salesCompanyId' => null,
                    ));

                $username = $adminProfile ? $adminProfile->getNickname() : '';
                break;
            case AdminStatusLog::TYPE_SALES_ADMIN:
                $adminProfile = $this->doctrine
                    ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdminProfiles')
                    ->findOneBy(array(
                        'userId' => $adminId,
                        'salesCompanyId' => $companyId,
                    ));

                if (!$adminProfile) {
                    $adminProfile = $this->doctrine
                        ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdminProfiles')
                        ->findOneBy(array(
                            'userId' => $adminId,
                            'salesCompanyId' => null,
                        ));
                }

                $username = $adminProfile ? $adminProfile->getNickname() : '';

                break;
            default:
                $username = '';
        }

        $adminStatusLog = new AdminStatusLog();
        $adminStatusLog->setUserId($adminId);
        $adminStatusLog->setUsername($username);
        $adminStatusLog->setObject($object);
        $adminStatusLog->setObjectId($objectId);
        $adminStatusLog->setStatus($status);
        $adminStatusLog->setRemarks($message);

        $em->persist($adminStatusLog);
        $em->flush();
    }

    /**
     * @param $adminId
     * @param $status
     * @param $message
     * @param $object
     * @param $objectId
     * @param $type
     * @param $companyId
     */
    public function addLog(
        $adminId,
        $status,
        $message,
        $object,
        $objectId,
        $type = AdminStatusLog::TYPE_CLIENT,
        $companyId = null
    ) {
        $em = $this->doctrine->getManager();

        switch ($type) {
            case AdminStatusLog::TYPE_CLIENT:
                $profile = $this->doctrine
                    ->getRepository('SandboxApiBundle:User\UserProfile')
                    ->findOneBy(['userId' => $adminId]);
                $username = $profile ? $profile->getName() : '';
                break;
            case AdminStatusLog::TYPE_OFFICIAL_ADMIN:
                $adminProfile = $this->doctrine
                    ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdminProfiles')
                    ->findOneBy(array(
                        'userId' => $adminId,
                        'salesCompanyId' => null,
                    ));

                $username = $adminProfile ? $adminProfile->getNickname() : '';
                break;
            case AdminStatusLog::TYPE_SALES_ADMIN:
                $adminProfile = $this->doctrine
                    ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdminProfiles')
                    ->findOneBy(array(
                        'userId' => $adminId,
                        'salesCompanyId' => $companyId,
                    ));

                if (!$adminProfile) {
                    $adminProfile = $this->doctrine
                        ->getRepository('SandboxApiBundle:SalesAdmin\SalesAdminProfiles')
                        ->findOneBy(array(
                            'userId' => $adminId,
                            'salesCompanyId' => null,
                        ));
                }

                $username = $adminProfile ? $adminProfile->getNickname() : '';

                break;
            default:
                $username = '';
        }

        $adminStatusLog = new AdminStatusLog();
        $adminStatusLog->setUserId($adminId);
        $adminStatusLog->setUsername($username);
        $adminStatusLog->setObject($object);
        $adminStatusLog->setObjectId($objectId);
        $adminStatusLog->setStatus($status);
        $adminStatusLog->setRemarks($message);

        $em->persist($adminStatusLog);
    }
}
