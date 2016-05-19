<?php

namespace Sandbox\ApiBundle\Repository\User;

use Doctrine\ORM\EntityRepository;
use Sandbox\ApiBundle\Controller\User\UserPhoneCodeController;

class UserPhoneCodeRepository extends EntityRepository
{
    /**
     * @param $language
     * 
     * @return array
     */
    public function getPhoneCodeByLanguage(
        $language
    ) {
        switch ($language) {
            case UserPhoneCodeController::LANGUAGE_ZH:
                $query = $this->createQueryBuilder('pc')
                    ->select('
                        pc.cnName as name,
                        pc.code
                    ');
                break;
            case UserPhoneCodeController::LANGUAGE_EN:
                $query = $this->createQueryBuilder('pc')
                    ->select('
                        pc.enName as name,
                        pc.code
                    ');
                break;
            default:
                return array();
        }

        return $query->getQuery()->getResult();
    }
}
