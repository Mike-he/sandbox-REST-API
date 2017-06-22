<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Sandbox\ApiBundle\Entity\User\UserClient;
use Sandbox\ApiBundle\Entity\User\UserToken;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170622021608 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

    }

    /**
     * @param Schema $schema
     */
    public function postUp(Schema $schema)
    {
        parent::postUp($schema);

        $em = $this->container->get('doctrine.orm.entity_manager');

        $user = $em->getRepository('SandboxApiBundle:User\User')
            ->findOneBy(array(
                'xmppUsername' => 'service',
            ));

        if (!is_null($user)) {
            $userClient = new UserClient();
            $userClient->setName('Sandbox Service');
            $userClient->setOs('Sandbox Service');
            $userClient->setVersion('1.0');
            $userClient->setIpAddress('127.0.0.1');
            $userClient->setCreationDate(new \DateTime('now'));
            $userClient->setModificationDate(new \DateTime('now'));
            $em->persist($userClient);

            $userToken = new UserToken();
            $userToken->setUser($user);
            $userToken->setClient($userClient);
            $userToken->setToken(md5(uniqid('service'.rand(), true)));
            $userToken->setRefreshToken(md5(uniqid('service'.rand(), true)));
            $userToken->setOnline(true);
            $userToken->setCreationDate(new \DateTime('now'));
            $userToken->setModificationDate(new \DateTime('now'));
            $em->persist($userToken);

            $em->flush();
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
