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
class Version920170825112900 extends AbstractMigration implements ContainerAwareInterface
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
                    'column' => 'address',
                    'name' => '活动地点',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'eventStartDate',
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
                    'column' => 'userId',
                    'name' => '购买者',
                    'default' => true,
                    'required' => false
                ),
                array(
                    'column' => 'creationDate',
                    'name' => '下单时间',
                    'default' => false,
                    'required' => false
                ),
                array(
                    'column' => 'publishCompany',
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
                    'column' => 'payChannel',
                    'name' => '支付渠道',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'description',
                    'name' => '活动描述',
                    'default' => false,
                    'required' => false,
                )
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
