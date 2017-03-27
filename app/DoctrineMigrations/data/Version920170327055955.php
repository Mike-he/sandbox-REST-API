<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Sandbox\ApiBundle\Entity\Admin\AdminPermission;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionGroupMap;
use Sandbox\ApiBundle\Entity\Admin\AdminPermissionGroups;
use Sandbox\ApiBundle\Entity\Room\RoomCity;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170327055955 extends AbstractMigration implements ContainerAwareInterface
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

        $chinaCountry = $em->getRepository('SandboxApiBundle:Room\RoomCity')
            ->findOneBy(array('name' => '中国'));
        $chinaCountry->setEnName('China');

        $usaCountry = $em->getRepository('SandboxApiBundle:Room\RoomCity')
            ->findOneBy(array('name' => '美国'));
        $usaCountry->setEnName('USA');

        $californiaState = $em->getRepository('SandboxApiBundle:Room\RoomCity')
            ->findOneBy(array('name' => '加利福尼亚州'));
        $californiaState->setEnName('State of California');

        $cSF = $em->getRepository('SandboxApiBundle:Room\RoomCity')
            ->findOneBy(array('name' => '旧金山'));
        $cSF->setHot(1);
        $cSF->setType(RoomCity::TYPE_INTERNATIONAL);

        $cLA = new RoomCity();
        $cLA->setName('洛杉矶');
        $cLA->setParent($californiaState);
        $cLA->setLevel(3);
        $cLA->setEnName('Los Angeles');
        $cLA->setKey('LA');
        $cLA->setLat('34.05');
        $cLA->setLng('-118.22');
        $cLA->setHot(1);
        $cLA->setType(RoomCity::TYPE_INTERNATIONAL);
        $em->persist($cLA);


        $nyState = new RoomCity();
        $nyState->setName('纽约州');
        $nyState->setParent($usaCountry);
        $nyState->setLevel(2);
        $nyState->setEnName('State of New York');
        $nyState->setKey(null);
        $em->persist($nyState);

        $cNY = new RoomCity();
        $cNY->setName('纽约');
        $cNY->setParent($nyState);
        $cNY->setLevel(3);
        $cNY->setEnName('New York');
        $cNY->setKey('NY');
        $cNY->setLat('40.67');
        $cNY->setLng('-73.94');
        $cNY->setHot(1);
        $cNY->setType(RoomCity::TYPE_INTERNATIONAL);
        $em->persist($cNY);

        $coloradoState = new RoomCity();
        $coloradoState->setName('科罗拉多州');
        $coloradoState->setParent($usaCountry);
        $coloradoState->setLevel(2);
        $coloradoState->setEnName('Colorado');
        $coloradoState->setKey(null);
        $em->persist($coloradoState);

        $cDenver = new RoomCity();
        $cDenver->setName('丹佛');
        $cDenver->setParent($coloradoState);
        $cDenver->setLevel(3);
        $cDenver->setEnName('Denver');
        $cDenver->setKey('Denver');
        $cDenver->setLat('39.45');
        $cDenver->setLng('-104.59');
        $cDenver->setHot(1);
        $cDenver->setType(RoomCity::TYPE_INTERNATIONAL);
        $em->persist($cDenver);

        $illinoisState = new RoomCity();
        $illinoisState->setName('伊利诺伊州');
        $illinoisState->setParent($usaCountry);
        $illinoisState->setLevel(2);
        $illinoisState->setEnName('Illinois');
        $illinoisState->setKey(null);
        $em->persist($illinoisState);

        $cChicago = new RoomCity();
        $cChicago->setName('芝加哥');
        $cChicago->setParent($illinoisState);
        $cChicago->setLevel(3);
        $cChicago->setEnName('Chicago');
        $cChicago->setKey('Chicago');
        $cChicago->setLat('41.53');
        $cChicago->setLng('-87.39');
        $cChicago->setHot(1);
        $cChicago->setType(RoomCity::TYPE_INTERNATIONAL);
        $em->persist($cChicago);

        $cBoston = $em->getRepository('SandboxApiBundle:Room\RoomCity')
            ->findOneBy(array('name' => '波士顿'));
        $cBoston->setHot(1);
        $cBoston->setType(RoomCity::TYPE_INTERNATIONAL);

        $washingtonState = new RoomCity();
        $washingtonState->setName('华盛顿州');
        $washingtonState->setParent($usaCountry);
        $washingtonState->setLevel(2);
        $washingtonState->setEnName('State of Washington');
        $washingtonState->setKey(null);
        $em->persist($washingtonState);

        $cSeattle = new RoomCity();
        $cSeattle->setName('西雅图');
        $cSeattle->setParent($washingtonState);
        $cSeattle->setLevel(3);
        $cSeattle->setEnName('Seattle');
        $cSeattle->setKey('Seattle');
        $cSeattle->setLat('47.37');
        $cSeattle->setLng('-122.19');
        $cSeattle->setHot(1);
        $cSeattle->setType(RoomCity::TYPE_INTERNATIONAL);
        $em->persist($cSeattle);

        $washingtonDCState = new RoomCity();
        $washingtonDCState->setName('华盛顿特区');
        $washingtonDCState->setParent($usaCountry);
        $washingtonDCState->setLevel(2);
        $washingtonDCState->setEnName('Washington, D.C.');
        $washingtonDCState->setKey(null);
        $em->persist($washingtonDCState);

        $cWashington = new RoomCity();
        $cWashington->setName('华盛顿');
        $cWashington->setParent($washingtonDCState);
        $cWashington->setLevel(3);
        $cWashington->setEnName('Washington, D.C.');
        $cWashington->setKey('WDC');
        $cWashington->setLat('38.53');
        $cWashington->setLng('-77.01');
        $cWashington->setHot(1);
        $cWashington->setType(RoomCity::TYPE_INTERNATIONAL);
        $em->persist($cWashington);


        $canadaCountry = new RoomCity();
        $canadaCountry->setName('加拿大');
        $canadaCountry->setParent(null);
        $canadaCountry->setLevel(1);
        $canadaCountry->setEnName('Canada');
        $canadaCountry->setKey(null);
        $em->persist($canadaCountry);

        $Ontario = new RoomCity();
        $Ontario->setName('安大略省');
        $Ontario->setParent($canadaCountry);
        $Ontario->setLevel(2);
        $Ontario->setEnName('Ontario');
        $Ontario->setKey(null);
        $em->persist($Ontario);

        $cToronto = new RoomCity();
        $cToronto->setName('多伦多');
        $cToronto->setParent($Ontario);
        $cToronto->setLevel(3);
        $cToronto->setEnName('Toronto');
        $cToronto->setKey('Toronto');
        $cToronto->setLat('43.4');
        $cToronto->setLng('-79.22');
        $cToronto->setHot(1);
        $cToronto->setType(RoomCity::TYPE_INTERNATIONAL);
        $em->persist($cToronto);

        $British = new RoomCity();
        $British->setName('不列颠哥伦比亚省');
        $British->setParent($canadaCountry);
        $British->setLevel(2);
        $British->setEnName('British Columbia');
        $British->setKey(null);
        $em->persist($British);

        $cVancouver = new RoomCity();
        $cVancouver->setName('温哥华');
        $cVancouver->setParent($British);
        $cVancouver->setLevel(3);
        $cVancouver->setEnName('Vancouver');
        $cVancouver->setKey('Vancouver');
        $cVancouver->setLat('49.13');
        $cVancouver->setLng('-123.06');
        $cVancouver->setHot(1);
        $cVancouver->setType(RoomCity::TYPE_INTERNATIONAL);
        $em->persist($cVancouver);

        $australiaCountry = new RoomCity();
        $australiaCountry->setName('澳大利亚');
        $australiaCountry->setParent(null);
        $australiaCountry->setLevel(1);
        $australiaCountry->setEnName('Australia');
        $australiaCountry->setKey(null);
        $em->persist($australiaCountry);

        $Wales = new RoomCity();
        $Wales->setName('新南威尔士州');
        $Wales->setParent($australiaCountry);
        $Wales->setLevel(2);
        $Wales->setEnName('New South Wales');
        $Wales->setKey(null);
        $em->persist($Wales);

        $cSydney = new RoomCity();
        $cSydney->setName('悉尼');
        $cSydney->setParent($Wales);
        $cSydney->setLevel(3);
        $cSydney->setEnName('Sydney');
        $cSydney->setKey('Sydney');
        $cSydney->setLat('-33.55');
        $cSydney->setLng('151.17');
        $cSydney->setHot(1);
        $cSydney->setType(RoomCity::TYPE_INTERNATIONAL);
        $em->persist($cSydney);

        $Victoria = new RoomCity();
        $Victoria->setName('维多利亚州');
        $Victoria->setParent($australiaCountry);
        $Victoria->setLevel(2);
        $Victoria->setEnName('Victoria');
        $Victoria->setKey(null);
        $em->persist($Victoria);

        $cMelbourne = new RoomCity();
        $cMelbourne->setName('墨尔本');
        $cMelbourne->setParent($Victoria);
        $cMelbourne->setLevel(3);
        $cMelbourne->setEnName('Melbourne');
        $cMelbourne->setKey('Melbourne');
        $cMelbourne->setLat('-37.52');
        $cMelbourne->setLng('145.08');
        $cMelbourne->setHot(1);
        $cMelbourne->setType(RoomCity::TYPE_INTERNATIONAL);
        $em->persist($cMelbourne);

        $sh = $em->getRepository('SandboxApiBundle:Room\RoomCity')
            ->findOneBy(array('name' => '上海市'));
        $sh->setHot(1);
        $sh->setType(RoomCity::TYPE_INTERNAL);

        $bj = $em->getRepository('SandboxApiBundle:Room\RoomCity')
            ->findOneBy(array('name' => '北京市'));
        $bj->setHot(1);
        $bj->setType(RoomCity::TYPE_INTERNAL);

        $sz = $em->getRepository('SandboxApiBundle:Room\RoomCity')
            ->findOneBy(array('name' => '深圳市'));
        $sz->setHot(1);
        $sz->setType(RoomCity::TYPE_INTERNAL);

        $gz = $em->getRepository('SandboxApiBundle:Room\RoomCity')
            ->findOneBy(array('name' => '广州市'));
        $gz->setHot(1);
        $gz->setType(RoomCity::TYPE_INTERNAL);


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
