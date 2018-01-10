<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Sandbox\ApiBundle\Entity\Parameter\Parameter;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920171121082242 extends AbstractMigration implements ContainerAwareInterface
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

        $vSales = $this->container->getParameter('property_client_apply_url');
        $aSales = $this->container->getParameter('property_client_customer_url');

        $data = array(
            Parameter::KEY_HTML_WAIT_APPLY =>  $vSales."/waitApply",
            Parameter::KEY_HTML_MY_APPLY =>  $vSales."/myApply",
            Parameter::KEY_HTML_ROOM_PARAM_ID =>  $aSales."/room?id=",
            Parameter::KEY_HTML_APPLY =>  $vSales."/apply",
            Parameter::KEY_HTML_ROOM_ORDER_PARAM_ID =>  $vSales."/roomorder?id=",
            Parameter::KEY_HTML_EVENT_ORDER_PARAM_ID =>  $vSales."/eventorder?id=",
            Parameter::KEY_HTML_MEMBER_ORDER_PARAM_ID =>  $vSales."/memberorder?id=",
            Parameter::KEY_HTML_OFFER_PARAM_ID =>  $vSales."/offer?id=",
            Parameter::KEY_HTML_BILL_PARAM_ID =>  $vSales."/bill?id=",
            Parameter::KEY_HTML_CONTRACT_PARAM_ID =>  $vSales."/contract?id=",
            Parameter::KEY_HTML_CLUE_PARAM_ID =>  $vSales."/clue?id=",
            Parameter::KEY_HTML_CUSTOMER =>  $aSales."/customer",
            Parameter::KEY_HTML_CUSTOMER_CREATE_PERSONAL =>  $aSales."/customer?pageType=creat&tabType=personal",
            Parameter::KEY_HTML_CUSTOMER_CREATE_COMPANY =>  $aSales."/customer?pageType=creat&tabType=company",
            Parameter::KEY_HTML_CLUE_CREATE =>  $aSales."/creatClue",
            Parameter::KEY_HTML_CUSTOMER_PROFILE_DETAIL =>  $aSales."/customer?pageType=personalDetail",
            Parameter::KEY_HTML_CUSTOMER_ENTERPRISE_DETAIL =>  $aSales."/customer?pageType=companyDetail&id=",
        );


        foreach ($data as $key => $value) {
            $parameter = new Parameter();
            $parameter->setKey($key);
            $parameter->setValue($value);

            $em->persist($parameter);
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
