<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyServiceInfos;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170117032803 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE lease_bill ADD sales_invoice TINYINT(1) NOT NULL, ADD invoiced TINYINT(1) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function postUp(Schema $schema)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        $bills = $em->getRepository('SandboxApiBundle:Lease\LeaseBill')
            ->findAll();

        foreach ($bills as $bill) {
            $salesCompany = $bill->getLease()->getProduct()->getRoom()->getBuilding()->getCompany();
            $serviceInfo = $em->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyServiceInfos')
                ->findOneBy(array(
                    'company' => $salesCompany,
                    'roomTypes' => 'longterm',
                    'status' => true,
                ));

            if (is_null($serviceInfo)) {
                continue;
            }

            if ($serviceInfo->getDrawer() == SalesCompanyServiceInfos::DRAWER_SALES) {
                $bill->setSalesInvoice(true);
            }
        }

        $em->flush();
        $em->clear();
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE lease_bill DROP user_id, DROP sales_invoice');
    }
}
