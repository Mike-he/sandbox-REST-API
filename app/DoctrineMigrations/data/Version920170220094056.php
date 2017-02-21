<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170220094056 extends AbstractMigration implements ContainerAwareInterface
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
            '北京市' => [39.9, 116.5],
            '上海市' => [31.24, 121.48],
            '广州市' => [23.13, 113.24],
            '深圳市' => [22.55, 114.09],
            '厦门市' => [24.45, 118.08],
            '杭州市' => [30.25, 120.16],
            '重庆市' => [29.54, 106.52],
            '青岛市' => [36.08, 120.32],
            '西安市' => [34.27, 108.88],
            '波士顿' => [42.37, -71.12],
            '旧金山' => [37.75, -122.44],
            '天津市' => [39.15, 117.2],
            '大连市' => [38.92, 121.57],
            '哈尔滨市' => [45.73, 126.61],
            '合肥市' => [31.86, 117.27],
            '南京市' => [32.05, 118.77],
            '苏州市' => [31.31, 120.61],
            '无锡市' => [31.58, 120.29],
            '宁波市' => [29.87, 121.53],
            '嘉兴市' => [120.762075, 30.752272],
            '成都市' => [30.67, 104.07],
            '长沙市' => [28.2, 112.97],
            '武汉市' => [30.57, 114.28]
        ];

        foreach ($cityNames as $name) {
            $city = $em->getRepository('SandboxApiBundle:Room\RoomCity')
                ->findOneBy(
                    array(
                        'name' => $name
                    )
                );

            if ($city) {
                $city->setLat($coordinates[$name][0]);
                $city->setLng($coordinates[$name][1]);
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
