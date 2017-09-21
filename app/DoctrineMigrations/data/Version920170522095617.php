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
class Version920170522095617 extends AbstractMigration implements ContainerAwareInterface
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
        $appVersionCheck->setZhNotification("<p><strong><span style='font-size:14px;font-family:思源黑体,Helvetica Neue'>检测到新版本V{{version}}</span></strong></p><p><span style='font-size:14px;font-family:思源黑体,Helvetica Neue;color:#717171'>更新创合秒租，为您带来更好的体验。</span></p>");
        $appVersionCheck->setEnNotification("<p><strong><span style='font-size:14px;font-family:思源黑体,Helvetica Neue'>Find&nbsp;a&nbsp;new&nbsp;V{{version}}&nbsp;!</span></strong></p><p><span style='font-size:14px;font-family:思源黑体,Helvetica Neue;color:#717171'>Update&nbsp;it&nbsp;to&nbsp;enjoy&nbsp;better&nbsp;experience&nbsp;!</span></p>");
        $appVersionCheck->setZhForceNotification("<p><strong><span style='font-size:14px;font-family:思源黑体,Helvetica Neue'>检测到新版本V{{version}}</span></strong></p><p><span style='font-size:14px;font-family:思源黑体,Helvetica Neue;color:#717171'>您当前的版本过低，可能导致部分功能无法使用，请立即更新。</span></p>");
        $appVersionCheck->setEnForceNotification("<p><strong><span style='font-size:14px;font-family:思源黑体,Helvetica Neue'>Find&nbsp;a&nbsp;new&nbsp;V{{version}}&nbsp;!</span></strong></p><p><span style='font-size:14px;font-family:思源黑体,Helvetica Neue;color:#717171'>Your&nbsp;version&nbsp;is&nbsp;too&nbsp;old,&nbsp;which&nbsp;could&nbsp;affect&nbsp;some&nbsp;functions.&nbsp;Please&nbsp;update&nbsp;now!</span></p>");
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
