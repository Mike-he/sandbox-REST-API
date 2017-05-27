<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Sandbox\ApiBundle\Entity\Product\ProductLeasingSet;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170515020301 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        parent::postUp($schema);

        $em = $this->container->get('doctrine.orm.entity_manager');

        $products = $em->getRepository('SandboxApiBundle:Product\Product')
            ->findBy(array('isDeleted'=> 0));

        foreach ($products as $product) {
            $productLeasingSet = new ProductLeasingSet();
            $productLeasingSet->setProduct($product);
            $productLeasingSet->setBasePrice($product->getBasePrice());
            $productLeasingSet->setUnitPrice($product->getUnitPrice());
            $productLeasingSet->setAmount(0);

            $em->persist($productLeasingSet);
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
