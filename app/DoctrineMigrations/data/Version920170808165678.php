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
class Version920170808165678 extends AbstractMigration implements ContainerAwareInterface
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
                    'name' => '订单/账单号',
                    'default' => true,
                    'required' => true,
                ),
                array(
                    'column' => 'lease_serial_number',
                    'name' => '合同号',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'name',
                    'name' => '账单名',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'base_price',
                    'name' => '单价',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'start_date',
                    'name' => '租赁起始时间',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'end_date',
                    'name' => '租赁结束时间',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'amount',
                    'name' => '订单/账单原价',
                    'default' => true,
                    'required' => true,
                ),
                array(
                    'column' => 'revised_amount',
                    'name' => '应收款',
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
                    'column' => 'drawee',
                    'name' => '付款人',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'send_date',
                    'name' => '推送时间',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'invoice',
                    'name' => '是否包含发票',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'drawer',
                    'name' => '开票方',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'order_method',
                    'name' => '下单方式',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'remark',
                    'name' => '修改备注',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'description',
                    'name' => '账单描述',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'room_name',
                    'name' => '空间名',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'room_type_tag',
                    'name' => '空间二级类型',
                    'default' => false,
                    'required' => false,
                ),
            );

        foreach ($cashierColumns as $cashierColumn) {
            $list = new GenericList();
            $list->setColumn($cashierColumn['column']);
            $list->setName($cashierColumn['name']);
            $list->setDefault($cashierColumn['default']);
            $list->setRequired($cashierColumn['required']);
            $list->setObject(GenericList::OBJECT_CASHIER);
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
