<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Sandbox\ApiBundle\Entity\Product\Product;
use Sandbox\ApiBundle\Entity\Lease\Lease;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170906152500 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }

    public function postUp(Schema $schema)
    {
        parent::postUp($schema);

        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        $receivables = $em->getRepository('SandboxApiBundle:Finance\FinanceReceivables')->findAll();

        foreach ($receivables as $receivable) {
            $orderNumber = $receivable->getOrderNumber();

            $productOrder = $em->getRepository('SandboxApiBundle:Order\ProductOrder')
                ->findOneBy(array('orderNumber' => $orderNumber));

            if ($productOrder) {
                /** @var Product $product */
                $product = $productOrder->getProduct();
                $receivable->setCompanyId($product->getRoom()->getBuilding()->getCompanyId());
            }

            $bill = $em->getRepository('SandboxApiBundle:Lease\LeaseBill')
                ->findOneBy(array('serialNumber' => $orderNumber));

            if ($bill) {
                /** @var Lease $lease */
                $lease = $bill->getLease();
                $receivable->setCompanyId($lease->getCompanyId());
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
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
