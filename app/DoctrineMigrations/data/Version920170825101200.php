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

        $cashierColumns =
            array(
                array(
                    'column' => 'orderNumber',
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
                    'column' => 'rentPeriod',
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
                    'column' => 'discountPrice',
                    'name' => '订单原价',
                    'default' => true,
                    'required' => true,
                ),
                array(
                    'column' => 'status',
                    'name' => '状态',
                    'default' => true,
                    'required' => true
                ),
                array(
                    'column' => 'payment_user_id',
                    'name' => '付款人',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'creationDate',
                    'name' => '下单时间',
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
                    'column' => 'description',
                    'name' => '账单描述',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'roomType',
                    'name' => '空间类型',
                    'default' => true,
                    'required' => true,
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
