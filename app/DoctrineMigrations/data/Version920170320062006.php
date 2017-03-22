<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Sandbox\ApiBundle\Entity\Menu\Menu;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170320062006 extends AbstractMigration implements ContainerAwareInterface
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
        $em = $this->container->get('doctrine.orm.entity_manager');

        $cityNames = [
            '北京市',
            '上海市',
            '广州市',
            '深圳市',
            '厦门市',
            '杭州市',
            '重庆市',
            '青岛市',
            '西安市',
            '波士顿',
            '旧金山',
            '天津市',
            '大连市',
            '哈尔滨市',
            '合肥市',
            '南京市',
            '苏州市',
            '无锡市',
            '宁波市',
            '嘉兴市',
            '成都市',
            '长沙市',
            '武汉市'
        ];

        $coordinates = [
            '北京市' => ['010'],
            '上海市' => ['021'],
            '广州市' => ['020'],
            '深圳市' => ['0755'],
            '厦门市' => ['0592'],
            '杭州市' => ['0571'],
            '重庆市' => ['023'],
            '青岛市' => ['0532'],
            '西安市' => ['029'],
            '波士顿' => ['617'],
            '旧金山' => ['415'],
            '天津市' => ['022'],
            '大连市' => ['0411'],
            '哈尔滨市' => ['0451'],
            '合肥市' => ['0551'],
            '南京市' => ['025'],
            '苏州市' => ['0512'],
            '无锡市' => ['0510'],
            '宁波市' => ['0574'],
            '嘉兴市' => ['0573'],
            '成都市' => ['028'],
            '长沙市' => ['0731'],
            '武汉市' => ['027']
        ];

        foreach ($cityNames as $name) {
            $city = $em->getRepository('SandboxApiBundle:Room\RoomCity')
                ->findOneBy(
                    array(
                        'name' => $name
                    )
                );

            if ($city) {
                $city->setCode($coordinates[$name][0]);
            }
        }

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