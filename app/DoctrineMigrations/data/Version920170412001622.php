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
class Version920170412001622 extends AbstractMigration implements ContainerAwareInterface
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

        $account = $em->getRepository('SandboxApiBundle:Payment\Payment')
            ->findOneBY(array('channel' => 'account'));

        $wx = $em->getRepository('SandboxApiBundle:Payment\Payment')
            ->findOneBY(array('channel' => 'wx'));

        $alipay = $em->getRepository('SandboxApiBundle:Payment\Payment')
            ->findOneBY(array('channel' => 'alipay'));

        $upacp = $em->getRepository('SandboxApiBundle:Payment\Payment')
            ->findOneBY(array('channel' => 'upacp'));

        if ($account) {
            $pm1 = new PaymentMap();
            $pm1->setPayment($account);
            $pm1->setType('member');
            $em->persist($pm1);
        }

        if ($wx) {
            $pm2 = new PaymentMap();
            $pm2->setPayment($wx);
            $pm2->setType('member');
            $em->persist($pm2);
        }

        if ($alipay) {
            $pm3 = new PaymentMap();
            $pm3->setPayment($alipay);
            $pm3->setType('member');
            $em->persist($pm3);
        }

        if ($upacp) {
            $pm4 = new PaymentMap();
            $pm4->setPayment($upacp);
            $pm4->setType('member');
            $em->persist($pm4);
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
