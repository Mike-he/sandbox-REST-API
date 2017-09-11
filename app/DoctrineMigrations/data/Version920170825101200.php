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
class Version920170825101200 extends AbstractMigration implements ContainerAwareInterface
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

        $productOrderColumns =
            array(
                array(
                    'column' => 'order_number',
                    'name' => '订单',
                    'default' => true,
                    'required' => true,
                ),
                array(
                    'column' => 'base_price',
                    'name' => '单价',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'rent_period',
                    'name' => '租赁时间段',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'price',
                    'name' => '订单/账单原价',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'discount_price',
                    'name' => '实付款',
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
                    'column' => 'payment_user_id',
                    'name' => '客户',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'creation_date',
                    'name' => '下单时间',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'payment_date',
                    'name' => '付款时间',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'invoice',
                    'name' => '是否包含发票',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'invoiced',
                    'name' => '开票状态',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'type',
                    'name' => '下单方式',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'room_type',
                    'name' => '空间类型',
                    'default' => true,
                    'required' => true,
                ),
                array(
                    'column' => 'pay_channel',
                    'name' => '付款渠道',
                    'default' => true,
                    'required' => false,
                ),
            );

        $eventOrderColumns =
            array(
                array(
                    'column' => 'order_number',
                    'name' => '订单',
                    'default' => true,
                    'required' => true,
                ),
                array(
                    'column' => 'address',
                    'name' => '活动地点',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'event_start_date',
                    'name' => '活动时间',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'price',
                    'name' => '实付款',
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
                    'column' => 'user_id',
                    'name' => '购买者',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'creation_date',
                    'name' => '下单时间',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'publish_company',
                    'name' => '发起公司',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'name',
                    'name' => '活动名',
                    'default' => true,
                    'required' => true,
                ),
                array(
                    'column' => 'pay_channel',
                    'name' => '支付渠道',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'description',
                    'name' => '活动描述',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'payment_date',
                    'name' => '付款时间',
                    'default' => true,
                    'required' => false,
                ),
            );

        $membershipOrderColumns =
            array(
                array(
                    'column' => 'order_number',
                    'name' => '订单',
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
                    'column' => 'valid_period',
                    'name' => '有效期',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'discount_price',
                    'name' => '实付款',
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
                    'column' => 'user_id',
                    'name' => '购买者',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'creation_date',
                    'name' => '下单时间',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'pay_channel',
                    'name' => '支付渠道',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'name',
                    'name' => '会员卡名',
                    'default' => true,
                    'required' => true,
                ),
                array(
                    'column' => 'specification',
                    'name' => '会员卡规格',
                    'default' => false,
                    'required' => false,
                ),
            );

        $customerInvioceColumns =
            array(
                array(
                    'column' => 'ID',
                    'name' => 'ID',
                    'default' => true,
                    'required' => true,
                ),
                array(
                    'column' => 'order_number',
                    'name' => '订单号',
                    'default' => true,
                    'required' => true,
                ),
                array(
                    'column' => 'invoice_number',
                    'name' => '发票号',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'amount',
                    'name' => '金额',
                    'default' => true,
                    'required' => true,
                ),
                array(
                    'column' => 'company_name',
                    'name' => '公司抬头',
                    'default' => true,
                    'required' => true,
                ),
                array(
                    'column' => 'category',
                    'name' => '科目',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'invoice_type',
                    'name' => '发票类型',
                    'default' => true,
                    'required' => true,
                ),
                array(
                    'column' => 'application_time',
                    'name' => '申请开票时间',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'tax_registration_number',
                    'name' => '纳税人识别号/身份证号',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'consignee_name',
                    'name' => '收件人',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'consignee_address',
                    'name' => '收件地址',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'customer_name',
                    'name' => '客户名',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'customer_phone',
                    'name' => '客户手机号',
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

        foreach ($productOrderColumns as $productOrderColumn) {
            $list = new GenericList();
            $list->setColumn($productOrderColumn['column']);
            $list->setName($productOrderColumn['name']);
            $list->setDefault($productOrderColumn['default']);
            $list->setRequired($productOrderColumn['required']);
            $list->setObject(GenericList::OBJECT_PRODUCT_ORDER);
            $list->setPlatform(GenericList::OBJECT_PLATFORM_SALES);

            $em->persist($list);
        }

        foreach ($eventOrderColumns as $eventOrderColumn) {
            $list = new GenericList();
            $list->setColumn($eventOrderColumn['column']);
            $list->setName($eventOrderColumn['name']);
            $list->setDefault($eventOrderColumn['default']);
            $list->setRequired($eventOrderColumn['required']);
            $list->setObject(GenericList::OBJECT_EVENT_ORDER);
            $list->setPlatform(GenericList::OBJECT_PLATFORM_SALES);

            $em->persist($list);
        }

        foreach ($membershipOrderColumns as $membershipOrderColumn) {
            $list = new GenericList();
            $list->setColumn($membershipOrderColumn['column']);
            $list->setName($membershipOrderColumn['name']);
            $list->setDefault($membershipOrderColumn['default']);
            $list->setRequired($membershipOrderColumn['required']);
            $list->setObject(GenericList::OBJECT_MEMBERSHIP_ORDER);
            $list->setPlatform(GenericList::OBJECT_PLATFORM_SALES);

            $em->persist($list);
        }

        foreach ($customerInvioceColumns as $customerInvioceColumn) {
            $list = new GenericList();
            $list->setColumn($customerInvioceColumn['column']);
            $list->setName($customerInvioceColumn['name']);
            $list->setDefault($customerInvioceColumn['default']);
            $list->setRequired($customerInvioceColumn['required']);
            $list->setObject(GenericList::OBJECT_CUSTOMER_INVOICE);
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
