<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Sandbox\ApiBundle\Entity\Order\ProductOrder;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170810172905 extends AbstractMigration implements ContainerAwareInterface
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

        $orders = $em->getRepository('SandboxApiBundle:Order\ProductOrder')
            ->findAll();

        foreach ($orders as $order) {
            if (is_null($order->getType())) {
                $order->setType(ProductOrder::OWN_TYPE);
            }

            if (!$order->getUnitPrice()) {
                $productInfo = $em->getRepository('SandboxApiBundle:Order\ProductOrderInfo')->findOneBy(array('order' => $order));

                if ($productInfo) {
                    $info = $productInfo->getProductInfo();

                    $info = json_decode($info, true);

                    if (isset($info['unit_price']) && !is_null($info['unit_price'])) {
                        $order->setUnitPrice($info['unit_price']);
                        if ($info['base_price']) {
                            $order->setBasePrice($info['base_price']);
                        } else {
                            $seat = $info['room']['seat'];
                            $order->setBasePrice($seat['base_price']);
                        }
                    } else {
                        if (isset($info['order'])) {
                            $order->setUnitPrice($info['order']['unit_price']);
                            $leasingSets = $info['room']['leasing_set'];

                            foreach ($leasingSets as $leasingSet) {
                                if ($leasingSet['unit_price'] == $info['order']['unit_price']) {
                                    $order->setBasePrice($leasingSet['base_price']);
                                }
                            }
                        } else {
                            $leasingSets = $info['room']['leasing_set'];
                            $order->setUnitPrice($leasingSets[0]['unit_price']);
                            $order->setBasePrice($leasingSets[0]['base_price']);
                        }
                    }
                }
            }
        }

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
