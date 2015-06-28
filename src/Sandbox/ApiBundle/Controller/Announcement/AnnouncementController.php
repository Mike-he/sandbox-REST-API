<?php

namespace Sandbox\ApiBundle\Controller\Announcement;

use Sandbox\ApiBundle\Controller\SandboxRestController;

/**
 * Announcement Controller
 *
 * @category Sandbox
 * @package  Sandbox\ApiBundle\Controller
 * @author   Sergi Uceda <sergiu@gobeta.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 * @link     http://www.Sandbox.cn/
 */
class AnnouncementController extends SandboxRestController
{
    /**
     * Get order by array
     *
     * @param $paramFetcher
     * @return null|array
     */
    protected function getSortBy(
        $sortBy
    ) {
        switch ($sortBy) {
            case 'creation_date':
                return ['creationDate' => 'ASC'];
                break;
            case '-creation_date':
                return ['creationDate' => 'DESC'];
                break;
            case 'modification_date':
                return ['modificationDate' => 'ASC'];
                break;
            case '-modification_date':
                return ['modificationDate' => 'DESC'];
                break;
            default:
                return;
                break;
        }
    }
}
