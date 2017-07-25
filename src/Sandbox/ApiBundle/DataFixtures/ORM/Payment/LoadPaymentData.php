<?php

namespace Sandbox\ApiBundle\DataFixtures\ORM\Payment;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sandbox\ApiBundle\Entity\Payment\Payment;
use Sandbox\ApiBundle\Entity\Payment\PaymentMap;

class LoadPaymentData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
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

        $p9 = new Payment();
        $p9->setChannel('sales_offline');
        $p9->setName('销售方收款');
        $p9->setNameEn('Sales Offline');

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

        $manager->persist($p1);
        $manager->persist($p2);
        $manager->persist($p3);
        $manager->persist($p4);
        $manager->persist($p5);
        $manager->persist($p6);
        $manager->persist($p7);
        $manager->persist($p8);
        $manager->persist($p9);

        $manager->persist($pm1);
        $manager->persist($pm2);
        $manager->persist($pm3);
        $manager->persist($pm4);
        $manager->persist($pm5);
        $manager->persist($pm6);
        $manager->persist($pm7);
        $manager->persist($pm8);
        $manager->persist($pm9);
        $manager->persist($pm10);
        $manager->persist($pm11);
        $manager->persist($pm12);
        $manager->persist($pm13);
        $manager->persist($pm14);
        $manager->persist($pm15);
        $manager->persist($pm16);
        $manager->persist($pm17);
        $manager->persist($pm18);
        $manager->persist($pm19);
        $manager->persist($pm20);
        $manager->persist($pm21);
        $manager->persist($pm22);
        $manager->persist($pm23);

        $manager->flush();
    }

    public function getOrder()
    {
        return 22;
    }
}
