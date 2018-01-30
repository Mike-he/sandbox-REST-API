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
class Version920180130084430 extends AbstractMigration implements ContainerAwareInterface
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
                    'column' => 'name',
                    'name' => '销售方名称',
                    'default' => true,
                    'required' => true,
                ),
                array(
                    'column' => 'building_counts',
                    'name' => '拥有社区数',
                    'default' => true,
                    'required' => true,
                ),
                array(
                    'column' => 'shop_counts',
                    'name' => '拥有店铺数',
                    'default' => true,
                    'required' => true,
                ),
                array(
                    'column' => 'banned',
                    'name' => '状态',
                    'default' => true,
                    'required' => true,
                ),
                array(
                    'column' => 'phone',
                    'name' => '公司/组织电话',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'address',
                    'name' => '公司/组织地址',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'contacter',
                    'name' => '联系人姓名',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'contacter_phone',
                    'name' => '联系人电话',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'contacter_email',
                    'name' => '联系人邮箱',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'admins',
                    'name' => '社区超级管理员',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'coffee_admins',
                    'name' => '店铺超级管理员',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'product_counts',
                    'name' => '上架空间',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'service_counts',
                    'name' => '已开放模块数',
                    'default' => false,
                    'required' => false,
                ),
            );

        foreach ($columns as $column) {
            $list = new GenericList();
            $list->setColumn($column['column']);
            $list->setName($column['name']);
            $list->setDefault($column['default']);
            $list->setRequired($column['required']);
            $list->setObject(GenericList::OBJECT_SALES_COMPANY);
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
