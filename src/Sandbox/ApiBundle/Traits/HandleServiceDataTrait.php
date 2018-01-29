<?php

namespace Sandbox\ApiBundle\Traits;

use Doctrine\ORM\EntityManager;
use Sandbox\ApiBundle\Entity\Service\Service;

trait HandleServiceDataTrait
{
    /**
     * @param Service $service
     *
     * @return mixed
     */
    private function handleServicesData(
        $service
    ) {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();

        if ($service->getCountryId()) {
            $country = $em->getRepository('SandboxApiBundle:Room\RoomCity')
                ->find($service->getCountryId());
            if ($country) {
                $countryInfo = [
                    'id' => $country->getId(),
                    'name' => $country->getName(),
                ];
                $service->setCountry($countryInfo);
            }
        }

        if ($service->getProvinceId()) {
            $province = $em->getRepository('SandboxApiBundle:Room\RoomCity')
                ->find($service->getProvinceId());

            if ($province) {
                $provinceInfo = [
                    'id' => $province->getId(),
                    'name' => $province->getName(),
                ];
                $service->setProvince($provinceInfo);
            }
        }

        if ($service->getCityId()) {
            $city = $em->getRepository('SandboxApiBundle:Room\RoomCity')
                ->find($service->getCityId());
            if ($city) {
                $cityInfo = [
                    'id' => $city->getId(),
                    'name' => $city->getName(),
                ];
                $service->setCity($cityInfo);
            }
        }

        if ($service->getDistrictId()) {
            $district = $em->getRepository('SandboxApiBundle:Room\RoomCity')
                ->find($service->getDistrictId());
            if ($district) {
                $districtInfo = [
                    'id' => $district->getId(),
                    'name' => $district->getName(),
                ];
                $service->setDistrict($districtInfo);
            }
        }

        $forms =  $em->getRepository('SandboxApiBundle:Service\ServiceAttachment')
            ->findBy(['service' => $service]);
        $service->setAttachments($forms);

        return $service;
    }
}
