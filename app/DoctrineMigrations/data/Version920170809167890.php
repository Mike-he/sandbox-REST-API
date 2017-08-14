<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Sandbox\ApiBundle\Entity\GenericList\GenericList;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170809167890 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }

    public function postUp(Schema $schema)
    {
        parent::postUp($schema);

        $em = $this->container->get('doctrine.orm.entity_manager');

        $cashierColumns =
            array(
                array(
                    'column' => 'serial_number',
                    'name' => '预约号',
                    'default' => true,
                    'required' => true,
                ),
                array(
                    'column' => 'room_name',
                    'name' => '空间名',
                    'default' => true,
                    'required' => true,
                ),
                array(
                    'column' => 'building_name',
                    'name' => '所属社区',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'contect_name',
                    'name' => '联系人',
                    'default' => false,
                    'required' => true,
                ),
                array(
                    'column' => 'phone',
                    'name' => '电话',
                    'default' => true,
                    'required' => true,
                ),
                array(
                    'column' => 'view_time',
                    'name' => '看房时间',
                    'default' => true,
                    'required' => true,
                ),
                array(
                    'column' => 'comment',
                    'name' => '客户备注',
                    'default' => true,
                    'required' => true,
                ),
                array(
                    'column' => 'status',
                    'name' => '状态',
                    'default' => true,
                    'required' => true,
                ),
                array(
                    'column' => 'price',
                    'name' => '单价',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'room_type',
                    'name' => '空间类型',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'admin_name',
                    'name' => '抢单人',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'user_name',
                    'name' => '预约人',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'creation_date',
                    'name' => '提交预约时间',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'grab_date',
                    'name' => '抢单时间',
                    'default' => true,
                    'required' => false,
                )
            );

        foreach ($cashierColumns as $cashierColumn) {
            $list = new GenericList();
            $list->setColumn($cashierColumn['column']);
            $list->setName($cashierColumn['name']);
            $list->setDefault($cashierColumn['default']);
            $list->setRequired($cashierColumn['required']);
            $list->setObject(GenericList::OBJECT_RESERVATION);
            $list->setPlatform(GenericList::OBJECT_PLATFORM_SALES);

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
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }
}
