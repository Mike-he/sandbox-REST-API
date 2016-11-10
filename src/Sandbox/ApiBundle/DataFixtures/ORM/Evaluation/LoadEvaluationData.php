<?php

namespace Sandbox\ApiBundle\DataFixtures\ORM\Evaluation;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sandbox\ApiBundle\Entity\Evaluation\Evaluation;
use Sandbox\ApiBundle\Entity\Evaluation\EvaluationAttachment;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\ApiBundle\Entity\Room\RoomBuildingAttachment;
use Sandbox\ApiBundle\Entity\Room\RoomBuildingServices;
use Sandbox\ApiBundle\Entity\Room\RoomBuildingTag;
use Sandbox\ApiBundle\Entity\Room\RoomCity;
use Sandbox\ApiBundle\Entity\Room\RoomTypes;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompany;

class LoadEvaluationData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $now = new \DateTime('now');

        $ev1 = new Evaluation();
        $ev1->setType(Evaluation::TYPE_BUILDING);
        $ev1->setTotalStar(4);
        $ev1->setServiceStar(3);
        $ev1->setEnvironmentStar(4);
        $ev1->setComment('nice place, i will come here again');
        $ev1->setUser($this->getReference('user-mike'));
        $ev1->setBuilding($this->getReference('room-building-for-data-structure'));
        $ev1->setProductOrder(null);
        $ev1->setVisible(1);
        $ev1->setCreationDate($now);
        $this->addReference('evaluation-with_comment-with_pic', $ev1);

        $evt1 = new EvaluationAttachment();
        $evt1->setEvaluation($ev1);
        $evt1->setContent('http://123.com.cn');
        $evt1->setAttachmentType('image/jpeg');
        $evt1->setFilename('123.jpg');
        $evt1->setSize(2);
        $this->addReference('evaluation-no1-attachment', $evt1);

        $ev2 = new Evaluation();
        $ev2->setType(Evaluation::TYPE_BUILDING);
        $ev2->setTotalStar(4);
        $ev2->setServiceStar(3);
        $ev2->setEnvironmentStar(4);
        $ev2->setComment(null);
        $ev2->setUser($this->getReference('user-mike'));
        $ev2->setBuilding($this->getReference('room-building-for-data-structure'));
        $ev2->setProductOrder(null);
        $ev2->setVisible(1);
        $ev2->setCreationDate($now);
        $this->addReference('evaluation-no_comment-with_pic', $ev2);

        $evt2 = new EvaluationAttachment();
        $evt2->setEvaluation($ev2);
        $evt2->setContent('http://123.com.cn');
        $evt2->setAttachmentType('image/jpeg');
        $evt2->setFilename('123.jpg');
        $evt2->setSize(2);
        $this->addReference('evaluation-no2-attachment', $evt2);

        $ev3 = new Evaluation();
        $ev3->setType(Evaluation::TYPE_BUILDING);
        $ev3->setTotalStar(4);
        $ev3->setServiceStar(3);
        $ev3->setEnvironmentStar(4);
        $ev3->setComment('i like it');
        $ev3->setUser($this->getReference('user-mike'));
        $ev3->setBuilding($this->getReference('room-building-for-data-structure'));
        $ev3->setProductOrder(null);
        $ev3->setVisible(1);
        $ev3->setCreationDate($now);
        $this->addReference('evaluation-with_comment-no_pic', $ev3);

        $manager->persist($ev1);
        $manager->persist($ev2);
        $manager->persist($ev3);

        $manager->persist($evt1);
        $manager->persist($evt2);

        $manager->flush();
    }

    public function getOrder()
    {
        return 4;
    }
}
