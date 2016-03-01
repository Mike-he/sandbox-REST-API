<?php

namespace Sandbox\AdminShopApiBundle\Controller;

use Sandbox\ApiBundle\Controller\Payment\PaymentController;

class ShopRestController extends PaymentController
{
    //-------------------- Repo --------------------//

    /**
     * @param $id
     * @param $path
     *
     * @return object $entity
     */
    public function findEntityById(
        $id,
        $path
    ) {
        $entity = $this->getRepo($path)->find($id);
        $this->throwNotFoundIfNull($entity, self::NOT_FOUND_MESSAGE);

        return $entity;
    }
}
