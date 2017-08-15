<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyProfileAccount;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompanyProfiles;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170811081820 extends AbstractMigration implements ContainerAwareInterface
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

        $accounts = $em->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyProfileAccount')
            ->findAll();
        foreach ($accounts as $account) {
            /** @var SalesCompanyProfileAccount $account */
            $salesCompanyId = $account->getSalesCompanyId();

            $profile = new SalesCompanyProfiles();
            $profile->setSalesCompanyId($salesCompanyId);
            $em->persist($profile);
            $em->flush();

            $account->setProfileId($profile->getId());

            $express = $em->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyProfileExpress')
                ->findOneBy(array('salesCompanyId' => $salesCompanyId));
            if ($express) {
                $express->setProfileId($profile->getId());
            }

            $invoice = $em->getRepository('SandboxApiBundle:SalesAdmin\SalesCompanyProfileInvoice')
                ->findOneBy(array('salesCompanyId' => $salesCompanyId));
            if ($invoice) {
                $invoice->setProfileId($profile->getId());
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
