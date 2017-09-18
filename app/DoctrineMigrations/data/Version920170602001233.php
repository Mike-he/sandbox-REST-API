<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Sandbox\ApiBundle\Entity\Payment\PaymentMap;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170602001233 extends AbstractMigration implements ContainerAwareInterface
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

        $offline = $em->getRepository('SandboxApiBundle:Payment\Payment')
            ->findOneBY(array('channel' => 'offline'));

        $wxPub = $em->getRepository('SandboxApiBundle:Payment\Payment')
            ->findOneBY(array('channel' => 'wx_pub'));

        if ($offline) {
            $pm1 = new PaymentMap();
            $pm1->setPayment($offline);
            $pm1->setType('recharge');
            $em->persist($pm1);
        }

        if ($wxPub) {
            $pm2 = new PaymentMap();
            $pm2->setPayment($wxPub);
            $pm2->setType('recharge');
            $em->persist($pm2);
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
