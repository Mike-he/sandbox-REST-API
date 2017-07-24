<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Sandbox\ApiBundle\Entity\Product\ProductRentSet;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170522054952 extends AbstractMigration implements ContainerAwareInterface
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

        $products = $em->getRepository('SandboxApiBundle:Product\Product')->findAll();
        foreach ($products as $product) {
            if ($product->getEarliestRentDate()) {
                $productRentSet = $em->getRepository('SandboxApiBundle:Product\ProductRentSet')
                    ->findOneBy(array('product' => $product));

                if (is_null($productRentSet)) {
                    $productRentSet = new ProductRentSet();
                    $productRentSet->setProduct($product);
                    $productRentSet->setBasePrice($product->getBasePrice());
                    $productRentSet->setUnitPrice($product->getUnitPrice());
                    $productRentSet->setEarliestRentDate($product->getEarliestRentDate());
                    $productRentSet->setDeposit($product->getDeposit());
                    $productRentSet->setRentalInfo($product->getRentalInfo());
                    $productRentSet->setFilename($product->getFilename());
                    $productRentSet->setStatus(1);
                    $em->persist($productRentSet);
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
