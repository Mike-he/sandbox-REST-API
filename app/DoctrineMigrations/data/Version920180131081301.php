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
class Version920180131081301 extends AbstractMigration implements ContainerAwareInterface
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
                    'column' => 'activity_name',
                    'name' => '活动名称',
                    'default' => true,
                    'required' => true,
                ),
                array(
                    'column' => 'conduct_company_name',
                    'name' => '活动组织方',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'registration_data',
                    'name' => '报名时间',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'activity_date',
                    'name' => '活动时间',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'activity_address',
                    'name' => '活动地点',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'price',
                    'name' => '收费金额',
                    'default' => true,
                    'required' => true,
                    'sort' => true,
                ),
                array(
                    'column' => 'is_verify',
                    'name' => '是否审核',
                    'default' => true,
                    'required' => true,
                ),
                array(
                    'column' => 'limit_number',
                    'name' => '人数上限',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'comments_count',
                    'name' => '评论数',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'registrations_number',
                    'name' => '报名人数',
                    'default' => true,
                    'required' => false,
                    'sort' => true,
                ),
                array(
                    'column' => 'description',
                    'name' => '活动描述',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'registration_type',
                    'name' => '报名方式',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'activity_status',
                    'name' => '活动状态',
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
            $list->setObject(GenericList::OBJECT_COMMNUE_ACTIVITY);
            $list->setPlatform(GenericList::OBJECT_PLATFORM_COMMNUE);

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
