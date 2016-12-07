<?php

namespace Sandbox\ApiBundle\DataFixtures\ORM\Lease;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sandbox\ApiBundle\Entity\Lease\Lease;

class LoadLeaseData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $lease = new Lease();
        $lease->setContact($this->getReference('user-mike'));
        $lease->setDrawee($this->getReference('user-mike'));

        $manager->persist($lease);

        $manager->flush();
    }

    public function getOrder()
    {
        return 17;
    }
}
