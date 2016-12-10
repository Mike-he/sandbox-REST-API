<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Sandbox\ApiBundle\Entity\Payment\Payment;
use Sandbox\ApiBundle\Entity\Payment\PaymentMap;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161210104434 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, channel VARCHAR(64) NOT NULL, name VARCHAR(128) NOT NULL, name_en VARCHAR(128) NOT NULL, status TINYINT(1) NOT NULL, UNIQUE INDEX key_UNIQUE (channel), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment_map (id INT AUTO_INCREMENT NOT NULL, payment_id INT DEFAULT NULL, type VARCHAR(128) NOT NULL, INDEX IDX_A4CE6B8F4C3A3BB (payment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE payment_map ADD CONSTRAINT FK_A4CE6B8F4C3A3BB FOREIGN KEY (payment_id) REFERENCES payment (id) ON DELETE CASCADE');
    }

    public function postUp(Schema $schema)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        $p1 = new Payment();
        $p1->setChannel('account');
        $p1->setName('余额支付');
        $p1->setNameEn('Balance');

        $p2 = new Payment();
        $p2->setChannel('wx');
        $p2->setName('微信支付');
        $p2->setNameEn('Wechat Pay');

        $p3 = new Payment();
        $p3->setChannel('alipay');
        $p3->setName('支付宝支付');
        $p3->setNameEn('Alipay');

        $p4 = new Payment();
        $p4->setChannel('upacp');
        $p4->setName('银联支付');
        $p4->setNameEn('Union Pay');

        $p5 = new Payment();
        $p5->setChannel('wx_pub');
        $p5->setName('微信公众号');
        $p5->setNameEn('Wechat Public Pay');

        $p6 = new Payment();
        $p6->setChannel('offline');
        $p6->setName('线下支付');
        $p6->setNameEn('Offline Pay');

        $p7 = new Payment();
        $p7->setChannel('cnp_u');
        $p7->setName('快捷支付(国内)');
        $p7->setNameEn('Domestic Quick Pay');
        $p7->setStatus(false);

        $p8 = new Payment();
        $p8->setChannel('cnp_f');
        $p8->setName('快捷支付(国外)');
        $p8->setNameEn('International Quick Pay');
        $p8->setStatus(false);

        $pm1 = new PaymentMap();
        $pm1->setPayment($p1);
        $pm1->setType('space');

        $pm2 = new PaymentMap();
        $pm2->setPayment($p2);
        $pm2->setType('space');

        $pm3 = new PaymentMap();
        $pm3->setPayment($p3);
        $pm3->setType('space');

        $pm4 = new PaymentMap();
        $pm4->setPayment($p4);
        $pm4->setType('space');

        $pm5 = new PaymentMap();
        $pm5->setPayment($p5);
        $pm5->setType('space');

        $pm6 = new PaymentMap();
        $pm6->setPayment($p6);
        $pm6->setType('space');

        $pm7 = new PaymentMap();
        $pm7->setPayment($p1);
        $pm7->setType('shop');

        $pm8 = new PaymentMap();
        $pm8->setPayment($p2);
        $pm8->setType('shop');

        $pm9 = new PaymentMap();
        $pm9->setPayment($p3);
        $pm9->setType('shop');

        $pm10 = new PaymentMap();
        $pm10->setPayment($p4);
        $pm10->setType('shop');

        $pm11 = new PaymentMap();
        $pm11->setPayment($p5);
        $pm11->setType('shop');

        $pm12 = new PaymentMap();
        $pm12->setPayment($p1);
        $pm12->setType('event');

        $pm13 = new PaymentMap();
        $pm13->setPayment($p2);
        $pm13->setType('event');

        $pm14 = new PaymentMap();
        $pm14->setPayment($p3);
        $pm14->setType('event');

        $pm15 = new PaymentMap();
        $pm15->setPayment($p4);
        $pm15->setType('event');

        $pm16 = new PaymentMap();
        $pm16->setPayment($p5);
        $pm16->setType('event');

        $pm17 = new PaymentMap();
        $pm17->setPayment($p2);
        $pm17->setType('recharge');

        $pm18 = new PaymentMap();
        $pm18->setPayment($p3);
        $pm18->setType('recharge');

        $pm19 = new PaymentMap();
        $pm19->setPayment($p4);
        $pm19->setType('recharge');

        $pm20 = new PaymentMap();
        $pm20->setPayment($p2);
        $pm20->setType('lease_bill');

        $pm21 = new PaymentMap();
        $pm21->setPayment($p3);
        $pm21->setType('lease_bill');

        $pm22 = new PaymentMap();
        $pm22->setPayment($p4);
        $pm22->setType('lease_bill');

        $pm23 = new PaymentMap();
        $pm23->setPayment($p6);
        $pm23->setType('lease_bill');

        $em->persist($p1);
        $em->persist($p2);
        $em->persist($p3);
        $em->persist($p4);
        $em->persist($p5);
        $em->persist($p6);
        $em->persist($p7);
        $em->persist($p8);
        $em->persist($pm1);
        $em->persist($pm2);
        $em->persist($pm3);
        $em->persist($pm4);
        $em->persist($pm5);
        $em->persist($pm6);
        $em->persist($pm7);
        $em->persist($pm8);
        $em->persist($pm9);
        $em->persist($pm10);
        $em->persist($pm11);
        $em->persist($pm12);
        $em->persist($pm13);
        $em->persist($pm14);
        $em->persist($pm15);
        $em->persist($pm16);
        $em->persist($pm17);
        $em->persist($pm18);
        $em->persist($pm19);
        $em->persist($pm20);
        $em->persist($pm21);
        $em->persist($pm22);
        $em->persist($pm23);

        $em->flush();
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE payment_map DROP FOREIGN KEY FK_A4CE6B8F4C3A3BB');
        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE payment_map');
    }
}
