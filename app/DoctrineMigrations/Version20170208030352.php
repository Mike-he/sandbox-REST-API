<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Sandbox\ApiBundle\Entity\Finance\FinanceOfficialInvoiceProfile;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170208030352 extends AbstractMigration implements ContainerAwareInterface
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
        $em = $this->container->get('doctrine.orm.entity_manager');

        $profile = new FinanceOfficialInvoiceProfile();
        $profile->setTitle('上海展想创合企业管理有限公司');
        $profile->setType('增值税专用发票');
        $profile->setCategory('服务费');
        $profile->setTaxpayerId('310115342032571');
        $profile->setBankName('上海浦东发展银行金桥支行');
        $profile->setBankAccount('98840154740011672');
        $profile->setCompanyInfo('中国（上海）自由贸易试验区祖冲之路 2288弄2号301室 61097555');
        $profile->setReceiver('财务部');
        $profile->setAddress('上海市浦东新区祖冲之路2288弄201室');
        $profile->setPhone('18612345678');
        $profile->setPostalCode('200000');

        $em->persist($profile);

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
