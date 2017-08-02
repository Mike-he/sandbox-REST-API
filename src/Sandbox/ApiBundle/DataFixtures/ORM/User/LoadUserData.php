<?php

namespace Sandbox\ApiBundle\DataFixtures\ORM\User;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sandbox\ApiBundle\Entity\Admin\AdminPlatform;
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
        $user1->setPhone('18621316861');
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

        $platform1 = new AdminPlatform();
        $platform1->setUser($user1);
        $platform1->setClient($client1);
        $platform1->setPlatform('official');
        $platform1->setSalesCompany(null);
        $platform1->setCreationDate($now);

        $user2 = new User();
        $user2->setXmppUsername('1000002');
        $user2->setPassword('202CB962AC59075B964B07152D234B70');
        $user2->setEmail('user2@sandbox3.cn');
        $user2->setPhone('18611111111');
        $user2->setPhoneCode('+86');
        $user2->setBanned(0);
        $user2->setCreationDate($now);
        $user2->setModificationDate($now);
        $user2->setAuthorized(1);
        $user2->setCardNo('8888888888');
        $user2->setCredentialNo('888666198608068865');
        $user2->setAuthorizedPlatform(User::AUTHORIZED_PLATFORM_OFFICIAL);
        $user2->setAuthorizedAdminUsername(10);
        $user2->setCustomerId(null);
        $this->addReference('user-2', $user2);

        $userProfile2 = new UserProfile();
        $userProfile2->setUser($user2);
        $userProfile2->setName('User2');
        $userProfile2->setCreationDate($now);
        $userProfile2->setModificationDate($now);
        $this->addReference('user-profile-2', $userProfile2);

        $client2 = new UserClient();
        $client2->setCreationDate($now);
        $client2->setModificationDate($now);
        $this->addReference('client-2', $client2);

        $token2 = new UserToken();
        $token2->setUser($user2);
        $token2->setClient($client2);
        $token2->setToken('33aff75e7823cd31896ecba936b9f0a2');
        $token2->setRefreshToken('42bfda04ff9178fc0fc4b22579952766');
        $token2->setOnline(1);
        $token2->setCreationDate($now);
        $token2->setModificationDate($now);
        $this->addReference('user-token-2', $token2);

        $platform2 = new AdminPlatform();
        $platform2->setUser($user2);
        $platform2->setClient($client2);
        $platform2->setPlatform('sales');
        $platform2->setSalesCompany($this->getReference('sales-company-sandbox'));
        $platform2->setCreationDate($now);

        $user3 = new User();
        $user3->setXmppUsername('1000003');
        $user3->setPassword('202CB962AC59075B964B07152D234B70');
        $user3->setEmail('user3@sandbox3.cn');
        $user3->setPhone('18622222222');
        $user3->setPhoneCode('+86');
        $user3->setBanned(0);
        $user3->setCreationDate($now);
        $user3->setModificationDate($now);
        $user3->setAuthorized(1);
        $user3->setCardNo('8888888888');
        $user3->setCredentialNo('888666198608068865');
        $user3->setAuthorizedPlatform(User::AUTHORIZED_PLATFORM_OFFICIAL);
        $user3->setAuthorizedAdminUsername(10);
        $user3->setCustomerId(null);
        $this->addReference('user-3', $user3);

        $userProfile3 = new UserProfile();
        $userProfile3->setUser($user3);
        $userProfile3->setName('User3');
        $userProfile3->setCreationDate($now);
        $userProfile3->setModificationDate($now);
        $this->addReference('user-profile-3', $userProfile3);

        $client3 = new UserClient();
        $client3->setCreationDate($now);
        $client3->setModificationDate($now);
        $this->addReference('client-3', $client3);

        $token3 = new UserToken();
        $token3->setUser($user3);
        $token3->setClient($client3);
        $token3->setToken('4d4c8002863894ebb0f94946639cf4ec');
        $token3->setRefreshToken('f78fa4984ac501160d0d13494f4c939c');
        $token3->setOnline(1);
        $token3->setCreationDate($now);
        $token3->setModificationDate($now);
        $this->addReference('user-token-3', $token3);

        $platform3 = new AdminPlatform();
        $platform3->setUser($user3);
        $platform3->setClient($client3);
        $platform3->setPlatform('shop');
        $platform3->setSalesCompany($this->getReference('sales-company-sandbox'));
        $platform3->setCreationDate($now);

        $user4 = new User();
        $user4->setXmppUsername('1000004');
        $user4->setPassword('202CB962AC59075B964B07152D234B70');
        $user4->setEmail('sales-user-without-position@sandbox3.cn');
        $user4->setPhone('18644444444');
        $user4->setPhoneCode('+86');
        $user4->setBanned(0);
        $user4->setCreationDate($now);
        $user4->setModificationDate($now);
        $user4->setAuthorized(1);
        $user4->setCardNo('8888888889');
        $user4->setCredentialNo('444666198608068865');
        $user4->setAuthorizedPlatform(User::AUTHORIZED_PLATFORM_SALES);
        $user4->setAuthorizedAdminUsername(10);
        $user4->setCustomerId(null);
        $this->addReference('sales-user-without-position', $user4);

        $userProfile4 = new UserProfile();
        $userProfile4->setUser($user4);
        $userProfile4->setName('sales-user-without-position');
        $userProfile4->setCreationDate($now);
        $userProfile4->setModificationDate($now);
        $this->addReference('sales-user-without-position-profile', $userProfile4);

        $client4 = new UserClient();
        $client4->setCreationDate($now);
        $client4->setModificationDate($now);
        $this->addReference('client-sales-user-without-position', $client4);

        $token4 = new UserToken();
        $token4->setUser($user4);
        $token4->setClient($client4);
        $token4->setToken('894ebb0f94d4c80028634946639cf4ec');
        $token4->setRefreshToken('4ac501160df78fa4980d13494f4c939c');
        $token4->setOnline(1);
        $token4->setCreationDate($now);
        $token4->setModificationDate($now);
        $this->addReference('sales-user-without-position-token', $token4);

        $platform4 = new AdminPlatform();
        $platform4->setUser($user4);
        $platform4->setClient($client4);
        $platform4->setPlatform('sales');
        $platform4->setSalesCompany($this->getReference('sales-company-sandbox'));
        $platform4->setCreationDate($now);

        $manager->persist($user1);
        $manager->persist($userProfile1);
        $manager->persist($client1);
        $manager->persist($token1);
        $manager->persist($platform1);
        $manager->persist($user2);
        $manager->persist($userProfile2);
        $manager->persist($client2);
        $manager->persist($token2);
        $manager->persist($platform2);
        $manager->persist($user3);
        $manager->persist($userProfile3);
        $manager->persist($client3);
        $manager->persist($token3);
        $manager->persist($platform3);
        $manager->persist($user4);
        $manager->persist($userProfile4);
        $manager->persist($client4);
        $manager->persist($token4);
        $manager->persist($platform4);

        $manager->flush();
    }

    public function getOrder()
    {
        return 3;
    }
}
