<?php

namespace Sandbox\ApiBundle\Traits;

use JMS\Serializer\SerializationContext;
use Sandbox\ApiBundle\Controller\SandboxRestController;

trait AdminTrait
{
    /**
     * @param $admin
     *
     * @return string
     */
    public function getHashResult(
        $admin
    ) {
        $json = $this->get('serializer')->serialize(
            $admin,
            'json',
            SerializationContext::create()->setGroups(['admin_basic'])
        );

        return hash(SandboxRestController::HASH_ALGO_SHA256, $json);
    }
}
