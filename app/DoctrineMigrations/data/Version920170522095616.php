<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Sandbox\ApiBundle\Entity\App\AppVersionCheck;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170522095616 extends AbstractMigration implements ContainerAwareInterface
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

        $appVersionCheck = new AppVersionCheck();
        $appVersionCheck->setCurrentVersion('2.7.1');
        $appVersionCheck->setZhNotification('检测到新版本{{version}}。更新创合秒租，为您带来更好的体验。');
        $appVersionCheck->setEnNotification('Find a new V{{version}} ! Update it to enjoy better experience !');
        $appVersionCheck->setZhForceNotification('检测到最新版本{{version}}。您当前的版本过低，可能导致部分功能无法使用，请立即更新。');
        $appVersionCheck->setEnForceNotification('Find a new V{{version}} ! Your version is too old, which could affect some functions. Please update now!');
        $appVersionCheck->setIosUrl('itms-apps://itunes.apple.com/app/id1015843788');
        $appVersionCheck->setAndroidUrl('http://download.sandbox3.cn/Sandbox3.apk');
        $appVersionCheck->setIsForce(true);
        $appVersionCheck->setVisible(true);

        $em->persist($appVersionCheck);
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
