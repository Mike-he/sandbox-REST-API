<?php

namespace Sandbox\ApiBundle\DataFixtures\ORM\User;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sandbox\ApiBundle\Entity\Room\RoomBuilding;
use Sandbox\ApiBundle\Entity\Room\RoomBuildingAttachment;
use Sandbox\ApiBundle\Entity\Room\RoomBuildingServices;
use Sandbox\ApiBundle\Entity\Room\RoomBuildingTag;
use Sandbox\ApiBundle\Entity\Room\RoomCity;
use Sandbox\ApiBundle\Entity\Room\RoomTypes;
use Sandbox\ApiBundle\Entity\SalesAdmin\SalesCompany;
use Sandbox\ApiBundle\Entity\User\User;
use Sandbox\ApiBundle\Entity\User\UserClient;
use Sandbox\ApiBundle\Entity\User\UserProfile;
use Sandbox\ApiBundle\Entity\User\UserToken;

class LoadUserData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $now = new \DateTime('now');

        $user1 = new User();
        $user1->setXmppUsername('1000001');
        $user1->setPassword('202CB962AC59075B964B07152D234B70');
        $user1->setEmail('mike.he@sandbox3.cn');
        $user1->setPhone('18621316860');
        $user1->setPhoneCode('+86');
        $user1->setBanned(0);
        $user1->setCreationDate($now);
        $user1->setModificationDate($now);
        $user1->setAuthorized(1);
        $user1->setCardNo('8888888888');
        $user1->setCredentialNo('888666198608068865');
        $user1->setAuthorizedPlatform(User::AUTHORIZED_PLATFORM_OFFICIAL);
        $user1->setAuthorizedAdminUsername(10);
        $user1->setCustomerId(null);
        $this->addReference('user-mike', $user1);

        $userProfile1 = new UserProfile();
        $userProfile1->setUser($user1);
        $userProfile1->setName('Mike');
        $userProfile1->setCreationDate($now);
        $userProfile1->setModificationDate($now);
        $this->addReference('user-profile-mike', $userProfile1);

        $client1 = new UserClient();
        $client1->setCreationDate($now);
        $client1->setModificationDate($now);
        $this->addReference('client-mike', $client1);

        $token1 = new UserToken();
        $token1->setUser($user1);
        $token1->setClient($client1);
        $token1->setToken('366d7a03ee14d6f806a3454cb62eaf18');
        $token1->setRefreshToken('6ad11d128d6bc07e963117ded423fc23');
        $token1->setOnline(1);
        $token1->setCreationDate($now);
        $token1->setModificationDate($now);
        $this->addReference('user-token-mike', $token1);

        $manager->persist($user1);
        $manager->persist($userProfile1);
        $manager->persist($client1);
        $manager->persist($token1);

        $manager->flush();
    }

    public function getOrder()
    {
        return 2;
    }
}
