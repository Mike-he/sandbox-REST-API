<?php

namespace Sandbox\AdminApiBundle\Controller\Traits;

/**
 * Announcement Notification Trait.
 *
 * @category Sandbox
 *
 * @author   Albert Feng <albert.f@sandbox3.cn>
 * @license  http://www.Sandbox3.cn/ Proprietary
 *
 * @link     http://www.Sandbox3.cn/
 */
trait HandleAdminLoginDataTrait
{
    use HandleArrayTrait;

    private function handlePositionData($positions)
    {
        $platform = array();
        foreach ($positions as $position) {
            switch ($position['platform']) {
                case 'shop':
                    $platform['shop'][] = $position;
                    break;
                case 'sales':
                    $platform['sales'][] = $position;
                    break;
                case 'commnue' :
                    $position['commnue_name'] = '合创社管理平台';
                    $platform['commnue'][] = $position;
                    break;
                default:
                    $position['office_name'] = '官方管理平台';
                    $platform['official'][] = $position;
            }
        }

        return $platform;
    }

    /**
     * @param $positions
     *
     * @return array
     */
    private function handleCompanyData(
        $positions
    ) {
        $shopCompanies = array();
        $salesCompanies = array();
        foreach ($positions as $position) {
            switch ($position['platform']) {
                case 'shop':
                    $shopCompanies[] = $position['sales_company_id'];
                    break;
                case 'sales':
                    $salesCompanies[] = $position['sales_company_id'];
                    break;
            }
        }

        $salesInfo = array();
        $salesCompanies = array_unique($salesCompanies);
        foreach ($salesCompanies as $salesCompany) {
            $attachment = $this->getContainer()->get('doctrine')
                ->getRepository('SandboxApiBundle:Room\RoomBuildingAttachment')
                ->findAttachmentByCompany($salesCompany);

            $salesInfo[] = array(
                'sales_company_id' => $salesCompany,
                'content' => $attachment ? $attachment[0]['content'] : '',
            );
        }

        $shopInfo = array();
        $shopCompanies = array_unique($shopCompanies);
        foreach ($shopCompanies as $shopCompany) {
            $attachment = $this->getContainer()->get('doctrine')
                ->getRepository('SandboxApiBundle:Shop\ShopAttachment')
                ->findAttachmentByCompany($shopCompany);

            $shopInfo[] = array(
                'sales_company_id' => $shopCompany,
                'content' => $attachment ? $attachment[0]['content'] : '',
            );
        }

        $company = array(
            'sales' => $salesInfo,
            'shop' => $shopInfo,
        );

        return $company;
    }

    private function handlePermissionData($permissions)
    {
        $data = array();
        foreach ($permissions as $permission) {
            $data[$permission['id']][] = $permission;
        }

        $newPermissions = array();
        foreach ($data as $item) {
            if (count($item) > 1) {
                $item = $this->array_sort($item, 'op_level', 'desc');
                $newPermissions[] = $item[0];
            } else {
                $newPermissions[] = $item[0];
            }
        }

        return $newPermissions;
    }
}
