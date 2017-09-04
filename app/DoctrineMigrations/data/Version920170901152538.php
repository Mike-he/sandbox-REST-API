<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Sandbox\ApiBundle\Entity\Finance\FinanceShortRentInvoice;
use Sandbox\ApiBundle\Entity\GenericList\GenericList;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170901152538 extends AbstractMigration implements ContainerAwareInterface
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

        $em = $this->container->get('doctrine.orm.entity_manager');

        $orders = $em->getRepository('SandboxApiBundle:Order\ProductOrder')->findAll();

        foreach ($orders as $order) {
            $userId = $order->getUserId();
            $product = $order->getProduct();
            if(is_null($product)){
               continue;
            }
            $companyId = $product->getRoom()->getBuilding()->getCompanyId();
            $customer = $em->getRepository('SandboxApiBundle:User\UserCustomer')
                            ->findOneBy(array(
                                'userId'=>$userId,
                                'companyId'=>$companyId
                            ));

            if (is_null($order->getCustomerId())) {
                if(!is_null($customer)){
                    $order->setCustomerId($customer->getId());
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
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
