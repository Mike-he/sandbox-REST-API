<?php

namespace Sandbox\ApiBundle\DataFixtures\ORM\Room;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\ApiBundle\Entity\Room\RoomBuildingAttachment;
use Sandbox\ApiBundle\Entity\Room\RoomBuildingServices;
use Sandbox\ApiBundle\Entity\Room\RoomBuildingTag;

class LoadRoomBuildingData extends AbstractFixture implements OrderedFixtureInterface
{
    const SHANGHAI_ZHANXIANG_LAT = 39.87658;
    const SHANGHAI_ZHANXIANG_LNG = 116.365549;
    const BEIJING_SOUTH_STATION_LAT = 39.87658;
    const BEIJING_SOUTH_STATION_LNG = 116.365549;

    public function load(ObjectManager $manager)
    {
        $rbt1 = new RoomBuildingTag();
        $rbt1->setKey('sandbox3_manage');
        $rbt1->setIcon('http://image.sandbox3.cn/icon/3.png');
        $rbt1->setIconWithBg('/icon/bg-3.png');
        $this->addReference('first-building-tag', $rbt1);

        $rbt2 = new RoomBuildingTag();
        $rbt2->setKey('round_the_clock_service');
        $rbt2->setIcon('http://image.sandbox3.cn/icon/4.png');
        $rbt2->setIconWithBg('/icon/bg-4.png');
        $this->addReference('second-building-tag', $rbt2);

        $rbs1 = new RoomBuildingServices();
        $rbs1->setKey('free_wifi');
        $rbs1->setIcon('http://image.sandbox3.cn/icon/5.png');
        $rbs1->setSelectedIcon('http://image.sandbox3.cn/icon/5.png');
        $this->addReference('first-building-service', $rbs1);

        $rbs2 = new RoomBuildingServices();
        $rbs2->setKey('printing_devices');
        $rbs2->setIcon('http://image.sandbox3.cn/icon/6.png');
        $rbs2->setSelectedIcon('http://image.sandbox3.cn/icon/6.png');
        $this->addReference('second-building-service', $rbs2);

        $date = new \DateTime('now');

        $sc1 = $this->getReference('sales-company-sandbox');

        $rb1 = new RoomBuilding();
        $rb1->setCity($this->getReference('shanghai'));
        $rb1->setDistrict($this->getReference('pudongxinqu'));
        $rb1->setName('展想广场');
        $rb1->setAddress('浦东新区祖冲之路2290, 1#楼');
        $rb1->setAvatar('http://devimage.sandbox3.cn/building/55ca4ac7-89d8-4c49-a448-83946f2b8cf4.jpg');
        $rb1->setDescription('张江高科技园区');
        $rb1->setDetail('上海市浦东张江高科技园区');
        $rb1->setSubtitle('buzhidao');
        $rb1->setLat('31.216193');
        $rb1->setLng('121.632682');
        $rb1->setBuildingEvaluationNumber(5);
        $rb1->setOrderEvaluationNumber(6);
        $rb1->setBuildingStar(4.2);
        $rb1->setStatus('accept');
        $rb1->setVisible(true);
        $rb1->setCompany($sc1);
        $rb1->setLessorName('name');
        $rb1->setLessorAddress('address');
        $rb1->setLessorPhone('12345678');
        $rb1->setLessorContact('sandbox');
        $rb1->setLessorEmail('account@sandbox3.cn');
        $this->addReference('room-building-for-data-structure', $rb1);

        $rb2 = new RoomBuilding();
        $rb2->setCity($this->getReference('shanghai'));
        $rb2->setName('展想广场2');
        $rb2->setAddress('浦东新区祖冲之路2290, 2#楼');
        $rb2->setAvatar('http://devimage.sandbox3.cn/building/55ca4ac7-89d8-4c49-a448-83946f2b8cf4.jpg');
        $rb2->setDescription('张江高科技园区2');
        $rb2->setDetail('上海是浦东张江高科技园区2');
        $rb2->setSubtitle('buzhidao2');
        $rb2->setLat('31.226193');
        $rb2->setLng('121.642682');
        $rb2->setBuildingEvaluationNumber(6);
        $rb2->setOrderEvaluationNumber(7);
        $rb2->setBuildingStar(4.5);
        $rb2->setStatus('accept');
        $rb2->setVisible(true);
        $rb2->setCompany($sc1);
        $rb2->setLessorName('name');
        $rb2->setLessorAddress('address');
        $rb2->setLessorPhone('12345678');
        $rb2->setLessorContact('sandbox');
        $rb2->setLessorEmail('account@sandbox3.cn');
        $this->addReference('room-building-without-room', $rb2);

        $rb3 = new RoomBuilding();
        $rb3->setCity($this->getReference('shanghai'));
        $rb3->setName('展想广场3');
        $rb3->setAddress('浦东新区祖冲之路2290, 3#楼');
        $rb3->setAvatar('http://devimage.sandbox3.cn/building/55ca4ac7-89d8-4c49-a448-83946f2b8cf4.jpg');
        $rb3->setDescription('张江高科技园区3');
        $rb3->setDetail('上海是浦东张江高科技园区3');
        $rb3->setSubtitle('buzhidao3');
        $rb3->setLat('31.256193');
        $rb3->setLng('121.672682');
        $rb3->setBuildingEvaluationNumber(7);
        $rb3->setOrderEvaluationNumber(9);
        $rb3->setBuildingStar(4.0);
        $rb3->setStatus('accept');
        $rb3->setVisible(true);
        $rb3->setCompany($sc1);
        $rb3->setLessorName('name');
        $rb3->setLessorAddress('address');
        $rb3->setLessorPhone('12345678');
        $rb3->setLessorContact('sandbox');
        $rb3->setLessorEmail('account@sandbox3.cn');

        $rb4 = new RoomBuilding();
        $rb4->setCity($this->getReference('beijing'));
        $rb4->setDistrict($this->getReference('dongchengqu'));
        $rb4->setName('北京南站');
        $rb4->setAddress('北京南站路, 1#楼');
        $rb4->setAvatar('http://devimage.sandbox3.cn/building/55ca4ac7-89d8-4c49-a448-83946f2b8cf4.jpg');
        $rb4->setDescription('北京南站商业园区');
        $rb4->setDetail('北京南站商业园区');
        $rb4->setSubtitle('buzhidao3');
        $rb4->setLat(self::BEIJING_SOUTH_STATION_LAT);
        $rb4->setLng(self::BEIJING_SOUTH_STATION_LNG);
        $rb4->setBuildingEvaluationNumber(7);
        $rb4->setOrderEvaluationNumber(9);
        $rb4->setBuildingStar(4.0);
        $rb4->setStatus('accept');
        $rb4->setVisible(true);
        $rb4->setCompany($sc1);
        $rb4->setLessorName('name');
        $rb4->setLessorAddress('address');
        $rb4->setLessorPhone('12345678');
        $rb4->setLessorContact('sandbox');
        $rb4->setLessorEmail('account@sandbox3.cn');

        $rb5 = new RoomBuilding();
        $rb5->setCity($this->getReference('shanghai'));
        $rb5->setName('展想广场5');
        $rb5->setAddress('浦东新区祖冲之路2290, 1#楼');
        $rb5->setAvatar('http://devimage.sandbox3.cn/building/55ca4ac7-89d8-4c49-a448-83946f2b8cf4.jpg');
        $rb5->setDescription('room-building-for-pengding-1');
        $rb5->setDetail('上海市浦东张江高科技园区');
        $rb5->setSubtitle('buzhidao');
        $rb5->setLat('31.216193');
        $rb5->setLng('121.632682');
        $rb5->setCompany($sc1);
        $rb5->setLessorName('name');
        $rb5->setLessorAddress('address');
        $rb5->setLessorPhone('12345678');
        $rb5->setLessorContact('sandbox');
        $rb5->setLessorEmail('account@sandbox3.cn');
        $this->addReference('room-building-for-pengding-1', $rb5);

        $rb6 = new RoomBuilding();
        $rb6->setCity($this->getReference('shanghai'));
        $rb6->setName('展想广场6');
        $rb6->setAddress('浦东新区祖冲之路2290, 1#楼');
        $rb6->setAvatar('http://devimage.sandbox3.cn/building/55ca4ac7-89d8-4c49-a448-83946f2b8cf4.jpg');
        $rb6->setDescription('room-building-for-accept-invisible-1');
        $rb6->setDetail('上海市浦东张江高科技园区');
        $rb6->setSubtitle('buzhidao');
        $rb6->setLat('31.216193');
        $rb6->setLng('121.632682');
        $rb6->setStatus('accept');
        $rb6->setCompany($sc1);
        $rb6->setLessorName('name');
        $rb6->setLessorAddress('address');
        $rb6->setLessorPhone('12345678');
        $rb6->setLessorContact('sandbox');
        $rb6->setLessorEmail('account@sandbox3.cn');
        $this->addReference('room-building-for-accept-invisible-1', $rb6);

        $rb7 = new RoomBuilding();
        $rb7->setCity($this->getReference('shanghai'));
        $rb7->setName('展想广场7');
        $rb7->setAddress('浦东新区祖冲之路2290, 1#楼');
        $rb7->setAvatar('http://devimage.sandbox3.cn/building/55ca4ac7-89d8-4c49-a448-83946f2b8cf4.jpg');
        $rb7->setDescription('room-building-for-banned-1');
        $rb7->setDetail('上海市浦东张江高科技园区');
        $rb7->setSubtitle('buzhidao');
        $rb7->setLat('31.216193');
        $rb7->setLng('121.632682');
        $rb7->setStatus('banned');
        $rb7->setVisible(true);
        $rb7->setCompany($sc1);
        $rb7->setLessorName('name');
        $rb7->setLessorAddress('address');
        $rb7->setLessorPhone('12345678');
        $rb7->setLessorContact('sandbox');
        $rb7->setLessorEmail('account@sandbox3.cn');
        $this->addReference('room-building-for-banned-1', $rb7);

        $rba1 = new RoomBuildingAttachment();
        $rba1->setBuilding($rb1);
        $rba1->setContent('http://devimage.sandbox3.cn/building/55ca4ac7-89d8-4c49-a448-83946f2b8cf4.jpg');
        $rba1->setAttachmentType('jpg');
        $rba1->setFilename('cover');
        $rba1->setSize(1024);
        $rba1->setPreview('http://devimage.sandbox3.cn/building/55ca4ac7-89d8-4c49-a448-83946f2b8cf4.jpg');
        $rba1->setCreationDate($date);
        $this->addReference('first-attachment-for-building-1', $rba1);

        $rba2 = new RoomBuildingAttachment();
        $rba2->setBuilding($rb1);
        $rba2->setContent('http://devimage.sandbox3.cn/building/a448-83946f2b8cf4.jpg');
        $rba2->setAttachmentType('jpg');
        $rba2->setFilename('cover');
        $rba2->setSize(1020);
        $rba2->setPreview('http://devimage.sandbox3.cn/building/49-a448-83946f2b8cf4.jpg');
        $rba2->setCreationDate($date);
        $this->addReference('second-attachment-for-building-1', $rba2);

        $rba3 = new RoomBuildingAttachment();
        $rba3->setBuilding($rb4);
        $rba3->setContent('http://devimage.sandbox3.cn/building/a3946f2b8cf4.jpg');
        $rba3->setAttachmentType('jpg');
        $rba3->setFilename('cover');
        $rba3->setSize(1023);
        $rba3->setPreview('http://devimage.sandbox3.cn/building/49-a448-6f2b8cf4.jpg');
        $rba3->setCreationDate($date);
        $this->addReference('first-attachment-for-building-4', $rba3);

        $manager->persist($rbt1);
        $manager->persist($rbt2);
        $manager->persist($rbs1);
        $manager->persist($rbs2);

        $manager->persist($sc1);

        $manager->persist($rb1);
        $manager->persist($rb2);
        $manager->persist($rb3);
        $manager->persist($rb4);
        $manager->persist($rb5);
        $manager->persist($rb6);
        $manager->persist($rb7);

        $manager->persist($rba1);
        $manager->persist($rba2);
        $manager->persist($rba3);

        $manager->flush();
    }

    public function getOrder()
    {
        return 7;
    }
}
