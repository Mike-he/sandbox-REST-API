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
     * @param $price
     * @param $tradeId
     * @param $source
     * @param string $type
     * @param null   $addAmount
     *
     * @return bool|string
     */
    public function postBeanChange(
        $userId,
        $price,
        $tradeId,
        $source,
        $type = UserBeanFlow::TYPE_ADD,
        $addAmount = null
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

        $parameter = $this->doctrine
            ->getRepository('SandboxApiBundle:Parameter\Parameter')
            ->findOneBy(array('key' => $source));
        $value = $parameter->getValue();

        $operator = substr($value, 0, 1);
        $number = substr($value, 1);

        switch ($operator) {
            case '+':
                $amount = $number;
                break;
            case '*':
                $amount = $price * $number;
                break;

            default:
                return;
        }

        $oldBean = $user->getBean();

        $newBean = $oldBean + $amount;

        $lastBeanFlow = $em->getRepository('SandboxApiBundle:User\UserBeanFlow')
            ->findOneBy(array(), array('id' => 'DESC'));

        $totalBean = $lastBeanFlow ? $lastBeanFlow->getTotal() : 0;

        $beanFlow = new UserBeanFlow();
        $beanFlow->setUserId($userId);
        $beanFlow->setType($type);
        $beanFlow->setChangeAmount('+'.$amount);
        $beanFlow->setBalance($newBean);
        $beanFlow->setSource($source);
        $beanFlow->setTradeId($tradeId);
        $beanFlow->setCreationDate($now);
        $beanFlow->setTotal($totalBean + $amount + $addAmount);
        $em->persist($beanFlow);

        $user->setBean($newBean);

        return $amount;
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
        $tradeId = null
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
                        $tradeId,
                        $startDate,
                        $endDate
                    );
                $result = $exits ? true : false;

                break;
            case Parameter::KEY_BEAN_USER_SHARE:
                $exits = $this->doctrine
                    ->getRepository('SandboxApiBundle:User\UserBeanFlow')
                    ->checkExits(
                        $userId,
                        $source,
                        $tradeId,
                        $startDate,
                        $endDate
                    );
                $result = $exits ? true : false;
                break;
            case Parameter::KEY_BEAN_SUCCESS_INVITATION:
                $result = false;
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
