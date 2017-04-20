<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Sandbox\ApiBundle\Entity\Parameter\Parameter;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version920170418001618 extends AbstractMigration implements ContainerAwareInterface
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

        $em = $this->container->get('doctrine.orm.entity_manager');

        $data = array(
            Parameter::KEY_BEAN_USER_REGISTER => '+500',
            Parameter::KEY_BEAN_USER_LOGIN => '+10',
            Parameter::KEY_BEAN_USER_SHARE => '+50',
            Parameter::KEY_BEAN_ORDER_EVALUATION => '*1',
            Parameter::KEY_BEAN_BUILDING_EVALUATION => '+50',
            Parameter::KEY_BEAN_SUCCESS_INVITATION => '+200',
            Parameter::KEY_BEAN_INVITEE_PRODUCT_ORDER => '*0.1',
            Parameter::KEY_BEAN_INVITEE_PAY_BILL => '*0.1',
            Parameter::KEY_BEAN_PRODUCT_ORDER => '*3',
            Parameter::KEY_BEAN_PAY_BILL => '*1',
            Parameter::KEY_BEAN_SHOP_ORDER => '*1',
            Parameter::KEY_BEAN_EVENT_ORDER => '*3',
            Parameter::KEY_BEAN_MEMBERSHIP_ORDER => '*3',
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
