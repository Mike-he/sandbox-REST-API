<?php

namespace Sandbox\ApiBundle\DataFixtures\ORM\User;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sandbox\ApiBundle\Entity\User\UserAppointmentProfile;

class LoadUserAppointmentProfileData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $ap1 = new UserAppointmentProfile();
        $ap1->setUser($this->getReference('user-3'));
        $ap1->setAddress('address 1');
        $ap1->setContact('contact 1');
        $ap1->setEmail('1@sandbox3.cn');
        $ap1->setName('name 1');
        $ap1->setPhone('1000000001');
        $this->addReference('user-appointment-profile-1', $ap1);

        $ap2 = new UserAppointmentProfile();
        $ap2->setUser($this->getReference('user-3'));
        $ap2->setAddress('address 2');
        $ap2->setContact('contact 2');
        $ap2->setEmail('2@sandbox3.cn');
        $ap2->setName('name 2');
        $ap2->setPhone('1000000002');
        $this->addReference('user-appointment-profile-2', $ap2);

        $ap3 = new UserAppointmentProfile();
        $ap3->setUser($this->getReference('user-3'));
        $ap3->setAddress('address 3');
        $ap3->setContact('contact 3');
        $ap3->setEmail('3@sandbox3.cn');
        $ap3->setName('name 3');
        $ap3->setPhone('1000000003');
        $this->addReference('user-appointment-profile-3', $ap3);

        $manager->persist($ap2);
        $manager->persist($ap3);

        $manager->persist($ap1);

        $manager->flush();
    }

    public function getOrder()
    {
        return 20;
    }
}
