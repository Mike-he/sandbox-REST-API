<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Sandbox\ApiBundle\Entity\GenericList\GenericList;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Validator\Constraints\True;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170717165529 extends AbstractMigration implements ContainerAwareInterface
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

        $leaseClueColumns =
            array(
                array(
                    'column' => 'serial_number',
                    'name' => '线索号',
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
                    'column' => 'room_type_tag',
                    'name' => '空间二级类型',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'lessee_name',
                    'name' => '承租方名称',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'lessee_address',
                    'name' => '承租方地址',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'lessee_customer',
                    'name' => '联系人',
                    'default' => true,
                    'required' => true,
                ),
                array(
                    'column' => 'lessee_email',
                    'name' => '邮箱',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'lessee_phone',
                    'name' => '电话',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'start_date',
                    'name' => '起租日',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'monthly_rent',
                    'name' => '租赁周期',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'number',
                    'name' => '使用人数',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'creation_date',
                    'name' => '创建时间',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'status',
                    'name' => '状态',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'total_rent',
                    'name' => '总租金',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'appointment_user',
                    'name' => '预约人',
                    'default' => false,
                    'required' => false,
                ),
            );

        $leaseOfferColumns =
            array(
                array(
                    'column' => 'serial_number',
                    'name' => '报价号',
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
                    'column' => 'room_type_tag',
                    'name' => '空间二级类型',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'lessee_type',
                    'name' => '承租方类型',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'lessee_enterprise',
                    'name' => '承租企业',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'lessee_customer',
                    'name' => '联系人',
                    'default' => true,
                    'required' => true,
                ),
                array(
                    'column' => 'start_date',
                    'name' => '租用时间段',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'monthly_rent',
                    'name' => '月租金',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'deposit',
                    'name' => '租赁押金',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'lease_rent_types',
                    'name' => '税金包含',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'creation_date',
                    'name' => '创建时间',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'status',
                    'name' => '状态',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'total_rent',
                    'name' => '总租金',
                    'default' => false,
                    'required' => false,
                ),
            );

        $leaseColumns = array(
            array(
                'column' => 'serial_number',
                'name' => '合同号',
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
                'column' => 'room_type_tag',
                'name' => '空间二级类型',
                'default' => true,
                'required' => false,
            ),
            array(
                'column' => 'lessee_type',
                'name' => '承租方类型',
                'default' => false,
                'required' => false,
            ),
            array(
                'column' => 'lessee_enterprise',
                'name' => '承租企业',
                'default' => false,
                'required' => false,
            ),
            array(
                'column' => 'lessee_customer',
                'name' => '联系人',
                'default' => true,
                'required' => true,
            ),
            array(
                'column' => 'start_date',
                'name' => '租用时间段',
                'default' => true,
                'required' => false,
            ),
            array(
                'column' => 'monthly_rent',
                'name' => '月租金',
                'default' => true,
                'required' => false,
            ),
            array(
                'column' => 'deposit',
                'name' => '租赁押金',
                'default' => true,
                'required' => false,
            ),
            array(
                'column' => 'lease_rent_types',
                'name' => '税金包含',
                'default' => true,
                'required' => false,
            ),
            array(
                'column' => 'creation_date',
                'name' => '创建时间',
                'default' => false,
                'required' => false,
            ),
            array(
                'column' => 'status',
                'name' => '状态',
                'default' => true,
                'required' => false,
            ),
            array(
                'column' => 'total_rent',
                'name' => '总租金',
                'default' => false,
                'required' => false,
            ),
            array(
                'column' => 'lease_bill',
                'name' => '合同账单',
                'default' => true,
                'required' => false,
            ),
            array(
                'column' => 'other_bill',
                'name' => '其他账单',
                'default' => true,
                'required' => false,
            ),
        );

        $billColumns = array(
            array(
                'column' => 'serial_number',
                'name' => '账单号',
                'default' => true,
                'required' => true,
            ),
            array(
                'column' => 'lease_serial_number',
                'name' => '合同号',
                'default' => true,
                'required' => true,
            ),
            array(
                'column' => 'drawer',
                'name' => '开票方',
                'default' => false,
                'required' => false,
            ),
            array(
                'column' => 'name',
                'name' => '账单名',
                'default' => true,
                'required' => true,
            ),
            array(
                'column' => 'description',
                'name' => '账单描述',
                'default' => false,
                'required' => false,
            ),
            array(
                'column' => 'amount',
                'name' => '账单金额',
                'default' => true,
                'required' => true,
            ),
            array(
                'column' => 'invoice',
                'name' => '发票',
                'default' => false,
                'required' => false,
            ),
            array(
                'column' => 'start_date',
                'name' => '账单时间段',
                'default' => false,
                'required' => false,
            ),
            array(
                'column' => 'drawee',
                'name' => '付款人',
                'default' => true,
                'required' => false,
            ),
            array(
                'column' => 'order_method',
                'name' => '下单方式',
                'default' => true,
                'required' => false,
            ),
            array(
                'column' => 'pay_channel',
                'name' => '付款渠道',
                'default' => true,
                'required' => false,
            ),
            array(
                'column' => 'send_date',
                'name' => '推送时间',
                'default' => true,
                'required' => false,
            ),
            array(
                'column' => 'status',
                'name' => '状态',
                'default' => true,
                'required' => false,
            ),
            array(
                'column' => 'revised_amount',
                'name' => '应收款',
                'default' => true,
                'required' => false,
            ),
            array(
                'column' => 'remark',
                'name' => '修改备注',
                'default' => false,
                'required' => false,
            ),
        );

        foreach ($leaseClueColumns as $leaseClueColumn) {
            $list = new GenericList();
            $list->setColumn($leaseClueColumn['column']);
            $list->setName($leaseClueColumn['name']);
            $list->setDefault($leaseClueColumn['default']);
            $list->setRequired($leaseClueColumn['required']);
            $list->setObject(GenericList::OBJECT_LEASE_CLUE);
            $list->setPlatform(GenericList::OBJECT_PLATFORM_SALES);

            $em->persist($list);
        }

        foreach ($leaseOfferColumns as $leaseOfferColumn) {
            $list = new GenericList();
            $list->setColumn($leaseOfferColumn['column']);
            $list->setName($leaseOfferColumn['name']);
            $list->setDefault($leaseOfferColumn['default']);
            $list->setRequired($leaseOfferColumn['required']);
            $list->setObject(GenericList::OBJECT_LEASE_OFFER);
            $list->setPlatform(GenericList::OBJECT_PLATFORM_SALES);

            $em->persist($list);
        }

        foreach ($leaseColumns as $leaseColumn) {
            $list = new GenericList();
            $list->setColumn($leaseColumn['column']);
            $list->setName($leaseColumn['name']);
            $list->setDefault($leaseColumn['default']);
            $list->setRequired($leaseColumn['required']);
            $list->setObject(GenericList::OBJECT_LEASE);
            $list->setPlatform(GenericList::OBJECT_PLATFORM_SALES);

            $em->persist($list);
        }

        foreach ($billColumns as $billColumn) {
            $list = new GenericList();
            $list->setColumn($billColumn['column']);
            $list->setName($billColumn['name']);
            $list->setDefault($billColumn['default']);
            $list->setRequired($billColumn['required']);
            $list->setObject(GenericList::OBJECT_LEASE_BILL);
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
