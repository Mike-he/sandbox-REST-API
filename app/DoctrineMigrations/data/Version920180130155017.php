<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Sandbox\ApiBundle\Entity\GenericList\GenericList;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920180130155017 extends AbstractMigration implements ContainerAwareInterface
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
        parent::postUp($schema);

        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        $columns =
            array(
                array(
                    'column' => 'space_name',
                    'name' => '空间名称',
                    'default' => true,
                    'required' => true,
                ),
                array(
                    'column' => 'sapce_type',
                    'name' => '空间类型',
                    'default' => true,
                    'required' => true,
                ),
                array(
                    'column' => 'sales_company',
                    'name' => '所属销售方',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'building',
                    'name' => '所属社区',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'area',
                    'name' => '面积',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'allowed_people',
                    'name' => '容纳人数',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'start_date',
                    'name' => '上架时间',
                    'default' => false,
                    'required' => false,
                    'sort' => true,
                ),
                array(
                    'column' => 'price',
                    'name' => '租赁价格',
                    'default' => true,
                    'required' => true,
                    'sort' => true,
                ),
                array(
                    'column' => 'favorite',
                    'name' => '收藏数',
                    'default' => false,
                    'required' => false,
                    'sort' => true,
                ),
                array(
                    'column' => 'url',
                    'name' => '商品链接',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'status',
                    'name' => '状态',
                    'default' => true,
                    'required' => true,
                ),
            );

        foreach ($columns as $column) {
            $sort = isset($column['sort']) ? $column['sort'] : false;

            $list = new GenericList();
            $list->setColumn($column['column']);
            $list->setName($column['name']);
            $list->setDefault($column['default']);
            $list->setRequired($column['required']);
            $list->setSort($sort);
            $list->setObject(GenericList::OBJECT_SPACE);
            $list->setPlatform(GenericList::OBJECT_PLATFORM_OFFICIAL);

            $em->persist($list);
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
