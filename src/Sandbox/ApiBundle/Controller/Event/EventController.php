<?php

namespace Sandbox\ApiBundle\Controller\Event;

use Sandbox\ApiBundle\Controller\SandboxRestController;
use Sandbox\ApiBundle\Entity\Event\Event;

/**
 * Event Controller.
 *
 * @category Sandbox
 *
 * @author   Mike He <mike.he@easylinks.com.cn>
 * @license  http://www.Sandbox.cn/ Proprietary
 *
 * @link     http://www.Sandbox.cn/
 */
class EventController extends SandboxRestController
{
    /**
     * Check if is over limit number.
     *
     * @param Event $event
     *
     * @return bool
     */
    protected function checkIfOverLimitNumber(
        $event
    ) {
        $limitNumber = $event->getLimitNumber();
        if ($limitNumber > 0) {
            $registrationCounts = $this->getRepo('Event\EventRegistration')
                ->getRegistrationCounts($event->getId());
            $registrationCounts = (int) $registrationCounts;
            if ($registrationCounts >= $limitNumber) {
                return true;
            }
        }

        return false;
    }
}
