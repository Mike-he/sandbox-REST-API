<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170225031606 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP VIEW trade_invoice_view');
        $this->addSql("
            CREATE VIEW trade_invoice_view AS
            SELECT
                `o`.`orderNumber` AS `number`,
                `o`.`userId` AS `user_id`,
                `o`.`salesInvoice` AS `sales_invoice`,
                `o`.`creationDate` AS `creation_date`
            FROM `product_order` AS `o`
            WHERE
                `o`.`status` = 'completed'
                AND `o`.`discountPrice` > 0
                AND `o`.`payChannel` != 'account'
                AND `o`.`rejected` = false
                AND `o`.`invoiced` = false
            UNION ALL
            SELECT
                `l`.`serial_number` AS `number`,
                `l`.`drawee` AS `user_id`,
                `l`.`sales_invoice` AS `sales_invoice`,
                `l`.`creation_date`
            FROM `lease_bill` AS `l`
            WHERE
                `l`.`status` = 'paid'
                AND `l`.`invoiced` = false;
        ");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
