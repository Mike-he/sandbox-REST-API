<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Sandbox\ApiBundle\Entity\Finance\FinanceSalesWallet;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170217024630 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {

    }

    public function postUp(Schema $schema)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        $companies = $em->getRepository('SandboxApiBundle:SalesAdmin\SalesCompany')
            ->findAll();

        foreach ($companies as $company) {
            $companyId = $company->getId();

            $existWallet = $em->getRepository('SandboxApiBundle:Finance\FinanceSalesWallet')
                ->findOneBy(['companyId' => $companyId]);
            if (!is_null($existWallet)) {
                continue;
            }
            
            $wallet = new FinanceSalesWallet();
            $wallet->setCompanyId($companyId);

            $em->persist($wallet);
        }

        $em->flush();
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {

    }
}
