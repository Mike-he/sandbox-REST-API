<?php

namespace Sandbox\ApiBundle\Traits;

use Sandbox\ApiBundle\Entity\Log\Log;

/**
 * Log Trait.
 *
 * @category Sandbox
 *
 * @author   Mike He <mike.he@sandbox3.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
trait HasAccessToEntityRepositoryTrait
{
    private function getUserRepo()
    {
        return $this->getDoctrine()
            ->getRepository('SandboxApiBundle:User\User');
    }

    private function getProductRepo()
    {
        return $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\Product');
    }

    private function getLeaseRentTypesRepo()
    {
        return $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseRentTypes');
    }

    private function getLeaseRepo()
    {
        return $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\Lease');
    }

    private function getProductAppointmentRepo()
    {
        return $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Product\ProductAppointment');
    }

    private function getLeaseBillRepo()
    {
        return $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Lease\LeaseBill');
    }

    private function getParameterRepo()
    {
        return $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Parameter\Parameter');
    }

    private function getSalesCompanyRepo()
    {
        return $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany');
    }

    private function getSalesCompanyServiceInfosRepo()
    {
        return $this->getDoctrine()
            ->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyServiceInfos');
    }

    private function getLogsRepo()
    {
        return $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Log\Log');
    }
}
