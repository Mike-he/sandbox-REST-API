<?php

namespace Sandbox\ApiBundle\Service;

use Sandbox\ApiBundle\Entity\Parameter\Parameter;
use Sandbox\ApiBundle\Entity\User\UserBeanFlow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class BeanService.
 */
class BeanService
{
    private $container;
    private $doctrine;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->doctrine = $this->container->get('doctrine');
    }

    /**
     * @param $userId
     * @param $amount
     * @param $tradeId
     * @param $type
     * @param $source
     */
    public function postBeanChange(
        $userId,
        $amount,
        $tradeId,
        $source,
        $type = UserBeanFlow::TYPE_ADD
    ) {
        $em = $this->doctrine->getManager();
        $now = new \DateTime('now');

        $user = $this->doctrine
            ->getRepository('SandboxApiBundle:User\User')
            ->find($userId);

        $exits = $this->checkExits(
            $userId,
            $source,
            $tradeId
        );

        if ($exits) {
            return;
        }

        $parameter = $this->getDoctrine()
            ->getRepository('SandboxApiBundle:Parameter\Parameter')
            ->findOneBy(array('key' => $source));
        $amount = $amount.$parameter->getValue();

        $oldBean = $user->getBean();

        $newBean = $oldBean + $amount;

        $beanFlow = new UserBeanFlow();
        $beanFlow->setUserId($userId);
        $beanFlow->setType($type);
        $beanFlow->setChangeAmount($amount);
        $beanFlow->setBalance($newBean);
        $beanFlow->setSource($source);
        $beanFlow->setTradeId($tradeId);
        $beanFlow->setCreationDate($now);
        $em->persist($beanFlow);

        $user->setBean($newBean);
    }

    /**
     * @param $userId
     * @param $source
     * @param $tradeId
     *
     * @return bool
     */
    public function checkExits(
        $userId,
        $source,
        $tradeId
    ) {
        $now = new \DateTime('now');
        $startDate = $now->setTime(0, 0, 0);

        $today = new \DateTime('now');
        $endDate = $today->setTime(23, 59, 59);

        switch ($source) {
            case Parameter::KEY_BEAN_USER_LOGIN:
                $exits = $this->doctrine
                    ->getRepository('SandboxApiBundle:User\UserBeanFlow')
                    ->checkExits(
                        $userId,
                        $source,
                        $startDate,
                        $endDate,
                        $tradeId
                    );
                $result = $exits ? true : false;

                break;
            case Parameter::KEY_BEAN_USER_SHARE:
                $exits = $this->doctrine
                    ->getRepository('SandboxApiBundle:User\UserBeanFlow')
                    ->checkExits(
                        $userId,
                        $source,
                        $startDate,
                        $endDate,
                        $tradeId
                    );
                $result = $exits ? true : false;
                break;
            default:
                $exits = $this->doctrine
                    ->getRepository('SandboxApiBundle:User\UserBeanFlow')
                    ->checkExits(
                        $userId,
                        $source,
                        $tradeId
                    );
                $result = $exits ? true : false;
        }

        return $result;
    }
}
