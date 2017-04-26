<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Sandbox\ApiBundle\Entity\Evaluation\Evaluation;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170420075902 extends AbstractMigration implements ContainerAwareInterface
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

        $evaluations = $em->getRepository('SandboxApiBundle:Evaluation\Evaluation')
            ->findBy(array('type' => Evaluation::TYPE_ORDER));

        foreach ($evaluations as $evaluation) {
            $orderId = $evaluation->getProductOrderId();

            $order = $em->getRepository('SandboxApiBundle:Order\ProductOrder')->find($orderId);

            if (!$order) {
                continue;
            }

            $order->setHasEvaluated(true);
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
