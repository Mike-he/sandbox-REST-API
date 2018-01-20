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
class Version920180120074949 extends AbstractMigration implements ContainerAwareInterface
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
                    'column' => 'company_logo_and_name',
                    'name' => '公司logo+名称',
                    'default' => true,
                    'required' => true,
                ),
                array(
                    'column' => 'company_name',
                    'name' => '公司电话',
                    'default' => true,
                    'required' => true,
                ),
                array(
                    'column' => 'company_fax',
                    'name' => '公司传真',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'company_building',
                    'name' => '所属大楼',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'company_address',
                    'name' => '公司地址',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'company_email',
                    'name' => '公司邮箱',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'company_website',
                    'name' => '公司网站',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'company_description',
                    'name' => '公司描述',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'company_industry',
                    'name' => '所属行业',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'company_portfolio',
                    'name' => '公司作品',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'company_members',
                    'name' => '公司成员',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'creator_name',
                    'name' => '创建者名字',
                    'default' => true,
                    'required' => false,
                ),
                array(
                    'column' => 'creator_phone',
                    'name' => '创建者手机号',
                    'default' => false,
                    'required' => false,
                ),
                array(
                    'column' => 'creation_date',
                    'name' => '创建时间',
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
            $list->setObject(GenericList::OBJECT_COMMNUE_COMPANY);
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
