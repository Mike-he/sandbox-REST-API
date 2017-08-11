<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Sandbox\ApiBundle\Entity\Parameter\Parameter;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170810172906 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
    }

    public function postUp(Schema $schema)
    {
        parent::postUp($schema);

        $em = $this->container->get('doctrine.orm.entity_manager');

        $data = array(
            Parameter::KEY_BEAN_PRODUCT_ORDER_PREORDER => "*0.2",
            Parameter::KEY_POUNDAGE_ACCOUNT => 1,
            Parameter::KEY_POUNDAGE_WX => 1,
            Parameter::KEY_POUNDAGE_ALIPAY => 1,
            Parameter::KEY_POUNDAGE_UPACP => 1,
            Parameter::KEY_POUNDAGE_WX_PUB => 1,
            Parameter::KEY_POUNDAGE_OFFLINE => 1,
        );

        foreach ($data as $key => $value) {
            $parameter = new Parameter();
            $parameter->setKey($key);
            $parameter->setValue($value);

            $em->persist($parameter);
        }

        $orderEvaluation = $em->getRepository('SandboxApiBundle:Parameter\Parameter')
            ->findOneBy(array(
                'key' => Parameter::KEY_BEAN_ORDER_EVALUATION,
            ));

        $orderEvaluation->setValue('*0.5');

        $membership = $em->getRepository('SandboxApiBundle:Parameter\Parameter')
            ->findOneBy(array(
                'key' => Parameter::KEY_BEAN_MEMBERSHIP_ORDER,
            ));

        $membership->setValue('*2');

        $order = $em->getRepository('SandboxApiBundle:Parameter\Parameter')
            ->findOneBy(array(
                'key' => Parameter::KEY_BEAN_PRODUCT_ORDER,
            ));

        $order->setValue('*2');

        $bill = $em->getRepository('SandboxApiBundle:Parameter\Parameter')
            ->findOneBy(array(
                'key' => Parameter::KEY_BEAN_PAY_BILL,
            ));

        $bill->setValue('*0.2');

        $em->flush();
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
