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
class Version920171228085726 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }

    public function postUp(Schema $schema)
    {
        parent::postUp($schema);

        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        $columns =
            array(
                array(
                    'column' => 'order_number',
                    'name' => '订单号',
                    'default' => true,
                    'required' => true,
                ),
                array(
                    'column' => 'service_location',
                    'name' => '服务区域',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'service_date',
                    'name' => '服务时间',
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
                    'column' => 'customer_id',
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
                    'column' => 'payment_date',
                    'name' => '付款时间',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'company',
                    'name' => '发起公司',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'service_name',
                    'name' => '服务名',
                    'default' => true,
                    'required' => true,
                ),
                array(
                    'column' => 'pay_channel',
                    'name' => '支付渠道',
                    'default' => true,
                    'required' => false,
                ),
            );

        foreach ($columns as $column) {
            $list = new GenericList();
            $list->setColumn($column['column']);
            $list->setName($column['name']);
            $list->setDefault($column['default']);
            $list->setRequired($column['required']);
            $list->setObject(GenericList::OBJECT_SERVICE_ORDER);
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
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');
    }
}
