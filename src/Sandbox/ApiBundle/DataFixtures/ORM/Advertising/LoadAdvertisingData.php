<?php

namespace Sandbox\ApiBundle\DataFixtures\ORM\Advertising;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sandbox\ApiBundle\Entity\Advertising\Advertising;
use Sandbox\ApiBundle\Entity\Advertising\AdvertisingAttachment;

class LoadAdvertisingData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $ad = new Advertising();
        $ad->setSource('url');
        $ad->setVisible(true);
        $ad->setIsDefault(true);

        $manager->persist($ad);

        $advat1 = new AdvertisingAttachment();
        $advat1->setAdvertising($ad);
        $advat1->setContent('https://image.sandbox3.cn/advertising/1326x1080_coffee_ad.jpg');
        $advat1->setAttachmentType('image/png');
        $advat1->setFilename('1326x1080_coffee_ad.jpg');
        $advat1->setSize(1);
        $advat1->setPreview('https://image.sandbox3.cn/advertising/1326x1080_coffee_ad.jpg');
        $advat1->setWidth(1080);
        $advat1->setHeight(1326);

        $advat2 = new AdvertisingAttachment();
        $advat2->setAdvertising($ad);
        $advat2->setContent('https://image.sandbox3.cn/advertising/1416x1080_coffee_ad.jpg');
        $advat2->setAttachmentType('image/png');
        $advat2->setFilename('1416x1080_coffee_ad.jpg');
        $advat2->setSize(1);
        $advat2->setPreview('https://image.sandbox3.cn/advertising/1416x1080_coffee_ad.jpg');
        $advat2->setWidth(1080);
        $advat2->setHeight(1416);

        $advat3 = new AdvertisingAttachment();
        $advat3->setAdvertising($ad);
        $advat3->setContent('https://image.sandbox3.cn/advertising/1486x1080_coffee_ad.jpg');
        $advat3->setAttachmentType('image/png');
        $advat3->setFilename('1486x1080_coffee_ad.jpg');
        $advat3->setSize(1);
        $advat3->setPreview('https://image.sandbox3.cn/advertising/1486x1080_coffee_ad.jpg');
        $advat3->setWidth(1080);
        $advat3->setHeight(1486);

        $advat4 = new AdvertisingAttachment();
        $advat4->setAdvertising($ad);
        $advat4->setContent('https://image.sandbox3.cn/advertising/1556x1080_coffee_ad.jpg');
        $advat4->setAttachmentType('image/png');
        $advat4->setFilename('1556x1080_coffee_ad.jpg');
        $advat4->setSize(1);
        $advat4->setPreview('https://image.sandbox3.cn/advertising/1556x1080_coffee_ad.jpg');
        $advat4->setWidth(1080);
        $advat4->setHeight(1556);

        $manager->persist($advat1);
        $manager->persist($advat2);
        $manager->persist($advat3);
        $manager->persist($advat4);

        $manager->flush();
    }

    public function getOrder()
    {
        return 5;
    }
}
